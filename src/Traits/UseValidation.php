<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Closure;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Callback;
use Rakit\Validation\Validator;
use Rkt\MageData\Data;
use Rkt\MageData\Exceptions\ValidationException;
use Rkt\MageData\Model\DataObjectFactory;

trait UseValidation
{
    public static function getValidationRules(array $input): array
    {
        $instance = DataObjectFactory::create(static::class, $input);

        return self::flattenRules($instance);
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    public function aliases(): array
    {
        return [];
    }

    public function customRules(): array
    {
        return [];
    }

    public function validate(bool $throwException = true): void
    {
        $validator = new Validator;

        // Inject custom validation rules
        $this->addCustomValidationRules($validator);

        // Dispatch event to allow extensions to rules, messages or aliases
        $transport = $this->dispatchValidationPrepareEvent([
            'rules' => $this->rules(),
            'messages' => $this->messages(),
            'aliases' => $this->aliases(),
            'validator' => $validator,
        ]);

        ['rules' => $rules, 'messages' => $messages, 'aliases' => $aliases, 'validator' => $validator] = $transport;
        $validation = $validator->make($this->toArray(), $rules, $messages);
        $validation->setAliases($aliases);
        $validation->validate();

        if ($throwException && $validation->fails()) {
            $this->throwValidationException($validation->errors()->firstOfAll());
        }

        // Validate nested DataObjects
        foreach ($this as $value) {
            if ($value instanceof self) {
                $value->validate();
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($item instanceof self) {
                        $item->validate();
                    }
                }
            }
        }
    }

    private function throwValidationException(array $errors): void
    {
        $exception = new ValidationException();
        $exception->setErrors($errors);

        throw $exception;
    }

    private function dispatchValidationPrepareEvent(array $eventData): array
    {
        $eventName = strtolower(str_replace('\\', '_', static::class)) . '_validate_before';

        $transport = new DataObject($eventData);

        DataObjectFactory::get(EventManager::class)->dispatch($eventName, [
            'object' => $this,
            'transport' => $transport,
        ]);

        return [
            'rules' => $transport->getData('rules'),
            'messages' => $transport->getData('messages'),
            'aliases' => $transport->getData('aliases'),
            'validator' => $transport->getData('validator'),
        ];
    }

    protected static function flattenRules(Data $data, string $prefix = ''): array
    {
        $rules = [];

        foreach ($data->rules() as $key => $rule) {
            $rules[$prefix . $key] = $rule;
        }

        foreach (get_object_vars($data) as $property => $value) {
            $fullKey = $prefix . $property;

            if ($value instanceof Data) {
                $rules[$fullKey] = $rules[$fullKey] ?? 'required';
                $rules += self::flattenRules($value, "{$fullKey}.");
            }

            if (is_array($value)) {
                $hasDataObjects = array_filter($value, fn($v) => $v instanceof Data);

                if ($hasDataObjects) {
                    $rules[$fullKey] = $rules[$fullKey] ?? 'nullable|array';

                    foreach ($value as $index => $item) {
                        if ($item instanceof Data) {
                            $rules += self::flattenRules($item, "{$fullKey}.{$index}.");
                        }
                    }
                }
            }
        }

        return $rules;
    }

    private function addCustomValidationRules(Validator $validator): void
    {
        foreach ($this->customRules() as $ruleName => $customRule) {
            $ruleInstance = match (true) {
                $customRule instanceof Closure => $this->createCallbackRule($customRule),
                $customRule instanceof Rule => $customRule,
                is_string($customRule) && class_exists($customRule) => $this->resolveRuleInstance($customRule),
                default => throw new \InvalidArgumentException(
                    sprintf("Invalid rule definition for '%s'", $ruleName)
                ),
            };

            $validator->addValidator($ruleName, $ruleInstance);
        }
    }

    private function createCallbackRule(Closure $closure): Rule
    {
        $callback = new Callback();
        $callback->setCallback($closure);
        return $callback;
    }

    private function resolveRuleInstance(string $className): Rule
    {
        $instance = DataObjectFactory::get($className);

        if (!$instance instanceof Rule) {
            throw new \InvalidArgumentException(
                sprintf("Class %s must implement Rakit\\Validation\\Rule.", $className)
            );
        }

        return $instance;
    }
}

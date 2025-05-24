<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
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

    public function validate(bool $throwException = true): void
    {
        $validator = new Validator;

        $attributes = $this->toArray();

        // Dispatch event to allow extensions to rules, messages or aliases
        ['rules' => $rules, 'messages' => $messages, 'aliases' => $aliases]
            = $this->dispatchValidationPrepareEvent($this->rules(), $this->messages(), $this->aliases());

        $validation = $validator->make($attributes, $rules, $messages);
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

    private function dispatchValidationPrepareEvent(array $rules, array $messages, array $aliases): array
    {
        $eventName = strtolower(str_replace('\\', '_', static::class)) . '_validate_before';

        $transport = new DataObject([
            'rules' => $rules,
            'messages' => $messages,
            'aliases' => $aliases,
        ]);

        DataObjectFactory::get(EventManager::class)->dispatch($eventName, [
            'object' => $this,
            'transport' => $transport,
        ]);

        return [
            'rules' => $transport->getData('rules'),
            'messages' => $transport->getData('messages'),
            'aliases' => $transport->getData('aliases'),
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

}

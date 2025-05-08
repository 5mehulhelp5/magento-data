<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Rakit\Validation\Validator;
use Rkt\MageData\Exceptions\ValidationException;
use Rkt\MageData\Model\DataObjectFactory;

trait UseValidation
{
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
}

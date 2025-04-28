<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Rakit\Validation\Validator;
use Rkt\MageData\Exceptions\ValidationException;

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

    public function validate(): void
    {
        $validator = new Validator;

        $attributes = $this->toArray();
        $validation = $validator->make($attributes, $this->rules(), $this->messages());

        $validation->setAliases($this->aliases());

        $validation->validate();

        if ($validation->fails()) {
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

    public function throwValidationException(array $errors): void
    {
        $exception = new ValidationException();
        $exception->setErrors($errors);

        throw $exception;
    }
}

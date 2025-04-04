<?php

declare(strict_types=1);

namespace Rkt\MageData\Exceptions;

use Exception;

class ValidationException extends Exception
{
    private ?array $errors = null;

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

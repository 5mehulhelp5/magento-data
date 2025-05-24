<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\TestDoubles;

use Rkt\MageData\Data;
use Rkt\MageData\Traits\UseValidation;

class Person extends Data
{
    use UseValidation;

    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
    ) {
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'firstname' => 'required',
            'lastname' => 'required',
        ];
    }

    public function messages(): array
    {
        return ['email.required' => 'Email is required.'];
    }

    public function aliases(): array
    {
        return ['email' => 'Email Address'];
    }
}

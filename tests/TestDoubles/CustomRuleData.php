<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\TestDoubles;

use Rkt\MageData\Data;

class CustomRuleData extends Data
{
    public function __construct(
        public string $email,
        public string $name,
    ) {
    }

    public function rules(): array
    {
        return [
            'name' => 'capitalized',
            'email' => 'email_ends_with_example'
        ];
    }

    public function customRules(): array
    {
        return [
            // Checks first letter in every word is uppercase letter.
            'capitalized' => function ($value) {
                $nameArr = explode(' ', $value);

                foreach ($nameArr as $name) {
                    if (ucfirst($name) !== $name) {
                        return false;
                    }
                }

                return true;
            },
            'email_ends_with_example' => ExampleDomainEmailRule::class,
        ];
    }
}

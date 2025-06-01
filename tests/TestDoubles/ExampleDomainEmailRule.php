<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\TestDoubles;

use Rakit\Validation\Rule;

class ExampleDomainEmailRule extends Rule
{
    protected $message = 'The :attribute must be end with example.com';

    public function check($value): bool
    {
        return str_contains($value, 'example.com');
    }
}

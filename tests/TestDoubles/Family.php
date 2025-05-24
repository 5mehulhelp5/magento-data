<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\TestDoubles;

use Rkt\MageData\Data;

class Family extends Data
{
    public function __construct(
        public Person $father,
        public Person $mother,
        public ?array $children = [],
    ) {
    }

    public function rules(): array
    {
        return [
            'father' => 'required',
            'mother' => 'required',
            'children' => 'array|nullable'
        ];
    }
}

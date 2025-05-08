<?php

declare(strict_types=1);

namespace Rkt\MageData;

use JsonSerializable;
use Rkt\MageData\Traits\CreatesSelfInstance;
use Rkt\MageData\Traits\UseJsonSerializable;
use Rkt\MageData\Traits\UseValidation;
use Stringable;

abstract class Data implements JsonSerializable, Stringable
{
    use CreatesSelfInstance, UseJsonSerializable, UseValidation;
}

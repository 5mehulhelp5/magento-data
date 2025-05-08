<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

trait UseJsonSerializable
{
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}

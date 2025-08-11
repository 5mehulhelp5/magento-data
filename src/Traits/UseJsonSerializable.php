<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

trait UseJsonSerializable
{
    private ?array $onlyKeys = null;
    private ?array $exceptKeys = null;

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
        $data = get_object_vars($this);

        if ($this->onlyKeys !== null) {
            return array_intersect_key($data, array_flip($this->onlyKeys));
        }

        if ($this->exceptKeys !== null) {
            return array_diff_key($data, array_flip($this->exceptKeys));
        }

        return $data;
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function only(string ...$keys): static
    {
        $clone = clone $this;
        $clone->onlyKeys = $keys;
        $clone->exceptKeys = null;
        return $clone;
    }

    public function except(string ...$keys): static
    {
        $clone = clone $this;
        $clone->exceptKeys = $keys;
        $clone->onlyKeys = null;
        return $clone;
    }

}

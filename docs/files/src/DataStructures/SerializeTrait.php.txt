<?php


declare(strict_types=1);

namespace ArangoDB\DataStructures;

trait SerializeTrait
{
    /**
     * Return a JSON representation of list
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->content;
    }
}

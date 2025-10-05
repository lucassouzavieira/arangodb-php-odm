<?php

declare(strict_types=1);

namespace ArangoDB\DataStructures;

trait CountTrait
{
    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->content);
    }
}

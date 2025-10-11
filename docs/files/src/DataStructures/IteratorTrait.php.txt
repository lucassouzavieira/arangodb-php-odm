<?php

declare(strict_types=1);

namespace ArangoDB\DataStructures;

trait IteratorTrait
{
    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->content[$this->position];
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->content[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}

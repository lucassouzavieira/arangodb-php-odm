<?php

declare(strict_types=1);

namespace ArangoDB\DataStructures;

use ArangoDB\DataStructures\Contracts\ListInterface;

/**
 * Class ArrayList
 *
 * @package ArangoDB\DataStructures
 * @author Lucas S. Vieira
 */
class ArrayList implements ListInterface
{
    use CountTrait;
    use IteratorTrait;
    use SerializeTrait;

    /**
     * List of data
     *
     * @var array
     */
    protected array $content;

    /**
     * Current index
     *
     * @var int
     */
    protected int $position = 0;

    /**
     * ArrayList constructor.
     *
     * @param array $content
     */
    public function __construct(array $content = [])
    {
        $this->content = $content;
    }

    /**
     * String representation of ArrayList
     *
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Get the first value of list
     *
     * @return mixed
     */
    public function first(): mixed
    {
        $content = array_values($this->content);
        return array_shift($content);
    }

    /**
     * Get the last value of list
     *
     * @return mixed
     */
    public function last(): mixed
    {
        $content = array_values($this->content);
        return array_pop($content);
    }

    /**
     * Get a value by its key
     *
     * @param int|string $key KeyType to verify on list.
     * @return mixed
     */
    public function get(int|string $key): mixed
    {
        if (array_key_exists($key, $this->content)) {
            return $this->content[$key];
        }

        return null;
    }

    /**
     * Add a value to list
     *
     * @param mixed $value Value to add.
     */
    public function push(mixed $value): void
    {
        $this->content[] = $value;
    }

    /**
     * Put a object into list on given key
     *
     * @param integer|string $key KeyType for manage the value.
     * @param mixed $value Value to add.
     */
    public function put(int|string $key, mixed $value): void
    {
        $this->content[$key] = $value;
    }

    /**
     * Check if a given key exists on list
     *
     * @param $key int|string to verify on list.
     * @return bool True if key exists, false otherwise.
     */
    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->content);
    }

    /**
     * Remove a value by its key on list
     *
     * @param $key int|string to remove from list.
     */
    public function remove(int|string $key): void
    {
        unset($this->content[$key]);
    }

    /**
     * Return an array representation for list
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->content;
    }

    /**
     * Return an array with list values only
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->content);
    }

    /**
     * Appends a list to another.
     *
     * @param ListInterface $list ListInterface object to append.
     */
    public function append(ListInterface $list): void
    {
        foreach ($list as $item) {
            $this->push($item);
        }
    }
}

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
    /**
     * List of data
     * @var array
     */
    protected $content;

    /**
     * Current index
     * @var int
     */
    protected $position = 0;

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
    public function first()
    {
        $content = array_values($this->content);
        return array_shift($content);
    }

    /**
     * Get the last value of list
     *
     * @return mixed
     */
    public function last()
    {
        $content = array_values($this->content);
        return array_pop($content);
    }

    /**
     * Get a value by it's key
     *
     * @param $key Key to verify on list.
     * @return mixed
     */
    public function get($key)
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
    public function push($value): void
    {
        $this->content[] = $value;
    }

    /**
     * Put a object into list on given key
     *
     * @param string|integer $key Key for manage the value.
     * @param mixed $value Value to add.
     */
    public function put($key, $value): void
    {
        $this->content[$key] = $value;
    }

    /**
     * Check if a given key exists on list
     *
     * @param $key Key to verify on list.
     * @return bool True if key exists, false otherwise.
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->content);
    }

    /**
     * Remove a value by it's key on list
     *
     * @param $key Key to remove from list.
     * @return mixed
     */
    public function remove($key)
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

    /**
     * Return a JSON representation of list
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->content;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return $this->content[$this->position];
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->content[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->content);
    }
}

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
class ArrayList implements ListInterface, \JsonSerializable, \Iterator, \Countable
{
    /**
     * List of data
     * @var array
     */
    private $content;

    /**
     * Current index
     * @var int
     */
    private $position = 0;

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
     * @param $key
     * @return mixed
     * @see ListInterface::get()
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->content)) {
            return $this->content[$key];
        }

        return null;
    }

    /**
     * @param mixed $value
     * @see ListInterface::push()
     */
    public function push($value): void
    {
        $this->content[] = $value;
    }

    /**
     * @param string|integer $key
     * @param mixed $value
     * @see ListInterface::put()
     */
    public function put($key, $value): void
    {
        $this->content[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     * @see ListInterface::has()
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->content);
    }

    /**
     * @param $key
     * @see ListInterface::remove()
     */
    public function remove($key)
    {
        unset($this->content[$key]);
    }

    /**
     * @see ListInterface::toArray()
     */
    public function toArray(): array
    {
        return $this->content;
    }

    /**
     * @see ListInterface::values()
     */
    public function values(): array
    {
        return array_values($this->content);
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->content;
    }

    /**
     * @return mixed
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->content[$this->position];
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @return mixed
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     * @see Iterator::valid()
     */
    public function valid()
    {
        return isset($this->content[$this->position]);
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return int
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->content);
    }
}

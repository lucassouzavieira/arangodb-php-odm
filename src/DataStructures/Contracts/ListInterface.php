<?php
declare(strict_types=1);

namespace ArangoDB\DataStructures\Contracts;

/**
 * ListInterface
 *
 * @package ArangoDB\DataStructures\Contracts
 * @author Lucas S. Vieira
 */
interface ListInterface extends \Iterator, \JsonSerializable, \Countable
{
    /**
     * Get the first value of list
     *
     * @return mixed
     */
    public function first();

    /**
     * Get the last value of list
     *
     * @return mixed
     */
    public function last();

    /**
     * Get a value by it's key
     *
     * @param $key Key to verify on list.
     * @return mixed
     */
    public function get($key);

    /**
     * Add a value to list
     *
     * @param mixed $value Value to add.
     */
    public function push($value): void;

    /**
     * Put a object into list on given key
     *
     * @param string|integer $key Key for manage the value.
     * @param mixed $value Value to add.
     */
    public function put($key, $value): void;

    /**
     * Check if a given key exists on list
     *
     * @param $key Key to verify on list.
     * @return bool True if key exists, false otherwise.
     */
    public function has($key): bool;

    /**
     * Remove a value by it's key on list
     *
     * @param $key Key to remove from list.
     * @return mixed
     */
    public function remove($key);

    /**
     * Return an array representation for list
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Return an array with list values only
     *
     * @return array
     */
    public function values(): array;

    /**
     * Appends a list to another.
     *
     * @param ListInterface $list ListInterface object to append.
     */
    public function append(ListInterface $list): void;
}

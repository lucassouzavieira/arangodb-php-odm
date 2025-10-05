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
    public function first(): mixed;

    /**
     * Get the last value of list
     *
     * @return mixed
     */
    public function last(): mixed;

    /**
     * Get a value by its key
     *
     * @param integer|string $key key to verify on list.
     * @return mixed
     */
    public function get(int|string $key): mixed;

    /**
     * Add a value to list
     *
     * @param mixed $value Value to add.
     */
    public function push(mixed $value): void;

    /**
     * Put an object into list on given key
     *
     * @param integer|string $key KeyType for manage the value.
     * @param mixed $value Value to add.
     */
    public function put(int|string $key, mixed $value): void;

    /**
     * Check if a given key exists on list
     *
     * @param integer|string $key Key to verify on list.
     * @return bool True if key exists, false otherwise.
     */
    public function has(int|string $key): bool;

    /**
     * Remove a value by its key on list
     *
     * @param integer|string $key key to remove from list.
     */
    public function remove(int|string $key): void;

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

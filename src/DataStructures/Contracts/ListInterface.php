<?php
declare(strict_types=1);

namespace ArangoDB\DataStructures\Contracts;

/**
 * ListInterface
 *
 * @package ArangoDB\DataStructures\Contracts
 * @author Lucas S. Vieira
 */
interface ListInterface
{
    /**
     * Get a value by it's key
     *
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Add a value to list
     *
     * @param mixed $value
     */
    public function push($value): void;

    /**
     * Put a object into list on given key
     *
     * @param string|integer $key
     * @param mixed $value
     */
    public function put($key, $value): void;

    /**
     * Check if a given key exists on list
     *
     * @param $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * Remove a value by it's key on list
     *
     * @param $key
     * @return mixed
     */
    public function remove($key);

    /**
     * Return an array representation for list
     *
     * @return array
     */
    public function toArray(): array;
}

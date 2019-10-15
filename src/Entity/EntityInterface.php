<?php
declare(strict_types=1);

namespace ArangoDB\Entity;

use ArangoDB\DataStructures\ArrayList;

/**
 * Entity interface
 *
 * @package ArangoDB\Handler
 * @author Lucas S. Vieira
 */
interface EntityInterface
{
    /**
     * Returns true if is a new object
     * @return bool
     */
    public function isNew(): bool;

    /**
     * Finds a instance of entity on server
     *
     * @param string $id
     * @return EntityInterface|null EntityInterface object or null if not found on server
     */
    public function find(string $id);

    /**
     * Returns all entities available on server
     *
     * @return ArrayList[User]
     */
    public function all(): ArrayList;

    /**
     * Save a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     */
    public function save(): bool;

    /**
     * Removes a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     */
    public function delete(): bool;

    /**
     * Returns a array representation of entity
     *
     * @return array
     */
    public function toArray(): array;
}

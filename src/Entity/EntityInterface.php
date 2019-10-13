<?php
declare(strict_types=1);

namespace ArangoDB\Entity;

use ArangoDB\Connection\Connection;

/**
 * Handler interface
 *
 * @package ArangoDB\Handler
 */
interface EntityInterface
{
    /**
     * Returns true if is a new object
     * @return bool
     */
    public function isNew(): bool;

    /**
     * Returns all entities available on server
     *
     * @return array
     */
    public function all(): array;

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
     * Set a connection object for the class
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection): void;

    /**
     * Make Entity objects from array
     *
     * @param array $data
     * @return Entity[]
     */
    public static function make($data = []): array;
}

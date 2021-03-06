<?php
declare(strict_types=1);

namespace ArangoDB\Entity\Contracts;

/**
 * Entity interface.
 *
 * @package ArangoDB\Handler
 * @author Lucas S. Vieira
 */
interface EntityInterface extends \JsonSerializable
{
    /**
     * Returns true if is a new object.
     *
     * @return bool
     */
    public function isNew(): bool;

    /**
     * Save a entity on server, if possible.
     *
     * @return bool True if operation was successful, false otherwise.
     */
    public function save(): bool;

    /**
     * Removes a entity on server, if possible.
     *
     * @return bool True if operation was successful, false otherwise.
     */
    public function delete(): bool;

    /**
     * Returns a array representation of entity.
     *
     * @return array
     */
    public function toArray(): array;
}

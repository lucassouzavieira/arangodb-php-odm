<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Contracts;

use ArangoDB\Collection\Collection;

/**
 * Interface IndexInterface
 *
 * @package ArangoDB\Collection\Contracts
 * @author Lucas S. Vieira
 */
interface IndexInterface extends \JsonSerializable
{
    /**
     * If the index is a new one
     *
     * @return bool True for new one index. False for a existing index.
     */
    public function isNew(): bool;

    /**
     * If index was set to use 'unique' constraint
     *
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * If index is sparse
     *
     * @return bool
     */
    public function isSparse(): bool;

    /**
     * Returns the index id
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns the index name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the index type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Return data for create index on server
     *
     * @return array
     */
    public function getCreateData(): array;

    /**
     * Returns the collection where the index belongs to
     *
     * @return Collection|null A collection object or null if the index was not set to an collection yet
     */
    public function getCollection();
}

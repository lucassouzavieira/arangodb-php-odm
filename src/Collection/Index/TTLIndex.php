<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class TTLIndex
 * Represents a TTL index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
class TTLIndex extends Index
{
    /**
     * Time (in seconds) after a document's creation after which the documents counts as expired
     *
     * @var int
     */
    protected $expiresAfter;

    /**
     * TTLIndex constructor.
     *
     * @param array $fields An array of attribute names.
     * @param int $expireAfter Time (in seconds) to expire a document
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, int $expireAfter = 60)
    {
        $this->expiresAfter = $expireAfter;
        parent::__construct("ttl", $fields);
    }

    /**
     * Return time (in seconds) to expire documents on index
     *
     * @return int
     */
    public function expireAfter(): int
    {
        return $this->expiresAfter;
    }

    /**
     * Return data for create index on server
     *
     * @return array
     */
    public function getCreateData(): array
    {
        return [
            'type' => $this->getType(),
            'expireAfter' => $this->expireAfter(),
            'fields' => $this->getFields()
        ];
    }

    /**
     * Returns a array representation of index
     *
     * @return array
     */
    public function toArray(): array
    {
        $values = parent::toArray();
        $values['expireAfter'] = $this->expireAfter();
        return $values;
    }
}

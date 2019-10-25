<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class FullTextIndex
 * Represents a fulltext index
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
class FullTextIndex extends Index
{
    /**
     * Minimum length of index
     *
     * @var int
     */
    protected $minLength;

    /**
     * FullTextIndex constructor.
     *
     * @param array $fields An array of attribute names. Normally with just one attribute.
     * @param int $minLength Minimum character length to index. Will default to a server-defined value if 0 is set.
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, int $minLength = 0)
    {
        $this->minLength = $minLength;
        parent::__construct("fulltext", $fields);
    }

    /**
     * Return index minimum length
     *
     * @return int
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * Return data for create index on server
     *
     * @return array
     */
    public function getCreateData(): array
    {
        $data = [
            'type' => $this->getType(),
            'fields' => $this->getFields(),
        ];

        if ($this->getMinLength() > 0) {
            $data['minLength'] = $this->getMinLength();
        }

        return $data;
    }
}

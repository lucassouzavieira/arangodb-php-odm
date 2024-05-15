<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Exceptions\IndexException;
use ArangoDB\Collection\Contracts\IndexInterface;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Index Factory class
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class Factory
{
    /**
     * @param array $attributes
     * @return IndexInterface
     *
     * @throws MissingParameterException|InvalidParameterException|IndexException
     */
    public static function factory(array $attributes): IndexInterface
    {
        if (!isset($attributes['type'])) {
            throw new MissingParameterException("attributes['type']");
        }

        $indexes = [
            'primary' => PrimaryIndex::class,
            'edge' => EdgeIndex::class,
            'fulltext' => FullTextIndex::class,
            'persistent' => PersistentIndex::class,
            'hash' => HashIndex::class,
            'geo' => GeoSpatialIndex::class,
            'skiplist' => SkipListIndex::class,
            'ttl' => TTLIndex::class,
        ];

        if (!array_key_exists($attributes['type'], $indexes)) {
            throw new IndexException("Couldn't identify the proper index");
        }

        // Specific indexes with proper constructors.
        if ($attributes['type'] === 'fulltext') {
            return new FullTextIndex($attributes['fields'], $attributes['minLength'], $attributes);
        }

        if ($attributes['type'] === 'persistent') {
            return new PersistentIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'geo') {
            return new GeoSpatialIndex($attributes['fields'], true, $attributes);
        }

        if ($attributes['type'] === 'ttl') {
            return new TTLIndex($attributes['fields'], $attributes['expireAfter'], $attributes);
        }

        $class = $indexes[$attributes['type']];
        return new $class($attributes['fields'], $attributes);
    }
}

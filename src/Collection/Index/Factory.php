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

        if ($attributes['type'] === 'primary') {
            return new PrimaryIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'edge') {
            return new EdgeIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'fulltext') {
            return new FullTextIndex($attributes['fields'], $attributes['minLength'], $attributes);
        }

        if ($attributes['type'] === 'persistent') {
            return new PersistentIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'hash') {
            return new HashIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'geo') {
            return new GeoSpatialIndex($attributes['fields'], true, $attributes);
        }

        if ($attributes['type'] === 'skiplist') {
            return new SkipListIndex($attributes['fields'], $attributes);
        }

        if ($attributes['type'] === 'ttl') {
            return new TTLIndex($attributes['fields'], $attributes['expireAfter'], $attributes);
        }

        throw new IndexException("Couldn't identify the proper index");
    }
}

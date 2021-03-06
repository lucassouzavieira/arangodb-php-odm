<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a geo-spatial index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class GeoSpatialIndex extends Index
{
    /**
     * If a geo-spatial index on a location is constructed
     * and geoJson is true, then the order within the array is longitude
     * followed by latitude.
     *
     * @link https://geojson.org/geojson-spec.html#positions
     * @var bool
     */
    protected $geoJson;

    /**
     * GeoSpatialIndex constructor.
     *
     * @param array $fields An array of attribute names.
     * @param bool $geoJson
     * @param array $attributes
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, bool $geoJson = true, array $attributes = [])
    {
        $this->geoJson = $geoJson;
        parent::__construct("geo", $fields, $attributes);
    }

    /**
     * If the index is a GeoJson.
     *
     * @return bool
     */
    public function isGeoJson(): bool
    {
        return $this->geoJson;
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
            'geoJson' => $this->isGeoJson(),
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
        $values['geoJson'] = $this->isGeoJson();
        return $values;
    }
}

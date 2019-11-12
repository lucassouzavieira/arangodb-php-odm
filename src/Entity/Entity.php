<?php
declare(strict_types=1);

namespace ArangoDB\Entity;

use ArangoDB\Connection\ManagesConnection;

/**
 * Entity class.
 *
 * @package ArangoDB\Handler
 * @author Lucas S. Vieira
 */
abstract class Entity extends ManagesConnection implements EntityInterface
{
    /**
     * Attributes of entity.
     *
     * @var array
     */
    protected $attributes;

    /**
     * If the entity is not an representation of a existing object on server,
     * this property is true.
     *
     * @var bool
     */
    protected $isNew = false;

    /**
     * Parameters sent by server, but not used on representation of a entity.
     *
     * @var array
     */
    protected $unsetAttributes = [
        'error',
        'code'
    ];

    /**
     * Entity constructor.
     *
     * @param array $attributes Entity attributes.
     * @param bool $isNew If the entity is a new one.
     */
    public function __construct(array $attributes = [], bool $isNew = false)
    {
        $this->isNew = $isNew;

        foreach ($this->unsetAttributes as $key) {
            unset($attributes[$key]);
        }

        $this->attributes = $attributes;
    }

    /**
     * Get some attribute.
     *
     * @param string $name Attribute name.
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * Set a attribute
     *
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     */
    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Default handling for discarding objects
     *
     * @return void
     */
    public function __destruct()
    {
        unset($this->connection);
    }

    /**
     * Return a JSON representation of Entity object.
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        // If a handler hasn't a custom serialize method,
        // by default we serialize your attributes array.
        return $this->attributes;
    }
}

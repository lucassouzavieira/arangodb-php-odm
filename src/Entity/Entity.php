<?php
declare(strict_types=1);

namespace ArangoDB\Entity;

use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Connection\ManagesConnection;

/**
 * Entity class
 *
 * @package ArangoDB\Handler
 * @author Lucas S. Vieira
 */
abstract class Entity extends ManagesConnection implements EntityInterface, \JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * If the user is not an representation of a existing user, this property is truelllllll
     * @var bool
     */
    protected $isNew = false;

    /**
     * Parameters sent by server, but not used on representation of a entity
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
     * @param array $attributes
     * @param bool $isNew
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
     * Get some attribute
     *
     * @param string $name
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
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * String representation for object
     *
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this);
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
     * Returns true if is a new object
     *
     * @return bool
     */
    final public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Returns a array representation of entity
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        // If a handler hasn't a custom serialize method,
        // by default we serialize your attributes array.
        return $this->attributes;
    }

    /**
     * Initialize a handler object with given attributes
     *
     * @param array $attributesNames
     * @param array $attributes
     * @throws \ReflectionException
     */
    protected function initialize(array $attributesNames, array $attributes = [])
    {
        foreach ($attributesNames as $attribute) {
            $reflection = new \ReflectionClass($this);

            if (array_key_exists($attribute, $attributes)) {
                $reflectionProperty = $reflection->getProperty($attribute);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($this, $attributes[$attribute]);
                $reflectionProperty->setAccessible(false);
            }
        }
    }

    /**
     * Returns base Uri for handle entity
     *
     * @return string
     */
    abstract protected function getEntityBaseUri(): string;

    /**
     * Make Entity objects from array
     *
     * @param array $data
     * @param bool $isNew
     * @return ArrayList[Entity]
     */
    abstract protected function make(array $data = [], bool $isNew = false): ArrayList;
}

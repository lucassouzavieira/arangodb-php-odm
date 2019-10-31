<?php

namespace ArangoDB\Validation\Document;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Validate the document values
 *
 * @package ArangoDB\Validation\Document
 * @author Lucas S. Vieira
 */
class DocumentValidator extends Validator
{
    /**
     * DocumentValidator constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->data = $attributes;
    }

    /**
     * Set the data for validation
     *
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        if (is_array($attributes)) {
            $this->data = $attributes;
        }

        // Must be in array form for validation.
        $this->data = [$attributes];
    }

    /**
     * Return document data
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->data;
        unset($attributes['_id'], $attributes['_rev'], $attributes['_key']);
        return $attributes;
    }

    /**
     * Returns if the given document validator has document descriptors
     *
     * @return bool
     */
    public function hasDescriptors()
    {
        return (isset($this->data['_id']) || isset($this->data['_key']) || isset($this->data['_rev']));
    }

    /**
     * Return document descriptors attributes
     *
     * @return array|null
     */
    public function getDescriptorsAttributes()
    {
        return [
            '_id' => isset($this->data['_id']) ? $this->data['_id'] : null,
            '_rev' => isset($this->data['_rev']) ? $this->data['_rev'] : null,
            '_key' => isset($this->data['_key']) ? $this->data['_key'] : null,
        ];
    }

    /**
     * Rules for validation
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Validate document data
     *
     * @return true if validation is successful, throw an exception otherwise
     * @throws InvalidParameterException
     */
    public function validate(): bool
    {
        $callback = function ($arr = []) use (&$callback) {
            $validator = Rules::isPrimitive();
            foreach ($arr as $key => $value) {
                if (is_object($value) || is_callable($value)) {
                    throw new InvalidParameterException($key, $value);
                }

                if (is_array($value)) {
                    $callback($value);
                }

                $validator->isValid($value);
            }
        };

        $callback($this->data);
        return true;
    }
}

<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Document;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Validate the document values. <br>
 * Used for avoid client errors when creating or updating documents on collections.
 *
 * @package ArangoDB\Validation\Document
 * @author Lucas S. Vieira
 */
class DocumentValidator extends Validator
{
    /**
     * Set the data for validation
     *
     * @param array $attributes Attributes to validate.
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
        unset($attributes['_id'], $attributes['_rev']);
        return $attributes;
    }

    /**
     * Check if this document validator has document descriptors.
     *
     * @return bool Returns true if has some descriptor, false otherwise.
     */
    public function hasDescriptors()
    {
        return (isset($this->data['_id']) || isset($this->data['_key']) || isset($this->data['_rev']));
    }

    /**
     * Return document descriptors attributes.
     *
     * @return array
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
     * Rules for validation.
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Validate document data.
     *
     * @return true if validation is successful, throw an exception otherwise.
     *
     * @throws InvalidParameterException
     */
    public function validate(): bool
    {
        $callback = function ($arr = []) use (&$callback) {
            $validator = Rules::isPrimitive();
            foreach ($arr as $key => $value) {
                if ((is_object($value) || is_callable($value)) && !is_string($value)) {
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

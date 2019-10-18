<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Document\DocumentValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Manager parameters/values binding to statements
 *
 * @package ArangoDB\AQL
 * @author Lucas S. Vieira
 */
class BindContainer extends ArrayList
{
    /**
     * Validate input data
     *
     * @var DocumentValidator
     */
    protected $validator;

    /**
     * BindContainer constructor.
     */
    public function __construct()
    {
        $this->validator = new DocumentValidator();
        parent::__construct([]);
    }

    /**
     * Add validation to value being set.
     * All values must be primitives data types (or conversible for them)
     *
     * @param int|string $key
     * @param mixed $value
     * @throws InvalidParameterException
     */
    public function put($key, $value): void
    {
        $this->validator->setData($value);
        if ($this->validator->validate()) {
            // Arrays are seen as subdocuments.
            if (is_array($value)) {
                $value = json_encode($value);
            }

            parent::put($key, $value);
        }
    }
}

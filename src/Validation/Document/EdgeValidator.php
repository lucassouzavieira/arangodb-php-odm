<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Document;

use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Validate the edge values
 *
 * @package ArangoDB\Validation\Document
 * @author Lucas S. Vieira
 */
class EdgeValidator extends DocumentValidator
{
    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [
        '_to', '_from'
    ];

    /**
     * Validate document data
     *
     * @return true if validation is successful, throw an exception otherwise
     * @throws InvalidParameterException|MissingParameterException
     */
    public function validate(): bool
    {
        $this->validateGraphParams();
        return parent::validate();
    }

    /**
     * Validate graph parameters
     *
     * @throws MissingParameterException|InvalidParameterException
     */
    protected function validateGraphParams()
    {
        if (!isset($this->data['_to'])) {
            throw new MissingParameterException("'_to'");
        }

        if (!isset($this->data['_from'])) {
            throw new MissingParameterException("'_to'");
        }

        $validator = Rules::string();

        if (!$validator->isValid($this->data['_to'])) {
            throw new InvalidParameterException("Invalid '_to' parameter to edge document", $this->data['_to']);
        }

        if (!$validator->isValid($this->data['_from'])) {
            throw new InvalidParameterException("Invalid '_from' parameter to edge document", $this->data['_from']);
        }
    }
}

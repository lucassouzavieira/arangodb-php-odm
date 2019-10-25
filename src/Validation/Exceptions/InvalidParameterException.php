<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * InvalidParameterException
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class InvalidParameterException extends BaseException
{
    /**
     * @var string
     */
    protected $parameter;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * MissingParameter constructor.
     *
     * @param $parameter
     * @param $value
     * @param Throwable|null $previous
     */
    public function __construct($parameter, $value, Throwable $previous = null)
    {
        $this->value = is_array($value) ? json_encode($value) : $value;
        $this->parameter = $parameter;
        $message = "Parameter '$parameter'('$this->value') of type " . gettype($value) . " given on " . $this->getFile() . " in line " . $this->getLine() . " is invalid.";
        parent::__construct($message, $previous);
    }
}

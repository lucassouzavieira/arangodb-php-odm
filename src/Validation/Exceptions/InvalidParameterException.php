<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * Invalid parameter exception.
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class InvalidParameterException extends BaseException
{
    /**
     * Parameter name.
     *
     * @var string
     */
    protected $parameter;

    /**
     * Parameter value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * InvalidParameterException constructor.
     *
     * @param string|int $parameter Parameter name.
     * @param mixed $value Parameter value.
     * @param Throwable|null $previous Previous exception or error.
     */
    public function __construct($parameter, $value, Throwable $previous = null)
    {
        $this->value = is_array($value) ? json_encode($value) : $value;
        $this->parameter = $parameter;
        $message = "Parameter '$parameter'('$this->value') of type " . gettype($value) . " given on " . $this->getFile() . " in line " . $this->getLine() . " is invalid.";
        parent::__construct($message, $previous);
    }
}

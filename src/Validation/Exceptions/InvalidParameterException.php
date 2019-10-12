<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;

/**
 * Class MissingParameter
 *
 * @package ArangoDB\Validation\Exceptions
 * @copyright 2019 Lucas S. Vieira
 */
class InvalidParameterException extends \Exception
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
        $this->value = $value;
        $this->parameter = $parameter;
        $message = "'$parameter' given on " . $this->getFile() . " in line " . $this->getLine() . " is invalid.";
        parent::__construct($message, 0, $previous);
    }
}

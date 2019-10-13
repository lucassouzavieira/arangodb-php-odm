<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;

/**
 * MissingParameterException
 *
 * @package ArangoDB\Validation\Exceptions
 * @copyright 2019 Lucas S. Vieira
 */
class MissingParameterException extends \Exception
{
    /**
     * @var string
     */
    protected $parameter;

    /**
     * MissingParameter constructor.
     *
     * @param $parameter
     * @param Throwable|null $previous
     */
    public function __construct($parameter, Throwable $previous = null)
    {
        $this->parameter = $parameter;
        $message = "Missing '$parameter' on: " . $this->getFile() . " in line " . $this->getLine();
        parent::__construct($message, 0, $previous);
    }
}

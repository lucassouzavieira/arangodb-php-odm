<?php

namespace ArangoDB\Exceptions;

use Throwable;

/**
 * Basic exception class for driver
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
abstract class BaseException extends \Exception
{
    /**
     * Base exception constructor.
     *
     * @param $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct($message, Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }

}
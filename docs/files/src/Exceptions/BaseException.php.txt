<?php
declare(strict_types=1);

namespace ArangoDB\Exceptions;

use Throwable;

/**
 * Basic exception class for driver
 *
 * @package ArangoDB\Exceptions
 * @author Lucas S. Vieira
 */
abstract class BaseException extends \Exception
{
    /**
     * Base exception constructor.
     *
     * @param string $message Exception message.
     * @param Throwable|null $previous Previous exception or error.
     * @param int $code Error code.
     */
    public function __construct(string $message, Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}

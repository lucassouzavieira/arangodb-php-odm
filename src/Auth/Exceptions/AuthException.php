<?php
declare(strict_types=1);

namespace ArangoDB\Auth\Exceptions;

use Throwable;

/**
 * AuthException
 *
 * @package ArangoDB\Auth\Exceptions
 * @author Lucas S. Vieira
 */
class AuthException extends \Exception
{
    /**
     * AuthException constructor.
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

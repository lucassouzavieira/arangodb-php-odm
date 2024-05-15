<?php
declare(strict_types=1);

namespace ArangoDB\Auth\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * AuthException
 *
 * @package ArangoDB\Auth\Exceptions
 * @author Lucas S. Vieira
 */
class AuthException extends BaseException
{
    /**
     * AuthException constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(string $message, Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}

<?php
declare(strict_types=1);

namespace ArangoDB\Exceptions;

use Throwable;

/**
 * ConnectionException
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class ConnectionException extends \Exception
{
    /**
     * ConnectionException constructor.
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

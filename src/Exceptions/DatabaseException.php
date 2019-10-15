<?php
declare(strict_types=1);

namespace ArangoDB\Exceptions;

use Throwable;

/**
 * DatabaseException
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class DatabaseException extends \Exception
{
    /**
     * DatabaseException constructor.
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

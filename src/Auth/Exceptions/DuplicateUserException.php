<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;

/**
 * DuplicateUserException
 *
 * @package ArangoDB\Auth\Exceptions
 * @copyright 2019 Lucas S. Vieira
 */
class DuplicateUserException extends \Exception
{
    /**
     * DuplicateUserException constructor.
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

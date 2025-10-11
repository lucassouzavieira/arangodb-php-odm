<?php

declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * Invalid collection key option exception.
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class InvalidKeyOptionException extends BaseException
{
    /**
     * Parameter name.
     *
     * @var string
     */
    protected string $parameter;

    /**
     * Parameter value.
     *
     * @var string
     */
    protected string $type;

    /**
     * InvalidKeyOptionException constructor.
     *
     * @param string $parameter Parameter name.
     * @param string $value Parameter value.
     * @param Throwable|null $previous Previous exception or error.
     */
    public function __construct(string $parameter, $value, ?Throwable $previous = null)
    {
        $message = "Parameter '$parameter' can not be used for collection with key type of '$value'.";
        parent::__construct($message, $previous);
    }
}

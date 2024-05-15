<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * Missing parameter exception
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class MissingParameterException extends BaseException
{
    /**
     * Parameter name.
     *
     * @var string
     */
    protected $parameter;

    /**
     * MissingParameterException constructor.
     *
     * @param string $parameter Parameter name.
     * @param Throwable|null $previous Previous exception or error.
     */
    public function __construct(string $parameter, Throwable $previous = null)
    {
        $this->parameter = $parameter;
        $message = "Missing '$parameter' on: " . $this->getFile() . " in line " . $this->getLine();
        parent::__construct($message, $previous);
    }
}

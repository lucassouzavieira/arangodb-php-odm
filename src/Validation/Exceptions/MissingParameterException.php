<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Exceptions;

use Throwable;
use ArangoDB\Exceptions\BaseException;

/**
 * MissingParameterException
 *
 * @package ArangoDB\Validation\Exceptions
 * @author Lucas S. Vieira
 */
class MissingParameterException extends BaseException
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
        parent::__construct($message, $previous);
    }
}

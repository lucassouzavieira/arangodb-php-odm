<?php
declare(strict_types=1);

namespace ArangoDB\Transaction;

use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Transaction\Contracts\Transaction;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Class StreamTransaction
 * Manages stream transactions on ArangoDB server
 *
 * @package ArangoDB\Transaction
 */
final class StreamTransaction extends Transaction
{
    /**
     * StreamTransaction constructor.
     *
     * @param Connection $connection
     * @param array $options
     * @throws TransactionException|InvalidParameterException|MissingParameterException
     */
    public function __construct(Connection $connection, array $options = [])
    {
        parent::__construct($connection, $options);
    }
}

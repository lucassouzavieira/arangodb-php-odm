<?php
declare(strict_types=1);

namespace ArangoDB\Transaction;

use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Transaction\TransactionOptionsValidator;

/**
 * Class StreamTransaction
 * Manages stream transactions on ArangoDB server
 *
 * @package ArangoDB\StreamTransaction
 */
final class StreamTransaction
{
    /**
     * Connection object
     *
     * @var Connection
     */
    protected $connection;

    /**
     * StreamTransaction options
     *
     * @var array
     */
    protected $options;

    /**
     * Some default options for transaction
     * Sets by default, 32MB as transaction size limit and
     * waits server write all data on disk before return any response.
     * Also, set by default a lock timeout of 30 seconds.
     *
     * @var array
     */
    protected $defaultOptions = [
        'maxTransactionSize' => 32000000,
        'waitForSync' => true,
        'allowImplicit' => false,
        'lockTimeout' => 30,
    ];


    /**
     * StreamTransaction constructor.
     *
     * @param Connection $connection
     * @param array $options
     * @throws TransactionException|InvalidParameterException|MissingParameterException
     */
    public function __construct(Connection $connection, array $options = [])
    {
        $this->connection = $connection;
        $options = array_merge($this->defaultOptions, $options);
        $validator = new TransactionOptionsValidator($options);
        if ($validator->validate()) {
            $this->options = $options;
        }
    }
}

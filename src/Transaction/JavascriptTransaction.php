<?php
declare(strict_types=1);

namespace ArangoDB\Transaction;

use ArangoDB\Connection\Connection;

/**
 * Manages Javascript transactions
 *
 * @package ArangoDB\Transaction
 * @author Lucas S. Vieira
 */
class JavascriptTransaction
{
    /**
     * Javascript code on a string to be executed on server
     *
     * @var string
     */
    protected $action;

    /**
     * Connection object to handle during transaction
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Transaction options
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
     * JavascriptTransaction constructor.
     *
     * @param Connection $connection
     * @param string $action
     * @param array $options
     */
    public function __construct(Connection $connection, string $action, array $options = [])
    {
        $this->action = $action;
        $this->connection = $connection;
        $options = array_merge($this->defaultOptions, $options);
    }
}

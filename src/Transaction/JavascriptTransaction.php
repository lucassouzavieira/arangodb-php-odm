<?php
declare(strict_types=1);

namespace ArangoDB\Transaction;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\TransactionException;
use GuzzleHttp\Exception\BadResponseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Transaction\TransactionOptionsValidator;

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
     * @throws TransactionException|InvalidParameterException|MissingParameterException
     */
    public function __construct(Connection $connection, string $action, array $options = [])
    {
        $this->action = $action;
        $this->connection = $connection;
        $options = array_merge($this->defaultOptions, $options);
        $validator = new TransactionOptionsValidator($options);
        if ($validator->validate()) {
            $this->options = $options;
        }
    }

    /**
     * Execute the transaction.
     * Throws an exception if any error is detected.
     *
     * @return mixed The result value of transaction
     * @throws TransactionException|GuzzleException
     */
    public function execute()
    {
        try {
            $options = array_merge($this->options, ['action' => $this->action]);
            $response = $this->connection->post(sprintf(Api::TRANSACTION), $options);
            $data = json_decode((string)$response->getBody(), true);
            return $data['result'] === 'NULL' ? null : $data['result'];
        } catch (BadResponseException $exception) {
            // An error was returned from server.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $transactionException = new TransactionException($response['errorMessage'], $exception, $response['errorNum']);
            throw $transactionException;
        }
    }
}

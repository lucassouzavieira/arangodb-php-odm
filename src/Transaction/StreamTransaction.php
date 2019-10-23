<?php
declare(strict_types=1);

namespace ArangoDB\Transaction;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\TransactionException;
use GuzzleHttp\Exception\BadResponseException;
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
     * Transaction Id
     *
     * @var string
     */
    protected $id;

    /**
     * Transaction status
     *
     * @var string
     */
    protected $status = '';

    /**
     * If the transaction object is already started
     *
     * @var bool
     */
    protected $started = false;

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

    /**
     * Returns the transaction id
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->id;
    }

    /**
     * Returns the transaction status
     *
     * @return string
     */
    public function getTransactionStatus(): string
    {
        return $this->status;
    }

    /**
     * Begin transaction
     *
     * @throws TransactionException|BadResponseException|GuzzleException
     */
    public function begin()
    {
        try {
            $response = $this->connection->post(sprintf(Api::TRANSACTION_BEGIN), $this->options);
            $data = json_decode((string)$response->getBody(), true);
            $this->id = $data['result']['id'];
            $this->status = $data['result']['status'];
            $this->connection->setDefaultHeaders(['x-arango-trx-id' => $this->id]);
            $this->started = true;
        } catch (BadResponseException $exception) {
            // An error was returned from server.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $transactionException = new TransactionException($response['errorMessage'], $exception, $response['errorNum']);
            throw $transactionException;
        }
    }

    /**
     * Commit transaction
     *
     * @throws TransactionException|BadResponseException|GuzzleException
     */
    public function commit()
    {
        try {
            if ($this->started) {
                $uri = Api::buildSystemUri($this->connection->getBaseUri(), Api::TRANSACTION);
                $response = $this->connection->put(Api::addUriParam($uri, $this->getTransactionId()), $this->options);
                $data = json_decode((string)$response->getBody(), true);
                $this->id = $data['result']['id'];
                $this->status = $data['result']['status'];
                $this->connection->setDefaultHeaders([]);
                return;
            }

            throw new TransactionException("Transaction not started. Use 'begin' method to start transaction");
        } catch (BadResponseException $exception) {
            // An error was returned from server.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $transactionException = new TransactionException($response['errorMessage'], $exception, $response['errorNum']);
            throw $transactionException;
        }
    }

    /**
     * Abort transaction
     *
     * @throws TransactionException|BadResponseException|GuzzleException
     */
    public function abort()
    {
        try {
            if ($this->started) {
                $uri = Api::buildSystemUri($this->connection->getBaseUri(), Api::TRANSACTION);
                $response = $this->connection->delete(Api::addUriParam($uri, $this->getTransactionId()), $this->options);
                $data = json_decode((string)$response->getBody(), true);
                $this->id = $data['result']['id'];
                $this->status = $data['result']['status'];
                $this->connection->setDefaultHeaders([]);
                return;
            }

            throw new TransactionException("Transaction not started. Use 'begin' method to start transaction");
        } catch (BadResponseException $exception) {
            // An error was returned from server.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $transactionException = new TransactionException($response['errorMessage'], $exception, $response['errorNum']);
            throw $transactionException;
        }
    }
}

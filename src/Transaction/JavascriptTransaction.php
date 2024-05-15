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
 * Manages Javascript transactions
 *
 * @package ArangoDB\Transaction
 * @author Lucas S. Vieira
 */
final class JavascriptTransaction extends Transaction
{
    /**
     * Javascript code on a string to be executed on server
     *
     * @var string
     */
    protected $action;

    /**
     * JavascriptTransaction constructor.
     *
     * @param Connection $connection Connection object to use.
     * @param string $action JavaScript code to execute on server
     * @param array $options Transaction options.
     *
     * @throws TransactionException|InvalidParameterException|MissingParameterException
     */
    public function __construct(Connection $connection, string $action, array $options = [])
    {
        $this->action = $action;
        parent::__construct($connection, $options);
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
            throw new TransactionException($response['errorMessage'], $exception, $response['errorNum']);
        }
    }
}

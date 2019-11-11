<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\AQL\Contracts\StatementInterface;
use ArangoDB\Cursor\Exceptions\CursorException;

/**
 * Represents an ArangoDB cursor
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
class Cursor extends Base
{
    /**
     * URI to manage the cursor
     *
     * @var string
     */
    protected $uri = Api::CURSOR;

    /**
     * Statement to execute
     *
     * @var StatementInterface
     */
    protected $statement;

    /**
     * Default options query for the cursor
     * 'count', 'batchSize' and 'options' are, by default, leaved for the server defaults.
     *
     * @var array
     */
    protected $defaultOptions = [
        'cache' => false,
        'memoryLimit' => 0,
        'ttl' => 60,
    ];

    /**
     * Cursor constructor.
     *
     * @param Connection $connection Connection object to use
     * @param StatementInterface $statement Statement to perform on server
     * @param array $options Options for cursor
     *
     * @throws CursorException|GuzzleException
     */
    public function __construct(Connection $connection, StatementInterface $statement, array $options = [])
    {
        $this->statement = $statement;
        $this->connection = $connection;
        $this->result = new ArrayList();
        $this->options = array_merge($this->defaultOptions, $options);
        $this->create();
    }

    /**
     * Return an string representation of document
     *
     * @return string
     */
    public function __toString()
    {
        $object = [
            'id' => $this->getId(),
            'cached' => $this->isCached(),
            'hasMore' => $this->hasMore,
            'lenght' => $this->length,
            'extra' => $this->extra,
            'fetches' => $this->fetches,
        ];

        return print_r(array_merge($this->options, $object), true);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return $this->result->get($this->position);
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     * @throws CursorException
     */
    public function valid()
    {
        // We still have results.
        if ($this->position <= ($this->length - 1)) {
            return true;
        }

        // We have no more results.
        if (!$this->hasMore) {
            return false;
        }

        $this->fetch();
        return ($this->position <= ($this->length - 1));
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Deletes the cursor and frees the resources associated with it.
     *
     * @return bool
     * @throws CursorException
     */
    public function delete(): bool
    {
        try {
            if (!is_null($this->id)) {
                $response = $this->connection->delete(sprintf($this->uri . "/%s", $this->getId()));
                $data = json_decode((string)$response->getBody(), true);
                $this->id = null;
                $this->hasMore = false;
                return true;
            }

            return false;
        } catch (GuzzleException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $cursorException = new CursorException($response['errorMessage'], $exception, $response['errorNum']);
            throw $cursorException;
        }
    }

    /**
     * Create/initialize the cursor
     *
     * @throws CursorException|GuzzleException
     */
    protected function create(): void
    {
        try {
            $response = $this->connection->post(sprintf($this->uri), $this->getBody());
            $data = json_decode((string)$response->getBody(), true);
            $this->fetches++;
            $this->hasMore = $data[self::HAS_MORE];
            $this->appendResults($data[self::RESULT]);
            $this->length = count($data[self::RESULT]);
            $this->count = isset($data[self::COUNT]) ? $data[self::COUNT] : $this->length;
            $this->id = isset($data[self::ID]) ? $data[self::ID] : null;

            if (!$this->hasMore) {
                $this->id = null;
            }
        } catch (GuzzleException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $cursorException = new CursorException($response['errorMessage'], $exception, $response['errorNum']);
            throw $cursorException;
        }
    }

    /**
     * Fetch more results from the server
     *
     * @throws CursorException
     */
    public function fetch(): void
    {
        try {
            if (!is_null($this->id)) {
                $response = $this->connection->put(sprintf($this->uri . "/%s", $this->getId()));
                $data = json_decode((string)$response->getBody(), true);
                $this->fetches++;
                $this->extra = $data[self::EXTRA];
                $this->cached = $data[self::CACHED];
                $this->hasMore = $data[self::HAS_MORE];
                $this->appendResults($data[self::RESULT]);
                $this->length += count($data[self::RESULT]);
                return;
            }

            throw new CursorException("Cursor id is null");
        } catch (GuzzleException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $cursorException = new CursorException($response['errorMessage'], $exception, $response['errorNum']);
            throw $cursorException;
        }
    }

    /**
     * Return body for creating cursor
     *
     * @return array
     */
    protected function getBody(): array
    {
        return array_merge($this->options, ['query' => $this->statement->toAql()]);
    }

    /**
     * Append the results
     *
     * @param array $results
     */
    protected function appendResults(array $results)
    {
        $this->result->append(new ArrayList($results));
    }
}

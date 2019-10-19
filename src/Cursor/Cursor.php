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
     * Statement to query
     *
     * @var StatementInterface
     */
    public $statement;

    /**
     * Default options query for the cursor
     * 'count', 'batchSize' and 'options' are, by default, leaved for the server defaults
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
     * @param Connection $connection
     * @param StatementInterface $statement
     * @param array $options
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
     * @see \Iterator::current()
     */
    public function current()
    {
        return $this->result->get($this->position);
    }

    /**
     * @see \Iterator::next()
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @see \Iterator::key()
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @see \Iterator::valid()
     */
    public function valid()
    {
        // We still have results.
        if ($this->position < ($this->length - 1)) {
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
     * @see \Iterator::rewind()
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        return $this->fullCount;
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
                $response = $this->connection->delete(sprintf(Api::CURSOR . "/%s", $this->getId()));
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
            $response = $this->connection->post(sprintf(Api::CURSOR), $this->getBody());
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
     * @return void
     * @throws CursorException
     */
    protected function fetch(): void
    {
        try {
            if (!is_null($this->id)) {
                $response = $this->connection->put(sprintf(Api::CURSOR . "/%s", $this->getId()));
                $data = json_decode((string)$response->getBody(), true);
                $this->fetches++;
                $this->extra = $data[self::EXTRA];
                $this->cached = $data[self::CACHED];
                $this->hasMore = $data[self::HAS_MORE];
                $this->appendResults($data[self::RESULT]);
                $this->length = count($data[self::RESULT]);
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

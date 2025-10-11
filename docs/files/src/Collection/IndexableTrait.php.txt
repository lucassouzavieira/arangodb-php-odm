<?php

declare(strict_types=1);

namespace ArangoDB\Collection;

use ArangoDB\Http\Api;
use ArangoDB\Collection\Index\Factory;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\IndexException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Collection\Contracts\IndexInterface;
use ArangoDB\Exceptions\Database\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Indexable trait
 *
 * @package ArangoDB\Collection
 * @author Lucas S. Vieira
 */
trait IndexableTrait
{
    /**
     * Return all indexes of collection
     *
     * @return ArrayList
     * @throws DatabaseException|GuzzleException|InvalidParameterException|IndexException|MissingParameterException
     */
    public function getIndexes(): ArrayList
    {
        try {
            if ($this->isNew()) {
                return new ArrayList();
            }

            $uri = Api::addQuery(Api::INDEX, ['collection' => $this->getName()]);
            $response = $this->connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            $indexes = new ArrayList();
            foreach ($data['indexes'] as $index) {
                $indexes->push(Factory::factory($index));
            }

            return $indexes;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            throw new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
        }
    }

    /**
     * Create an index for collection
     * @param IndexInterface $index
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function addIndex(IndexInterface $index): bool
    {
        try {
            // If the collection is a new one,
            // we cannot add indexes on server.
            if ($this->isNew()) {
                return false;
            }

            $uri = Api::addQuery(Api::INDEX, ['collection' => $this->getName()]);
            $response = $this->connection->post($uri, $index->getCreateData());

            json_decode((string)$response->getBody(), true);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            throw new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
        }
    }

    /**
     * Drops an index of collection
     * @param IndexInterface $index
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function dropIndex(IndexInterface $index): bool
    {
        try {
            // If the collection is a new one, or the index,
            // we cannot drop it on server.
            if ($this->isNew() || $index->isNew()) {
                return false;
            }

            $uri = Api::addUriParam(Api::INDEX, $index->getId());
            $response = $this->connection->delete($uri);
            $data = json_decode((string)$response->getBody(), true);
            return !$data['error'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $databaseException;
        }
    }
}

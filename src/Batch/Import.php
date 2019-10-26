<?php
declare(strict_types=1);

namespace ArangoDB\Batch;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Class Import
 * Allow import multiple documents to a collection
 *
 * @package ArangoDB\Batch
 * @author Lucas S. Vieira
 */
abstract class Import
{
    /**
     * Import multiple documents from a JSON document
     *
     * @param Connection $connection Connection object to use.
     * @param string $collection Collection to import. Must exist on server.
     * @param string $jsonDocuments json documents as string representation
     *
     * @return array with the results of operation
     *
     * @throws ServerException|GuzzleException|InvalidParameterException|MissingParameterException
     * @link https://www.arangodb.com/docs/stable/http/bulk-imports-importing-self-contained.html
     */
    public static function importFromJsonDocuments(Connection $connection, string $collection, string $jsonDocuments): array
    {
        try {
            if (!$connection->getDatabase()->hasCollection($collection)) {
                throw new ServerException("Collection ($collection) doesn't exists.");
            }

            $uri = Api::addQuery(Api::IMPORT, ['type' => 'documents', 'collection' => $collection]);
            $response = $connection->customHttpRequest('POST', $uri, $jsonDocuments);
            $data = json_decode((string)$response->getBody(), true);
            return $data;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        } catch (DatabaseException $exception) {
            $serverException = new ServerException($exception->getMessage(), $exception, $exception->getCode());
            throw $serverException;
        }
    }
}

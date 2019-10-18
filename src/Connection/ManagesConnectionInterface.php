<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

/**
 * Interface for classes that use the Connection object
 *
 * @package ArangoDB\Handler
 * @author Lucas S. Vieira
 */
interface ManagesConnectionInterface
{
    /**
     * Set a connection object for the class
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection): void;
}

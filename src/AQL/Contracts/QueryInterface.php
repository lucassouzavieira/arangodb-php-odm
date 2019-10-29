<?php
declare(strict_types=1);

namespace ArangoDB\AQL\Contracts;

use ArangoDB\Cursor\Base;
use ArangoDB\Cursor\Contracts\CursorInterface;

/**
 * Interface QueryInterface
 *
 * @package ArangoDB\AQL
 * @author Lucas S. Vieira
 */
interface QueryInterface
{
    /**
     * Instantiate a new StatementInterface object with the specified query
     *
     * @param string $query
     * @return StatementInterface
     */
    public function query(string $query): StatementInterface;

    /**
     * Execute an statment on server and returns a cursor
     *
     * @param StatementInterface $statement
     * @return Base
     */
    public function execute(StatementInterface $statement): CursorInterface;
}

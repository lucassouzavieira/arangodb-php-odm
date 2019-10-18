<?php
declare(strict_types=1);

namespace ArangoDB\AQL\Contracts;

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
}

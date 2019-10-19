<?php
declare(strict_types=1);

namespace ArangoDB\AQL\Contracts;

/**
 * Interface StatementInterface
 *
 * @package ArangoDB\AQL
 * @author Lucas S. Vieira
 */
interface StatementInterface
{
    /**
     * Get the string representation of query
     *
     * @return string
     */
    public function __toString();

    /**
     * Binds a value to specified parameter name.
     *
     * @param string $parameter
     * @param $value
     *
     * @return bool
     */
    public function bindValue(string $parameter, $value): bool;

    /**
     * If the query has some alias (e.g: "@myparam" )
     * on it to receive a value after through binding
     *
     * @return bool
     */
    public function hasAliases(): bool;

    /**
     * Returns the query string
     *
     * @return string
     */
    public function getQuery(): string;

    /**
     * Get the bind vars
     *
     * @return array
     */
    public function getBindVars(): array;

    /**
     * 'Resolves' the query, returning the string after bind all params and values
     *
     * @return string
     */
    public function toAql(): string;
}

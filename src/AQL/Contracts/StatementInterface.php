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
     * Binds a value to specified parameter name.
     *
     * @param string $parameter
     * @param $value
     *
     * @return bool
     */
    public function bindValue(string $parameter, $value): bool;

    /**
     * 'Resolves' the query, returning the string after bind all params and values
     *
     * @return string
     */
    public function toAql(): string;
}

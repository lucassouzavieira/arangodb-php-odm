<?php
declare(strict_types=1);

namespace ArangoDB\Cursor\Contracts;

/**
 * Common interface for Cursors
 *
 * @package ArangoDB\Cursor\Contracts
 * @author Lucas S. Vieira
 */
interface CursorInterface extends \Iterator, \Countable
{
    /**
     * Deletes the cursor and frees the resources associated with it.
     *
     * @return bool
     */
    public function delete(): bool;
}

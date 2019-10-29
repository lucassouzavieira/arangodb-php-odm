<?php
declare(strict_types=1);

namespace ArangoDB\Document;

/**
 * Represents an ArangoDB Edge document
 *
 * @package ArangoDB\Document
 * @author Lucas S. Vieira
 */
class Edge extends Document
{
    /**
     * The edges 'to' attribute
     *
     * @var string
     */
    protected $to;

    /**
     * The edges 'from' attribute
     *
     * @var string
     */
    protected $from;
}

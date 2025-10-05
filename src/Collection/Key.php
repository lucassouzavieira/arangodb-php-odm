<?php

namespace ArangoDB\Collection;

/**
 * Groups the available key generations for new collections
 *
 * @package ArangoDB\Collection
 * @author Lucas S. Vieira
 */
class Key
{
    public const string TRADITIONAL = 'traditional';
    public const string AUTOINCREMENT = 'autoincrement';
    public const string PADDED = 'padded';
    public const string UUID = 'uuid';
}

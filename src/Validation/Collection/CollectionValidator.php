<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Collection;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Validate the collection options values. <br>
 * Used for avoid client errors when creating or updating collections
 *
 * @package ArangoDB\Validation\Collection
 * @author Lucas S. Vieira
 */
class CollectionValidator extends Validator
{
    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [
        'name'
    ];

    /**
     * Optional keys
     *
     * @var array
     */
    protected $canHave = [];

    /**
     * CollectionValidator constructor.
     *
     * @param array $options Attributes to validate.
     */
    public function __construct(array $options)
    {
        $this->data = $options;
    }

    /**
     * Rules for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => Rules::string(),
            'journalSize' => Rules::integer(),
            'replicationFactor' => Rules::equalsOrGreaterThan(1),
            'waitForSync' => Rules::boolean(),
            'doCompact' => Rules::boolean(),
            'shardingStrategy' => Rules::in(['community-compat', 'enterprise-compat', 'enterprise-smart-edge-compat', 'hash', 'enterprise-hash-smart-edge']),
            'isVolatile' => Rules::boolean(),
            'shardKeys' => Rules::arr(),
            'numberOfShards' => Rules::equalsOrGreaterThan(1),
            'isSystem' => Rules::boolean(),
            'type' => Rules::in([2, 3]),
            'keyOptions' => Rules::arr(),
            'indexBuckets' => Rules::in([2, 4, 8, 16, 32, 64, 128, 256, 512, 1024])
        ];
    }
}

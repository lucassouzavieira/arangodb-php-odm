<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Graph;

use ArangoDB\Exceptions\Exception;
use ArangoDB\Graph\EdgeDefinition;
use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Validate the graph options values. <br>
 * Used for avoid client errors when creating or updating graphs
 *
 * @package ArangoDB\Validation\Graph
 * @author Lucas S. Vieira
 */
class GraphValidator extends Validator
{
    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [
        'edgeDefinitions',
    ];

    /**
     * Optional keys
     *
     * @var array
     */
    protected $canHave = [
        '_id', '_rev', '_key',
        'numberOfShards', 'replicationFactor', 'minReplicationFactor', 'isSmart'
    ];

    /**
     * Rules for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            '_id' => Rules::string(),
            '_key' => Rules::string(),
            '_rev' => Rules::string(),
            'numberOfShards' => Rules::equalsOrGreaterThan(1),
            'replicationFactor' => Rules::equalsOrGreaterThan(1),
            'minReplicationFactor' => Rules::equalsOrGreaterThan(1),
            'isSmart' => Rules::boolean(),
            'edgeDefinitions' => Rules::callbackValidation(self::validateEdgeDefinitionsParameter())
        ];
    }

    /**
     * Validate 'edgeDefinitions' param
     *
     * @return \Closure
     */
    protected static function validateEdgeDefinitionsParameter()
    {
        /**
         * @param $edgeDefinitions array|ArrayList
         * @return bool
         */
        return function ($edgeDefinitions) {
            foreach ($edgeDefinitions as $key => $edgeDefinition) {
                if ($edgeDefinition instanceof EdgeDefinition) {
                    continue;
                }

                if (!(isset($edgeDefinition['collection']) && isset($edgeDefinition['to'])
                    && isset($edgeDefinition['from']))) {
                    $message = "'edgeDefinition[$key]' parameter must contains the following keys: 'collection', 'from' and 'to'";
                    throw new Exception($message);
                };

                $vertexesValidator = Rules::arr();
                $collectionValidator = Rules::string();

                if (!$collectionValidator->isValid($edgeDefinition['collection'])) {
                    throw new InvalidParameterException("edgeDefinition['collection']", $edgeDefinition['collection']);
                }

                if (!$vertexesValidator->isValid($edgeDefinition['from'])) {
                    throw new InvalidParameterException("edgeDefinition['from']", $edgeDefinition['collection']);
                }

                if (!$vertexesValidator->isValid($edgeDefinition['to'])) {
                    throw new InvalidParameterException("edgeDefinition['to']", $edgeDefinition['collection']);
                }
            }

            return true;
        };
    }
}

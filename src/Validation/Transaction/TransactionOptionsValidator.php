<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Transaction;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Exceptions\TransactionException;

/**
 * Validate transactions options values. <br>
 * Used for avoid client errors when running javascript or stream transactions.
 *
 * @package ArangoDB\Validation\Transaction
 */
class TransactionOptionsValidator extends Validator
{
    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [
        'collections'
    ];

    /**
     * Optional keys
     *
     * @var array
     */
    protected $canHave = [
        'maxTransactionSize', 'waitForSync', 'allowImplicit', 'lockTimeout'
    ];

    /**
     * Rules for connection
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'collections' => Rules::callbackValidation(self::validateCollectionsParameter()),
            'maxTransactionSize' => Rules::equalsOrGreaterThan(0),
            'waitForSync' => Rules::boolean(),
            'allowImplicit' => Rules::boolean(),
            'lockTimeout' => Rules::equalsOrGreaterThan(0)
        ];
    }

    /**
     * Validate 'collections' param
     *
     * @return \Closure
     */
    protected static function validateCollectionsParameter()
    {
        return function (array $collections) {
            // Must contains one or more of following attributes:
            // 'write', 'read' or 'exclusive'
            if (!isset($collections['write']) && !isset($collections['read']) && !isset($collections['exclusive'])) {
                // None of required keys on 'collections'.
                $message = "'collections' attribute must contains one or more of following attributes: 'write', 'read' or 'exclusive'";
                throw new TransactionException($message);
            }

            $validator = Rules::string();
            $names = [];

            if (isset($collections['write'])) {
                $names = array_merge($names, array_values($collections['write']));
            }

            if (isset($collections['read'])) {
                $names = array_merge($names, array_values($collections['read']));
            }

            if (isset($collections['exclusive'])) {
                $names = array_merge($names, array_values($collections['exclusive']));
            }

            foreach ($names as $collection) {
                if (!$validator->isValid($collection)) {
                    throw new TransactionException("Invalid param type " . gettype($collection) . " on defined collections to transaction");
                }
            }

            return true;
        };
    }
}

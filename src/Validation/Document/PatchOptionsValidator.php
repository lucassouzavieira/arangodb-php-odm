<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Document;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Class PatchOptionsValidator
 *
 * @package ArangoDB\Validation\Document
 * @author Lucas S. Vieira
 */
class PatchOptionsValidator extends Validator
{
    /**
     * @var array
     */
    protected $canHave = [
        'keepNull', 'mergeObjects', 'waitForSync', 'ignoreRevs', 'returnOld', 'returnNew'
    ];

    /**
     * Rules for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'keepNull' => Rules::boolean(),
            'mergeObjects' => Rules::boolean(),
            'waitForSync' => Rules::boolean(),
            'ignoreRevs' => Rules::boolean(),
            'returnOld' => Rules::boolean(),
            'returnNew' => Rules::boolean()
        ];
    }
}

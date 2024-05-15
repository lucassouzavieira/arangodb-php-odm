<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Document;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Class UpdateOptionsValidator
 *
 * @package ArangoDB\Validation\Document
 * @author Lucas S. Vieira
 */
class UpdateOptionsValidator extends Validator
{
    /**
     * @var array
     */
    protected $canHave = [
        'waitForSync', 'ignoreRevs', 'returnOld', 'returnNew'
    ];

    /**
     * UpdateOptionsValidator constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
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
            'waitForSync' => Rules::boolean(),
            'ignoreRevs' => Rules::boolean(),
            'returnOld' => Rules::boolean(),
            'returnNew' => Rules::boolean(),
            'silent' => Rules::boolean()
        ];
    }
}

<?php
declare(strict_types=1);

namespace ArangoDB\Validation;

/**
 * Interface ValidatorInterface
 *
 * @package ArangoDB\Validation
 * @copyright 2019 Lucas S. Vieira
 */
interface ValidatorInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * Validate data
     * @return bool
     */
    public function validate(): bool;
}

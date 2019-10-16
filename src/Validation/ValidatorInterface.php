<?php
declare(strict_types=1);

namespace ArangoDB\Validation;

/**
 * Interface ValidatorInterface
 *
 * @package ArangoDB\Validation
 * @author Lucas S. Vieira
 */
interface ValidatorInterface
{
    /**
     * Rules for validation
     *
     * @return array
     */
    public function rules(): array;

    /**
     * Validate data
     *
     * @return bool
     */
    public function validate(): bool;
}

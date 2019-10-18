<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\Validation\Rules\Rules;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\ValidatorInterface;
use ArangoDB\AQL\Contracts\StatementInterface;
use ArangoDB\AQL\Exceptions\StatementException;

/**
 * Represents an prepared AQL Statement
 *
 * @package ArangoDB\AQL
 * @author Lucas S. Vieira
 */
class Statement implements StatementInterface
{
    /**
     * The query string
     *
     * @var string
     */
    protected $query;

    /**
     * Validator for binding values or params
     *
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Parameters set with '@' in query
     *
     * @var array
     */
    protected $queryParameters = [];

    /**
     * Contains all references calling 'bindValue' method
     *
     * @var BindContainer
     */
    protected $valuesContainer;

    /**
     * Formats to format output string
     *
     * @var array
     */
    protected $formats = [
        'float' => "%F",
        'integer' => "%d",
        'string' => "'%s'",
        'boolean' => "%s"
    ];

    /**
     * Statement constructor.
     *
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
        $this->processQueryStr();
        $this->validator = Rules::isPrimitive();
        $this->valuesContainer = new ArrayList();
    }

    /**
     * Binds a value to specified parameter name.
     *
     * @param string $parameter
     * @param $value
     *
     * @return bool
     */
    public function bindValue(string $parameter, $value): bool
    {
        if ($this->hasParam($parameter)) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->valuesContainer->put($parameter, $value);
            return true;
        }

        return false;
    }

    /**
     * 'Resolves' the query, returning the string after bind all params and values
     *
     * @return string
     * @throws StatementException
     * @todo escape strings
     */
    public function toAql(): string
    {
        $query = $this->query;

        foreach ($this->queryParameters as $parameter) {
            if (!$this->valuesContainer->has($parameter)) {
                throw new StatementException("Parameter ($parameter) was not defined for this statement");
            }

            $query = str_replace($parameter, $this->formats[gettype($this->valuesContainer->get($parameter))], $query);
        }

        return sprintf($query, ...$this->valuesContainer->values());
    }

    /**
     * Check if parameter exists
     *
     * @param string $parameter
     *
     * @return bool
     */
    protected function hasParam(string $parameter): bool
    {
        return in_array($parameter, $this->queryParameters);
    }

    /**
     * Find occurrences of bind params in query string
     *
     * @return void
     */
    protected function processQueryStr(): void
    {
        $matches = [];
        $regex = '~(@\w+)~';
        preg_match_all($regex, $this->query, $matches, PREG_PATTERN_ORDER);
        $this->queryParameters = array_pop($matches);
    }
}

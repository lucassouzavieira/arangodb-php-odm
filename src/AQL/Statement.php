<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\ValidatorInterface;
use ArangoDB\AQL\Contracts\StatementInterface;
use ArangoDB\AQL\Exceptions\StatementException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

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
    protected $container;

    /**
     * Formats to format output string
     *
     * @var array
     */
    protected $formats = [
        'float' => "%F",
        'integer' => "%d",
        'string' => "'%s'",
        'boolean' => "%s",
        'collection' => "%s"
    ];

    /**
     * Statement constructor.
     *
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
        $this->processQueryParameters();
        $this->validator = Rules::isPrimitive();
        $this->container = new BindContainer();
    }

    /**
     * String representation of query
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getQuery();
    }

    /**
     * Binds a value to specified parameter name.
     *
     * @param string $parameter
     * @param $value
     *
     * @return bool
     * @throws InvalidParameterException
     */
    public function bindValue(string $parameter, $value): bool
    {
        if ($this->hasParam($parameter)) {
            $this->container->put($parameter, $value);
            return true;
        }

        return false;
    }

    /**
     * If the query has some alias on it to receive an value after through binding
     *
     * @return bool
     */
    public function hasAliases(): bool
    {
        return (bool)count($this->queryParameters);
    }

    /**
     * Returns the query string
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Get the bind vars
     *
     * @return array
     */
    public function getBindVars(): array
    {
        return $this->container->getAll();
    }

    /**
     * 'Resolves' the query, returning the string after bind all params and values
     *
     * @return string
     * @throws StatementException
     */
    public function toAql(): string
    {
        $query = $this->query;

        foreach ($this->queryParameters as $parameter) {
            if (!$this->container->has($parameter)) {
                throw new StatementException("Parameter ($parameter) was not defined for this statement");
            }

            $query = str_replace($parameter, $this->output($parameter), $query);
        }

        return $query;
    }

    /**
     * Returns the proper output formatted given parameter
     *
     * @param string $parameter
     * @return string
     */
    protected function output(string $parameter)
    {
        $value = $this->container->get($parameter);
        $format = $this->formats[gettype($value)];

        if ($parameter === '@collection') {
            $format = $this->formats['collection'];
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return sprintf($format, $value);
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
    protected function processQueryParameters(): void
    {
        $matches = [];
        $regex = '~(@\w+)~';
        preg_match_all($regex, $this->query, $matches, PREG_PATTERN_ORDER);
        $this->queryParameters = array_pop($matches);
    }
}

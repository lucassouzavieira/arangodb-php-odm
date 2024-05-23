<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\ValidatorInterface;
use ArangoDB\AQL\Contracts\StatementInterface;
use ArangoDB\AQL\Exceptions\StatementException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a prepared AQL Statement
 *
 * @package ArangoDB\AQL
 * @author  Lucas S. Vieira
 */
class Statement implements StatementInterface
{
    /**
     * The query string
     *
     * @var string
     */
    protected string $query;

    /**
     * If the statement use an alias for collection,
     * store the alias here
     * (e.g "@collection", "@coll")
     *
     * @var string
     */
    protected string $collectionAlias = '';

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
    protected array $queryParameters = [];

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
    protected array $formats = [
        'float' => "%F",
        'integer' => "%d",
        'string' => "'%s'",
        'boolean' => "%s",
        'collection' => "%s"
    ];

    /**
     * Statement constructor.
     *
     * @param string $query AQL query string.
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
     * @param string $parameter Parameter name.
     * @param mixed $value Value for parameter.
     *
     * @return bool True if the parameter has an alias on query string. False otherwise.
     *
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
     *
     * @throws StatementException
     */
    public function toAql(): string
    {
        $query = $this->query;

        foreach ($this->queryParameters as $parameter) {
            if (!$this->container->has($parameter)) {
                throw new StatementException(sprintf("Parameter (%s) was not defined for this statement", $parameter));
            }

            $query = str_replace($parameter, $this->output($parameter), $query);
        }

        return mb_convert_encoding($query, "UTF-8", mb_detect_encoding($query));
    }

    /**
     * Returns the proper output formatted given parameter
     *
     * @param string $parameter Parameter name.
     *
     * @return string
     */
    private function output(string $parameter): string
    {
        $value = $this->container->get($parameter);
        $format = $this->formats[gettype($value)];

        if ($this->isCollectionAlias($parameter)) {
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
     * @param string $parameter Parameter name.
     *
     * @return bool
     */
    private function hasParam(string $parameter): bool
    {
        return in_array($parameter, $this->queryParameters);
    }

    /**
     * Find occurrences of bind params in query string
     *
     * @return void
     */
    private function processQueryParameters(): void
    {
        // Check if a collection alias was defined
        $matches = [];
        preg_match_all('~(IN @\w+)~', $this->query, $matches, PREG_PATTERN_ORDER);
        $matches = array_pop($matches);

        if (count($matches)) {
            // Stores if found.
            $collection = [];
            $match = array_pop($matches);
            preg_match_all('~(@\w+)~', $match, $collection, PREG_PATTERN_ORDER);
            $occurrence = array_shift($collection);
            $this->collectionAlias = array_pop($occurrence);
        }

        $matches = [];
        preg_match_all('~(@\w+)~', $this->query, $matches, PREG_PATTERN_ORDER);
        $this->queryParameters = array_pop($matches);
    }

    /**
     * Verify if an alias is defined for collection aliasing
     *
     * @param string $alias The candidate alias to be checked.
     *
     * @return bool
     */
    private function isCollectionAlias(string $alias = '@collection'): bool
    {
        return $this->collectionAlias === $alias;
    }
}

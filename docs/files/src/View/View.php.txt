<?php
declare(strict_types=1);

namespace ArangoDB\View;

/**
 * Represents a View on ArangoDB server.
 *
 * @package ArangoDB\View
 * @author Lucas S. Vieira
 */
class View
{
    /**
     * View ID.
     */
    protected string $id;

    /**
     * Globally unique ID.
     */
    protected string $globallyUniqueId;

    /**
     * The name of the View.
     */
    protected string $name;

    /**
     * The type of View
     */
    protected string $type;

    /**
     * If the View is a new one or a representation of an existing view on server.
     */
    protected bool $isNew;

    /**
     * The view links
     *
     * @var mixed
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#link-properties
     */
    protected $links;

    /**
     * View properties.
     * Check ArangoDB Server documentation for more details.
     *
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected array $attributes = [];

    /**
     * View properties default values.
     * Check ArangoDB Server documentation for more details.
     *
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected array $defaults = [
        'writebufferActive' => 0,
        'writebufferSizeMax' => 33554432,
        'writebufferIdle' => 64,
        'commitIntervalMsec' => 1000,
        'consolidationIntervalMsec' => 10000,
        'consolidationPolicy' => [],
        'cleanupIntervalStep' => 2
    ];

    /**
     * View constructor.
     *
     * @param string $name The name of View
     * @param string $type The type of View
     * @param array $attributes View attributes
     *
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    public function __construct(string $name, string $type = "arangosearch", array $attributes = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->attributes = array_merge($this->defaults, $attributes);

        $this->isNew = true;
        $this->id = isset($this->attributes['id']) ? $this->attributes['id'] : '';
        $this->globallyUniqueId = isset($this->attributes['globallyUniqueId']) ? $this->attributes['globallyUniqueId'] : '';

        if ($this->id && $this->globallyUniqueId) {
            $this->isNew = false;
        }
    }

    /**
     * Returns true if is a new object
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }
}

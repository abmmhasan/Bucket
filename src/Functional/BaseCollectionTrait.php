<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Functional;

use ArrayIterator;
use JsonSerializable;
use Traversable;

trait BaseCollectionTrait
{
    /**
     * Holds the underlying array data for the collection.
     */
    protected array $data = [];

    /**
     * Construct with optional initial data array.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Pipeline / Finalization
    |--------------------------------------------------------------------------
    */

    /**
     * Start a chain of operations on this collection's array data.
     * Creates a new "Pipeline" instance that allows chainable transformations.
     */
    public function process(): Pipeline
    {
        // Provide a copy (or reference) of $this->data to the pipeline
        return new Pipeline($this->data, $this);
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Return a fresh DataCollection containing the current array.
     *
     * Possibly used if you do in-place modifications and want a new final collection.
     */
    public function get(): static
    {
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Interface Implementations (ArrayAccess, Iterator, Countable, JsonSerializable)
    |--------------------------------------------------------------------------
    */

    // ArrayAccess
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    // Iterator
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function key(): string|int|null
    {
        return key($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    // Countable
    public function count(): int
    {
        return count($this->data);
    }

    // JsonSerializable
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

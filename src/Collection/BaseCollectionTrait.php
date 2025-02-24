<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Collection;

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
     * Constructor. Initializes the collection with the given array data.
     *
     * @param array $data The initial data for the collection.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    /**
     * Create and return a new Pipeline instance using the current collection's data.
     *
     * This method initializes a processing pipeline, allowing method chaining
     * for array transformations or operations.
     *
     * @return Pipeline A new pipeline instance for further processing.
     */
    public function process(): Pipeline
    {
        return new Pipeline($this->data, $this);
    }

    /**
     * Set the underlying array data for the collection.
     * This method is chainable.
     *
     * @param array $data The new data to set.
     * @return static The current collection instance, for chaining.
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }


    /**
     * Retrieve the current collection instance.
     *
     * This method returns the current collection object itself, allowing
     * for further method chaining or operations on the existing collection.
     *
     * @return static The current collection instance.
     */
    public function get(): static
    {
        return $this;
    }

    /**
     * Magic getter to retrieve an item via property access: $collection->key
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->offsetGet($key);
    }

    /**
     * Magic setter to set an item via property access: $collection->key = value
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Magic isset to check existence of an item via property access: isset($collection->key)
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Magic unset to remove an item via property access: unset($collection->key)
     *
     * @param string $key
     */
    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * Convert various structures (collections, Traversable, etc.) to an array.
     *
     * @param mixed $items
     * @return array
     */
    public function getArrayableItems(mixed $items): array
    {
        return match (true) {
            $items instanceof self => $items->items(),
            $items instanceof JsonSerializable => $items->jsonSerialize(),
            $items instanceof Traversable => iterator_to_array($items),
            default => (array)$items
        };
    }

    /**
     * Return the raw array of items in this collection.
     *
     * @return array
     */
    public function items(): array
    {
        return $this->data;
    }

    /**
     * Get the collection of items as a JSON string.
     *
     * @param int $options JSON encoding options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Convert the collection to a JSON string when treated as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get the collection of items as a plain array (same as items()).
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Return an array of all the keys in the collection.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Clear all items from the collection.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
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


    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(
            static function ($value) {
                if ($value instanceof JsonSerializable) {
                    return $value->jsonSerialize();
                }
                return $value;
            },
            $this->data,
        );
    }
}

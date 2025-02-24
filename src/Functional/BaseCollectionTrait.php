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

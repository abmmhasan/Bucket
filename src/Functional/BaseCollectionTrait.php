<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Functional;

use ArrayIterator;
use JsonSerializable;
use Traversable;

/**
 * Trait BaseCollectionTrait
 *
 * Provides core collection functionality (ArrayAccess, Iterator,
 * Countable, JsonSerializable) for array-based classes.
 */
trait BaseCollectionTrait
{
    /**
     * Underlying storage for the collection.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Base constructor for classes using this trait.
     *
     * @param array $data Initial data for the collection
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
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
     * @param mixed  $value
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
     * Merge another collection or array into this one.
     *
     * @param BucketCollection|HookedCollection|array $data Another collection instance or raw array
     * @return static
     */
    public function merge(BucketCollection|HookedCollection|array $data): static
    {
        // Convert collection to array if needed
        if ($data instanceof BucketCollection || $data instanceof HookedCollection) {
            $data = $data->items();
        }

        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Map each item using a callback and return the resulting array (not a new collection).
     *
     * @param callable $callback fn($value, $key): mixed
     * @return array
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->data);
    }

    /**
     * Create a new collection instance from an array after mapping each item with a callback.
     *
     * @param array    $items An array of raw items
     * @param callable $fn    fn($value, $key): mixed
     * @return static
     */
    public static function fromMap(array $items, callable $fn): static
    {
        return new static(array_map($fn, $items));
    }

    /**
     * Reduce the collection to a single value using a callback.
     *
     * @param callable $fn       fn($carry, $value): mixed
     * @param mixed    $initial  Initial accumulator value
     * @return mixed
     */
    public function reduce(callable $fn, mixed $initial): mixed
    {
        return array_reduce($this->data, $fn, $initial);
    }

    /**
     * Apply a callback to each item of the collection.
     *
     * @param callable $fn fn($value, $key): void
     */
    public function each(callable $fn): void
    {
        array_walk($this->data, $fn);
    }

    /**
     * Check if at least one item in the collection passes the callback test.
     *
     * @param callable $fn fn($value, $key, $allData): bool
     * @return bool
     */
    public function exists(callable $fn): bool
    {
        foreach ($this->data as $index => $element) {
            if ($fn($element, $index, $this->data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Filter the collection by a callback, returning a new collection instance.
     *
     * @param callable $fn fn($value, $key): bool
     * @return static A new collection with filtered items
     */
    public function filter(callable $fn): static
    {
        $filtered = array_filter($this->data, $fn, ARRAY_FILTER_USE_BOTH);
        return new static($filtered);
    }

    /**
     * Return the first item in the collection.
     *
     * @return mixed
     */
    public function first(): mixed
    {
        return reset($this->data);
    }

    /**
     * Return the last item in the collection.
     *
     * @return mixed
     */
    public function last(): mixed
    {
        return end($this->data);
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
            default => (array) $items
        };
    }

    /**
     * Replace the internal data with a new array.
     *
     * @param array $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get all values from the collection (without keys).
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->data);
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
            $this->data
        );
    }

    /**
     * Get the collection of items as a plain array (same as items()).
     *
     * @return array
     */
    public function toArray(): array
    {
        // If you need a deeper serialization, you could do it here.
        return array_map(static fn ($value) => $value, $this->data);
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
     * Convert the collection to a JSON string when treated as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
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
     * Get an iterator for the items (for foreach loops).
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * ArrayAccess: Return the item at the given key/offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * ArrayAccess: Set the item at the given key/offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * ArrayAccess: Check if an item exists at the given offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * ArrayAccess: Remove the item at the given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Iterator: Rewind the iterator to the first item.
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Iterator: Return the current item.
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Iterator: Return the key of the current element.
     *
     * @return string|int|null
     */
    public function key(): string|int|null
    {
        return key($this->data);
    }


    /**
     * Iterator: Move forward to next element.
     *
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Iterator: Check if current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * Countable: Return the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
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
}

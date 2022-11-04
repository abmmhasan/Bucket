<?php

namespace AbmmHasan\Bucket\traits;

use AbmmHasan\Bucket\Functional\Arrject;
use AbmmHasan\Bucket\Functional\Belt;
use ArrayIterator;
use JsonSerializable;
use Traversable;

trait FunctionalTrait
{
    protected array $data = [];

    /**
     * Constructor.
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Gets an item.
     *
     * @param string $key Key
     * @return mixed Value
     */
    public function __get(string $key)
    {
        return $this->offsetGet($key) ?? null;
    }

    /**
     * Set an item.
     *
     * @param string $key Key
     * @param mixed $value Value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Checks if an item exists.
     *
     * @param string $key Key
     * @return bool Item status
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Removes an item.
     *
     * @param string $key Key
     */
    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * Add more data to the existing collection
     *
     * @param Arrject|Belt|array $data
     * @return Arrject|Belt
     */
    public function merge(Arrject|Belt|array $data): Arrject|Belt
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Filter exiting data by callback
     *
     * @param callable $callback
     * @return Arrject|Belt
     */
    public function filter(callable $callback): Arrject|Belt
    {
        return $this->setData(array_filter($this->data, $callback));
    }

    /**
     * Map data by callback
     *
     * @param callable $callback
     * @return Arrject|Belt
     */
    public function map(callable $callback): Arrject|Belt
    {
        return $this->setData(array_map($callback, $this->data));
    }

    /**
     * Results array of items from Arrayable Collection.
     *
     * @param mixed $items
     * @return array
     */
    public function getArrayableItems(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->getData();
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    /**
     * Sets the collection data.
     *
     * @param array $data
     * @return Arrject|Belt
     */
    public function setData(array $data): Arrject|Belt
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the collection data.
     *
     * @return array Collection data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(
            function ($value) {
                if ($value instanceof JsonSerializable) {
                    return $value->jsonSerialize();
                }
                return $value;
            },
            $this->data
        );
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(
            function ($value) {
                return $value;
            },
            $this->data
        );
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Gets an item at the offset.
     *
     * @param mixed $offset Offset
     * @return mixed Value
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Sets an item at the offset.
     *
     * @param mixed $offset Offset
     * @param mixed $value Value
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
     * Checks if an item exists at the offset.
     *
     * @param mixed $offset Offset
     * @return bool Item status
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * Removes an item at the offset.
     *
     * @param mixed $offset Offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Resets the collection.
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Gets current collection item.
     *
     * @return mixed Value
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Gets current collection key.
     *
     * @return string|int|null Value
     */
    public function key(): string|int|null
    {
        return key($this->data);
    }

    /**
     * Gets the next collection value.
     *
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Checks if the current collection key is valid.
     *
     * @return bool Key status
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * Gets the size of the collection.
     *
     * @return int Collection size
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Gets the item keys.
     *
     * @return array Collection keys
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Removes all items from the collection.
     */
    public function clear(): void
    {
        $this->data = [];
    }
}

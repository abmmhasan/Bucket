<?php

namespace AbmmHasan\Bucket\Functional;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class Belt implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    use Common;

    private array $hooks;

    /**
     * Gets an item at the offset.
     *
     * @param mixed $offset Offset
     * @return mixed Value
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->processValue($offset, $this->data[$offset], 'get');
    }

    /**
     * Sets an item at the offset.
     *
     * @param mixed $offset Offset
     * @param mixed $value Value
     */
    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->data[$offset] = $this->processValue($offset, $value, 'set');
    }

    /**
     * Set on-get rule
     *
     * @param mixed $offset
     * @param callable $callback
     * @return $this
     */
    public function onGet(mixed $offset, callable $callback): static
    {
        return $this->addHook($offset, 'get', $callback);
    }

    /**
     * Set on-set rule
     *
     * @param mixed $offset
     * @param callable $callback
     * @return $this
     */
    public function onSet(mixed $offset, callable $callback): static
    {
        return $this->addHook($offset, 'set', $callback);
    }

    /**
     * Add callable hook
     *
     * @param mixed $offset
     * @param string $direction
     * @param callable $callback
     * @return $this
     */
    protected function addHook(mixed $offset, string $direction, callable $callback): static
    {

        $name = $this->getHookName($offset, $direction);
        if (!in_array($callback, $this->hooks[$name] ?? [])) {
            $this->hooks[$name][] = $callback;
        }
        return $this;
    }

    /**
     * Get the value after processing
     *
     * @param mixed $offset
     * @param mixed $value
     * @param string $direction
     * @return mixed
     */
    protected function processValue(mixed $offset, mixed $value, string $direction): mixed
    {
        $hooks = $this->hooks[$this->getHookName($offset, $direction)] ?? [];
        foreach ($hooks as $hook) {
            $value = $hook($value);
        }
        return $value;
    }

    /**
     * Get hook name
     *
     * @param string $hook
     * @param string $direction
     * @return string
     */
    protected function getHookName(string $hook, string $direction): string
    {
        return $hook . "-" . $direction;
    }
}
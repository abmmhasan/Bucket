<?php

namespace AbmmHasan\Bucket\traits;

trait Hook
{
    protected array $hooks;

    /**
     * Set on-get rule
     *
     * @param string $offset
     * @param callable $callback
     * @return $this
     */
    public function onGet(string $offset, callable $callback): static
    {
        return $this->addHook($offset, 'get', $callback);
    }

    /**
     * Set on-set rule
     *
     * @param string $offset
     * @param callable $callback
     * @return $this
     */
    public function onSet(string $offset, callable $callback): static
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

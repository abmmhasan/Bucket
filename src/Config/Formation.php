<?php

namespace AbmmHasan\Bucket\Config;

use AbmmHasan\Bucket\Array\Dotted;
use AbmmHasan\Bucket\traits\ConfigTrait;
use AbmmHasan\Bucket\traits\Hook;
use AbmmHasan\OOF\Fence\Multi;

class Formation
{
    use ConfigTrait, Multi, Hook;

    /**
     * Get configuration by key
     *
     * @param int|string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(int|string $key = null, mixed $default = null): mixed
    {
        return $this->processValue(
            $key,
            Dotted::get($this->items, $key, $default),
            'get'
        );
    }

    /**
     * Set configuration value by key
     *
     * @param string|null $key
     * @param mixed|null $value
     * @return bool
     */
    public function set(string $key = null, mixed $value = null): bool
    {
        return Dotted::set(
            $this->items,
            $key,
            $this->processValue($key, $value, 'set')
        );
    }
}

<?php

declare(strict_types=1);

namespace AbmmHasan\Bucket\Config;

use AbmmHasan\Bucket\Array\DotNotation;
use AbmmHasan\Bucket\traits\HookTrait;
use AbmmHasan\InterMix\Fence\Multi;

/**
 * Class DynamicConfig
 *
 * Provides dynamic configuration handling with hooks for
 * "on get" and "on set" value transformations.
 * Inherits base config operations from BaseConfigTrait
 * and advanced multi-config features from the Multi trait.
 */
class DynamicConfig
{
    use BaseConfigTrait;
    use Multi;
    use HookTrait;

    /**
     * Retrieve a configuration value by dot-notation key, applying any "on get" hooks.
     *
     * @param int|string|null $key     Dot-notation key (or null for entire config)
     * @param mixed|null      $default Default value if key not found
     * @return mixed
     */
    public function get(int|string $key = null, mixed $default = null): mixed
    {
        // First retrieve from the config array
        $value = DotNotation::get($this->items, $key, $default);

        // Then apply any "on get" hook transformations
        return $this->processValue($key, $value, 'get');
    }

    /**
     * Set a configuration value by dot-notation key, applying any "on set" hooks.
     *
     * @param string|null $key   Dot-notation key (null replaces entire config)
     * @param mixed|null  $value The value to set
     * @return bool True on success
     */
    public function set(?string $key = null, mixed $value = null): bool
    {
        // Apply "on set" hook transformations
        $processedValue = $this->processValue($key, $value, 'set');

        // Update the config array using DotNotation
        return DotNotation::set($this->items, $key, $processedValue);
    }
}

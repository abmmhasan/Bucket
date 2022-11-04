<?php

namespace AbmmHasan\Bucket\Functional;

use AbmmHasan\Bucket\traits\FunctionalTrait;
use AbmmHasan\Bucket\traits\Hook;
use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class Belt implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    use FunctionalTrait, Hook;

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
}

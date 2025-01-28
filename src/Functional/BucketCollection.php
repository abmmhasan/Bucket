<?php

declare(strict_types=1);

namespace AbmmHasan\Bucket\Functional;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

/**
 * Class BucketCollection
 *
 * A simple array-based collection that implements common
 * interfaces (ArrayAccess, Iterator, Countable, JsonSerializable).
 * Inherits most of its behavior from BaseCollectionTrait.
 */
class BucketCollection implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    use BaseCollectionTrait;
}

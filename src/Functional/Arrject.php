<?php


namespace AbmmHasan\Bucket\Functional;


use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class Arrject implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    use Common;
}
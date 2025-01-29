<?php

declare(strict_types=1);

use Infocyph\ArrayKit\Array\ArraySingle;

it('checks if a key exists in a single-dimensional array', function () {
    $data = ['one' => 1, 'two' => 2];
    expect(ArraySingle::exists($data, 'two'))
        ->toBeTrue()
        ->and(ArraySingle::exists($data, 'three'))->toBeFalse();
});

it('retrieves only specified keys', function () {
    $data   = ['name' => 'Alice', 'age' => 30, 'job' => 'Developer'];
    $subset = ArraySingle::only($data, ['name', 'job']);
    expect($subset)->toBe(['name' => 'Alice', 'job' => 'Developer']);
});

it('can detect if array is a list', function () {
    $list  = [10, 20, 30];
    $assoc = ['a' => 1, 'b' => 2];
    expect(ArraySingle::isList($list))
        ->toBeTrue()
        ->and(ArraySingle::isList($assoc))->toBeFalse();
});

it('calculates average of numeric values', function () {
    $nums = [2, 4, 6, 8];
    expect(ArraySingle::avg($nums))->toBe(5);
});

it('searches an array for a callback condition', function () {
    $data = [1, 2, 3, 4];
    $key  = ArraySingle::search($data, fn($value) => $value === 3);
    expect($key)->toBe(2);
});

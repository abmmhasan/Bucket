<?php

declare(strict_types=1);

use Infocyph\ArrayKit\Array\ArrayMulti;

it('can collapse a multi-dimensional array', function () {
    $source = [[1, 2], [3, 4], [5]];
    $result = ArrayMulti::collapse($source);
    expect($result)->toBe([1, 2, 3, 4, 5]);
});

it('can flatten an array fully by default', function () {
    $source = [1, [2, [3, 4]], 5];
    $result = ArrayMulti::flatten($source);
    expect($result)->toBe([1, 2, 3, 4, 5]);
});

it('can flatten an array by one level', function () {
    $source = [1, [2, [3, 4]], 5];
    // Flatten only one level
    $result = ArrayMulti::flatten($source, 1);
    expect($result)->toBe([1, 2, [3, 4], 5]);
});

it('can get the depth of a nested array', function () {
    $source = [1, [2, [3]], 4];
    $depth  = ArrayMulti::depth($source);
    expect($depth)->toBe(3);
});

it('sorts a multi-dimensional array recursively (asc)', function () {
    $data = [
        ['z' => 3, 'a' => 2],
        ['z' => 1, 'a' => 4],
    ];
    $sorted = ArrayMulti::sortRecursive($data);
    // Expect each sub-array to have sorted keys 'a' => x, 'z' => y
    expect($sorted)->toEqual([
        ['a' => 2, 'z' => 3],
        ['a' => 4, 'z' => 1],
    ]);
});

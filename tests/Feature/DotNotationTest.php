<?php

declare(strict_types=1);

use Infocyph\ArrayKit\Array\DotNotation;

it('flattens a multi-level array into dot notation', function () {
    $source = ['user' => ['name' => 'Alice', 'roles' => ['admin', 'editor']]];
    $flat   = DotNotation::flatten($source);
    expect($flat)->toBe([
        'user.name'      => 'Alice',
        'user.roles.0'   => 'admin',
        'user.roles.1'   => 'editor',
    ]);
});

it('expands a dot-notation array back to nested structure', function () {
    $dotArray = [
        'app.name' => 'MyApp',
        'app.env'  => 'local',
    ];
    $expanded = DotNotation::expand($dotArray);
    expect($expanded)->toBe([
        'app' => [
            'name' => 'MyApp',
            'env'  => 'local',
        ],
    ]);
});

it('gets a nested value with dot notation', function () {
    $array = ['db' => ['host' => 'localhost', 'port' => 3306]];
    expect(DotNotation::get($array, 'db.port'))
        ->toBe(3306)
        ->and(DotNotation::get($array, 'db.user', 'root'))->toBe('root');
});

it('sets a nested value with dot notation', function () {
    $array = [];
    DotNotation::set($array, 'session.timeout', 120);
    expect($array)->toBe(['session' => ['timeout' => 120]]);
});

it('forgets a nested key with dot notation', function () {
    $array = ['user' => ['name' => 'Alice', 'email' => 'alice@example.com']];
    DotNotation::forget($array, 'user.email');
    expect($array)->toBe(['user' => ['name' => 'Alice']]);
});

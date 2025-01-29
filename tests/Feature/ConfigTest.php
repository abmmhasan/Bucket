<?php

declare(strict_types=1);

use Infocyph\ArrayKit\Config\Config;

$config = new Config();

it('can load an array into config', function () use ($config)  {
    $success = $config->loadArray(['app' => ['name' => 'ArrayKit']]);
    expect($success)
        ->toBeTrue()
        ->and($config->loadArray(['another' => 'test']))->toBeFalse();
    // subsequent calls return false because items are no longer empty
});

it('retrieves config items via dot notation', function () use ($config) {
    expect($config->get('app.name'))->toBe('ArrayKit');
});

it('sets config items via dot notation', function () use ($config) {
    $config->set('db.host', 'localhost');
    expect($config->get('db.host'))->toBe('localhost');
});

it('checks if a config key exists', function () use ($config) {
    expect($config->has('app.name'))
        ->toBeTrue()
        ->and($config->has('app.unknown'))->toBeFalse();
});

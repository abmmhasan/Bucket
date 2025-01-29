<?php

declare(strict_types=1);

use Infocyph\ArrayKit\Config\Config;

beforeEach(function () {
    $this->config = Config::instance();
});

it('can load an array into config', function () {
    $success = $this->config->loadArray(['app' => ['name' => 'ArrayKit']]);
    expect($success)
        ->toBeTrue()
        ->and($this->config->loadArray(['another' => 'test']))->toBeFalse();
    // subsequent calls return false because items are no longer empty
});

it('retrieves config items via dot notation', function () {
    expect($this->config->get('app.name'))->toBe('ArrayKit');
});

it('sets config items via dot notation', function () {
    $this->config->set('db.host', 'localhost');
    expect($this->config->get('db.host'))->toBe('localhost');
});

it('checks if a config key exists', function () {
    expect($this->config->has('app.name'))
        ->toBeTrue()
        ->and($this->config->has('app.unknown'))->toBeFalse();
});

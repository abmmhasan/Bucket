<?php

declare(strict_types=1);

use Infocyph\ArrayKit\traits\DTOTrait;

it('can create a DTO from an array', function () {
    // Define a quick test class inline
    $dtoClass = new class {
        use DTOTrait;
        public string $name;
        public int $age;
    };

    $dto = $dtoClass::create(['name' => 'Alice', 'age' => 30, 'extra' => 'ignored']);

    // Cast to array using ->toArray()
    $data = $dto->toArray();
    expect($data)->toBe([
        'name' => 'Alice',
        'age'  => 30,
    ]);
});

<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Config;

use Infocyph\InterMix\Fence\Multi;

/**
 * Class Config
 *
 * Example usage of the BaseConfigTrait to provide
 * core configuration handling, plus any additional
 * features from the Multi trait.
 */
class Config
{
    use BaseConfigTrait;
    use Multi;
}

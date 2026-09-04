<?php

declare(strict_types=1);

namespace In2code\Powermail\Fluid\Cache;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmerInterface;
use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;

/**
 * Keeps compiled templates of parsed strings out of the persistent fluid_template cache.
 *
 * Without this, every distinct value powermail parses - and a sender name or subject can be supplied
 * by a website visitor - is compiled into its own PHP class file below var/cache/code/fluid_template/,
 * which is unbounded growth driven from the outside. These strings are short and cheap to parse, so
 * there is nothing to gain from caching them.
 */
final class NullFluidCache implements FluidCacheInterface
{
    public function get(string $name): mixed
    {
        return null;
    }

    public function set(string $name, mixed $value): void
    {
    }

    public function flush(?string $name = null): void
    {
    }

    /**
     * Part of FluidCacheInterface up to Fluid v4. Nothing is ever written to this cache, so there is
     * nothing to warm up either - the default warmer is returned to satisfy the interface.
     */
    public function getCacheWarmer(): FluidCacheWarmerInterface
    {
        return new StandardCacheWarmer();
    }
}

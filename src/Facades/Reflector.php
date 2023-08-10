<?php

namespace Shift\Cli\Sdk\Facades;

/**
 * @method static \ReflectionClass|null classFromPath(string $path)
 *
 * @see \Shift\Cli\Sdk\Support\Reflector
 */
class Reflector extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return static::class;
    }

    protected static function getInstance()
    {
        return new \Shift\Cli\Sdk\Support\Reflector(getcwd());
    }
}

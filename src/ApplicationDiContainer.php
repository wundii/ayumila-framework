<?php

namespace Ayumila;

use Ayumila\Traits\SingletonStandard;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

class ApplicationDiContainer
{
    use SingletonStandard;

    private ?Container $container = null;

    /**
     * @param Container $container
     * @return void
     */
    public static function addPhpDi(Container $container): void
    {
        $instance = self::create();
        $instance->container = $container;
    }

    /**
     * @param string|null $class
     * @return object|null
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function getDiContainer(?string $class = null): object|null
    {
        $instance = self::create();

        if($class && $instance->container instanceof Container)
        {
            if(in_array($class, $instance->container->getKnownEntryNames()))
            {
                return $instance->container->get($class);
            }
        }

        return $instance->container ?? null;
    }
}
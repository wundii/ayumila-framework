<?php

namespace Ayumila\Traits;

use Ayumila\ApplicationDiContainer;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use smarthome\login;

trait CreateDi
{
    /**
     * @return login|CreateDi
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function create(): self
    {
        $container = ApplicationDiContainer::getDiContainer();

        if($container instanceof Container)
        {
            return $container->get(self::class);
        }

        return new self;
    }

    private function __construct() {}
    private function __clone() {}
}
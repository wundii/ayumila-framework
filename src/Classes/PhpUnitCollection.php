<?php

namespace Ayumila\Classes;

use Ayumila\Traits\Iterator AS TraitIterator;
use Ayumila\Traits\SingletonStandard;
use Iterator;

final class PhpUnitCollection implements Iterator
{
    use SingletonStandard;
    use TraitIterator;

    /**
     * @param string $description
     * @return void
     */
    public static function addEntity(string $description)
    {
        $instance = self::create();
        $instance->collection[] = $description;
    }

    /**
     * @return PhpUnitCollection
     */
    public static function getCollection(): self
    {
        return self::create();
    }

    /**
     * @param string $description // str_contains
     * @return bool
     */
    public static function isEntityExists(string $description): bool
    {
        $instance = self::create();
        foreach($instance AS $entity)
        {
            if(str_contains($entity, $description))
            {
                return true;
            }
        }

        return false;
    }
}
<?php

namespace Ayumila\Traits;

use ReflectionClass;

trait ClassEnum
{
    public string $key;
    public mixed  $value;

    /**
     * @param string $constant
     * @return self|null
     */
    public static function enum(string $constant): null|self
    {
        if(defined(self::class.'::'.$constant))
        {
            $classEnum = new self;
            $classEnum->key = $constant;
            $classEnum->value = constant(self::class.'::'.$constant);

            return $classEnum;
        }

        return null;
    }

    /**
     * @param string $value
     * @return self|null
     */
    public static function tryFrom(string $value): null|self
    {
        $reflectionClass = new ReflectionClass(self::class);
        $constants = $reflectionClass->getConstants();
        if($constants)
        {
            $key = array_search($value, $constants);

            if($key !== false)
            {
                $classEnum = new self;
                $classEnum->key = $key;
                $classEnum->value = $constants[$key];

                return $classEnum;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
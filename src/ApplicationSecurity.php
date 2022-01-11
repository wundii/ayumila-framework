<?php

namespace Ayumila;

use Ayumila\Interfaces\SecurityInterface;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\DefaultResponse;
use Ayumila\Traits\SingletonStandard;
use ReflectionMethod;

class ApplicationSecurity
{
    use SingletonStandard{
        SingletonStandard::__construct as SingletonStandardConstruct;
    }
    use DefaultResponse {
        DefaultResponse::__construct as DefaultResponseConstruct;
    }

    private string           $securityClass = '';


    private function __construct()
    {
        $this->SingletonStandardConstruct();
        $this->DefaultResponseConstruct();
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function isSecurityClassExists(): bool
    {
        $instance = self::create();
        if(class_exists($instance->securityClass))
        {
            if(method_exists($instance->securityClass, 'run'))
            {
                $reflectionMethod = new ReflectionMethod($instance->securityClass, 'run');
                if((string)$reflectionMethod->getReturnType() === 'bool')
                {
                    return true;
                }else{
                    throw new AyumilaException('The return type of the "run" method is not of the type bool');
                }
            }else{
                throw new AyumilaException('No method "run" was found in the Scurity Class');
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getSecurityClass(): string
    {
        $instance = self::create();
        return $instance->securityClass;
    }

    /**
     * @param SecurityInterface|null $securityClass
     * @return bool
     */
    public static function startSecurityClass(?SecurityInterface $securityClass = null): bool
    {
        $instance = self::create();

        if($securityClass)
        {
            return $securityClass->run();

        }elseif($instance->securityClass instanceof SecurityInterface)
        {
            return (new $instance->securityClass)->run();
        }

        return false;
    }

    /**
     * @param string $securityClass
     * @return void
     */
    public function setSecurityClass(string $securityClass): void
    {
        $instance = self::create();
        $instance->securityClass = $securityClass;
    }

}
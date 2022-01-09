<?php

namespace Ayumila;

use Ayumila\Exceptions\AyumilaException;

class ApplicationControllerData extends ApplicationController{
    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getCurrantApplicationKey(): string
    {
        $instance = self::create();
        if(!$instance->currentApplicationKey)
        {
            throw new AyumilaException('ApplicationControllerData: The current AppKey must not be empty');
        }

        return $instance->currentApplicationKey;
    }

    /**
     * @return array
     */
    public static function getApplicationKeys(): array
    {
        $instance = self::create();
        return $instance->applicationKeys;
    }

    /**
     * @return array
     */
    public static function getApplicationMultitons(): array
    {
        $instance = self::create();
        return $instance->applicationMultitons;
    }

    /**
     * @return int
     */
    public static function getCurrentApplicationLayer(): int
    {
        $instance = self::create();
        return count($instance->applicationKeys);
    }

    /**
     * @return string
     */
    public static function getFirstApplicationKey(): string
    {
        $instance = self::create();
        if(!$instance->applicationKeys)
        {
            return '';
        }

        $firstKey = array_key_first($instance->applicationKeys);
        return $instance->applicationKeys[$firstKey];
    }

    /**
     * @param Application|null $app
     * @return string
     * @throws AyumilaException
     */
    public static function getAppKey(?Application $app = null): string
    {
        if($app instanceof Application)
        {
            return $app->getKey();
        }else{
            return ApplicationControllerData::getCurrantApplicationKey();
        }
    }
}
<?php

namespace Ayumila\Http;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\Exceptions\AyumilaException;

class ResponseData extends Response
{
    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function getStatus(): bool
    {
        $instance = self::create();
        return $instance->obj->getStatus();
    }

    /**
     * @return int
     * @throws AyumilaException
     */
    public static function getDataCount(): int
    {
        $instance = self::create();

        if($instance->obj instanceof ResponseAbstract)
        {
            return $instance->obj->getDataCount();
        }

        return 0;
    }

    /**
     * @return mixed
     * @throws AyumilaException
     */
    public static function getData(): mixed
    {
        $instance = self::create();

        if($instance->obj instanceof ResponseAbstract)
        {
            return $instance->obj->getData();
        }

        return null;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getContentType(): string
    {
        $instance = self::create();
        return $instance->obj->getContentType();
    }

    /**
     * @return int
     * @throws AyumilaException
     */
    public static function getHttpStatuscode(): int
    {
        $instance = self::create();
        return $instance->obj->getHttpStatuscode();
    }
}
<?php
/**
 * This is the central class for processing all responses.
 *
 * @version 1.0.0
 * @copyright WESTPRESS GmbH & Co. KG
 * @package SSO
 * @subpackage System
 */

namespace Ayumila\Http;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\Application;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\MultitonStandard;

class Response
{
    use MultitonStandard;

    protected ?ResponseAbstract $obj        = null;
    private   array             $statusList = [
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
    ];

    /**
     * @param string $status
     * @param string $description
     * @throws AyumilaException
     */
    public static function addError(string $status, string $description): void
    {
        $instance = self::create();
        $instance->obj->addError($status, $description);
    }

    /**
     * @param string $status
     * @param string $description
     * @throws AyumilaException
     */
    public static function addWarning(string $status, string $description): void
    {
        $instance = self::create();
        $instance->obj->addWarning($status, $description);
    }

    /**
     * @param string $status
     * @param string $description
     * @throws AyumilaException
     */
    public static function addException(string $status, string $description): void
    {
        $instance = self::create();
        $instance->obj->addException($status, $description);
    }

    /**
     * @param string $key
     * @param string|array $value
     * @throws AyumilaException
     */
    public static function addOutputAddonData(string $key, string|array $value): void
    {
        $instance = self::create();
        $instance->obj->addOutputAddonData($key, $value);
    }

    /**
     * @param mixed $data
     * @throws AyumilaException
     */
    public static function addData(mixed $data): void
    {
        $instance = self::create();
        $instance->obj->addData($data);
    }

    /**
     * @param array $data
     * @throws AyumilaException
     */
    public static function addDataArray(array $data): void
    {
        $instance = self::create();
        $instance->obj->addDataArray($data);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @throws AyumilaException
     */
    public static function addDataWithKey(string $key, mixed $data): void
    {
        $instance = self::create();
        $instance->obj->addDataWithKey($key, $data);
    }

    /**
     * @param mixed $data
     * @throws AyumilaException
     */
    public static function setData(mixed $data): void
    {
        $instance = self::create();
        $instance->obj->setData($data);
    }

    /**
     * @return int
     */
    private function getHttpStatuscode(): int
    {
        return $this->obj->getHttpStatuscode();
    }

    /**
     * @param int $http_statuscode
     * @return int
     * @throws AyumilaException
     */
    public static function setHttpStatuscode(int $http_statuscode): int
    {
        $instance = self::create();
        if(array_key_exists($http_statuscode, $instance->statusList))
        {
            $instance->obj->setHttpStatuscode($http_statuscode);
        }

        return $instance->obj->getHttpStatuscode();
    }

    /**
     * @param int $dataCount
     * @throws AyumilaException
     */
    public static function setDataCount(int $dataCount): void
    {
        $instance = self::create();
        $instance->obj->setDataCount($dataCount);
    }

    /**
     * @param ResponseAbstract $obj
     * @throws AyumilaException
     */
    public static function setResponseContentType(ResponseAbstract $obj): void
    {
        $instance = self::create();
        if($instance->obj instanceof ResponseAbstract)
        {
            unset($instance->obj);
        }

        $instance->obj = $obj;
    }

    /**
     * @return void
     * @throws AyumilaException
     */
    public static function setResponseContentTypeByRouter(): void
    {
        if(class_exists(RouterData::getRouteResponse()))
        {
            $instance = self::create();
            $responseType = call_user_func(RouterData::getRouteResponse().'::create');
            $instance::setResponseContentType($responseType);
        }
    }

    /**
     * @return string|null
     * @throws AyumilaException
     */
    public static function getResponseContentTypeClassName(): ?string
    {
        $instance = self::create();
        if($instance->obj instanceof ResponseAbstract)
        {
            return $instance->obj::class;
        }

        return null;
    }

    /**
     * Sends the data to the client.
     *
     * @throws AyumilaException
     */
    public static function send(Application $app): string|object
    {
        $instance = self::create($app);

        if($instance->obj->getContentType())
        {
            header($instance->obj->getContentType());
            http_response_code($instance->getHttpStatuscode());
        }

        return $instance->obj->outputData();
    }
}
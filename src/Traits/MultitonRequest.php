<?php

namespace Ayumila\Traits;

use Ayumila\Application;
use Ayumila\ApplicationController;
use Ayumila\ApplicationControllerData;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Request;
use Ayumila\Http\RequestMock;

trait MultitonRequest
{
    private   static array  $instances = array();
    protected string $key;


    /**
     * @param Application|null $app
     * @param RequestMock|null $requestMock
     * @return MultitonRequest|Request
     * @throws AyumilaException
     */
    public static function create(?Application $app = null, ?RequestMock $requestMock = null): self
    {
        $key = ApplicationControllerData::getAppKey($app);

        if (!array_key_exists($key, self::$instances)) {
            $initialise = !$requestMock instanceof RequestMock;
            self::$instances[$key] = new self($initialise);
            self::$instances[$key]->key = $key;

            ApplicationController::registerMultiton($key, self::class);
        }

        if($requestMock instanceof RequestMock)
        {
            self::$instances[$key]->setRequestDataFromRequestMock($requestMock);
        }

        return self::$instances[$key];
    }

    /**
     * @param string $key
     * @return MultitonRequest|Request
     */
    public function changeInstanceByKey(string $key): self
    {
        return self::$instances[$key];
    }

    /**
     * @param string $key
     */
    public static function delete(string $key):void
    {
        if (array_key_exists($key, self::$instances)) {
            unset(self::$instances[$key]);
        }
    }

    /**
     * @return array
     */
    public static function getInstancesByKeys(): array
    {
        return array_keys(self::$instances);
    }


    /**
     * The cloning-functionality must be private to prevent multiple instances
     */
    private function __clone():void {}

    /**
     * The wakeup-functionality must throw an AyumilaException to prevent multiple instances
     */
    public function __wakeup()
    {
        // A wakeup must be possible for the MultitonRequest, otherwise it cannot be viewed later during the log evaluation.
        // throw new AyumilaException("Cannot unserialize singleton");
    }

    private function __construct() {}
}
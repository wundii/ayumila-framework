<?php

namespace Ayumila\Traits;

use Ayumila\Application;
use Ayumila\ApplicationController;
use Ayumila\ApplicationControllerData;
use Ayumila\ApplicationMiddleware;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Process;
use Ayumila\Http\Response;
use Ayumila\Http\Router;

trait MultitonStandard
{
    private   static array  $instances = array();
    protected string $key;


    /**
     * @param Application|null $app
     * @return Router|ApplicationMiddleware|Process|Response|MultitonStandard
     * @throws AyumilaException
     */
    public static function create(?Application $app = null): self
    {
        $key = ApplicationControllerData::getAppKey($app);

        if (!array_key_exists($key, self::$instances)) {
            self::$instances[$key] = new self();
            self::$instances[$key]->key = $key;

            ApplicationController::registerMultiton($key, self::class);
        }

        return self::$instances[$key];
    }

    /**
     * @param string $key
     * @return Router|ApplicationMiddleware|Process|Response|MultitonStandard
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
     * The wakeup-functionality must throw an Exception to prevent multiple instances
     *
     * @throws AyumilaException
     */
    public function __wakeup()
    {
        throw new AyumilaException("Cannot unserialize singleton");
    }

    private function __construct() {}
}
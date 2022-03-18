<?php

namespace Ayumila;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\SingletonStandard;
use Exception;

class ApplicationController {

    use SingletonStandard;

    protected array        $applicationKeys       = array();
    protected array        $applicationMultitons  = array();
    protected array        $applicationSingletons = array();
    protected string       $currentApplicationKey = '';
    protected bool         $devMode               = false;
    protected bool         $testMode              = false;

    /**
     * @param Application $app
     * @return $this
     * @throws AyumilaException
     */
    public function registerApplication(Application $app):self
    {
        $key = $app->getKey();

        if(!in_array($key, $this->applicationKeys))
        {
            $this->applicationKeys[]     = $key;
            $this->currentApplicationKey = $key;

        }else{
            throw new AyumilaException('Double Application register with Key: '.$key);
        }

        return $this;
    }

    /**
     * @param string $applicationKey
     * @param string $className
     * @return array
     * @throws AyumilaException
     */
    public static function registerMultiton( string $applicationKey, string $className ):array
    {
        $instance = self::create();

        if(!in_array($applicationKey, $instance->applicationKeys))
        {
            throw new AyumilaException('To register a multiton on an app '.$applicationKey.' the AppKey must be registered');
        }

        if(!array_key_exists($applicationKey, $instance->applicationMultitons))
        {
            $instance->applicationMultitons[$applicationKey][] = $className;

        }elseif(!in_array($className, $instance->applicationMultitons[$applicationKey]))
        {
            $instance->applicationMultitons[$applicationKey][] = $className;

        }else{
            throw new AyumilaException('Double MultitonStandard register with Key: '.$applicationKey);
        }

        return $instance->applicationMultitons;
    }

    /**
     * @param string $className
     * @return array
     */
    public static function registerSingleton( string $className ):array
    {
        $instance = self::create();

        if(!in_array($className, $instance->applicationSingletons))
        {
            $instance->applicationSingletons[] = $className;

        }

        return $instance->applicationSingletons;
    }

    /**
     * @param Application $app
     * @return $this
     * @throws AyumilaException
     */
    public function deleteApplicationAndMultitons(Application $app): self
    {
        $key = $app->getKey();

        if(in_array($key, $this->applicationKeys))
        {
            $deleteKey = array_pop($this->applicationKeys);

            if($deleteKey !== $key)
            {
                throw new AyumilaException('$deleteKey und $key müssen Identisch sein!');
            }

            /** set the new current application by key */
            $currentApplicationKey       = end($this->applicationKeys) ? end($this->applicationKeys) : '';
            $this->currentApplicationKey = $currentApplicationKey;

            $this->deleteMultitons($app);
            $this->deleteApplication($app);
            $this->deleteSingletons();

        }else{
            throw new AyumilaException('Application '.$key.' not found');
        }

        return $this;
    }

    /**
     * @param Application $app
     * @return $this
     * @throws AyumilaException
     */
    public function deleteMultitons(Application $app): self
    {
        $key = $app->getKey();

        if(array_key_exists($key, $this->applicationMultitons))
        {
            /** unset all multitons by applicationkey */
            foreach ($this->applicationMultitons[$key] as $appKey => $value)
            {
                $value::delete($key);
                unset($this->applicationMultitons[$key][$appKey]);
            }

            if(count($this->applicationMultitons[$key]) === 0)
            {
                unset($this->applicationMultitons[$key]);

            }else{
                throw new AyumilaException('After delete Multitons, not all static classes have been deleted');
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws AyumilaException
     */
    public function deleteSingletons(): self
    {
        if(!$this->applicationMultitons)
        {
            /** unset all Singleton when Multitons is clear */
            foreach ($this->applicationSingletons as $key => $value)
            {
                if($value !== ApplicationController::class)
                {
                    $value::delete();
                    unset($this->applicationSingletons[$key]);
                }
            }

            $singletonWithoutController = array_filter($this->applicationSingletons, function ($value){return $value !== ApplicationController::class;});

            if (count($singletonWithoutController) !== 0)
            {
                throw new AyumilaException('After delete Singleton, not all static classes have been deleted');
            }
        }

        return $this;
    }

    /**     *
     * @param Application $app
     * @return $this
     * @throws AyumilaException
     */
    public function deleteApplication(Application $app): self
    {
        $key = $app->getKey();

        if(
            !isset($this->applicationKeys[$key])
            && !isset($this->applicationMultitons[$key])
        ){
            unset($app);

        }else{
            throw new AyumilaException('delete Application, not all static classes have been deleted');
        }

        return $this;
    }

    /**
     * @return self
     */
    public function setDevMode(): self
    {
        $this->devMode = true;
        return $this;
    }

    /**
     * @return self
     */
    public function setTestMode(): self
    {
        $this->testMode = true;
        return $this;
    }
}
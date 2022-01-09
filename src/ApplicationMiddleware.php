<?php

namespace Ayumila;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Response;
use Ayumila\Traits\MultitonStandard;
use Exception;

class ApplicationMiddleware
{
    use MultitonStandard;

    private array $middleware = array();

    /**
     * @param callable $middleware
     * @param bool $alsoDefaultExecute
     */
    public function addMiddleware(callable $middleware, bool $alsoDefaultExecute):void
    {
        $this->middleware[] =
            [
                'alsoDefaultExecute' => $alsoDefaultExecute,
                'middleware'         => $middleware,
            ];

        // wie bekommt man aus einer anonymous function einen md5 wert?
        // $md5Callable = md5(serialize($middleware));
        //
        // $instance = self::create();
        // if(!array_key_exists($md5Callable, $instance->middleware))
        // {
        //     $instance->middleware[$md5Callable] = $middleware;
        // }else{
        //     throw new AyumilaException("This callable Middleware Function is already registered");
        // }
    }

    /**
     *
     */
    public function run(bool $defaultExecuteOnly = false)
    {
        foreach ($this->middleware AS $callable)
        {
            if(!$defaultExecuteOnly || (array_key_exists('alsoDefaultExecute', $callable) && $callable['alsoDefaultExecute'] == $defaultExecuteOnly))
            {
                try {
                   $callable['middleware']();
                }catch (AyumilaException | Exception $ex)
                {
                   Response::addException('ApplicationMiddleware', $ex->getMessage());
                }
            }
        }
    }
}
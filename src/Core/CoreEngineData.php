<?php

namespace Ayumila\Core;

class CoreEngineData extends CoreEngine
{
    /**
     * @return array
     */
    public static function getRoutes(): array
    {
        $instance = self::create();
        return $instance->app_routes;
    }

    /**
     * @param string $method
     * @param string $route
     * @return array|string
     */
    public static function getRouteResponse(string $method = '', string $route = ''): array|string
    {
        $instance = self::create();

        if($method && $route && isset($instance->app_routeResponse[$method][$route]))
        {
            return $instance->app_routeResponse[$method][$route];
        }elseif($method && $route){
            return '';
        }

        return $instance->app_routeResponse;
    }

    /**
     * @param string $method
     * @param string $route
     * @return array|string
     */
    public static function getTwig(string $method = '', string $route = ''): array|string
    {
        $instance = self::create();

        if($method && $route && isset($instance->app_twig[$method][$route]))
        {
            return $instance->app_twig[$method][$route];
        }elseif($method && $route){
            return '';
        }

        return $instance->app_twig;
    }
}
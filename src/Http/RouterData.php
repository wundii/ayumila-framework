<?php

namespace Ayumila\Http;

use Ayumila\ApplicationDiContainer;
use Ayumila\ApplicationSecurity;
use Ayumila\Core\CoreEngineData;
use Ayumila\Exceptions\AyumilaException;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

class RouterData extends Router
{
    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getClass():string
    {
        $instance = self::create();
        return $instance->route->routeClass;
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getRoutes(string $key): array
    {
        $routes = CoreEngineData::getRoutes();

        if(array_key_exists($key, $routes))
        {
            return $routes[$key];
        }

        return $routes;
    }

    /**
     * @param string $var
     * @return string
     * @throws AyumilaException
     */
    public static function getParameter(string $var):string
    {
        $instance = self::create();

        if(array_key_exists($var, $instance->route->parameters))
        {
            return $instance->route->parameters[$var];
        }else{
            return '';
        }
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public static function getParametersFromUrlPath(): array
    {
        $instance = self::create();
        return $instance->route->parametersFromUrlPath;
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public static function getParameters(): array
    {
        $instance = self::create();
        return $instance->route->parameters;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function isRouteAuthenticated(): bool
    {
        $instance = self::create();
        return $instance->route->routeAuthentication;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function isRouteAuthorized(): bool
    {
        $instance = self::create();
        return $instance->route->routeAuthorization;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getRouteResponse(): string
    {
        $instance = self::create();
        return $instance->route->routeResponse;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getTwig(): string
    {
        $instance = self::create();
        return $instance->route->routeTwig;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function isPrivate(): bool
    {
        $instance = self::create();
        return $instance->route->routePrivate;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getMethod(): string
    {
        $instance = self::create();
        return $instance->route->routeMethod;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getAction(): string
    {
        $instance = self::create();
        return $instance->route->routeAction;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public static function isRouteFound(): bool
    {
        $instance = self::create();
        return $instance->routeFound;
    }

    /**
     * @return bool
     * @throws AyumilaException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function isAuthentication(): bool
    {
        if(!RouterData::isRouteAuthenticated())
        {
            return true;
        }

        if(ApplicationSecurity::isSecurityClassExists())
        {
            $container = ApplicationDiContainer::getDiContainer();

            if($container instanceof Container)
            {
                if(in_array(ApplicationSecurity::getSecurityClass(), $container->getKnownEntryNames()))
                {
                    $securityClassFromDi = $container->get(ApplicationSecurity::getSecurityClass());

                    return ApplicationSecurity::startSecurityClass($securityClassFromDi);
                }
            }

            return ApplicationSecurity::startSecurityClass();
        }

        $instance = self::create();
        return !$instance->route->routeAuthentication;
    }
}
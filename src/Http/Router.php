<?php

namespace Ayumila\Http;

use Ayumila\Classes\Helper;
use Ayumila\Core\CoreEngine;
use Ayumila\Core\CoreEngineData;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\MultitonStandard;
use Exception;

class Router
{
    use MultitonStandard;

    private   string $requestMethod = '';
    private   string $requestUri    = '';
    private   string $path          = '';
    private   array  $queries       = array();
    private   array  $routes        = array();
    protected Route  $route;
    protected bool   $routeFound    = false;
    private   int    $dispatchCount = 0;

    /**
     * CustomTrait constructor
     *
     * @throws AyumilaException
     */
    private function __construct()
    {
        $this->setDefaultRoute();
        $this->loadServerVariable();
        $this->processUri();
    }

    /**
     * @return void
     */
    private function setDefaultRoute(): void
    {
        $route = new Route();

        $route->routeClass            = '\smarthome\errorSites';
        $route->parameters            = array();
        $route->parametersFromUrlPath = array();
        $route->routeAuthentication   = false;
        $route->routeAuthorization    = false;
        $route->routePrivate          = false;
        $route->routeMethod           = 'error404';
        $route->routeAction           = 'method';
        $route->routeResponse         = 'Ayumila\Http\ResponseTwig';
        $route->routeTwig             = 'error/error.twig';

        $this->route = $route;
    }

    /**
     * Load the ServerVariables
     *
     * @throws AyumilaException
     */
    private function loadServerVariable(): void
    {
        $this->requestMethod = RequestData::getRequestMethod();
        $this->requestUri    = RequestData::getRequestUri();
    }

    /**
     * @throws AyumilaException
     */
    private function processUri(): void
    {
        if (!$this->requestUri) {
            throw new AyumilaException('RequestUri is empty');
        }

        $parseUrl = parse_url($this->requestUri);

        if (!array_key_exists('path', $parseUrl)) {
            throw new AyumilaException('RequestUri has no path');
        }

        $this->path = strtolower($parseUrl['path']);

        if(!str_ends_with($this->path, '/'))
        {
            $this->path .= '/';
        }

        if (array_key_exists('query', $parseUrl)) {
            $query = $parseUrl['query'];
            $queryArray = array();

            $paraExplode = explode('&', $query);
            foreach ($paraExplode as $parameter) {
                $varExplode = explode('=', $parameter, 2);
                if (count($varExplode) === 2) {
                    $queryArray[$varExplode[0]] = $varExplode[1];
                } else {
                    $queryArray[$varExplode[0]] = true;
                }
            }

            $this->queries = $queryArray;
        }
    }

    /**
     * @throws AyumilaException
     */
    private function callableSpecialRoutes(): void
    {
        $this->listRoutes();
        $this->resetRoutes();
    }

    /**
     * @throws AyumilaException
     */
    private function resetRoutes(): void
    {
        if (str_starts_with(strtolower($this->path), '/router/reset')) {
            $this->loadRoutes(true);
            echo "reload Routes";
            exit();
        }
    }

    /**
     * @throws AyumilaException
     * @throws Exception
     */
    private function listRoutes(): void
    {
        if (str_starts_with(strtolower($this->path), '/router/list')) {
            $this->loadRoutes(true);
            foreach ($this->routes as $requestMethods => $routes) {
                echo "<p>";
                echo $requestMethods;

                echo "<lu>";
                foreach ($routes as $route) {
                    $authentication = Helper::convertToBool($route['authentication']) ? 'true' : 'false';
                    $authorization  = Helper::convertToBool($route['authorization']) ? 'true' : 'false';
                    $private        = Helper::convertToBool($route['private']) ? 'true' : 'false';

                    echo "<li>" . $route['requestUrl'] . " > " . $route['class'] . " (authent: " . $authentication . " authori: " . $authorization . " private: " . $private . " )</li>";
                }
                echo "</lu>";
                echo "</p>";
            }
            exit();
        }
    }

    /**
     * @param bool $force
     * @throws AyumilaException
     */
    public function loadRoutes(bool $force = false)
    {
        if($force)
        {
            CoreEngine::create()->run($force);
        }

        $this->routes = CoreEngineData::getRoutes();
    }

    /**
     *
     * @throws AyumilaException
     */
    private function dispatch(array $routes): ?Route
    {
        $routeElement = null;


        if (!array_key_exists($this->requestMethod, $routes)) {
            throw new AyumilaException('RequestMethod in the routes not found ' . $this->requestMethod);
        }

        foreach ($routes[$this->requestMethod] as $route => $class)
        {
            // Variablenname abholen
            $route = strtolower($route);
            preg_match_all('/{[^}]+}/', $route, $paramsMatches);
            $paramsMatches = array_key_exists(0, $paramsMatches) ? $paramsMatches[0] : array();

            // $route = preg_replace('/{[^}]+}/', '(.+)', $route);
            $route = preg_replace('/{[^}]+}/', '([a-zA-Z0-9]{1,})', $route);

            if (preg_match("%^{$route}/?$%", $this->path, $matches) === 1)
            {
                $routeElement      = new Route();
                $coreRouteResponse = CoreEngineData::getRouteResponse($class['requestMethods'], $class['requestUrl']);
                $coreRouteTwig     = CoreEngineData::getTwig($class['requestMethods'], $class['requestUrl']);

                $routeElement->routeClass          = $class['class'];
                $routeElement->routeAuthentication = $class['authentication'];
                $routeElement->routeAuthorization  = $class['authorization'];
                $routeElement->routePrivate        = $class['private'];
                $routeElement->routeMethod         = $class['method'];
                $routeElement->routeAction         = $class['action'];
                $routeElement->routeResponse       = !$coreRouteResponse ? ResponseTwig::class : $coreRouteResponse;
                $routeElement->routeTwig           = !$coreRouteTwig ? $routeElement->routeResponse : $coreRouteTwig; // @todo $routeElement->routeResponse?! :>

                // path delete
                unset($matches[0]);

                $parameters = !$matches ? array() : $matches;
                $parameters = array_values($parameters);

                foreach ($parameters as $key => $value) {
                    // Variablenname mit Variablen Inhalt zusammenführen
                    if (array_key_exists($key, $paramsMatches)) {
                        $newKey = $paramsMatches[$key];
                        $newKey = substr($newKey, 1, -1);
                        unset($parameters[$key]);
                        $parameters[$newKey] = $value;
                    }
                }

                $routeElement->parametersFromUrlPath = $parameters;

                // add the parameters from the url-query
                foreach ($this->queries as $var => $value) {
                    $parameters[$var] = $value;
                }

                $routeElement->parameters = $parameters;
            }
        }

        $this->dispatchCount++;

        if ($routeElement instanceof Route)
        {
            $this->route      = $routeElement;
            $this->routeFound = true;

        } else if ($this->dispatchCount == 1)
        {
            $this->loadRoutes(true);
            $this->dispatch($routes);

        }

        return $routeElement;
    }

    /**
     *
     */
    public function run():void
    {
        try {
            $this->loadRoutes();
            $this->callableSpecialRoutes();
            $this->dispatch($this->routes);
        } catch (AyumilaException|Exception $ex) {
            echo $ex->getFile() . ': Line ' . $ex->getLine() . "<br>";
            echo $ex->getMessage();

            exit();
        }
    }
}

class Route
{
    public array  $parameters = array();
    public array  $parametersFromUrlPath = array();
    public string $routeClass;
    public bool   $routeAuthentication = true;
    public bool   $routeAuthorization = true;
    public bool   $routePrivate = false;
    public string $routeMethod;
    public string $routeAction;
    public string $routeResponse = '';
    public string $routeTwig = '';
}
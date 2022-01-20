<?php

namespace Ayumila\Core;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\SingletonStandard;
use Ayumila\Classes\Helper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class CoreEngine
{
    use SingletonStandard;

    protected array  $app_routes        = array();
    protected array  $app_routeResponse = array();
    protected array  $app_twig          = array();
    private   array  $appClassDirectory = array();

    /**
     * CustomTrait constructor
     *
     * @throws AyumilaException
     */
    private function __construct()
    {
        if(!extension_loaded('apcu') || !apcu_enabled())
        {
            throw new AyumilaException('apcu is not enable');
        }
    }

    /**
     * @param string|array $appClassDirectory
     * @return $this
     * @throws AyumilaException
     */
    public function setAppClassDirectory(string|array $appClassDirectory): self
    {
        $appClassDirectory = is_string($appClassDirectory) ? (array)$appClassDirectory : $appClassDirectory;

        foreach ($appClassDirectory AS $directory)
        {
            if(!is_dir($directory)) {
                throw new AyumilaException('directory not found');
            }
        }

        $this->appClassDirectory = $appClassDirectory;

        return $this;
    }

    /**
     * @throws AyumilaException
     */
    public function run(bool $force = false): void
    {
        if($force || !apcu_exists('app_routes') || !apcu_exists('app_response'))
        {
            $this->processEngineData();
        }

        $this->saveDataToApcu('app_routes',        $this->app_routes       , $force);
        $this->saveDataToApcu('app_routeResponse', $this->app_routeResponse, $force);
        $this->saveDataToApcu('app_twig',          $this->app_twig         , $force);
    }

    /**
     * @param string $key
     * @param array $data
     * @param bool $force
     * @throws AyumilaException
     */
    private function saveDataToApcu(string $key, array $data, bool $force = false): void
    {
        $key = !str_starts_with($key, 'app_') ? 'app_'.$key : $key;

        if(!property_exists($this, $key))
        {
            throw new AyumilaException('Cannot find the Variable '.$key.' in the CoreEngine');
        }

        if($force || !apcu_exists($key))
        {
            if(!apcu_store($key, $data))
            {
                throw new AyumilaException('Failed to write '.$key.' to the apcu cache');
            }
        }

        $this->{$key} = apcu_fetch($key);
    }

    /**
     * @throws AyumilaException
     */
    private function processEngineData(): void
    {
        $this->app_routes        = array();
        $this->app_routeResponse = array();
        $this->app_twig          = array();
        $routesIsDoubleCheck     = array();

        foreach ($this->directoryIterator() AS $class)
        {
            if(class_exists($class['class']))
            {
                $route      = '';
                $routeClass = '';
                $routeArray = array();

                $orderArray = [
                    'route',
                    'routeResponse',
                    'twig',
                ];
                $annotations = Helper::sortArrayByArray($class['annotations'], $orderArray, true);

                foreach ($annotations AS $variable => $value)
                {
                    $variable = strstr($variable, '#', true);

                    if($routeClass != $class['class'])
                    {
                        $routeClass = $class['class'];
                        $routeArray = array();

                    }

                    switch ($variable)
                    {
                        case 'route':
                            $value = explode('>', $value, 2);

                            if(count($value) !== 2)
                            {
                                throw new AyumilaException('The Route-Definitions are not correct');
                            }

                            $requestMethod  = trim($value[0]);
                            $route          = trim($value[1]);
                            $authentication = true;
                            $authorization  = true;
                            $private        = false;

                            $routeExplode = explode(' ', $route);
                            foreach ($routeExplode AS $key => $valueExplode)
                            {
                                if($key === 0)
                                {
                                    $route = $valueExplode;
                                }

                                switch(strtolower($valueExplode))
                                {
                                    case 'authentication_off':
                                        $authentication = false;
                                        break;
                                    case 'authorization_off':
                                        $authorization = false;
                                        break;
                                    case 'private':
                                        $private = true;
                                        break;
                                    default:
                                        break;
                                }
                            }

                            if( isset($routesIsDoubleCheck[$requestMethod]) && in_array($route, $routesIsDoubleCheck[$requestMethod]) )
                            {
                                throw new AyumilaException('this route already exists: '.$route);
                            }

                            $routeArray[md5($route)]                  = $route;
                            $routesIsDoubleCheck[$requestMethod][]    = $route;
                            $this->app_routes[$requestMethod][$route] = [
                                'requestMethods' => $requestMethod,
                                'requestUrl'     => $route,
                                'action'         => $class['action'],
                                'class'          => $class['class'],
                                'method'         => $class['method'],
                                'authentication' => $authentication,
                                'authorization'  => $authorization,
                                'private'        => $private,
                            ];
                            break;

                        case 'routeresponse':
                            if($this->app_routes){
                                $methods = array_keys($this->app_routes);
                                foreach ($methods AS $method)
                                {
                                    foreach ($routeArray AS $route)
                                    {
                                        if(array_key_exists($route, $this->app_routes[$method]))
                                        {
                                            $this->app_routeResponse[$method][$route] = $value;
                                        }
                                    }
                                }
                            }
                            break;

                        case 'twig':
                            if($this->app_routes){
                                $methods = array_keys($this->app_routes);
                                foreach ($methods AS $method)
                                {
                                    foreach ($routeArray AS $route)
                                    {
                                        if(array_key_exists($route, $this->app_routes[$method]))
                                        {
                                            $this->app_twig[$method][$route] = $value;
                                        }
                                    }
                                }
                            }
                            break;

                        default:
                            break;
                    }
                }
            }else{
                throw new AyumilaException('Not found in the Autoload-List: '.$class['class']);
            }
        }
    }

    private function directoryIterator():array
    {
        $classes           = array();

        foreach ($this->appClassDirectory AS $directory)
        {
            $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            $phpFiles = new RegexIterator($allFiles, '/\.php$/');

            foreach ($phpFiles as $phpFile)
            {
                $content           = file_get_contents($phpFile->getRealPath());
                $tokens            = token_get_all($content);
                $namespace         = '';
                $class             = '';
                $methods           = array();
                $methodAnnotations = false;
                $annotations       = array();

                for ($index = 0; isset($tokens[$index]); $index++)
                {
                    if (!isset($tokens[$index][0]))
                    {
                        continue;
                    }

                    if (T_NAMESPACE === $tokens[$index][0])
                    {
                        $index += 2; // Skip namespace keyword and whitespace
                        while (isset($tokens[$index]) && is_array($tokens[$index]))
                        {
                            $namespace .= !str_starts_with('\\', $tokens[$index][1]) ? '\\' . $tokens[$index][1] : $tokens[$index][1];
                            $index++;
                        }
                    }

                    if (T_DOC_COMMENT === $tokens[$index][0])
                    {
                        preg_match_all('/\* @(?P<variable>\w+) (?P<value>[\w\s\-_%:\\\\.{}\/>]+)/', $tokens[$index][1], $matches);

                        if(isset($matches['variable']) && isset($matches['value']))
                        {
                            $cntMatrix     = array();
                            $variableArray = array();
                            foreach ($matches['variable'] AS $value)
                            {
                                $cnt = array_key_exists($value, $cntMatrix) ? $cntMatrix[$value] : 0;

                                $variableArray[] = strtolower($value).'#'.$cnt;

                                $cntMatrix[$value] = ++$cnt;
                            }
                            foreach ($matches['value'] AS $key => $value)
                            {
                                $matches['value'][$key] = trim($value);
                            }

                            $annotations = array_combine($variableArray, $matches['value']);
                        }
                    }

                    /**
                     * Wichtig: Die For-Schleife um die jeweiligen PHP Dateien auszuwerten wird immer nach
                     * der Class <classname> mit einem break; abbrechen. Beim Auslesen der Annotation werden
                     * somit nur der letzte AnnotationArray zurückgegeben der vor der Class <classname> steht.
                     */
                    if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0])
                    {
                        $index += 2; // Skip class keyword and whitespace

                        // $class = !str_starts_with('\\', $namespace) ? '\\' . $namespace : $namespace;
                        // $class = $class.'\\'.$tokens[$index][1];
                        $class = $namespace.'\\'.$tokens[$index][1];

                        // break if you have one class per file (psr-4 compliant)
                        // otherwise you'll need to handle class constants (Foo::class)
                        if($namespace && $annotations && !$methodAnnotations){
                            break;
                        }
                    }

                    if (
                        T_PUBLIC === $tokens[$index][0]
                        && T_WHITESPACE === $tokens[$index + 1][0]
                        && T_FUNCTION === $tokens[$index + 2][0]
                        && T_WHITESPACE === $tokens[$index + 3][0]
                        && T_STRING === $tokens[$index + 4][0] )
                    {

                        $index += 4; // Skip class keyword and whitespace

                        if(!array_key_exists($tokens[$index][1], $methods) && $class)
                        {
                            $methods[] = $tokens[$index][1];

                            $classes[] = [
                                'action'      => 'method',
                                'class'       => $class,
                                'method'      => $tokens[$index][1],
                                'annotations' => $annotations
                            ];

                            $annotations       = array();
                            $methodAnnotations = true;
                        }
                    }
                }

                if($class && !$methods && isset($tokens[$index][1]) && $tokens[$index][1])
                {
                    $classes[] = [
                        'action'      => 'class',
                        'class'       => $namespace.'\\'.$tokens[$index][1],
                        'method'      => '',
                        'annotations' => $annotations
                    ];
                }
            }
        }

        return $classes;
    }
}
<?php

/**
 * @todo: appControllerDirector
 * @todo: appTwigDirector
 * @todo: appScheduleDirector
 */

namespace Ayumila;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\Classes\ToastCollection;
use Ayumila\Core\CoreEngine;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Process;
use Ayumila\Http\Request;
use Ayumila\Http\RequestData;
use Ayumila\Http\RequestMock;
use Ayumila\Http\Response;
use Ayumila\Http\ResponseApplication;
use Ayumila\Http\ResponseTwigException;
use Ayumila\Http\Router;
use Ayumila\Http\RouterData;
use Ayumila\Http\Session;
use Ayumila\Traits\MultitonStandard;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Symfony\Component\Yaml\Yaml;

require_once (__DIR__.'/Functions/errorHandling.php');

class Application
{
    use MultitonStandard;

    private ?RequestMock  $requestMock = null;
    private array         $appControllerDirectory = array();

    /**
     * @param string $key
     * @param RequestMock|null $requestMock
     * @return static
     * @throws AyumilaException
     */
    public static function create(string $key, ?RequestMock $requestMock = null): self
    {
        if (!array_key_exists($key, self::$instances)) {
            self::$instances[$key] = new self();
            self::$instances[$key]->key = $key;
            self::$instances[$key]->loadAyumilaYaml();
            ApplicationController::create()->registerApplication(self::$instances[$key]);
        }

        self::$instances[$key]->requestMock = $requestMock;

        return self::$instances[$key];
    }

    private function __construct()
    {
        error_reporting(0);
        // error_reporting(E_ALL);

        // set_error_handler("AyumilaErrorHandler", E_ALL);
        register_shutdown_function("AyumilaShutdownFunction");
    }

    /**
     * @return array
     * @throws Exception
     */
    private function loadAyumilaYaml(): array
    {
        $ayumilaYaml = Yaml::parseFile(__DIR__.'/../../../../config/ayumila.yaml');
        try{
            $this->appControllerDirectory = $ayumilaYaml['Ayumila']['Controller']['Path'];
        }catch (Exception $ex)
        {
            throw new Exception('The entry Ayumila > Controller > Path must exist in the Ayumila yml.');
        }
        return $ayumilaYaml;
    }

    /**
     * @return array
     */
    public static function getKeys(): array
    {
        return array_keys(self::$instances);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param callable $middleware
     * @param bool $alsoDefaultExecute
     * @return $this
     * @throws AyumilaException
     */
    public function addMiddleware(callable $middleware, bool $alsoDefaultExecute = false): self
    {
        ApplicationMiddleware::create($this)
            ->addMiddleware($middleware, $alsoDefaultExecute);

        return $this;
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setDiContainer(Container $container): self
    {
        ApplicationDiContainer::create()
            ->addPhpDi($container);

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setSecurityClass(string $class): self
    {
        ApplicationSecurity::create()
            ->setSecurityClass($class);

        return $this;
    }

    /**
     * @param ResponseAbstract $responseAbstract
     * @param array $data
     * @param bool $forwardResponseData
     * @param string|null $responseClassNameAssign ( null is for all ResponseContentTypes )
     * @return $this
     */
    public function setSecurityDefaultResponse(ResponseAbstract $responseAbstract, array $data = array(), bool $forwardResponseData = false, ?string $responseClassNameAssign = null ): self
    {
        ApplicationSecurity::create()
            ->setDefaultResponse($responseAbstract, $data, $forwardResponseData, $responseClassNameAssign);

        return $this;
    }

    /**
     * @param bool $applicationResponse
     * @return mixed
     * @throws AyumilaException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function miao(bool $applicationResponse = false): mixed
    {
        /** initialise CoreEngine, Request, Response and ToastsCollection */
        CoreEngine     ::create()->setAppClassDirectory($this->appControllerDirectory)->run();
        Request        ::create($this, $this->requestMock);
        Response       ::create($this);
        ToastCollection::create($this)->run();

        /** initialise and start the Router */
        Router::create($this)->run();

        /** set ResponseDefault */
        Response::setResponseContentTypeByRouter();

        /** change the Response Content-Type to ResponseApplication */
        if($applicationResponse){
            Response::setResponseContentType(ResponseApplication::create());
        }

        /** initialise the Security Class, required the class for authentication is stored there */
        ApplicationSecurity::create();

        if(!$this->isFirstApplicationRoutePrivate() && RouterData::isAuthentication())
        {
            /** clear all session-keys from type SessionRedirect they not from the current uri*/
            Session::create()->clearSessionRedirect();

            /** initialise and start the middleware (pre) */
            ApplicationMiddleware::create($this)->run();

            /** initialise and start the Process */
            Process::create($this)->run();

        }else{
            /** initialise and start the middleware (pre) with only defaultExecute middleware callable */
            ApplicationMiddleware::create($this)->run(true);

            /** default site */
            ApplicationSecurity::create()->defaultResponse();
        }

        /** get the result from the Response */
        $result = Response::send($this);

        /** sends the log at the end of the application */
        ApplicationLog::send();

        /** ScheduleInterface planer */
        ApplicationSchedule::create()->run();

        /** delete all initialised classes */
        ApplicationController::create()->deleteApplicationAndMultitons($this);

        return $result;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    private function isFirstApplicationRoutePrivate(): bool
    {
        if(RouterData::isPrivate() && $this->getKey() === ApplicationControllerData::getFirstApplicationKey())
        {
            Response::setResponseContentType(ResponseTwigException::create());
            Response::addDataWithKey('Title', 'Ayumila AyumilaException');
            Response::addException('Router', 'The calling class or method is private and can\'t called directly as the first Application');
            Response::addException('Router', 'Uri: '.RequestData::getRequestUri());
            Response::addException('Router', 'Class: '.RouterData::getClass());
            Response::addException('Router', 'Method: '.RouterData::getMethod());
            return true;
        }
        return false;
    }
}
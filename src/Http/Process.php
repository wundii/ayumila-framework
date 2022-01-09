<?php

namespace Ayumila\Http;

use Ayumila\ApplicationDiContainer;
use Ayumila\ApplicationLog;
use Ayumila\Classes\Controller;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\MultitonStandard;
use DI\Container;
use Exception;

class Process
{
    use MultitonStandard;

    private ?Container $container = null;

    /**
     * @throws AyumilaException|Exception
     */
    public function run(): void
    {
        $container = ApplicationDiContainer::getDiContainer();

        switch(RouterData::getAction())
        {
            case 'class':
                $class      = RouterData::getClass();
                $routeFound = RouterData::isRouteFound();
                try{
                    if($container instanceof Container)
                    {
                        $addStatusTitle = '-DI';
                        $processClass = $container->get($class);
                    }else{
                        $addStatusTitle = '';
                        $processClass = (new $class());
                    }

                    if(!$processClass instanceof Controller && $routeFound)
                    {
                        throw new AyumilaException('This Class '.$class.' is not extends from Ayumila\Classes\Controller');
                    }
                    $processClass->process();
                } catch (AyumilaException $ex)
                {
                    ApplicationLog::addLog('Process-Exception'.$addStatusTitle, $ex->getMessage(), $ex);

                    $ex->twigAyumilaException();

                } catch (Exception $ex)
                {
                    ApplicationLog::addLog('Process-Exception'.$addStatusTitle, $ex->getMessage(), $ex);

                    $exception = processPhpException($ex, true);

                    Response::setResponseContentType(ResponseTwig::create('error/ayumilaException.twig'));
                    Response::addDataWithKey('Title', 'AyumilaException');
                    Response::addDataWithKey('Exception', $exception);

                }
                break;
            case 'method':
                $class      = RouterData::getClass();
                $method     = RouterData::getMethod();
                $routeFound = RouterData::isRouteFound();

                try{
                    if($container instanceof Container)
                    {
                        $addStatusTitle = '-DI';
                        $processClass = $container->get($class);
                    }else{
                        $addStatusTitle = '';
                        $processClass = (new $class());
                    }

                    if(!$processClass instanceof Controller && $routeFound)
                    {
                        throw new AyumilaException('This Class '.$class.' is not extends from Ayumila\Classes\Controller');
                    }
                    $processClass->$method();
                } catch (AyumilaException $ex)
                {
                    ApplicationLog::addLog('Process-Exception'.$addStatusTitle, $ex->getMessage(), $ex);

                    $ex->twigAyumilaException();

                } catch (Exception $ex)
                {
                    ApplicationLog::addLog('Process-Exception'.$addStatusTitle, $ex->getMessage(), $ex);

                    $exception = processPhpException($ex, true);

                    Response::setResponseContentType(ResponseTwig::create('error/ayumilaException.twig'));
                    Response::addDataWithKey('Title', 'AyumilaException');
                    Response::addDataWithKey('Exception', $exception);
                }
                break;
            default:
                throw new Exception('Es wurde keine gültige RouterAction übermittelt');
        }
    }
}
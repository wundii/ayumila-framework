<?php

namespace Ayumila\Classes;

use Ayumila\Application;
use Ayumila\ApplicationControllerData;
use Ayumila\ApplicationLog;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\RequestData;
use Ayumila\Http\RequestMock;
use Ayumila\Http\Response;
use Ayumila\Http\Session;
use Ayumila\Http\SessionRedirect;
use Exception;

class Controller
{
    /**
     * @param ToastStatus $status ['primary','secondary','success','danger','warning','info','light','dark']
     * @param string $title
     * @param string $content
     * @param bool $direct
     * @return void
     * @throws Exception
     */
    protected function addToast(ToastStatus $status, string $title, string $content = '', bool $direct = false): void
    {
        $newToast = ToastNotification::create()
            ->setStatus($status)
            ->setTitle(trim($title))
            ->setContent(trim($content));

        if($direct && ApplicationControllerData::getCurrantApplicationKey() === ApplicationControllerData::getFirstApplicationKey())
        {
            ToastCollection::create()->addToastNotification($newToast);
            
        }else{
            $session = Session::create();

            $toasts  = is_array($session->toasts) ? $session->toasts : array();

            $toasts[] = $newToast;

            $session->toasts = $toasts;
        }
    }

    /**
     * @param string $uri
     * @param array $postData
     * @throws AyumilaException
     */
    protected function redirect(string $uri, array $postData = array()): void
    {
        if($uri == RequestData::getRequestUri())
        {
            throw new AyumilaException('Redirect Loop!');
        }

        if($postData)
        {
            $session = Session::create();

            foreach ($postData AS $key => $data)
            {
                if(is_string($key))
                {
                    $session->{'redirect_'.$key} = (new SessionRedirect())
                        ->setUri($uri)
                        ->setKey($key)
                        ->setData($data);
                }else{
                    ApplicationLog::addLog('Redirect-PostData', 'Der $postData Key ist kein String', $postData);
                }
            }
        }

        ApplicationLog::send(true);

        if(!ApplicationControllerData::isTestMode())
        {
            header("Location: {$uri}");
            die();
        }
    }

    /**
     * @param string $key
     * @param RequestMock $mock
     * @return mixed
     * @throws AyumilaException
     */
    protected function callApplication(string $key, RequestMock $mock): mixed
    {
        return Application::create($key, $mock)->miao(true);
    }

    /**
     * @param string $tokenId
     * @param string|null $inputName
     * @return bool
     * @throws AyumilaException
     */
    protected function isCsrfTokenValid(string $tokenId, ?string $inputName = null): bool
    {
        $token = $inputName ? RequestData::getREQUEST($inputName) : RequestData::getREQUEST('csrf_token');

        $tokenValidStatus = CsrfManager::create()->isCsrfEqual($tokenId, $token);
        if(!$tokenValidStatus)
        {
            ApplicationLog::addLog('Csrf-Token', 'Die Token-Validierung war nicht erfolgreich.', [$tokenId, $token, Session::create()->getSessionDatalist('csrfToken')]);

            Response::addDataWithKey($inputName ?? 'csrf_token', $token);
            return false;
        }

        return true;
    }

    /**
     * @param string $tokenId
     * @param string|null $inputName
     * @return string
     * @throws AyumilaException
     * @throws Exception
     */
    protected function createCsrfTokenTwigPost(string $tokenId, ?string $inputName = null): string
    {
        $token = CsrfManager::create()->getCsrfToken($tokenId);
        Response::addDataWithKey($inputName ?? 'csrf_token', $token);

        return $token;
    }
}
<?php

namespace Ayumila\Classes;

use Ayumila\Application;
use Ayumila\ApplicationLog;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\RequestData;
use Ayumila\Http\RequestMock;
use Ayumila\Http\Session;
use Ayumila\Http\SessionRedirect;

class Controller
{
    /**
     * @param string $status ['primary','secondary','success','danger','warning','info','light','dark']
     * @param string $title
     * @param string $content
     * @return void
     */
    protected function addToast(string $status, string $title, string $content): void
    {
        $status = strtolower(trim($status));
        $bootstrapStatus = [
            'primary',
            'secondary',
            'success',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
        ];

        if(in_array($status, $bootstrapStatus))
        {
            $session = Session::create();

            $toasts  = is_array($session->toasts) ? $session->toasts : array();

            $newToast = ToastNotification::create()
                ->setStatus($status)
                ->setTitle(trim($title))
                ->setContent(trim($content));

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

        header("Location: {$uri}");
        die();
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
}
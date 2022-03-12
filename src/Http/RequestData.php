<?php

namespace Ayumila\Http;

use Ayumila\Exceptions\AyumilaException;

class RequestData extends Request
{
    /**
     * @return array
     * @throws AyumilaException
     */
    public static function getHEADER():array
    {
        $instance = Request::create();
        return $instance->var_HEADER;
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public static function getSERVER():array
    {
        $instance = Request::create();
        return $instance->var_SERVER;
    }

    /**
     * @param int|string|null $key
     * @return mixed
     * @throws AyumilaException
     */
    public static function getGET( int|string|null $key = null ):mixed
    {
        $instance = Request::create();
        if($key){
            if(array_key_exists($key, $instance->var_GET))
            {
                if(is_string($instance->var_GET[$key]))
                {
                    return trim($instance->var_GET[$key]);
                }

                return $instance->var_GET[$key];
            }else{
                return null;
            }
        }

        return $instance->var_GET;
    }

    /**
     * @param int|string $key
     * @return bool
     * @throws AyumilaException
     */
    public static function isGET( int|string $key ): bool
    {
        $instance = Request::create();
        if(array_key_exists($key, $instance->var_GET))
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param int|string|null $key
     * @return mixed
     * @throws AyumilaException
     */
    public static function getPOST( int|string|null $key = null ):mixed
    {
        $session  = Session::create();
        $instance = Request::create();
        if($key){
            if(array_key_exists($key, $instance->var_POST))
            {
                if(is_string($instance->var_POST[$key]))
                {
                    return trim($instance->var_POST[$key]);
                }

                return $instance->var_POST[$key];

            }elseif($session instanceof Session && isset($session->{'redirect_'.$key})) {

                return SessionRedirect::getRedirectDataFromSession(self::getRequestUri(), $key);

            }{
                return null;
            }
        }

        return $instance->var_POST;
    }

    /**
     * @param int|string $key
     * @return bool
     * @throws AyumilaException
     */
    public static function isPOST( int|string $key ): bool
    {
        $instance = Request::create();
        if(array_key_exists($key, $instance->var_POST))
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param int|string|null $key
     * @return mixed
     * @throws AyumilaException
     */
    public static function getREQUEST( int|string|null $key = null ):mixed
    {
        $session  = Session::create();
        $instance = Request::create();

        if($key){
            if(array_key_exists($key, $instance->var_REQUEST))
            {
                if(is_string($instance->var_REQUEST[$key]))
                {
                    return trim($instance->var_REQUEST[$key]);
                }

                return $instance->var_REQUEST[$key];

            }elseif($session instanceof Session && isset($session->{'redirect_'.$key})) {

                return SessionRedirect::getRedirectDataFromSession(self::getRequestUri(), $key);

            }else{
                return null;
            }
        }

        return $instance->var_REQUEST;
    }

    /**
     * @param int|string $key
     * @return bool
     * @throws AyumilaException
     */
    public static function isREQUEST( int|string $key ): bool
    {
        $instance = Request::create();
        if(array_key_exists($key, $instance->var_REQUEST))
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param string|null $key
     * @return mixed
     * @throws AyumilaException
     */
    public static function getFILES(string|null $key = null): mixed
    {
        $instance = Request::create();

        if($key){
            if(array_key_exists($key, $instance->var_FILES))
            {
                return $instance->var_FILES[$key];
            }else{
                return null;
            }
        }

        return $instance->var_FILES;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getFileContent():string
    {
        $instance = Request::create();
        return $instance->var_FileContent;
    }

    /**
     * @param string|null $key
     * @return mixed
     * @throws AyumilaException
     */
    public static function getENV(string|null $key = null): mixed
    {
        $instance = Request::create();
        $phpIniEnv = $instance->var_ENV;

        if($key)
        {
            if(isset($phpIniEnv[$key]))
            {
                return $phpIniEnv[$key];
            }elseif(getenv($key, true))
            {
                return getenv($key, true);
            }else{
                return null;
            }
        }

        return $phpIniEnv;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getRequestMethod():string
    {
        self::checkRequestMethod();

        $instance = Request::create();
        return $instance->var_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    private static function checkRequestMethod(): bool
    {
        $instance = Request::create();
        switch($instance->var_SERVER['REQUEST_METHOD'])
        {
            case 'HEAD':
            case 'POST':
            case 'GET':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                return true;
            default:
                throw new AyumilaException("Unknown request method ".$instance->var_SERVER['REQUEST_METHOD']."!");
        }
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getRequestUri():string
    {
        $instance = Request::create();
        return $instance->var_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getRouterPathVariable( int|string|null $key = null ):mixed
    {
        if($key){
            $key  = mb_strtolower($key);
            $path = RouterData::getParametersFromUrlPath();
            if(array_key_exists($key, $path))
            {
                return $path[$key];
            }else{
                return null;
            }
        }

        return RouterData::getParametersFromUrlPath();
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getUrl(): string
    {
        $instance = Request::create();

        $url  = isset($instance->var_SERVER['HTTPS']) && $instance->var_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $url .= "://{$instance->var_SERVER['HTTP_HOST']}";
        $url .= parse_url($instance->var_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return $url;
    }

    /**
     * @param string $scheme
     * @return string
     * @throws AyumilaException
     */
    public static function getBaseUrl(string $scheme = ''): string
    {
        $instance = Request::create();

        $scheme = strtolower(trim($scheme));
        $validScheme = [
            'http',
            'https',
        ];

        if($scheme && in_array($scheme, $validScheme)){
            $url = $scheme;
        }else{
            $url = isset($instance->var_SERVER['HTTPS']) && $instance->var_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        }

        $url .= "://{$instance->var_SERVER['HTTP_HOST']}";

        return $url;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getHost(): string
    {
        $instance = Request::create();
        return $instance->var_SERVER['HTTP_HOST'];
    }

    /**
     * @return string|null
     * @throws AyumilaException
     */
    public static function getHttps(): ?string
    {
        $instance = Request::create();
        return isset($instance->var_SERVER['HTTPS']) && $instance->var_SERVER['HTTPS'] === 'on' ? 'on' : null;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public static function getSerializedObject(): string
    {
        return serialize(Request::create());
    }
}
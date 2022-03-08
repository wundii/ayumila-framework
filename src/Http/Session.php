<?php

namespace Ayumila\Http;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\SingletonStandard;
use SmUser;

/**
 * @property string $loginSession;
 * @property SmUser $loginUser;
 * @property array  $toasts;
 * @property string $redirect_;
 */
class Session
{
    use SingletonStandard;

    private bool $status = false;

    /**
     * constructor
     */
    private function __construct()
    {
        if(!$this->status AND !isset($_SESSION))
        {
            if(php_sapi_name() != "cli")
            {
                $this->status = session_start();
            }else{
                $this->status = true;
            }
        }
    }

    /**
     * overwrite SingletonStandard->delete()
     */
    public static function delete(): void
    {
        if (self::$instance !== null)
        {
            if(php_sapi_name() === "cli" && isset($_SESSION) && array_keys($_SESSION))
            {
                foreach ($_SESSION AS $key => $session)
                {
                    unset($_SESSION[$key]);
                }
            }
            self::$instance = null;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        if (isset($_SESSION[$key])){

            return $_SESSION[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     */
    public function __unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * @param string|null $sessionKey
     * @return array|string|null
     */
    public function getSessionDatalist(?string $sessionKey = null): array|string|null
    {
        $sessionData = array();
        foreach ($_SESSION as $key => $value)
        {
            $sessionData[$key] = $value;
        }

        if($sessionKey)
        {
            $sessionData = [ $sessionKey => array_key_exists($sessionKey, $sessionData) ? $sessionData[$sessionKey] : null ];
        }

        return $sessionData;
    }

    /**
     * @return bool
     */
    public function killSession(): bool
    {
        if(php_sapi_name() != "cli")
        {
            return session_destroy();
        }else{
            return true;
        }
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    public function clearSessionRedirect(): bool
    {
        if(!RouterData::isRouteFound())
        {
            return false;
        }

        foreach ($this->getSessionDatalist() AS $key => $data)
        {
            if(str_starts_with($key, 'redirect_'))
            {
                if($data instanceof SessionRedirect)
                {
                    if($data->getUri() != RequestData::getRequestUri())
                    {
                        unset($this->{$key});
                    }
                }
            }
        }

        return true;
    }
}
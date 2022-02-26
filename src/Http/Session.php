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
     * @return mixed
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
     * @return array
     */
    public function getSessionDatalist(): array
    {
        $returnArray = array();
        foreach ($_SESSION as $key => $value)
        {
            $returnArray[$key] = $value;
        }

        return $returnArray;
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
     * @return void
     * @throws AyumilaException
     */
    public function clearSessionRedirect(): void
    {
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
    }
}
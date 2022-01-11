<?php

namespace Ayumila\Http;

class SessionRedirect
{
    private string $uri;
    private string $key;
    private mixed  $data;

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return SessionRedirect
     */
    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return SessionRedirect
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return SessionRedirect
     */
    public function setData(mixed $data): self
    {
        $this->data = $data;

        return $this;
    }


    public static function getRedirectDataFromSession(string $completeUrl, string $key): mixed
    {
        $session = Session::create();

        $redirect = $session->{'redirect_'.$key};

        if($redirect instanceof SessionRedirect)
        {
            if($redirect->getKey() == $key && $redirect->getUri() == $completeUrl )
            {
                $data = $redirect->getData();
                if(is_string($data))
                {
                    return trim($data);
                }

                return $data;
            }
        }

        return null;
    }
}
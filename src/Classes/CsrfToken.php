<?php

namespace Ayumila\Classes;

use \Ayumila\Interfaces\CsrfTokenInterface;

final class CsrfToken implements CsrfTokenInterface
{
    private string $id;
    private string $token;

    /**
     * @param string $id
     * @param string $token
     */
    public function __construct(string $id, string $token)
    {
        $this->id    = $id;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function __toString(): string
    {
        return $this->token;
    }
}
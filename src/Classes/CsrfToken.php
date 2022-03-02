<?php

namespace Ayumila\Classes;

use \Ayumila\Interfaces\CsrfTokenInterface;

final class CsrfToken implements CsrfTokenInterface
{
    private string $id;
    private string $token;
    private int    $timestamp;

    /**
     * @param string $id
     * @param string $token
     */
    public function __construct(string $id, string $token)
    {
        $this->id        = $id;
        $this->token     = $token;
        $this->timestamp = time();
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
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return mixed
     */
    public function __toString(): string
    {
        return $this->token;
    }
}
<?php

namespace Ayumila\Classes;

use Ayumila\Http\Session;
use Ayumila\Interfaces\CsrfManagerInterface;
use Ayumila\Interfaces\CsrfTokenInterface;
use Exception;

final class CsrfManager implements CsrfManagerInterface
{

    private function __construct() {}

    /**
     * @return CsrfManager
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param string $tokenId
     * @param string $token
     * @return bool
     */
    public function isCsrfEqual(string $tokenId, string $token): bool
    {
        if(!$this->hasStorageCsrfToken($tokenId))
        {
            return false;
        }

        return hash_equals($this->getStorageCsrfToken($tokenId), $token);
    }

    /**
     * @param string $tokenId
     * @return CsrfTokenInterface
     * @throws Exception
     */
    public function getCsrfToken(string $tokenId): CsrfTokenInterface
    {
        $token = hash_hmac('sha3-256', $tokenId, random_bytes(32));

        $this->setStorageCsrfToken($tokenId, $token);

        return new CsrfToken($tokenId, $token);
    }

    /**
     * @param string $tokenId
     * @return bool
     */
    public function hasStorageCsrfToken(string $tokenId): bool
    {
        if(!isset(Session::create()->csrfToken))
        {
            return false;
        }

        return isset(Session::create()->csrfToken[$tokenId]);
    }

    /**
     * @param string $tokenId
     * @param string $token
     * @return void
     */
    private function setStorageCsrfToken(string $tokenId, string $token): void
    {
        $csrfToken = Session::create()->csrfToken ?? array();
        $csrfToken[$tokenId] = $token;
        Session::create()->csrfToken = $csrfToken;
    }

    /**
     * @param string $tokenId
     * @return string|null
     */
    public function getStorageCsrfToken(string $tokenId): ?string
    {
        if(!$this->hasStorageCsrfToken($tokenId))
        {
            return null;
        }

        return Session::create()->csrfToken[$tokenId];
    }

    /**
     * @param string $tokenId
     * @return string|null
     */
    public function removeStorageCsrfToken(string $tokenId): ?string
    {
        if(!$this->hasStorageCsrfToken($tokenId))
        {
            return null;
        }

        $token = Session::create()->csrfToken[$tokenId];

        unset(Session::create()->csrfToken[$tokenId]);

        return $token;
    }
}
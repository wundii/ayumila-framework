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

        return hash_equals((string)$this->getStorageCsrfToken($tokenId), $token);
    }

    /**
     * @param string $tokenId
     * @return CsrfTokenInterface
     * @throws Exception
     */
    public function getCsrfToken(string $tokenId): CsrfTokenInterface
    {
        $token = hash_hmac('sha3-256', $tokenId, random_bytes(32));

        $csrfToken = new CsrfToken($tokenId, $token);

        $this->setStorageCsrfToken($tokenId, $csrfToken);

        return $csrfToken;
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
     * @param CsrfTokenInterface $csrfToken
     * @return void
     */
    private function setStorageCsrfToken(string $tokenId, CsrfTokenInterface $csrfToken): void
    {
        $token = Session::create()->csrfToken ?? array();

        foreach ($token AS $tokenEntityId => $tokenEntity)
        {
            if($tokenEntity instanceof CsrfTokenInterface && $tokenEntity->getTimestamp() <= time()-60*60)
            {
                unset($token[$tokenEntityId]);
            }
        }

        $token[$tokenId] = $csrfToken;
        Session::create()->csrfToken = $token;
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
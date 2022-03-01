<?php

namespace Ayumila\Interfaces;

interface CsrfManagerInterface
{
    public function isCsrfEqual(string $tokenId, string $token): bool;
    public function getCsrfToken(string $tokenId): CsrfTokenInterface;
    public function hasStorageCsrfToken(string $tokenId): bool;
    public function getStorageCsrfToken(string $tokenId): ?string;
    public function removeStorageCsrfToken(string $tokenId): ?string;
}
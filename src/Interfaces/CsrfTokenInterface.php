<?php

namespace Ayumila\Interfaces;

interface CsrfTokenInterface
{
    public function getId(): string;
    public function getToken(): string;
    public function getTimestamp(): int;
    public function __toString(): string;
}
<?php

namespace Ayumila\Classes;

class ValidateRule
{
    private bool $isRequest = true;
    private mixed $value;
    private string $rule;

    /**
     * @return bool
     */
    public function isRequest(): bool
    {
        return $this->isRequest;
    }

    /**
     * @param bool $isRequest
     */
    public function setIsRequest(bool $isRequest): void
    {
        $this->isRequest = $isRequest;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @param array|string $rule
     */
    public function setRule(array|string $rule): void
    {
        if(is_array($rule))
        {
            $rule = implode('|', $rule);
        }

        $this->rule = $rule;
    }
}
<?php

namespace Ayumila\Classes;

use Exception;

class ShellExec
{
    public const JSON   = 'json';
    public const NULL   = 'null';
    public const STRING = 'string';
    public const XML    = 'xml';

    private ?string $result = '';
    private array   $error  = array();

    /**
     * @throws Exception
     */
    public function run(string $command): self
    {
        $result = shell_exec($command);

        $this->result = $result;

        return $this;
    }

    /**
     * @param string $expectedType
     * @return bool
     */
    public function isValid(string $expectedType = self::STRING): bool
    {
        $expectedType = match($expectedType)
        {
            self::JSON,
            self::NULL,
            self::STRING,
            self::XML => $expectedType,
            default => false
        };

        if($expectedType)
        {
            if(!method_exists($this, 'expect'.ucfirst($expectedType)))
            {
                $this->error[] = 'the method for validation is not available';
                return false;
            }

            if($this->{'expect'.ucfirst($expectedType)}($this->result))
            {
                return true;
            }
        }

        $this->error[] = 'the shell_exec return type '.$expectedType.' was not transmitted';
        return false;
    }

    /**
     * @param string|null $result
     * @return bool
     */
    private function expectJson(?string $result): bool
    {
        json_decode($result);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param string|null $result
     * @return bool
     */
    private function expectNull(?string $result): bool
    {
        return $result === null;
    }

    /**
     * @param string|null $result
     * @return bool
     */
    private function expectString(?string $result): bool
    {
        return is_string($result);
    }

    /**
     * @param string|null $result
     * @return bool
     */
    private function expectXml(?string $result): bool
    {
        return simplexml_load_string($result) !== false;
    }

    /**
     * @return ?string
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }
}
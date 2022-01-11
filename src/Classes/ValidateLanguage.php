<?php

namespace Ayumila\Classes;

use Ayumila\Traits\CreateStandard;

class ValidateLanguage
{
    use CreateStandard;

    private string $validateMethod = '';
    private string $output = '';

    /**
     * @return string
     */
    public function getValidateMethod(): string
    {
        return $this->validateMethod;
    }

    /**
     * @param string $validateMethod
     * @return self
     */
    public function setValidateMethod(string $validateMethod): self
    {
        $this->validateMethod = $validateMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return self
     */
    public function setOutput(string $output): self
    {
        $this->output = $output;
        return $this;
    }
}
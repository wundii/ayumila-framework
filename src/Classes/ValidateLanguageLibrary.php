<?php

namespace Ayumila\Classes;

use Ayumila\Traits\CreateStandard;

class ValidateLanguageLibrary
{
    use CreateStandard;

    private array $library = array();

    /**
     * @return array
     */
    public function getLibrary(): array
    {
        return $this->library;
    }

    /**
     * @param string|null $validateMethod
     * @return ?ValidateLanguage
     */
    public function getLanguageByValidateMethod(?string $validateMethod = null): ?ValidateLanguage
    {
        if(array_key_exists($validateMethod, $this->library))
        {
            return $this->library[$validateMethod];
        }

        return null;
    }

    /**
     * @param ValidateLanguage $library
     */
    public function setLibrary(ValidateLanguage $library): void
    {
        $key = $library->getValidateMethod();

        $this->library[$key] = $library;
    }

    /**
     * @param string $validateMethod
     * @return bool
     */
    public function isValidateMethodAvailable(string $validateMethod): bool
    {
        return array_key_exists($validateMethod, $this->library);
    }

    /**
     * @param ValidateLanguage $language
     * @return self
     */
    public function setLanguage(ValidateLanguage $language): self
    {
        $validateMethod = $language->getValidateMethod();

        $this->library[$validateMethod] = $language;

        return $this;
    }

    /**
     * @param string $validateMethod
     * @param string $output
     * @return self
     */
    public function setLanguageWithParameter(string $validateMethod, string $output): self
    {
        $this->library[$validateMethod] = ValidateLanguage::create()
            ->setValidateMethod($validateMethod)
            ->setOutput($output);

        return $this;
    }

    /**
     * @param string $validateMethod
     * @return string
     */
    public function getOutputByVariableMethod(string $validateMethod): string
    {
        if(array_key_exists($validateMethod, $this->library))
        {
            return $this->getLanguageByValidateMethod($validateMethod)->getOutput();
        }

        return '';
    }
}
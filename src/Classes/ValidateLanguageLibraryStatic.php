<?php

namespace Ayumila\Classes;

use Ayumila\Traits\CreateStandard;

class ValidateLanguageLibraryStatic
{
    use CreateStandard;

    private array $library = array();
    private array $libraryTemp = array();

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
     * @param string $output
     * @return self
     */
    public function setTempLanguageWithParameter(string $validateMethod, string $output): self
    {
        $this->libraryTemp[$validateMethod] = ValidateLanguage::create()
            ->setValidateMethod($validateMethod)
            ->setOutput($output);

        return $this;
    }

    /**
     * @return self
     */
    public function resetTempLibrary(): self
    {
        $this->libraryTemp = array();

        return $this;
    }

    /**
     * @return ValidateLanguageLibrary
     */
    public function getLanguageLibrary(): ValidateLanguageLibrary
    {
        $languageLibrary = ValidateLanguageLibrary::create();

        foreach ($this->library AS $language)
        {
            $languageLibrary->setLanguage($language);
        }

        foreach ($this->libraryTemp AS $language)
        {
            $languageLibrary->setLanguage($language);
        }

        return $languageLibrary;
    }
}
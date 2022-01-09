<?php

namespace Ayumila\Traits;

use Ayumila\Classes\Propel;
use Ayumila\Classes\ToastNotification;
use Ayumila\Classes\ValidateLanguage;
use Ayumila\Classes\ValidateLanguageLibrary;
use Ayumila\Classes\ValidateLanguageLibraryStatic;
use smarthome\PdoTool;

trait CreateStandard
{
    /**
     * @return CreateStandard|Propel|ToastNotification|ValidateLanguage|ValidateLanguageLibrary|ValidateLanguageLibraryStatic|PdoTool
     */
    public static function create(): self
    {
        return new self;
    }

    private function __construct() {}
}
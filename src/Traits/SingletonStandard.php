<?php

namespace Ayumila\Traits;

use Ayumila\ApplicationController;
use Ayumila\ApplicationDiContainer;
use Ayumila\ApplicationEvent;
use Ayumila\ApplicationLog;
use Ayumila\ApplicationSchedule;
use Ayumila\ApplicationSecurity;
use Ayumila\Core\CoreEngine;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Session;

trait SingletonStandard
{
    private static ?self $instance = null;

    /**
     * @return SingletonStandard|ApplicationController|ApplicationDiContainer|ApplicationEvent|ApplicationLog|ApplicationSchedule|ApplicationSecurity|CoreEngine|Session
     */
    public static function create(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new AyumilaException("Cannot unserialize singleton");
    }
    private function __construct() {}

    /**
     *
     */
    public static function delete(): void
    {
        if (self::$instance !== null) {
            self::$instance = null;
        }
    }
}
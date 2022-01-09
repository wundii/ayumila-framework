<?php

namespace Ayumila\Schedule;

abstract class ScheduleAbstract
{
    abstract public function process(): void;
    abstract public function trigger(): Trigger;
}
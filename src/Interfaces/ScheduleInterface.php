<?php

namespace Ayumila\Interfaces;

use Ayumila\Schedule\Trigger;

interface ScheduleInterface
{
    public function process(): void;
    public function trigger(): Trigger;
}
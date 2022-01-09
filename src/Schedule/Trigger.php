<?php

namespace Ayumila\Schedule;

class Trigger
{
    private bool $monday    = false;
    private bool $tuesday   = false;
    private bool $wednesday = false;
    private bool $thursday  = false;
    private bool $friday    = false;
    private bool $saturday  = false;
    private bool $sunday    = false;

    private int  $startHour   = 8;
    private int  $startMinute = 0;

    private int  $endHour     = 18;
    private int  $endMinute   = 0;

    private int  $whileMinute = 5;

    private bool $alwaysOn    = false;

    /**
     * @return bool
     */
    public function isMonday(): bool
    {
        return $this->monday;
    }

    /**
     * @param bool $monday
     */
    public function setMonday(bool $monday): void
    {
        $this->monday = $monday;
    }

    /**
     * @return bool
     */
    public function isTuesday(): bool
    {
        return $this->tuesday;
    }

    /**
     * @param bool $tuesday
     */
    public function setTuesday(bool $tuesday): void
    {
        $this->tuesday = $tuesday;
    }

    /**
     * @return bool
     */
    public function isWednesday(): bool
    {
        return $this->wednesday;
    }

    /**
     * @param bool $wednesday
     */
    public function setWednesday(bool $wednesday): void
    {
        $this->wednesday = $wednesday;
    }

    /**
     * @return bool
     */
    public function isThursday(): bool
    {
        return $this->thursday;
    }

    /**
     * @param bool $thursday
     */
    public function setThursday(bool $thursday): void
    {
        $this->thursday = $thursday;
    }

    /**
     * @return bool
     */
    public function isFriday(): bool
    {
        return $this->friday;
    }

    /**
     * @param bool $friday
     */
    public function setFriday(bool $friday): void
    {
        $this->friday = $friday;
    }

    /**
     * @return bool
     */
    public function isSaturday(): bool
    {
        return $this->saturday;
    }

    /**
     * @param bool $saturday
     */
    public function setSaturday(bool $saturday): void
    {
        $this->saturday = $saturday;
    }

    /**
     * @return bool
     */
    public function isSunday(): bool
    {
        return $this->sunday;
    }

    /**
     * @param bool $sunday
     */
    public function setSunday(bool $sunday): void
    {
        $this->sunday = $sunday;
    }

    public function getWeekArray():array
    {
        $return = array();

        if($this->isMonday())    $return[1] = true;
        if($this->isTuesday())   $return[2] = true;
        if($this->isWednesday()) $return[3] = true;
        if($this->isThursday())  $return[4] = true;
        if($this->isFriday())    $return[5] = true;
        if($this->isSaturday())  $return[6] = true;
        if($this->isSunday())    $return[7] = true;

        return $return;
    }

    /**
     * @return int
     */
    public function getStartHour(): int
    {
        return $this->startHour;
    }

    /**
     * @param int $startHour
     */
    public function setStartHour(int $startHour): void
    {
        if($startHour < 1)
        {
            $startHour = 1;
        }

        if($startHour > 24)
        {
            $startHour = 24;
        }

        $this->startHour = $startHour;
    }

    /**
     * @return int
     */
    public function getStartMinute(): int
    {
        return $this->startMinute;
    }

    /**
     * @param int $startMinute
     */
    public function setStartMinute(int $startMinute): void
    {
        if($startMinute < 0)
        {
            $startMinute = 0;
        }

        if($startMinute > 59)
        {
            $startMinute = 59;
        }

        $this->startMinute = $startMinute;
    }

    /**
     * @return int
     */
    public function getEndHour(): int
    {
        if($this->startHour >= $this->endHour)
        {
            return $this->startHour;
        }

        return $this->endHour;
    }

    /**
     * @param int $endHour
     */
    public function setEndHour(int $endHour): void
    {
        if($endHour < 1)
        {
            $endHour = 1;
        }

        if($endHour > 24)
        {
            $endHour = 24;
        }

        $this->endHour = $endHour;
    }

    /**
     * @return int
     */
    public function getEndMinute(): int
    {
        if($this->startHour >= $this->endHour && $this->startMinute >= $this->endMinute)
        {
            return $this->startMinute;
        }

        return $this->endMinute;
    }

    /**
     * @param int $endMinute
     */
    public function setEndMinute(int $endMinute): void
    {
        if($endMinute < 0)
        {
            $endMinute = 0;
        }

        if($endMinute > 59)
        {
            $endMinute = 59;
        }

        $this->endMinute = $endMinute;
    }

    /**
     * @return int
     */
    public function getWhileMinute(): int
    {
        return $this->whileMinute;
    }

    /**
     * @param int $whileMinute
     */
    public function setWhileMinute(int $whileMinute): void
    {
        if($whileMinute < 1)
        {
            $whileMinute = 1;
        }

        if($whileMinute > 720)
        {
            $whileMinute = 720;
        }

        $this->whileMinute = $whileMinute;
    }

    /**
     * @return bool
     */
    public function isAlwaysOn(): bool
    {
        return $this->alwaysOn;
    }

    /**
     *
     */
    public function setAlwaysOn()
    {
        $this->alwaysOn = true;
    }
}
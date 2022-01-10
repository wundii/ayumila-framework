<?php

ini_set('display_errors', '1');
ini_set("date.timezone", "Europe/Berlin");

require(__DIR__ . "/../../../../autoload.php");

use Ayumila\Classes\Helper;
use Ayumila\Schedule\ScheduleAbstract;
use Ayumila\Schedule\Trigger;
use Symfony\Component\Yaml\Yaml;

$ayumilaYaml = Yaml::parseFile(__DIR__.'/../../../../../config/ayumila.yaml');

if(isset($ayumilaYaml['Ayumila']['Schedule']['AutoloadFiles'])){
    foreach ($ayumilaYaml['Ayumila']['Schedule']['AutoloadFiles'] AS $autoloadFile)
    {
        if(file_exists($autoloadFile))
        {
            require_once($autoloadFile);
        }
    }
}

$scheduleClasses = array();
if(isset($ayumilaYaml['Ayumila']['Schedule']['ProcessClasses'])){
    foreach ($ayumilaYaml['Ayumila']['Schedule']['ProcessClasses'] AS $class)
    {
        if(class_exists($class))
        {
            $scheduleClasses[] = $class;
        }
    }
}

$time          = 0;
$processList   = array();
$secondExecute = [0, 15, 30, 45];
while(true)
{
    $date    = new DateTime();
    $second  = $date->format( 's' );
    $minute  = $date->format( 'i' );
    $hour    = $date->format( 'H' );

    if($time != $hour.$minute)
    {
        $time = $hour.$minute;
        $processList = array();
    }

    if(in_array($second, $secondExecute))
    {
        foreach ($scheduleClasses AS $scheduleClass)
        {
            if(class_exists($scheduleClass) && !in_array($scheduleClass, $processList))
            {
                $schedule = new $scheduleClass;
                if($schedule instanceof ScheduleAbstract)
                {
                    $trigger = $schedule->trigger();
                    if(isTrigger($trigger, $date))
                    {
                        $schedule->process();
                        $processList[] = $scheduleClass;
                    }
                }
                unset($schedule);
            }
        }
    }

    sleep(1);
}

/**
 * @param Trigger $trigger
 * @param DateTime $date
 * @return bool
 */
function isTrigger(Trigger $trigger, DateTime $date): bool
{
    $weekDay = $date->format( 'N' );
    $minute  = $date->format( 'i' );
    $hour    = $date->format( 'H' );

    if(array_key_exists($weekDay, $trigger->getWeekArray()))
    {
        if($trigger->isAlwaysOn() || $trigger->getStartHour() <= $hour && $trigger->getEndHour() >= $hour)
        {
            if(!$trigger->isAlwaysOn() && $trigger->getStartHour() == $hour && $trigger->getStartMinute() > $minute)
            {
                return false;
            }

            if(!$trigger->isAlwaysOn() && $trigger->getEndHour() == $hour && $trigger->getEndMinute() < $minute)
            {
                return false;
            }

            if(Helper::getModulo($minute, $trigger->getWhileMinute()) == 0)
            {
                return true;
            }
        }
    }

    return false;
}
<?php

namespace Ayumila;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\SingletonStandard;

class ApplicationSchedule
{
    use SingletonStandard;

    private int    $ttl            = 60*15;
    private string $tag            = 'ayumila-back-process';
    private string $scheduleScript = __DIR__ . '/Schedule/Schedule.php';

    /**
     * @throws AyumilaException
     */
    private function __construct()
    {
        if(!extension_loaded('apcu') || !apcu_enabled())
        {
            throw new AyumilaException('apcu is not enable');
        }
    }

    /**
     * @param bool $force
     * @throws AyumilaException
     */
    public function run(bool $force = false)
    {

        $force = true;

        if($force || ApplicationControllerData::getCurrantApplicationKey() === ApplicationControllerData::getFirstApplicationKey())
        {
            if($force || !apcu_exists('app_schedule_pid'))
            {
                if($force)
                {
                    // exist a ScheduleAbstract-Process, then kill this
                    $pid = apcu_fetch('app_schedule_pid');
                    if($pid)
                    {
                        $this->killProcess($pid);
                    }
                }

                // is a ScheduleAbstract-ProcessPid active?
                $pid = $this->processList();


                if($pid)
                {
                    if(!apcu_store('app_schedule_pid', $pid, $this->ttl))
                    {
                        throw new AyumilaException('Failed to write app_schedule_pid to the apcu cache');
                    }
                }else{
                    // start a new ScheduleAbstract-Process
                    $this->executeProcess();

                    // get the ScheduleAbstract-ProcessPid
                    $pid = $this->processList();

                    if($pid)
                    {
                        if(!apcu_store('app_schedule_pid', $pid, $this->ttl))
                        {
                            throw new AyumilaException('Failed to write app_schedule_pid to the apcu cache');
                        }
                    }else{
                        throw new AyumilaException('ApplicationSchedule can\'t found the ScheduleScript');
                    }
                }
            }
        }
    }

    /**
     * @return int
     */
    private function processList(): int
    {
        $exec = shell_exec('pgrep -a php | grep '.$this->tag);
        $explode = explode('--tag '.$this->tag, substr(trim($exec), 0, strlen($this->tag)*-1));

        if($exec && $explode)
        {
            $explodeCnt = count($explode);

            if($explodeCnt === 1)
            {
                $process = explode(' ', trim($explode[0]));
                return $process[0];
            }else{
                $i = 0;
                $returnPid = 0;
                foreach ($explode AS $processes)
                {
                    $process = explode(' ', trim($processes));

                    echo $process[0]."<br>";
                    if($explodeCnt == ++$i)
                    {
                        $returnPid = $process[0];
                    }else{
                        $this->killProcess($process[0]);
                    }
                }
                return $returnPid;
            }
        }else{
            return 0;
        }
    }

    /**
     *
     */
    private function executeProcess()
    {
        $command = 'php '.$this->scheduleScript.' --tag '.$this->tag;

        if (str_starts_with(PHP_OS, 'Linux'))
        {
            shell_exec($command.' >/dev/null 2>&1 &');
        }else{
            system($command." > NUL");
        }
    }

    /**
     * @param int $pid
     */
    private function killProcess(int $pid)
    {
        if (str_starts_with(PHP_OS, 'Linux'))
        {
            shell_exec('kill '.$pid);
        }else{
            system('taskkill /F /PID '.$pid);
        }
    }
}
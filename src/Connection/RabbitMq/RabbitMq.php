<?php

namespace Ayumila\Connection\RabbitMq;

use Ayumila\Exceptions\AyumilaException;
use Exception;

class RabbitMq extends RabbitMqAbstract
{
    private int $errorCounter      = 0;
    private int $errorCounterBreak = 5;

    /**
     * @param RabbitMqWorkerAbstract $rabbitMqWorker
     * @param bool $doWhile
     * @param int $whileRuns
     * @return array
     * @throws AyumilaException|Exception
     */
    public final function run(RabbitMqWorkerAbstract $rabbitMqWorker, bool $doWhile = false, int $whileRuns = 1): array
    {
        /*
         * Setzt den whileMode und die angegebene Anzahl an durchläufen
         * für die while Schleife fest
         * -> wenn whileMode true ist, läuft die while Schleife so lange, bis
         *    die Queue keine Nachrichten mehr enthält
         * -> wenn whileMode false ist, läuft die while Schleife standardmäßig
         *    1-mal durch, außer die whileRuns sind angegeben
         */
        $this->setWhileMode($doWhile);
        $this->setWhileRuns($whileRuns);

        // Öffnet die Verbindung und den Channel zur RabbitMQ
        $this->openConnection();
        $this->openChannel();

        // Setzt den Quality of Service -> mit den standard Werten ändert sich nichts
        $this->setQoS();

        $break = false;

        // Wiederholte Abholung aus der Queue mit Verarbeitung
        while ($this->getWhileRun()) {
            $rabbitMqWorker->setRmqObj($this);

            // Verarbeitung der Nachricht
            try {
                $returnProcess = $rabbitMqWorker->process();
            } catch (Exception $ex) {
                return ['Exception' => $ex->getMessage()];
            }

            // Wenn ein Fehler zurückgegeben wird, wird dieser in ein Error Array geschrieben
            if ($returnProcess instanceof AyumilaException) {
                $this->data['Error'][] = $this->setError(
                    $returnProcess->getCode(),
                    $returnProcess->getMessage(),
                    serialize($this->getBasicMsg())
                );

                $this->errorCounter++;

                // Nach einer festgelegten Anzahl wird die while Schleife abgebrochen
                if ($this->errorCounter == $this->errorCounterBreak) {
                    $break = true;
                    $this->data['Error'][] = $this->setError(
                        null,
                        'Abgebrochen nach 5 Versuchen',
                        serialize($this->getBasicMsg())
                    );
                }

            } else {
                // Wenn kein Fehler zurückgegeben wird, werden die Daten in ein
                // Array geschrieben, welches als obere Ebene den Queue Namen hat
                $this->data[$this->getQueueOrExchangeName()] = $returnProcess;
            }

            // Prüft, ob noch Messages in der Queue vorhanden sind,
            // falls nicht, wird die while Schleife abgebrochen
            if ($this->getMsgCount() < 1) {
                $break = true;
            }

            if ($break) {
                break;
            }
        }

        // Schreibt die Anzahl an vorhandenen Nachrichten in der Queue in ein Array
        $this->setDataMessageCount();

        // Schließt den Channel und die Verbindung
        $this->closeAll();

        // Gibt die gesendeten/erhaltenen Nachrichten zurück
        return $this->data;
    }

    /**
     * @param RabbitMqWorkerAbstract $rabbitMqWorker
     * @return array
     * @throws AyumilaException
     */
    public function runWhile(RabbitMqWorkerAbstract $rabbitMqWorker): array
    {
        return $this->run($rabbitMqWorker, true);
    }

    /**
     * @param RabbitMqWorkerAbstract $rabbitMqWorker
     * @param int $whileCount
     * @return array
     * @throws AyumilaException
     */
    public function runWhileCount(RabbitMqWorkerAbstract $rabbitMqWorker, int $whileCount = 1): array
    {
        return $this->run($rabbitMqWorker, false, $whileCount);
    }
}
<?php

namespace Ayumila\Connection\RabbitMq\Worker;

use Ayumila\Connection\RabbitMq\RabbitMqAbstract;
use Ayumila\Connection\RabbitMq\RabbitMqWorkerAbstract;
use Ayumila\Exceptions\AyumilaException;
use Exception;

class PullAndAcknowledMessage extends RabbitMqWorkerAbstract
{
    /**
     * Verarbeitet die Messages aus einer Queue
     *
     * @return string
     * @throws AyumilaException
     */
    public function process(): string
    {
        if ($this->rmqObj instanceof RabbitMqAbstract)
        {
            // basicGet um eine Nachricht aus der Queue zu erhalten
            $this->rmqObj->basicGet();

            // Hole die Nachrichten aus dem Objekt
            $msg = $this->rmqObj->getBasicMsg();

            if($msg)
            {
                // Nachrichten bestätigen, um Queue zu leeren
                try {
                    $this->rmqObj->setBasicAck();
                } catch (Exception $ex) {
                    throw new AyumilaException($ex->getMessage());
                }
                return $msg;
            }else{
                return '';
            }
        }else{
            throw new AyumilaException('Fatalerror: Worker can\'t read the RabbitMq object');
        }
    }
}
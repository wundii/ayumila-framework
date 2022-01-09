<?php

namespace Ayumila\Connection\RabbitMq;

abstract class RabbitMqWorkerAbstract implements RabbitMqWorkerInterface
{
    protected RabbitMq $rmqObj;

    abstract function process();

    /**
     * Objekt der Klasse FactoryRmq2 übergeben
     *
     * @param RabbitMq $obj
     */
    public function setRmqObj(RabbitMqAbstract $obj): void
    {
        $this->rmqObj = $obj;
    }
}
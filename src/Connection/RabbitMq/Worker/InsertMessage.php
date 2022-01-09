<?php

namespace Ayumila\Connection\RabbitMq\Worker;

use Ayumila\Connection\RabbitMq\RabbitMqAbstract;
use Ayumila\Connection\RabbitMq\RabbitMqWorkerAbstract;
use Ayumila\Exceptions\AyumilaException;

class InsertMessage extends RabbitMqWorkerAbstract
{
    private string $exchange;
    private string $message;

    /**
     * @param string $exchange
     * @param string $message
     */
    function __construct(string $exchange, string $message)
    {
        $this->exchange = $exchange;
        $this->message  = $message;
    }

    /**
     * @return bool
     * @throws AyumilaException
     */
    function process(): bool
    {
        if ($this->rmqObj instanceof RabbitMqAbstract)
        {
            $this->rmqObj->setPublishMsg($this->message);
            $this->rmqObj->basicPublish($this->exchange);
            return true;
        }
        return false;
    }
}
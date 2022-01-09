<?php

namespace Ayumila\Connection\RabbitMq;

interface RabbitMqWorkerInterface
{
    public function setRmqObj(RabbitMq $obj);
}
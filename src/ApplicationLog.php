<?php

namespace Ayumila;

use Ayumila\Connection\RabbitMq\RabbitMq;
use Ayumila\Connection\RabbitMq\RabbitMqConfig;
use Ayumila\Connection\RabbitMq\Worker\InsertMessage;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\RequestData;
use Ayumila\Traits\SingletonStandard;
use Exception;

class ApplicationLog
{
    use SingletonStandard;

    private array $logs = array();

    /**
     * @param string $status
     * @param string $description
     * @param mixed $data
     * @throws AyumilaException
     */
    public static function addLog(string $status, string $description, mixed $data):void
    {
        $instance = self::create();
        $instance->logs[] = [
            'timestamp'   => time(),
            'firstAppKey' => ApplicationControllerData::getFirstApplicationKey(),
            'appKey'      => ApplicationControllerData::getCurrantApplicationKey(),
            'appLayer'    => ApplicationControllerData::getCurrentApplicationLayer(),
            'appUrl'      => RequestData::getUrl(),
            'status'      => $status,
            'description' => $description,
            'data'        => serialize($data),
            'request'     => RequestData::getSerializedObject(),
        ];
    }

    /**
     * @param bool $force
     * @throws AyumilaException
     */
    public static function send(bool $force = false): void
    {
        if($force || ApplicationControllerData::getCurrantApplicationKey() === ApplicationControllerData::getFirstApplicationKey())
        {
            $instance = self::create();

            if($instance->logs)
            {
                $serializedLog = serialize($instance->logs);

                $rmqConfig = new RabbitMqConfig();
                $rmqConfig->setHost(RequestData::getENV('RabbitMqHost'));
                $rmqConfig->setUsername(RequestData::getENV('RabbitMqUser'));
                $rmqConfig->setPassword(RequestData::getENV('RabbitMqPassword'));

                $rmq = new RabbitMq($rmqConfig, RequestData::getENV('RabbitMqQueue'));
                $rmq->run(new InsertMessage(RequestData::getENV('RabbitMqExchange'), $serializedLog));

                $instance->logs = array();
            }
        }
    }
}
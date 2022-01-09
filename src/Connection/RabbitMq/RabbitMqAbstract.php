<?php

/**
 * Doku:
 * https://www.rabbitmq.com/resources/specs/amqp-xml-doc0-9-1.pdf
 * http://php-amqplib.github.io/php-amqplib/
 */

namespace Ayumila\Connection\RabbitMq;

use Ayumila\Exceptions\AyumilaException;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class RabbitMqAbstract
{
    protected RabbitMqConfig       $config;
    protected AMQPStreamConnection $connection;
    protected AMQPChannel          $channel;
    protected array|string         $queueName;
    protected array                $exchangeName     = array();
    protected array                $data;
    protected int                  $whileRuns        = 0;
    protected bool                 $whileMode        = false;
    protected int                  $whileCount       = 0;
    protected AMQPMessage          $basicMsg;
    protected int                  $basicMsgCount    = 0;
    protected AMQPMessage          $publishMsg;
    protected int                  $publishMsgCount  = 0;
    protected bool                 $usedBasicMsg     = false;
    protected bool                 $usedBasicPublish = false;

    abstract function run(RabbitMqWorkerAbstract $rabbitMqWorker, bool $doWhile = false, int $whileRuns = 1);
    abstract function runWhile(RabbitMqWorkerAbstract $rabbitMqWorker);
    abstract function runWhileCount(RabbitMqWorkerAbstract $rabbitMqWorker, int $whileCount = 1);

    /**
     * @param RabbitMqConfig $config
     * @param array|string $queueName
     */
    public function __construct(RabbitMqConfig $config, array|string $queueName = '')
    {
        $this->config           = $config;
        $this->setQueueName($queueName);
        $this->setBasicMsg('');
        $this->setPublishMsg('');
        $this->basicMsgCount    = 0;
        $this->publishMsgCount  = 0;
        $this->usedBasicMsg     = false;
        $this->usedBasicPublish = false;
        $this->exchangeName     = array();
    }

    /**
     * Öffnet eine Verbindung zur RabbitMQ mit den Config Daten
     *
     * @throws AyumilaException
     */
    protected function openConnection(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->config->getHost(),
                $this->config->getPort(),
                $this->config->getUsername(),
                $this->config->getPassword(),
                $this->config->getVhost()
            );
        } catch (Exception $ex) {
            throw new AyumilaException('Connection konnte nicht hergestellt werden', $ex);
        }

        if (isset($this->connection) && $this->connection->isConnected()) {
            register_shutdown_function([$this, 'closeAll']);
        }
    }

    /**
     * Channel öffnen
     *
     * @throws AyumilaException
     */
    protected function openChannel(): void
    {
        try {
            $this->channel = $this->connection->channel();
        } catch (Exception $ex) {
            throw new AyumilaException($ex);
        }
    }

    /**
     * Channel schließen
     */
    protected function closeChannel(): void
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
    }

    /**
     * Connection schließen
     *
     * @throws Exception
     */
    protected function closeConnection(): void
    {
        if (isset($this->connection) && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    /**
     * Channel und Connection schließen
     *
     * @throws Exception
     */
    public function closeAll(): void
    {
        $this->closeChannel();
        $this->closeConnection();
    }

    /**
     * Quality of Service setzen
     * -> mit den standard Werten ändert sich nichts
     *
     * @param int|null $prefetch_size
     * @param int $prefetch_count
     * @param bool|null $a_global
     */
    protected function setQoS(int $prefetch_size = null, int $prefetch_count = 1, bool $a_global = null): void
    {
        if (isset($this->channel)) {
            $this->channel->basic_qos($prefetch_size, $prefetch_count, $a_global);
        }
    }

    /**
     * Setzt den Inhalt einer Nachricht für die Queue
     *
     * @param string $publishMsg
     */
    public function setPublishMsg(string $publishMsg): void
    {
        $this->publishMsg = new AMQPMessage($publishMsg, array('delivery_mode' => 2));
    }

    /**
     * @return AMQPMessage
     */
    public function getPublishMsg(): AMQPMessage
    {
        return $this->publishMsg;
    }

    /**
     * Fügt eine Nachricht in die Queue ein
     *
     * @param string $exchange
     * @throws AyumilaException
     */
    public function basicPublish(string $exchange): void
    {
        $this->setExchangeName($exchange);

        if (isset($this->channel)) {
            try {
                $this->channel->basic_publish($this->getPublishMsg(), $exchange);
                $this->publishMsgCount++;

                $this->usedBasicPublish = true;
            } catch (Exception $ex) {
                throw new AyumilaException($ex->getMessage());
            }
        }
    }

    /**
     * @param string $exchangeName
     */
    public function setExchangeName(string $exchangeName): void
    {
        if (array_search($exchangeName, $this->exchangeName) === false) {
            array_push($this->exchangeName, $exchangeName);
        }
    }

    /**
     * Neue Nachricht aus der Queue erhalten
     *
     * @return bool
     * @throws AyumilaException
     */
    public function basicGet(): bool
    {
        try {
            $msg = $this->channel->basic_get($this->getQueueName());
        } catch (Exception $ex) {
            throw new AyumilaException($ex);
        }

        if (!$msg) {
            return false;
        }

        if ($msg instanceof AMQPMessage) {
            $this->setBasicMsg($msg);
            $this->basicMsgCount++;

            $this->usedBasicMsg = true;

            return true;
        } else {
            throw new AyumilaException('Fehlerhafte Nachrichtenklasse');
        }
    }

    /**
     * Aktuelle Nachricht bestätigen
     *
     * @throws AyumilaException
     */
    public function setBasicAck(): void
    {
        try {
            $this->channel->basic_ack($this->getDeliveryTag());
            $this->basicMsgCount--;
        } catch (Exception $ex) {

            throw new AyumilaException('Nachricht konnte nicht bestätigt werden', $ex);
        }
    }

    /**
     * @param string|AMQPMessage $basicMsg
     */
    public function setBasicMsg(string|AMQPMessage $basicMsg): void
    {
        if($basicMsg instanceof AMQPMessage)
        {
            $this->basicMsg = $basicMsg;
        }else{
            $this->basicMsg = (new AMQPMessage())->setBody($basicMsg);
        }

    }

    /**
     * @return string
     */
    public function getBasicMsg(): string
    {
        return $this->basicMsg->getBody();
    }

    /**
     * Gibt den delivery_tag der aktuellen Nachrichten zurück
     *
     * @return string
     * @throws AyumilaException
     */
    public function getDeliveryTag(): string
    {
        if ($this->basicMsg)
        {
            return $this->basicMsg->getDeliveryTag();
        }
        throw new AyumilaException('Keine Nachricht vorhanden');

    }

    /**
     * Gibt die Anzahl an Nachrichten in der Queue zurück
     *
     * @param bool $withBasicMsgCount
     * @return int
     */
    public function getMsgCount(bool $withBasicMsgCount = false): int
    {
        $msgCount = $this->basicMsg ? $this->basicMsg->getMessageCount() : 0;

        if ($withBasicMsgCount)
        {
            $msgCount += $this->basicMsgCount;
        }

        if(!is_int($msgCount))
        {
            $msgCount = 0;
        }

        return $msgCount;
    }

    /**
     * @return int
     */
    public function getPublishMsgCount(): int
    {
        return $this->publishMsgCount;
    }

    /**
     *
     */
    public function setDataMessageCount(): void
    {
        if ($this->usedBasicMsg) {
            $this->data['remainingMessages'] = $this->getMsgCount(true);
        }

        if ($this->usedBasicPublish) {
            foreach ($this->exchangeName as $exchangeName) {
                $this->data['publishedMessages'][$exchangeName] = $this->getPublishMsgCount();
            }
        }
    }

    /**
     * Queue Namen setzen
     *
     * @param array|string $queueName
     */
    public function setQueueName(array|string $queueName): void
    {
        $this->queueName = $queueName;
    }

    /**
     * @return mixed
     * @throws AyumilaException
     */
    public function getQueueName(): string
    {
        if (is_array($this->queueName)) {
            // Für zukünftige Multi-Queue Abfrage [0]
            $queueName = $this->queueName[0];
        } else {
            $queueName = $this->queueName;
        }

        if ($queueName) {
            return $queueName;
        }

        throw new AyumilaException('Kein QueueName vorhanden');
    }

    /**
     * @return mixed
     * @throws AyumilaException
     */
    public function getQueueOrExchangeName(): string
    {
        if (is_array($this->queueName)) {
            // Für zukünftige Multi-Queue Abfrage [0]
            $queueName = $this->queueName[0];
        } else {
            $queueName = $this->queueName;
        }

        if ($queueName) {
            return $queueName;
        } else if ($this->exchangeName) {
            return implode('-', $this->exchangeName);
        }

        throw new AyumilaException('Kein Queue oder Exchange Name vorhanden');
    }

    /**
     * Gibt entweder eine Zahl zurück, welche den aktuellen Aufruf wiedergibt
     * oder true um die while Schleife so lange laufen zu lassen, bis die Queue
     * leer ist
     *
     * @return bool|int
     */
    public function getWhileRun(): bool|int
    {
        if ($this->isWhileMode()) {
            return true;
        } else {
            return $this->getWhileCount(true);
        }
    }

    /**
     * Legt die Anzahl an durchläufen fest
     *
     * @param int $whileRuns
     */
    protected function setWhileRuns(int $whileRuns): void
    {
        $this->whileCount = 0;
        $this->whileRuns = $whileRuns;
    }

    /**
     * @return int
     */
    protected function getWhileRuns(): int
    {
        return $this->whileRuns;
    }

    /**
     * Gibt die aktuelle Anzahl von whileCount zurück
     *
     * @param bool $positivCalculate
     * @return int
     */
    protected function getWhileCount(bool $positivCalculate = false): int
    {
        if ($positivCalculate) {
            $this->whileCount++;
        }

        if ($this->whileCount > $this->getWhileRuns()) {
            $this->whileCount = 0;
        }

        return $this->whileCount;
    }

    /**
     * Setzt den whileMode -> standard ist false
     *
     * @param bool $doWhile
     */
    protected function setWhileMode(bool $doWhile = false): void
    {
        $this->whileMode = $doWhile;
    }

    /**
     * @return bool
     */
    protected function isWhileMode(): bool
    {
        return $this->whileMode;
    }

    /**
     * Erstellt ein Error Array nach gegebenen Schema
     *
     * @param string $errorCode
     * @param string $errorMsg
     * @param string $queueMsg
     * @return array
     */
    public function setError(string $errorCode, string $errorMsg, string $queueMsg): array
    {
        return [
            'ErrorCode' => $errorCode,
            'ErrorMsg' => $errorMsg,
            'QueueMsg' => $queueMsg,
        ];
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->data['Error'];
    }

    /**
     * @return mixed
     * @throws AyumilaException
     */
    public function getQueueReturn(): mixed
    {
        return $this->data[$this->getQueueName()];
    }
}
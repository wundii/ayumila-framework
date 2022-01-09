<?php

namespace Ayumila\Connection\RabbitMq\Worker;

use Ayumila\Connection\RabbitMq\RabbitMqAbstract;
use Ayumila\Connection\RabbitMq\RabbitMqWorkerAbstract;
use Ayumila\Exceptions\AyumilaException;
use FilesystemIterator;

class InsertDirectoryContent extends RabbitMqWorkerAbstract
{
    private int    $inputFileCount;
    private string $exchange = '';
    private string $path = '';

    /**
     * @param string $exchange
     * @param string $path
     * @param ?int   $inputFileCount
     */
    function __construct(string $exchange, string $path, ?int $inputFileCount)
    {
        $this->exchange       = $exchange;
        $this->path           = $path;
        $this->inputFileCount = $inputFileCount;
    }

    /**
     * @return bool|string
     * @throws AyumilaException
     */
    public function process(): bool|string
    {
        if ($this->rmqObj instanceof RabbitMqAbstract) {

            // Anzahl an Dateien in einem Verzeichnis ausgeben
            $fi = new FilesystemIterator($this->path, FilesystemIterator::SKIP_DOTS);
            $numberOfFilesInDirectory = iterator_count($fi);

            $numberOfFilesToInsert = !$this->inputFileCount ? $numberOfFilesInDirectory : $this->inputFileCount;

            $i = 1;

            if ($numberOfFilesToInsert > 0) {
                foreach ($fi as $file) {

                    // Inhalt einer Datei auslesen und als string in einer Variable speichern
                    $fileContent = file_get_contents($file->getRealPath());

                    // Nachricht setzten und publishen
                    $this->rmqObj->setPublishMsg($fileContent);
                    $this->rmqObj->basicPublish($this->exchange);

                    if ($i++ == $numberOfFilesToInsert) {
                        break;
                    }
                }
            } else {
                return "\nError: Anzahl ungültig\n";
            }
            return "Message published";
        }
        return false;
    }
}
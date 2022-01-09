<?php

namespace Ayumila\Http;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\Classes\Helper;
use Ayumila\Exceptions\AyumilaException;

class ResponseJson extends ResponseAbstract
{
    private string $contentType = "Content-Type:application/json; charset=utf-8";

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self;
    }

    private function __construct(){}

    /**
     * @return string
     */
    public function getContentType():string
    {
        return $this->contentType;
    }

    /**
     * @return string
     * @throws AyumilaException
     */
    public function outputData():string
    {
        $dataSets = $this->processDataSets($this->data);

        $this->processWarningAndError();

        $outputData = [
            "Status"        => $this->getStatus() ? 'ok' : 'error',
            "RequestMethod" => RequestData::getRequestMethod(),
            "DataCount"     => $this->getDataCount(),
            "DataSets"      => $dataSets,
        ];

        $outputData = $this->getAddOutputAddonData($outputData);

        return json_encode($this->sortOutputData($outputData));
    }

    /**
     * @param array $outputData
     * @return array
     */
    private function sortOutputData(array $outputData): array
    {
        $orderArray = [
            'Status',
            'RequestMethod',
            'Method',
            'Warning',
            'Error',
            'Exception',
            'DataCount',
            'DataSets',
        ];

        return Helper::sortArrayByArray($outputData, $orderArray);
    }

    /**
     *
     */
    private function processDataSets(string|array $data): array
    {
        /**
         * Es sollen alle Daten Zeilenbasiert zurückgegeben werden, damit ein Count über die Anzahl der enthaltenen Datensätze möglich ist.
         * Im ersten Schritt wird auf ein Array geprüft und anschließend die Keys (int+str) gegen die Anzahl der Datensätze (int).
         * Wenn das fehl schlägt, werden die Daten zu seinem Datensatz zusammengeführt.
         */
        $formatOutputDataArray = false;
        if(is_array($data)){
            if(array_keys($data) !== range(0, count($data) - 1)){
                $formatOutputDataArray = true;
            }
        }else{
            $formatOutputDataArray = true;
        }

        $dataSets = array();
        if($formatOutputDataArray){
            if($data){
                $dataSets[] = $data;
            }else{
                $dataSets = $data;
            }
        }else{
            $dataSets = $data;
        }

        if(is_string($dataSets))
        {
            $this->data = [$dataSets];
            $dataSets = $this->data;
        }

        return $dataSets;
    }
}
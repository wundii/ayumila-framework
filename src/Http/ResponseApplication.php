<?php

namespace Ayumila\Http;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\ApplicationInternalResponse;

class ResponseApplication extends ResponseAbstract
{
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
        return '';
    }

    /**
     * @return mixed
     */
    public function outputData():object
    {
        $response = new ApplicationInternalResponse;
        $response->setStatus($this->getStatus());
        $response->setHttpStatuscode($this->getHttpStatuscode());
        $response->setData($this->data);

        return $response;
    }
}
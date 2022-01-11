<?php

namespace Ayumila;

class ApplicationInternalResponse
{
    private bool  $status;
    private int   $http_statuscode;
    private mixed $data;

    /**
     * @param bool $status
     */
    public function setStatus(bool $status):void
    {
        $this->status = $status;
    }

    /**
     * @param int $statuscode
     */
    public function setHttpStatuscode(int $statuscode):void
    {
        $this->http_statuscode = $statuscode;
    }

    /**
     * @return mixed
     */
    public function setData(mixed $data):void
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getHttpStatuscode(): int
    {
        return $this->http_statuscode;
    }

    /**
     * @return mixed
     */
    public function getData():mixed
    {
        return $this->data;
    }
}
<?php

namespace Ayumila\Abstract;

abstract class ResponseAbstract
{
    protected string|array $data            = '';
    protected array        $warning         = array();
    protected array        $error           = array();
    protected array        $exception       = array();
    protected ?int         $dataCount       = null;
    protected bool         $status          = true;
    protected int          $http_statuscode = 200;
    protected array        $outputAddonData = array();

    abstract public function getContentType(): string;
    abstract public function outputData(): string|object;

    /**
     * @param string $status
     * @param string $description
     * @return self
     */
    public function addError(string $status, string $description): self
    {
        $this->error[] = ['Status'=>trim($status), 'Description'=>trim($description)];
        return $this;
    }

    /**
     * @param string $status
     * @param string $description
     * @return self
     */
    public function addWarning(string $status, string $description): self
    {
        $this->warning[] = ['Status'=>trim($status), 'Description'=>trim($description)];
        return $this;
    }

    /**
     * @param string $status
     * @param string $description
     * @return self
     */
    public function addException(string $status, string $description): self
    {
        $this->exception[] = ['Status'=>trim($status), 'Description'=>trim($description)];
        return $this;
    }

    /**
     * @param mixed $data
     */
    public function addData(mixed $data): void
    {
        if(!$this->data)
        {
            $this->data = $data;
        }else{
            if(!is_array($this->data))
            {
                if($this->data != $data )
                {
                    $oldValue = $this->data;
                    $this->data = array();
                    $this->data[] = $oldValue;
                }
            }elseif(!array_key_exists(0, $this->data))
            {
                $oldValue = $this->data;
                $this->data = array();
                $this->data[] = $oldValue;
            }

            if(is_array($this->data))
            {
                $this->data[] = $data;
            }
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function addDataArray(array $data): void
    {
        if(!is_array($this->data))
        {
            $this->data = [$this->data];
        }

        $this->data = $this->data + $data;
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function addDataWithKey(string $key, mixed $data): void
    {
        if(!$this->data)
        {
            $this->data = array();
            $this->data[$key] = $data;

        }else{
            if(is_string($this->data))
            {
                $oldValue = $this->data;
                $this->data = array();
                $this->data[$key][] = $oldValue;

            }elseif(is_array($this->data))
            {
                if(!array_key_exists($key, $this->data))
                {
                    $this->data[$key] = $data;

                }elseif(!array_key_exists(0, $this->data[$key]))
                {
                    $oldValue = $this->data[$key];
                    $this->data[$key] = array();
                    $this->data[$key][] = $oldValue;
                    $this->data[$key][] = $data;
                }else{
                    $this->data[$key][] = $data;
                }
            }
        }
    }

    /**
     * @param mixed $data
     */
    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        $this->processWarningAndError();
        return $this->status;
    }

    /**
     * @return int
     */
    public function getHttpStatuscode(): int
    {
        $this->processWarningAndError();
        return $this->http_statuscode;
    }

    /**
     * @param int $status
     * @return int
     */
    public function setHttpStatuscode(int $status): int
    {
        return $this->http_statuscode = $status;
    }

    /**
     * @param string|null $key
     * @return int
     */
    public function getDataCount(?string $key = null): int
    {
        if($this->dataCount !== null)
        {
            return $this->dataCount;
        } else {
            if ($key && array_key_exists($key, $this->data)) {
                return count($this->data[$key]);
            }else{
                return count($this->data);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param int $dataCount
     * @return self
     */
    public function setDataCount(int $dataCount): self
    {
        $this->dataCount = $dataCount;
        return $this;
    }

    /**
     *
     */
    protected function processWarningAndError(): void
    {
        foreach ($this->warning AS $warning)
        {
            $message = $warning['Status'].': '.$warning['Description'];
            $this->addOutputAddonData('Warning', $message);
        }

        foreach ($this->error AS $error)
        {
            $this->status = false;
            $message = $error['Status'].': '.$error['Description'];
            $this->addOutputAddonData('Error', $message);
        }

        foreach ($this->exception AS $exception)
        {
            $this->status = false;
            $this->http_statuscode = 500;
            $message = $exception['Status'].': '.$exception['Description'];
            $this->addOutputAddonData('Exception', $message);
        }
    }

    /**
     * @param string $key
     * @param string|array $value
     */
    public function addOutputAddonData(string $key, string|array $value): void
    {
        if(!$this->outputAddonData || !array_key_exists($key, $this->outputAddonData))
        {
            $this->outputAddonData[$key] = $value;
        }else{
            if(!is_array($this->outputAddonData[$key]))
            {
                if($this->outputAddonData[$key] != $value )
                {
                    $oldValue = $this->outputAddonData[$key];
                    $this->outputAddonData[$key] = array();
                    $this->outputAddonData[$key][] = $oldValue;
                }
            }elseif(!array_key_exists(0, $this->outputAddonData[$key]))
            {
                $oldValue = $this->outputAddonData[$key];
                $this->outputAddonData[$key] = array();
                $this->outputAddonData[$key][] = $oldValue;
            }

            if(is_array($this->outputAddonData[$key]) && !in_array($value, $this->outputAddonData[$key]))
            {
                $this->outputAddonData[$key][] = $value;
            }
        }
    }

    /**
     * @param $outputData
     * @return array
     */
    protected function getAddOutputAddonData($outputData): array
    {
        foreach ($this->outputAddonData AS $key => $value)
        {
            if(!array_key_exists($key, $outputData))
            {
                $outputData[$key] = $value;
            }
        }

        return $outputData;
    }
}
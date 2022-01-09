<?php

namespace Ayumila\Http;

use Ayumila\Exceptions\AyumilaException;

class RequestMock{
    private array $var_HEADER     = array();
    private array $var_SERVER     = array();
    private array $var_REQUEST    = array();
    private array $var_GET        = array();
    private array $var_POST       = array();
    private array $var_FILES      = array();
    private array $var_ENV        = array();

    /**
     * @param string $requestMethod
     * @param string $uri
     * @throws AyumilaException
     */
    public function setServer(string $requestMethod, string $uri):void
    {
        $requestUri   = parse_url($uri, PHP_URL_PATH);
        $requestQuery = parse_url($uri, PHP_URL_QUERY);
        $requestHttps = parse_url($uri, PHP_URL_SCHEME) === 'https' ? 'on': null;
        $requestHost  = parse_url($uri, PHP_URL_HOST);
        $requestPort  = parse_url($uri, PHP_URL_PORT);

        if(!$requestHost)
        {
            $requestHost  = RequestData::getHost();
            $requestHttps = RequestData::getHttps();
        }

        if($requestPort)
        {
            $requestHost .= ':'.$requestPort;
        }
        if($requestQuery)
        {
            $requestUri .= '?'.$requestQuery;
        }

        if(!$this->var_SERVER)
        {
            $this->var_SERVER = RequestData::getSERVER();
        }

        $server = [
            'REQUEST_METHOD' => $requestMethod,
            'REQUEST_URI'    => $requestUri,
            'HTTP_HOST'      => $requestHost,
        ];

        foreach ($server AS $key => $value)
        {
            $this->var_SERVER[$key] = $value;
        }

        if($requestHttps){
            $this->var_SERVER['HTTPS'] = $requestHttps;
        }

        if(!$this->var_HEADER)
        {
            $this->var_HEADER = RequestData::getHEADER();
        }
    }

    /**
     * @param array $header
     * @throws AyumilaException
     */
    public function setHeader(array $header):void
    {
        if(!$this->var_HEADER)
        {
            $this->var_HEADER = RequestData::getHEADER();
        }

        foreach ($header AS $key => $value)
        {
            $this->var_HEADER[$key] = $value;
        }
    }

    /**
     * @param array $get
     */
    public function setGet(array $get):void
    {
        $this->var_GET = $get;
    }

    /**
     * @param array $get
     */
    public function addGet(array $get):void
    {
        $this->var_POST = $this->var_POST + $get;
    }

    /**
     * @param array $post
     */
    public function setPost(array $post):void
    {
        $this->var_POST = $post;
    }

    /**
     * @param array $post
     */
    public function addPost(array $post):void
    {
        $this->var_POST = $this->var_POST + $post;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files):void
    {
        $this->var_FILES = $files;
    }

    /**
     * @param array $env
     */
    public function setEnv(array $env):void
    {
        $this->var_ENV = $env;
    }

    /**
     * @param string $variable
     * @return mixed
     * @throws AyumilaException
     */
    private function checkOfEmtpyVariable(string $variable):mixed
    {
        if(property_exists(self::class, 'var_'.$variable))
        {
            $data = $this->{'var_'.$variable};

            if(!$data)
            {
                $data = RequestData::{'get'.$variable}();
            }

            return $data;
        }else{
            throw new AyumilaException('class variable not found');
        }
    }


    /**
     * @return array
     * @throws AyumilaException
     */
    public function getSERVER(): array
    {
        return $this->checkOfEmtpyVariable('SERVER');
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public function getHEADER(): array
    {
        return $this->checkOfEmtpyVariable('HEADER');
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public function getREQUEST(): array
    {
        foreach ($this->var_GET AS $key => $value)
        {
            if(!isset($this->var_REQUEST[$key]))
            {
                $this->var_REQUEST[$key] = $value;
            }
        }

        foreach ($this->var_POST AS $key => $value)
        {
            if(!isset($this->var_REQUEST[$key]))
            {
                $this->var_REQUEST[$key] = $value;
            }
        }

        return $this->checkOfEmtpyVariable('REQUEST');
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public function getGET(): array
    {
        return $this->checkOfEmtpyVariable('GET');
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public function getPOST(): array
    {
        return $this->checkOfEmtpyVariable('POST');
    }

    /**
     * @return array
     */
    public function getFILES(): array
    {
        return $this->var_FILES;
    }

    /**
     * @return array
     * @throws AyumilaException
     */
    public function getENV(): array
    {
        return $this->checkOfEmtpyVariable('ENV');
    }
}
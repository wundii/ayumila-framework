<?php

namespace Ayumila\Http;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\MultitonRequest;

class Request
{
    use MultitonRequest;

    protected ?string      $var_FileContent = null;

    private   array        $var_Array   = ['_SERVER', '_GET', '_POST', '_REQUEST', '_FILES', '_ENV'];
    private   bool         $initialise  = true;

    protected array        $var_HEADER  = array();
    protected array        $var_SERVER  = array();
    protected array        $var_GET     = array();
    protected array        $var_POST    = array();
    protected array        $var_REQUEST = array();
    protected array        $var_FILES   = array();
    protected array        $var_ENV     = array();

    /**
     * CustomTrait Constructor
     *
     * @throws AyumilaException
     */
    private function __construct(bool $initialise = true)
    {
        if($initialise){
            $this->initialise();
            $this->initialiseHeader();
            $this->initialiseFileContent();
        }
    }

    /**
     * Initialise the server Variable
     *
     * @throws AyumilaException
     */
    private function initialise():void
    {
        foreach ($this->var_Array AS $value)
        {
            if(property_exists($this, 'var'.$value) && !$this->{'var'.$value})
            {
                switch ($value)
                {
                    case '_SERVER':
                        $this->var_SERVER = $_SERVER;
                        break;
                    case '_ENV':
                        $this->var_ENV = $_ENV;
                        break;
                    case '_FILES':
                        $this->var_FILES = $_FILES;
                        break;
                    case '_REQUEST':
                        $this->var_REQUEST = $_REQUEST;
                        break;
                    case '_GET':
                        $this->var_GET = $_GET;
                        break;
                    case '_POST':
                        $this->var_POST = $_POST;
                        break;

                    default:
                        throw new AyumilaException('The server variable is unknown'.$value);
                }
            }
        }
    }

    /**
     * Initialise the server Header
     */
    private function initialiseHeader():void
    {
        // $this->var_HEADER = apache_request_headers();
        $this->var_HEADER = getallheaders();
    }

    /**
     * Initialise the FileContent
     */
    private function initialiseFileContent():void
    {
        $this->var_FileContent = file_get_contents('php://input');
    }

    /**
     * @param RequestMock $requestMock
     * @throws AyumilaException
     */
    private function setRequestDataFromRequestMock(RequestMock $requestMock)
    {
        $this->var_HEADER  = $requestMock->getHEADER();
        $this->var_SERVER  = $requestMock->getSERVER();
        $this->var_ENV     = $requestMock->getENV();
        $this->var_FILES   = $requestMock->getFILES();
        $this->var_REQUEST = $requestMock->getREQUEST();
        $this->var_GET     = $requestMock->getGET();
        $this->var_POST    = $requestMock->getPOST();
    }

    /**
     * @return RequestMock
     * @throws AyumilaException
     */
    public function getRequestMock(): RequestMock
    {
        $requestMock = new RequestMock();
        $requestMock->setHeader($this->var_HEADER);
        $requestMock->setServer(RequestData::getRequestMethod(), RequestData::getRequestUri());
        $requestMock->setEnv($this->var_ENV);
        $requestMock->setGet($this->var_GET);
        $requestMock->setPost($this->var_POST);
        $requestMock->setFiles($this->var_FILES);

        return $requestMock;
    }
}
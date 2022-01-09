<?php

namespace Ayumila\Traits;

use Ayumila\Abstract\ResponseAbstract;
use Ayumila\ApplicationSecurity;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\Response;
use Ayumila\Http\ResponseData;
use Ayumila\Http\ResponseTwig;
use Ayumila\Http\RouterData;

trait DefaultResponse
{
    private array $defaultResponseCollection = array();

    /**
     * constructor
     */
    private function __construct()
    {
        $this->setDefaultResponse(ResponseTwig::create('extends/ayumilaDefault.twig'), array(), false, null);
    }

    /**
     * @return void
     * @throws AyumilaException
     */
    public function defaultResponse(): void
    {
        $currentResponseData      = ResponseData::getData();
        $currentResponseClassName = RouterData::getRouteResponse();

        if(!array_key_exists($currentResponseClassName, $this->defaultResponseCollection))
        {
            $currentResponseClassName = 'default';
        }

        $defaultResponseClass       = $this->defaultResponseCollection[$currentResponseClassName]['class'];
        $defaultResponseData        = $this->defaultResponseCollection[$currentResponseClassName]['data'];
        $defaultResponseForwardData = $this->defaultResponseCollection[$currentResponseClassName]['forwardData'];
        //
        // var_dump($currentResponseClassName);
        // var_dump($defaultResponseForwardData);
        // var_dump($defaultResponseData);
        // exit();

        Response::setResponseContentType($defaultResponseClass);
        if($currentResponseData && $defaultResponseForwardData)
        {
            Response::setData($currentResponseData);
        }

        if($defaultResponseData)
        {
            Response::addDataArray($defaultResponseData);
        }
    }

    /**
     * @param string $className
     * @return string
     */
    private function getClassWithNamespaces(string $className): string
    {
        if(class_exists($className))
        {
            return $className;
        }

        return '';
    }

    /**
     * @param ResponseAbstract $responseAbstract
     * @param array $defaultResponseData
     * @param bool $forwardResponseData
     * @param string|null $responseClassNameAssign
     * @return DefaultResponse|ApplicationSecurity
     */
    public function setDefaultResponse(ResponseAbstract $responseAbstract, array $defaultResponseData, bool $forwardResponseData, ?string $responseClassNameAssign): self
    {
        $responseClassNameAssign = !$responseClassNameAssign ? 'default' : $responseClassNameAssign;

        $this->defaultResponseCollection[$responseClassNameAssign] = [
            'class'       => $responseAbstract,
            'data'        => $defaultResponseData,
            'forwardData' => $forwardResponseData,
        ];

        return $this;
    }
}
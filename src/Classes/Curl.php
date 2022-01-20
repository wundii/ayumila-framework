<?php

/**
 * $data_string = [
 *   'Data' => [
 *     'Hash' => '1635317537-9460-LQEgmVCzljFQkaDK'
 *   ]
 * ];
 *
 * $url = "http://localhost:8080/api/v2/TenantWrapper/Wrapper/";
 * $requestMethod = 'GET';
 * $getParams = '';
 * $postData = $data_string;
 * $headerParams = [
 *   'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zc28ud2VzdHByZXNzLmRlIiwiaWF0IjoxNjM1MzM0NDE1LCJleHAiOjE2MzUzNzc2MTUsImhhc2hfdXNlciI6IjE2MzUzMTc1MzctOTQ2MC1ESmFncVl4VlpGTk1pZld4IiwiaGFzaF90ZW5hbnQiOiIxNjM1MzE3NTM3LTk0NjAtd0FwQ0JvbVpCc0VTaFFodCIsImRhdGEiOltdfQ.AJen9wf_FpQYRK2guTdiwhDE9NF105o2aWpAzTzG2tQ',
 * ];
 *
 * $result = \System\General\Curl::create()->callAsJson($url, $requestMethod, $getParams, $postData, $headerParams);
 */

namespace Ayumila\Classes;

use Ayumila\Exceptions\AyumilaException;

class Curl
{
    CONST PATCH   = "PATCH";
    CONST POST    = "POST";
    CONST GET     = "GET";
    CONST HEAD    = "HEAD";
    CONST OPTIONS = "OPTIONS";
    CONST PUT     = "PUT";
    CONST DELETE  = "DELETE";

    private int     $curlTimeout        = 120;
    private int     $curlConnectTimeout = 120;
    private bool    $SSLVerification    = false;
    private bool    $allowEncoding      = false;
    private bool    $debug              = false;
    private ?string $userAgent          = null;
    private array   $basicAuth          = array();

    public function __construct(string $variable = '')
    {

    }

    /**
     * @return Curl
     */
    public static function create():Curl
    {
        return new self();
    }

    /**
     * @return $this
     */
    public function setConfig():Curl
    {
        $this->curlTimeout = 0;
        $this->curlConnectTimeout = 0;
        $this->allowEncoding = false;
        $this->userAgent = null;
        $this->basicAuth = array();
        $this->debug = 0;

        return $this;
    }

    /**
     * @param string $url
     * @param string $requestMethod
     * @param array|string|null $getParams
     * @param array|string|null $postData
     * @param array|string|null $headerParams
     * @return array
     * @throws AyumilaException
     */
    public function callAsJson(
        string $url,
        string $requestMethod = 'GET',
        array|string|null $getParams = null,
        array|string|null $postData = null,
        array|string|null $headerParams = null
    ):array
    {
        return $this->call( $url, $requestMethod, $getParams , $postData, $headerParams, 'application/json');
    }


    /**
     * @param string $url
     * @param string $requestMethod
     * @param array|string|null $getParams
     * @param array|string|null $postData
     * @param array|string|null $headerParams
     * @param string $headerType
     * @return array
     * @throws AyumilaException
     */
    public function call(
        string $url,
        string $requestMethod = 'GET',
        array|string|null $getParams = null,
        array|string|null $postData = null,
        array|string|null $headerParams = null,
        string $headerType = 'multipart/form-data'
    ):array
    {
        // aufbereiten der array|string|null Variablem
        $getParams    = $this->convertMixedToArray($getParams);
        $postData     = $this->convertMixedToArray($postData);
        $headerParams = $this->convertMixedToArray($headerParams);

        $port = 80;
        $parseUrl = parse_url($url);

        if(!isset($parseUrl['scheme']))
        {
            $url = 'http://'.$url;
        }

        if(isset($parseUrl['port']) && $parseUrl['port'] !== 80 )
        {
            $port = $parseUrl['port'];
        }

        if(isset($parseUrl['host']) && ($parseUrl['host'] === 'localhost' || $parseUrl['host'] === '127.0.0.1'))
        {
            $port = 80;
        }

        $headers   = array();
        $headers[] = "Content-Type: ".$headerType;

        foreach ($headerParams as $key => $val)
        {
            $headers[] = "{$key}: {$val}";
        }

        if($postData)
        {
            if(strtolower($headerType) === 'application/json')
            {
                $postData  = json_encode($postData);

                // Content-Length nur bei application/json?
                $headers[] = "Content-Length: " . strlen($postData);
            }
        }

        // $http = mb_substr($url, 0, 5);
        // if(strtolower($http) === "https"){
        if(str_starts_with($url, "https://")){
            $this->SSLVerification = true;
            $port = $port == 80 ? 443 : $port;
        }

        $curl = curl_init();

        if ($this->curlTimeout !== 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->curlTimeout);
        }

        if ($this->curlConnectTimeout != 0) {
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->curlConnectTimeout);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // if ($this->SSLVerification === false) {
        // }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if ($getParams) {
            $url = ($url . '?' . http_build_query($getParams));
        }

        if ($this->allowEncoding) {
            curl_setopt($curl, CURLOPT_ENCODING, '');
        }

        switch($requestMethod)
        {
            case self::POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::HEAD:
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case self::OPTIONS:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "OPTIONS");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::PATCH:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::PUT:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::GET:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;

            default:
                throw new AyumilaException('Method ' . $requestMethod . ' is not recognized.');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PORT, $port);

        if($this->userAgent){
            curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        }

        if($this->basicAuth) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->basicAuth['username'] . ":" . $this->basicAuth['password']);
        }

        if ($this->debug) {
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
        } else {
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
        }

        curl_setopt($curl, CURLOPT_HEADER, 1);

        $response         = curl_exec($curl);
        $response_info    = curl_getinfo($curl);
        $http_header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $http_header      = $this->httpParseHeaders(substr($response, 0, $http_header_size));
        $http_body        = substr($response, $http_header_size);

        if ($response_info['http_code'] === 0) {
            $curl_error_message = curl_error($curl);

            if (!empty($curl_error_message)) {
                $error_message = "API call to {$url} failed: ".$curl_error_message;
            } else {
                $error_message = "API call to {$url} failed, but for an unknown reason. " .
                    "This could happen if you are disconnected from the network.";
            }
            $data = $error_message;

        } elseif ($response_info['http_code'] >= 200 && $response_info['http_code'] <= 299) {
            $data = json_decode($http_body, true);
            if (json_last_error() > 0) {
                $data = $http_body;
            }
        } else {
            $data = json_decode($http_body, true);
            if (json_last_error() > 0) {
                $data = $http_body;
            }
        }
        return ['http_body' => $data, 'http_code' => $response_info['http_code'], 'http_header' => $http_header, 'called_again' => 0];
    }

    /**
     * @param string $raw_headers
     * @return array
     */
    private function httpParseHeaders(string $raw_headers):array
    {
        $headers = array();
        $key     = '';

        foreach (explode("\n", $raw_headers) as $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], [trim($h[1])]);
                } else {
                    $headers[$h[0]] = array_merge([$headers[$h[0]]], [trim($h[1])]);
                }

                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) === "\t") {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                } elseif (!$key) {
                    $headers[0] = trim($h[0]);
                }
            }
        }

        return $headers;
    }

    /**
     * @param mixed $value
     * @return array
     */
    private function convertMixedToArray(mixed $value):array
    {
        if(!is_array($value))
        {
            if($value)
            {
                $returnArray = (array)$value;
            }else{
                $returnArray = array();
            }
        }else{
            $returnArray = $value;
        }

        return $returnArray;
    }
}
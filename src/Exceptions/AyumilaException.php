<?php

namespace Ayumila\Exceptions;

use Ayumila\Http\Response;
use Ayumila\Http\ResponseTwig;
use Exception;

class AyumilaException extends Exception
{
    /**
     * @throws AyumilaException
     */
    public function twigAyumilaException(): void
    {
        $AyumilaException  = "<span>Message: ".$this->getMessage()."</span>";
        $AyumilaException .= str_replace(['#'], ['<br>#'], $this->getTraceAsString());
        $AyumilaException .= loadCodeLines($this->getFile(), $this->getLine(), 15, 10);

        preg_match_all('/#[0-9] (?<path>[\/a-zA-Z0-9_\-.]+)\\((?<line>[0-9]+)/', $this->getTraceAsString(), $matches);
        foreach ($matches['path'] AS $key => $path)
        {
            $AyumilaException .= loadCodeLines($path, $matches['line'][$key], 15, 10);
        }

        Response::setResponseContentType(ResponseTwig::create('error/ayumilaException.twig'));
        Response::addDataWithKey('Title', 'AyumilaException');
        Response::addDataWithKey('Exception', $AyumilaException);
    }
}
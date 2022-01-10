<?php

namespace Ayumila\Http;

use Ayumila\Abstract\ResponseAbstract;
use Exception;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class ResponseTwigException extends ResponseAbstract
{
    private string $contentType = "Content-Type:text/html; charset=utf-8";
    private string $directory;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self;
    }

    private function __construct()
    {
        $this->loadAyumilaYaml();
    }

    /**
     * @return array
     * @throws Exception
     */
    private function loadAyumilaYaml(): array
    {
        $ayumilaYaml = Yaml::parseFile(__DIR__.'/../../../../../config/ayumila.yaml');
        try{
            $this->directory = $ayumilaYaml['Ayumila']['Twig']['Path'];
        }catch (Exception $ex)
        {
            throw new Exception('The entry Ayumila > Twig > Path must exist in the Ayumila yml.');
        }
        return $ayumilaYaml;
    }

    /**
     * @return string
     */
    public function getContentType():string
    {
        return $this->contentType;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function outputData():string
    {
        $loader	= new FilesystemLoader($this->directory);
        $twig	= new Environment($loader);

        $outputData = $this->getAddOutputAddonData($this->data);

        $data = is_array($outputData['Exception']) ? implode('</div><div>', $outputData['Exception']) : $outputData['Exception'];

        $outputData['Exception'] = '<div>'.$data.'</div>';

        try{
            return $twig->render('/error/ayumilaException.twig', $outputData);
        }catch (Exception $ex)
        {
            $this->addDataWithKey('Title', 'Twig Exception');
            $this->addDataWithKey('Exception', $ex->getMessage());
            return $twig->render('/error/ayumilaException.twig', $outputData);
        }
    }
}
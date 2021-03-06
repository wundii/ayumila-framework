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

class ResponseTwig extends ResponseAbstract
{
    private string $contentType = "Content-Type:text/html; charset=utf-8";
    private string $directory;
    private string $directTwigTemplate;

    /**
     * @param string $defaultTwig
     * @return self
     */
    public static function create(string $defaultTwig = ''): self
    {
        return new self($defaultTwig);
    }

    private function __construct(string $defaultTwig)
    {
        $this->directTwigTemplate = $defaultTwig;
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
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function outputData(): string
    {
        $loader	= new FilesystemLoader($this->directory);
        $twig	= new Environment($loader, ['strict_variables' => true]);

        try{
            return $twig->render(!$this->directTwigTemplate ? RouterData::getTwig(): $this->directTwigTemplate, (array)$this->data);
        }catch (Exception $ex)
        {
            $exception = processPhpException($ex);

            $this->addDataWithKey('Title', 'Twig Exception');
            $this->addDataWithKey('Exception', $exception);
            return $twig->render('/error/ayumilaException.twig', $this->data);
        }
    }
}
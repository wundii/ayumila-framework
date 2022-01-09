<?php

use Ayumila\Classes\Helper;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

static $lastPath = '';
static $lastLine = '';


function AyumilaErrorHandler(int $fehlercode, string $fehlertext, string $fehlerdatei, int $fehlerzeile): void
{

}

/**
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function AyumilaShutdownFunction(): void
{
    $directory   = __DIR__.'/../../../../../twig';

    $error = error_get_last();

    $type    = $error['type'];
    $message = $error['message'];
    $file    = $error['file'];
    $line    = $error['line'];

    $type = match ($type)
    {
        E_ERROR => 'FATAL ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
    };

    $message = str_replace(['Stack trace', '#'], ['</span><br>Stack trace','<br>#'], $message);

    preg_match_all('/#[0-9] (?<path>[\/a-zA-Z0-9_\-.]+)\\((?<line>[0-9]+)/', $message, $matches);

    $exception  = "<span class='font-weight-bold'>Message: {$message}<br>";
    $exception .= !str_contains($message, 'Stack trace') ? '</span>' : '';
    $exception .= loadCodeLines($file, $line, 15, 10);

    foreach ($matches['path'] AS $key => $path)
    {
        $exception .= loadCodeLines($path, $matches['line'][$key], 15, 10);
    }

    $array = [
        'Title' => "AyumilaException: ".$type,
        'Exception' => $exception,
    ];

    $loader	= new FilesystemLoader($directory);
    $twig	= new Environment($loader);

    echo $twig->render('error/ayumilaException.twig', $array);
    exit();
}

/**
 * @param string $path
 * @param int $line
 * @param int $preLine
 * @param int $postLine
 * @return string
 */
function loadCodeLines(string $path, int $line, int $preLine = 5, int $postLine = 5): string
{
    global $lastPath, $lastLine;

    if($lastPath == $path && $lastLine == $line)
    {
        return '';
    }else{
        $lastPath = $path;
        $lastLine = $line;
    }

    $returnString  = "<div class='mt-5'>File: {$path} ({$line})</div>";

    $lineStart = $line-$preLine;
    $lineEnd   = $line+$postLine;

    $content = file_get_contents($path);
    $contentArray = explode("\n", $content);

    $lineCnt = 0;
    foreach ($contentArray AS $key => $contentLine)
    {
        if($lineCnt < $lineStart || $lineCnt > $lineEnd)
        {
            unset($contentArray[$key]);
        }else{
            $lineNumberWhile = (string)$lineCnt+1;
            $lineNumber = $lineNumberWhile;

            while(true)
            {
                if(strlen($lineNumberWhile) < 5)
                {
                    $lineNumberWhile .= '0';
                    $lineNumber = "<span class='text-black-50'>0</span>".$lineNumber;
                }

                if(strlen($lineNumberWhile) >= 5)
                {
                    break;
                }
            }

            $contentLine = htmlspecialchars($contentLine);

            if($lineCnt+1 == $line)
            {
                $contentArray[$key] = "{$lineNumber}:"."<span class='text-danger'>".$contentLine."</span>";
            }else{
                $contentArray[$key] = "{$lineNumber}:".$contentLine;
            }
        }
        $lineCnt++;
    }

    $returnString .= Helper::getPrintr(implode("\n", $contentArray), false, 'mt-3');

    return $returnString;
}

function processPhpException(Exception $ex, bool $extend = false): string
{
    $exception  = "<span>Message: ".$ex->getMessage()."</span><span>";

    if($extend){
        $exception .= str_replace(['#', '): '], ['</span><div style="height: 7px"></div><span>#', ')</span><br><span class="bg-light border rounded p-1 mt-3" style="margin-left:23px;">'], $ex->getTraceAsString());

    }

    $exception .= '</span>';
    $exception .= loadCodeLines($ex->getFile(), $ex->getLine(), 15, 10);

    if($extend)
    {
        preg_match_all('/#[0-9] (?<path>[\/a-zA-Z0-9_\-.]+)\\((?<line>[0-9]+)/', $ex->getTraceAsString(), $matches);
        foreach ($matches['path'] AS $key => $path)
        {
            $exception .= loadCodeLines($path, $matches['line'][$key], 15, 10);
        }
    }

    return $exception;
}
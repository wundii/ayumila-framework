<?php

namespace Ayumila\Classes;

class Output
{
    public const Default = "\e[39m";
    public const Black = "\e[30m";
    public const Red = "\e[31m";
    public const Green = "\e[32m";
    public const Yellow = "\e[33m";
    public const Blue = "\e[34m";
    public const Magenta = "\e[35m";
    public const Cyan = "\e[36m";
    public const LightGray = "\e[37m";
    public const DarkGray = "\e[90m";
    public const LightRed = "\e[91m";
    public const LightGreen = "\e[92m";
    public const LightYellow = "\e[93m";
    public const LightBlue = "\e[94m";
    public const LightMagenta = "\e[95m";
    public const LightCyan = "\e[96m";
    public const White = "\e[97m";

    /**
     * @param string $value
     * @return void
     */
    public static function echo(string $value): void
    {
        echo $value . PHP_EOL;
    }

    /**
     * @return void
     */
    public static function echoBreak(): void
    {
        echo PHP_EOL;
    }

    /**
     * @param string $redValue
     * @param string $whiteValue
     * @param int $redLeftSpaces
     * @param int $whiteLeftSpaces
     * @return void
     */
    public static function echoRedWhite(string $redValue, string $whiteValue, int $redLeftSpaces = 0, int $whiteLeftSpaces = 1): void
    {
        $redValue = str_pad($redValue, strlen($redValue) + $redLeftSpaces, " ", STR_PAD_LEFT);
        $whiteValue = str_pad($whiteValue, strlen($whiteValue) + $whiteLeftSpaces, " ", STR_PAD_LEFT);
        echo self::Red . $redValue . self::Default . $whiteValue . PHP_EOL;
    }

    /**
     * @param string $greenValue
     * @param string $whiteValue
     * @param int $greenLeftSpaces
     * @param int $whiteLeftSpaces
     * @return void
     */
    public static function echoGreenWhite(string $greenValue, string $whiteValue, int $greenLeftSpaces = 0, int $whiteLeftSpaces = 1): void
    {
        $greenValue = str_pad($greenValue, strlen($greenValue) + $greenLeftSpaces, " ", STR_PAD_LEFT);
        $whiteValue = str_pad($whiteValue, strlen($whiteValue) + $whiteLeftSpaces, " ", STR_PAD_LEFT);
        echo self::Green . $greenValue . self::Default . $whiteValue . PHP_EOL;
    }

    /**
     * @param string $yellowValue
     * @param string $whiteValue
     * @param int $yellowLeftSpaces
     * @param int $whiteLeftSpaces
     * @return void
     */
    public static function echoYellowWhite(string $yellowValue, string $whiteValue, int $yellowLeftSpaces = 0, int $whiteLeftSpaces = 1): void
    {
        $yellowValue = str_pad($yellowValue, strlen($yellowValue) + $yellowLeftSpaces, " ", STR_PAD_LEFT);
        $whiteValue = str_pad($whiteValue, strlen($whiteValue) + $whiteLeftSpaces, " ", STR_PAD_LEFT);
        echo self::Yellow . $yellowValue . self::Default . $whiteValue . PHP_EOL;
    }

    /**
     * @param string $blueValue
     * @param string $whiteValue
     * @param int $blueLeftSpaces
     * @param int $whiteLeftSpaces
     * @return void
     */
    public static function echoBlueWhite(string $blueValue, string $whiteValue, int $blueLeftSpaces = 0, int $whiteLeftSpaces = 1): void
    {
        $blueValue = str_pad($blueValue, strlen($blueValue) + $blueLeftSpaces, " ", STR_PAD_LEFT);
        $whiteValue = str_pad($whiteValue, strlen($whiteValue) + $whiteLeftSpaces, " ", STR_PAD_LEFT);
        echo self::Blue . $blueValue . self::Default . $whiteValue . PHP_EOL;
    }
}
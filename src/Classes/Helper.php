<?php
/**
 * This class represents a container for all possible helper functions.
 *
 * @version 1.0.0
 * @copyright WESTPRESS GmbH & Co. KG
 * @package SSO
 * @subpackage System
 */

namespace Ayumila\Classes;

use Exception;

class Helper
{
    /**
     * Converts a string from the snake case style to the camel case style.
     *
     * @param string $text The text in snake case
     * @param bool $capitalizeFirstCharacter (Optional) The marker for capitalizing the first character (default true)
     *
     * @example Snake case looks like this: my_short_text
     * @example Camel case looks like this: MyShortText
     *
     * @return string The text in camel case
     */
    public static function convertSnakeCaseToCamelCase(string $text, bool $capitalizeFirstCharacter=true): string
    {
        $text = str_replace('_', '', ucwords($text, '_'));
        if (!$capitalizeFirstCharacter) {
            $text = lcfirst($text);
        }
        return $text;
    }

    public static function convertCamelCaseToSnakeCase(string $text, bool $capitalizeFirstCharacter=false): string
    {
        $text = lcfirst($text);
        $text = str_replace(
            Array('A', 'B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
            Array('_a', '_b','_c','_d','_e','_F','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z'),
            $text
        );
        if ($capitalizeFirstCharacter) {
            $text = ucfirst($text);
        }
        return $text;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function convertArrayKeysFromCamelCaseToSnakeCase(array $array):array
    {
        $result = Array();
        foreach ($array as $key=>$value) {
            $result[self::convertCamelCaseToSnakeCase($key)] = $value;
        }
        return $result;
    }

    /**
     * Converts the given string into a MD5-hash.
     *
     * Example: 098f6bcd4621d373cade4e832627b4f6
     *
     * @param string $string The text to convert
     *
     * @return string The generated MD5-hash
     */
    public static function getHashMd5(string $string):string
    {
        return md5($string);
    }

    /**
     * Generates a random MD5-hash
     *
     * Example: 098f6bcd4621d373cade4e832627b4f6
     *
     * @return string The generated MD5-hash
     */
    public static function getRandomHashMd5():string
    {
        return md5(rand(0,9999999).'|'.microtime(true));
    }

    /**
     * Generates a random hash with the current microtime as prefix.
     *
     * Example: 1633520069-8520-PNDyDpgWMRxInKPu
     *
     * @return string
     */
    public static function getRandomHashWithTime():string
    {
        $time = number_format(microtime(true),4, '.', '');
        $string = str_shuffle('aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzzAABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ');
        $hash = str_replace('.', '-', $time).'-'.substr($string, 0, 31-strlen($time));
        return $hash;
    }

    /**
     * Converts the name of an entity to its name for the url.
     *
     * @param string $name The name of the entity
     * @return string The name fot the url
     */
    public static function convertEntityNameToNameForUrl(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = str_replace(
            Array(' ', '-', '+', 'ä' , 'ö' , 'ü' , 'ß'),
            Array('_', '_', '_', 'ae', 'oe', 'ue', 'ss'),
            $name
        );
        $name = mb_ereg_replace("/[^a-zA-Z0-9]/", "", $name);
        return $name;
    }

    /**
     * @param int|float|string $numeric
     * @return float
     */
    public static function convertToFloat(int|float|string $numeric) : int|float|string
    {

        if (is_numeric($numeric) && $numeric <= 0.0000001 && $numeric >= -0.0000001)
        {
            $numeric = 0;
        }

        $numeric = preg_replace("/[^-0-9,.]/","",$numeric);

        if(!is_numeric($numeric))
        {
            if( strpos($numeric, ',') && strpos($numeric, '.') && strpos($numeric, ',')<strpos($numeric, '.')) {
                $numeric = str_replace(",", "", $numeric);
            }elseif( strpos($numeric, ',') && strpos($numeric, '.') && strpos($numeric, ',')>strpos($numeric, '.')) {
                $numeric = str_replace(",", "_", $numeric);
                $numeric = str_replace(".", "", $numeric);
                $numeric = str_replace("_", ".", $numeric);
            }else{
                $numeric = str_replace(",", ".", $numeric);
            }
        }

        // Es gibt zwei unterschiedliche Punkt-Codierungen... dieser Trick beugt falsche Werte aus round() vor
        // round(161.865, 2) ist ohne dem Trick 161.86 = falsch
        // round(161.865, 2) ist mit dem Trick 161.87 = richtig
        // /de/auftraege/auftrag_bearbeiten.html?id_bc=1105816
        $numeric = str_replace('.', '.',$numeric);

        return $numeric;
    }

    /**
     * Konvertiert einen Mixed Parameter zu einem korrekten boolschen Wert
     *
     * @param mixed $bool
     * @return bool
     * @throws Exception
     */
    public static function convertToBool(mixed $bool):bool
    {
        if (is_string($bool))
        {
            $boolArray = [
                'true',
                'false',
                '0',
                '1',
                ''
            ];

            if(in_array($bool, $boolArray)){

                if (strtolower($bool) === 'true' && $bool === '1') {
                    return true;
                }else{
                    return false;
                }
            }else{
                throw new Exception('this is not a bool value');
            }
        }

        return boolval($bool);
    }

    /**
     * Modulo Berechnung (Restwert)
     * awu 02.12.2020
     * @param int|string $wert
     * @param int $teiler
     * @return int
     */
    public static function getModulo(int|string $wert, int $teiler = 97) : int
    {
        if(is_int($wert))
        {
            $wert = (string)$wert;
        }

        $restWert = "";
        $i        = 0;

        while ($i < strlen($wert))
        {
            do
            {
                $restWert .= $wert[$i++];
                $erg       = (int)$restWert % $teiler;

            } while ( $erg == (int)$restWert && $i < strlen($wert) );

            $restWert = $erg;
        }

        return $restWert;
    }

    /**
     * Bei dem erstellen einer PDF-Datei werden manche Zeichen nicht richtig erkannt und
     * somit durch ein Steuerzeichen (z.B. ) ersetzt.
     *
     * ASCII Tabelle: https://theasciicode.com.ar/ascii-printable-characters/lowercase-letter-s-minuscule-ascii-code-115.html
     * Encoding Problem: http://www.dfki.uni-kl.de/se/Encoding/problem.php
     * Erklärungstabelle für Steuerzeichen: https://dewiki.de/Lexikon/Steuerzeichen
     *
     * Getestet mit 27290 Wörtern (181.941 Zeichen). -> ca. 0.09 Sekunden
     *
     * $test = 'ISO-8859-1: ¡	¢	£	¤	¥	¦	§	¨	©	ª	«	¬	SHY:­:	®	¯
     * °	±	²	³	´	µ	¶	·	¸	¹	º	»	¼	½	¾	¿
     * À	Á	Â	Ã	Ä	Å	Æ	Ç	È	É	Ê	Ë	Ì	Í	Î	Ï
     * Ð	Ñ	Ò	Ó	Ô	Õ	Ö	×	Ø	Ù	Ú	Û	Ü	Ý	Þ	ß
     * à	á	â	ã	ä	å	æ	ç	è	é	ê	ë	ì	í	î	ï
     * ð	ñ	ò	ó	ô	õ	ö	÷	ø	ù	ú	û	ü	ý	þ	ÿ
     * Kyrillisch: А а Б б В в Г г Д д Е е Ё ё Ж ж З з И и Й й К к Л л М м Н н О о П п Р р C c Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я
     * Türkisch: A,a B,b C,c Ç,ç D,d E,e F,f G,g Ğ,ğ H,h I,ı İ,i J,j K,k L,l M,m N,n O,o Ö,ö P,p R,r S,s Ş,ş T,t U,u Ü,ü V,v Y,y Z,z
     * Koreanisch: ㄱ ㄴ ㄷ ㄹ ㅁ ㅂ ㅅ ㅇ ㅈ ㅊ ㅋ ㅌ ㅍ ㅎ ㄲ ㄸ ㅃ ㅆ ㅉ ㄱ (ㄲ)	ㄴ	ㄷ (ㄸ)	ㄹ	ㅁ	ㅂ (ㅃ)	ㅅ (ㅆ)	ㅇ	ㅈ (ㅉ)	ㅊ	ㅋ	ㅌ	ㅍ	ㅎ ㅏ	ㅐ	ㅑ	ㅒ	ㅓ	ㅔ	ㅕ	ㅖ	ㅗ	ㅘ	ㅙ	ㅚ	ㅛ	ㅜ	ㅝ	ㅞ	ㅟ	ㅠ	ㅡ	ㅢ	ㅣ
     * Japanisch: ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんゔ
     * ゕゖ゚゛゜ゝゞゟ゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ㍐㍿';
     *
     * @param mixed $inputString
     * @return string
     */
    public static function replaceSteuerzeichen(mixed $inputString):string
    {
        /**
         * ASCII Zeichen ohne Störfaktor für die Weiterverarbeitung im Infinity
         */
        // 9   Horizontales Tabulatorzeichen;
        // 10  Zeilenvorschub;
        // 12  Seitenvorschub;
        // 13  Wagenrücklauf;

        if (is_string($inputString)) {
            // Prüft, ob der eingegebene String im UTF-8 Format kodiert ist
            if (strtolower(mb_detect_encoding($inputString)) != 'utf-8') {
                $fromConvert = mb_detect_encoding($inputString);
                $inputString = iconv($fromConvert, 'UTF-8//TRANSLIT', $inputString);
            }

            $charArray = preg_split('//u', $inputString, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($charArray as $key => $value) {
                $charToOrd = ord($value);

                // <editor-fold defaultstate="collapsed" desc="ASCII Zeichen mit Störfaktor für die Weiterverarbeitung im Infinity">
                /**
                 * ASCII Zeichen mit Störfaktor für die Weiterverarbeitung im Infinity
                 */
                switch ($charToOrd) {
                    case $charToOrd == 0:      // 0   null;
                    case $charToOrd == 1:      // 1   Beginn der Kopfzeile;
                    case $charToOrd == 2:      // 2   Beginn der Nachricht;
                    case $charToOrd == 3:      // 3   Ende der Nachricht;
                    case $charToOrd == 4:      // 4   Ende der Übertragung;
                    case $charToOrd == 5:      // 5   Anfrage;
                    case $charToOrd == 6:      // 6   Empfangsbestätigung;
                    case $charToOrd == 7:      // 7   Tonsignal;
                    case $charToOrd == 8:      // 8   Rückschritt;
                    case $charToOrd == 11:     // 11  Vertikales Tabulatorzeichen;
                    case $charToOrd == 14:     // 14  Umschaltung;
                    case $charToOrd == 15:     // 15  Rückschaltung;
                    case $charToOrd == 16:     // 16  „Datenverbindungs-Fluchtsymbol“ (wörtlich übersetzt);
                    case $charToOrd == 17:     // 17  Gerätekontrollzeichen 1;
                    case $charToOrd == 18:     // 18  Gerätekontrollzeichen 2;
                    case $charToOrd == 19:     // 19  Gerätekontrollzeichen 3;
                    case $charToOrd == 20:     // 20  Gerätekontrollzeichen 4;
                    case $charToOrd == 21:     // 21  Negative Bestätigung;
                    case $charToOrd == 22:     // 22  Synchronisierungssignal;
                    case $charToOrd == 23:     // 23  Ende des Übertragungsblockes;
                    case $charToOrd == 24:     // 24  Abbruch;
                    case $charToOrd == 25:     // 25  Ende des Mediums;
                    case $charToOrd == 26:     // 26  Ersatz;
                    case $charToOrd == 27:     // 27  Fluchtsymbol;
                    case $charToOrd == 28:     // 28  Dateitrenner;
                    case $charToOrd == 29:     // 29  Gruppentrenner;
                    case $charToOrd == 30:     // 30  Datensatztrenner;
                    case $charToOrd == 31:     // 31  Einheitentrenner;
                    case $charToOrd == 127:    // 127 Zeichen löschen;
                        $charArray[$key] = ' ';
                        break;
                    default:
                        break;
                }
                // </editor-fold>

                // <editor-fold defaultstate="collapsed" desc="Special-Umlaut-Zeichen macht wirklich aus jedem Zeichen ein Umlaut">
                if ($charToOrd === 204) {
                    unset($charArray[$key]);

                    $preKey = $key - 1;
                    if (isset($charArray[$preKey]) && $charArray[$preKey]) {
                        switch ($charArray[$preKey]) {
                            case 'a':
                                $charArray[$preKey] = 'ä';
                                break;
                            case 'A':
                                $charArray[$preKey] = 'Ä';
                                break;
                            case 'u':
                                $charArray[$preKey] = 'ü';
                                break;
                            case 'U':
                                $charArray[$preKey] = 'Ü';
                                break;
                            case 'o':
                                $charArray[$preKey] = 'ö';
                                break;
                            case 'O':
                                $charArray[$preKey] = 'Ö';
                                break;
                            default:
                                break;
                        }
                    }
                }
                // </editor-fold>
            }
            return implode($charArray);
        } else {
            return $inputString;
        }
    }

    /**
     * @param array $array
     * @param array $orderArray
     * @param bool $keyListSort
     * @return array
     */
    public static function sortArrayByArray(array $array, array $orderArray, bool $keyListSort = false): array
    {
        $ordered = array();

        if($keyListSort)
        {
            $keyNumbers          = array_keys($array);
            $keyNumbersList      = array_keys($array);

            foreach ($keyNumbers AS $key => $value)
            {
                $keyNumbersList[$key] = strstr($value, '#', true);
            }

            foreach ($orderArray AS $key)
            {
                foreach ($keyNumbersList AS $keyNumber => $keyList)
                {
                    if($key === $keyList)
                    {
                        $keyArray = $keyNumbers[$keyNumber];
                        $ordered[$keyArray] = $array[$keyArray];
                        unset($array[$key]);
                    }
                }
            }

        }else{

            foreach ($orderArray AS $key)
            {
                if(array_key_exists($key, $array))
                {
                    $ordered[$key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }


        return $ordered + $array;
    }

    /**
     * @param mixed $value
     * @param bool $htmlSpecialChars
     * @param string $classAddon
     * @return string
     */
    public static function getPrintr(mixed $value, bool $htmlSpecialChars = false, string $classAddon = ''): string
    {
        $output = '<pre class="bg-light border rounded p-2 '.$classAddon.'">';

        if(is_bool($value)) {
            $output .= ($value ? "true" : "false");
        }else{
            $printr  = print_r($value, true);
            $output .= $htmlSpecialChars ? htmlspecialchars($printr) : $printr;
        }

        $output .= "</pre>";
        return $output;
    }
}

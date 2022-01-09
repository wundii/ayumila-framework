<?php
/***************************************************************************

 *
 * $xml_array = [
 *     'job' => [
 *         '&0' => [
 *             'hallo' => 'test1',
 *             'bye' => 'test2'
 *         ],
 *         '&1' => [
 *             'hallo' => 'test1',
 *             'bye' => 'test2'
 *         ]
 *     ]
 * ];
 * 
 * awu - 16.11.2020
 */

namespace Ayumila\Classes;

class ArrayToXML{
    
    private string  $version              = '1.0';
    private string  $encoding             = 'UTF-8';
    private bool    $zugferd              = false;
    private array   $array                = array();
    private ?string $xml                  = null;
    private string  $root                 = 'root';
    private array   $root_atr             = array();
    private ?string $root_start           = null;
    private ?string $root_content         = null;
    private ?string $root_end             = null;
    private bool    $stripTags            = false;
    private bool    $lineBreak            = false;
    private bool    $emptyValueHidden     = false;
    private bool    $elementStringToLower = false;

    public function __construct(array $array, ?string $root = null)
    {
        $this->array = $array;
        if($root){
            $this->root = $root;
        }
    }

    /**
     * @param array $array
     * @param string|null $root
     * @return ArrayToXML
     */
    public static function create(array $array, ?string $root = null): ArrayToXML
    {
        return new self($array, $root);
    }

    /**
     * @return $this
     */
    public function setLineBreak(): ArrayToXML
    {
        $this->lineBreak = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function setEmptyValueHidden(): ArrayToXML
    {
        $this->emptyValueHidden = true;
        return $this;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): ArrayToXML
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $encoding
     * @return $this
     */
    public function setEncoding(string $encoding): ArrayToXML
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @return $this
     */
    public function setZugferd(): ArrayToXML
    {
        $this->zugferd = true;
        return $this;
    }

    /**
     * @param string $root
     * @return $this
     */
    public function setRoot(string $root): ArrayToXML
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @param array $root_atr
     * @return $this
     */
    public function setRootAtr(array $root_atr): ArrayToXML
    {
        $this->root_atr = $root_atr;
        return $this;
    }

    /**
     * @return $this
     */
    public function setItemElementToLower(): ArrayToXML
    {
        $this->elementStringToLower = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function setStripTagsEnable(): ArrayToXML
    {
        $this->stripTags = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getXml():string
    {
        if(!$this->xml)
        {
            $this->generateXml();
        }

        return $this->xml;
    }

    /**
     * @param array $array
     * @param int $i
     */
    private function array_to_xml(array $array, int $i=0): void
    {
        $i++;
        
        if($this->lineBreak){
            $n = "\n";
            $t = str_repeat("\t",$i);
        }else{
            $n = "";
            $t = "";
        }
        
        foreach($array AS $key => $value){
                
            $key = preg_replace('![^0-9a-zA-Z_=@:#]!', '', $key);

            if($this->elementStringToLower){
                $key = strtolower($key);
            }

            if($this->zugferd){
                if($i == 1){
                    $key = "rsm:".$key;
                }else{
                    if( $key === 'DateTimeString' ||
                        $key === 'DateString' ||
                        $key === 'Indicator'
                    ){
                        $key = "udt:".$key;
                    }else{
                        $key = "ram:".$key;
                    }
                }
            }

            if( is_object($value) )
            {
                $value = (string)$value;
            }

            if( is_array($value) || (is_string($value) || is_double($value) || is_integer($value) || is_float($value)) && strlen((string)$value)>0 ){

                $myatrb = False;
                $myatrb_addon = "";
                
                if(is_array($value)){

                                        
                    foreach($value AS $key_atr => $value_atr){
                        if(mb_substr($key_atr,0,1) == "@"){
                            $key_atr       = mb_substr($key_atr,1);
                            if($value_atr){
                                $myatrb_addon .= " {$key_atr}=\"{$value_atr}\"";
                            }
                            $myatrb        = True;
                        }
                    }
                    
                    if($myatrb){
                        if(isset($value['#text'])){
                            if(is_array($value['#text'])){
                                
                                $first_key = array_key_first($value['#text']);
                                $first_key_char = mb_substr($first_key,0,1);
                                if($first_key_char === "&"){
                                    foreach($value['#text'] AS $key_list => $value_list){
                                        
                                        $this->root_content	.= $t;
                                        $this->root_content	.= "<{$key}{$myatrb_addon}>".$n;
                                        $this->array_to_xml($value['#text'][$key_list],$i);
                                        $this->root_content	.= $t;
                                        $this->root_content	.= "</{$key}>".$n;
                                    }
                                }else{
                                    $this->root_content	.= $t;
                                    $this->root_content	.= "<{$key}{$myatrb_addon}>".$n;
                                    $this->array_to_xml($value['#text'],$i);
                                    $this->root_content	.= $t;
                                    $this->root_content	.= "</{$key}>".$n;
                                }
                            }else{
                                $value['#text'] = $this->getCheckValue($value['#text']);
                                $this->root_content	.= $t;
                                $this->root_content	.= "<{$key}{$myatrb_addon}>{$value['#text']}</{$key}>".$n;
                            }
                        }elseif(!$this->emptyValueHidden){
                            $this->root_content	.= $t;
                            $this->root_content	.= "<{$key}{$myatrb_addon}/>".$n;
                        }
                    }else{
                        $first_key = array_key_first($value);
                        $first_key_char = mb_substr($first_key,0,1);
                        if($first_key_char === "&")
                        {
                            foreach($value AS $key_list => $value_list)
                            {
                                if(is_array($value_list))
                                {
                                    foreach ($value_list as $key_atr => $value_atr)
                                    {
                                        if (mb_substr($key_atr, 0, 1) == "@")
                                        {
                                            $key_atr = mb_substr($key_atr, 1);
                                            if ($value_atr)
                                            {
                                                $myatrb_addon .= " {$key_atr}=\"{$value_atr}\"";
                                            }
                                            $myatrb = true;
                                        }
                                    }
                                }

                                if ($myatrb) {
                                    if (isset($value[$key_list]['#text'])) {
                                        if (is_array($value['#text'])) {
                                            if (is_array($value[$key_list])) {
                                                $this->root_content .= $t;
                                                $this->root_content .= "<{$key}{$myatrb_addon}>" . $n;
                                                $this->array_to_xml($value[$key_list], $i);
                                                $this->root_content .= $t;
                                                $this->root_content .= "</{$key}>" . $n;
                                            } else {
                                                $this->root_content .= $t;
                                                $this->root_content .= "<{$key}{$myatrb_addon}>{$value[$key_list]['#text']}</{$key}>" . $n;
                                            }
                                        } else {
                                            $this->root_content .= $t;
                                            $this->root_content .= "<{$key}{$myatrb_addon}>{$value[$key_list]['#text']}</{$key}>" . $n;
                                        }
                                    } else {
                                        $this->root_content .= $t;
                                        $this->root_content .= "<{$key}{$myatrb_addon}>{$value[$key_list]}</{$key}>" . $n;
                                    }
                                } else if (is_array($value[$key_list])) {
                                    $this->root_content .= $t;
                                    $this->root_content .= "<{$key}>" . $n;
                                    $this->array_to_xml($value[$key_list], $i);
                                    $this->root_content .= $t;
                                    $this->root_content .= "</{$key}>" . $n;
                                } else {
                                    $this->root_content .= $t;
                                    if ($value_list) {
                                        $value_list = $this->getCheckValue($value_list);
                                        $this->root_content .= "<{$key}>{$value_list}</{$key}>".$n;
                                    }else{
                                        $this->root_content .= "<{$key}/>".$n;
                                    }
                                }
                            }

                        }else{
                            $this->root_content	.= $t;
                            $this->root_content	.= "<{$key}>".$n;
                            $this->array_to_xml($value,$i);
                            $this->root_content	.= $t;
                            $this->root_content	.= "</{$key}>".$n;
                        }
                    }
                }else{
                    $value = $this->getCheckValue($value);
                    $this->root_content .= $t;
                    $this->root_content .= "<{$key}>{$value}</{$key}>".$n;
                }
            }elseif(!$this->emptyValueHidden){
                $this->root_content .= $t;
                $this->root_content .= "<{$key}/>".$n;
            }
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getCheckValue(mixed $value): string
    {
        $value = trim($value);

        $isSpecialCDATA = false;

        if( is_numeric($value) ){
            $tempFirst = mb_substr($value, 0, 1);
            $tempSecond = mb_substr($value, 1, 1);
            if($tempFirst == '0' && $tempSecond != ',' && $tempSecond != '.'){
                $isSpecialCDATA = true;
            }
        }
        
        if( !is_numeric($value) || $isSpecialCDATA ){
            $value = $this->getCdataValue($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private function getCdataValue(string $value): string
    {
        if(!$this->stripTags){
            $tempvalue = strip_tags($value);
        }else{
            $tempvalue = $value;
        }

        $tempvalue = str_replace('<![CDATA[','', $tempvalue);
        $tempvalue = str_replace(']]>','', $tempvalue);

        return "<![CDATA[".$tempvalue."]]>";
    }

    /**
     * @return $this
     */
    public function generateXml(): ArrayToXML
    {
        
        if($this->lineBreak){
            $n = "\n";
            $t = "\t";
        }else{
            $n = "";
            $t = "";
        }
        
        // XML Kopf vorbereiten
        $this->xml = "<?xml version='{$this->version}' encoding='{$this->encoding}' ?>".$n;
        
        // Rootelemente aufbereiten
        if($this->root_atr){
            $atr = "";
            if(is_array($this->root_atr) && count($this->root_atr)){
                foreach($this->root_atr AS $key => $value){
                    $atr .= " {$key}=\"{$value}\"";
                }
            }

            $this->root_start	= "<{$this->root}{$atr}>".$n;
            $this->root_end	= "</{$this->root}>";
        }else{
            $this->root_start	= "<{$this->root}>".$n;
            $this->root_end	= "</{$this->root}>";
        }
        
        // Konvertierung des Array`s in eine XML Struktur
        $this->array_to_xml($this->array);
        
        // erstellte XML Elemente zusammensetzen
        $this->xml .= $this->root_start;
        $this->xml .= $this->root_content;
        $this->xml .= $this->root_end;

        return $this;
    }
}
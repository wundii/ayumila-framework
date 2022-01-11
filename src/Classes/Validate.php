<?php

namespace Ayumila\Classes;

use Ayumila\Exceptions\AyumilaException;
use DateTime;
use Exception;

class Validate extends ValidateProcess
{
    private array                   $optional = array();
    private ValidateLanguageLibrary $language;

    /**
     * @param ValidateLanguageLibrary|null $languageLibrary
     */
    private function __construct(?ValidateLanguageLibrary $languageLibrary)
    {
        $this->language = $this->autofillDefaultLanguage($languageLibrary);
    }

    /**
     * @param ValidateLanguageLibrary|null $languageLibrary
     * @return static
     */
    public static function create(ValidateLanguageLibrary $languageLibrary = null): self
    {
        return new self($languageLibrary);
    }

    /**
     * @param ValidateLanguageLibrary|null $languageLibrary
     * @return ValidateLanguageLibrary
     */
    private function autofillDefaultLanguage(?ValidateLanguageLibrary $languageLibrary): ValidateLanguageLibrary
    {
        if(!$languageLibrary)
        {
            $languageLibrary = ValidateLanguageLibrary::create();
        }

        $defaultLibrary = ValidateLanguageLibrary::create()
            ->setLanguageWithParameter('required', 'The variable is empty or not available')
            ->setLanguageWithParameter('min_length', 'The input is to short')
            ->setLanguageWithParameter('max_length', 'The input is to long')
            ->setLanguageWithParameter('matches', 'The variable match is to identical')
            ->setLanguageWithParameter('not_matches', 'The variable match does not match identically with')
            ->setLanguageWithParameter('valid_url', 'The url #replace# is not valid')
            ->setLanguageWithParameter('valid_email', 'The email #replace# is not valid')
            ->setLanguageWithParameter('valid_password', 'The password is not valid')
            ->setLanguageWithParameter('valid_password_simple', 'The password is not valid')
            ->setLanguageWithParameter('valid_datetime', 'The Date #replace# is not valid')
            ->setLanguageWithParameter('instanceof', 'The Object #object# is not from Class #classname#')
            ->setLanguageWithParameter('is_bool', 'This Variable is not a bool')
            ->setLanguageWithParameter('is_number', 'This Variable is not numeric')
            ->setLanguageWithParameter('is_string', 'This Variable is not a string')
            ->setLanguageWithParameter('is_array', 'This Variable is not a array')
        ;

        foreach ($defaultLibrary->getLibrary() AS $validateMethod => $defaultLanguage)
        {
            if(!$languageLibrary->isValidateMethodAvailable($validateMethod))
            {
                $languageLibrary->setLanguage($defaultLanguage);
            }
        }

        return $languageLibrary;
    }

    public function geErrorOutputByMethod(string $validateMethod): string
    {
        return $this->language->getOutputByVariableMethod($validateMethod);
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function required(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            return true;
        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
        return false;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function optional(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $this->optional[$key] = true;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    private function isOptional(?string $key): bool
    {
        if(array_key_exists($key, $this->optional) && $this->getREQUEST($key) === '')
        {
            return true;
        }

        return false;
    }

    /**
     * @param ?string $key
     * @param int $length
     * @return bool
     * @throws AyumilaException
     */
    protected function min_length(?string $key, int $length): bool
    {
        if($this->isREQUEST($key))
        {
            if(mb_strlen($this->getREQUEST($key)) >= $length)
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @param int $length
     * @return bool
     * @throws AyumilaException
     */
    protected function max_length(?string $key, int $length): bool
    {
        if($this->isREQUEST($key))
        {
            if(mb_strlen($this->getREQUEST($key)) <= $length)
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @param string $matchKey
     * @return bool
     * @throws AyumilaException
     */
    protected function matches(?string $key, string $matchKey): bool
    {
        if($this->isREQUEST($key) || $this->isREQUEST($matchKey))
        {
            if($this->getREQUEST($key) == $this->getREQUEST($matchKey))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @param string $matchKey
     * @return bool
     * @throws AyumilaException
     */
    protected function not_matches(?string $key, string $matchKey): bool
    {
        if($this->isREQUEST($key) || $this->isREQUEST($matchKey))
        {
            if($this->getREQUEST($key) != $this->getREQUEST($matchKey))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function valid_url(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $parsUrl = parse_url($this->getREQUEST($key));

            $url  = $parsUrl['scheme'] ?? 'http';
            $url .= "://";
            $url .= $parsUrl['host'] ?? '';
            $url .= isset($parsUrl['port'])     ? ':'.$parsUrl['port']     : '';
            $url .= $parsUrl['path'] ?? '';
            $url .= isset($parsUrl['query'])    ? '?'.$parsUrl['query']    : '';
            $url .= isset($parsUrl['fragment']) ? '#'.$parsUrl['fragment'] : '';

            if(filter_var($url, FILTER_VALIDATE_URL) || $this->isOptional($key))
            {
                return true;
            }
        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__),  $this->getREQUEST($key));
        return false;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function valid_email(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $email = $this->getREQUEST($key);

            if (function_exists('idn_to_ascii') && preg_match('#\A([^@]+)@(.+)\z#', $email, $matches))
            {
                $domain = defined('INTL_IDNA_VARIANT_UTS46')
                    ? idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46)
                    : idn_to_ascii($matches[2]);

                if ($domain !== FALSE)
                {
                    $email = $matches[1].'@'.$domain;
                }
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email   = trim($email);
                $domain  = explode("@", $email)[1];
                $mxhosts = null;
                $weight  = null;

                if (getmxrr($domain, $mxhosts, $weight))
                {
                    return true;
                }
            }

            if($this->isOptional($key))
            {
                return true;
            }
        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__),  $this->getREQUEST($key));
        return false;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function valid_password(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            if(preg_match('/^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/',$this->getREQUEST($key)))
            {
                return true;
            }

        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
        return false;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function valid_password_simple(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            if(preg_match('/^.*(?=.*[a-z])(?=.*[A-Z]).*$/',$this->getREQUEST($key)))
            {
                return true;
            }

        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
        return false;
    }

    /**
     * @param ?string $key
     * @param string $format
     * @return bool
     * @throws AyumilaException
     */
    protected function valid_datetime(?string $key, string $format): bool
    {
        if($this->isREQUEST($key))
        {
            $date     = $this->getREQUEST($key);
            $datetime = DateTime::createFromFormat($format, $date);

            if($datetime && $datetime->format($format) == $date)
            {
                return true;
            }

            /** from 01-31 > 1-31; 01-12 > 1-12; 00-23 > 0-23 */
            $format = str_replace(['d','m','H'], ['j','n', 'G'], $format);

            $datetime = DateTime::createFromFormat($format, $date);

            if($datetime && $datetime->format($format) == $date || $this->isOptional($key))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__),  $this->getREQUEST($key));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $object
     * @param string $className
     * @return bool
     */
    protected function instanceof(?string $object, string $className): bool
    {
        if($object === $className){
            return true;
        }

        $this->addError($this->geErrorOutputByMethod(__FUNCTION__),  ['#object#'=>$object,'#classname#'=>$className]);
        return false;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException|Exception
     */
    protected function is_bool(mixed $key): bool
    {
        if($this->isREQUEST($key, true))
        {
            $bool = $this->getREQUEST($key);

            try{
                $bool = Helper::convertToBool($bool);
            }catch (Exception)
            {
                $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
                return false;
            }

            if(is_bool($bool))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function is_number(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $float = $this->getREQUEST($key);
            $floatConvert = Helper::convertToFloat($float);

            if(is_numeric($floatConvert) && strlen($floatConvert) == strlen($float) || $this->isOptional($key))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
        return false;
        }


        return true;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function is_string(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $string = $this->getREQUEST($key);

            if(is_string($string) || $this->isOptional($key))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }

    /**
     * @param ?string $key
     * @return bool
     * @throws AyumilaException
     */
    protected function is_array(?string $key): bool
    {
        if($this->isREQUEST($key))
        {
            $array = $this->getREQUEST($key);

            if($array == 'is_array' || $this->isOptional($key))
            {
                return true;
            }

            $this->addError($this->geErrorOutputByMethod(__FUNCTION__));
            return false;
        }

        return true;
    }
}
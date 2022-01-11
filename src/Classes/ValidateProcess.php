<?php

namespace Ayumila\Classes;

use Ayumila\ApplicationLog;
use Ayumila\Exceptions\AyumilaException;
use Ayumila\Http\RequestData;
use function DI\value;

abstract class ValidateProcess
{
    private   array  $rules            = array();
    private   int    $errorCount       = 0;
    private   array  $errors           = array();
    private   array  $errorRequestKeys = array();
    private   string $currentKey       = '';
    private   bool   $direct           = false;
    private   bool   $run              = false;
    private   bool   $breakFirstFalse  = false;
    protected bool   $validate         = true;

    /**
     * @param string|null $key
     * @param string $rule
     * @param bool $isRequest
     * @return $this
     */
    public function addRuleByRequest(?string $key, string $rule, bool $isRequest = true): self
    {
        $newRule = new ValidateRule();
        $newRule->setIsRequest($isRequest);
        $newRule->setValue($key);
        $newRule->setRule($rule);

        if(!in_array($newRule, $this->rules))
        {
            $this->run              = false;
            $this->validate         = true;
            $this->errorCount       = 0;
            $this->errors           = array();
            $this->errorRequestKeys = array();
            $this->rules[]          = $newRule;
        }

        return $this;
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @return $this
     */
    public function addRuleByArgument(mixed $value, string $rule): self
    {
        $value = is_object($value) ? $value::class : $value;
        $value = is_array($value)  ? 'is_array'    : $value;

        $this->direct = true;
        $this->addRuleByRequest($value, $rule, false);
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function deleteRuleByKey(string $key): self
    {
        foreach ($this->rules AS $keyRules => $rule)
        {
            if(array_key_exists(0, $rule) && $rule[0] === $key)
            {
                unset($this->rules[$keyRules]);
            }
        }

        return $this;
    }

    /**
     * @param ?string $key
     * @param bool $emptyAllow
     * @return bool
     * @throws AyumilaException
     */
    protected function isREQUEST(?string $key, bool $emptyAllow = false): bool
    {
        $this->currentKey = '';
        if($this->direct)
        {
            if(!empty($key) || strlen($key) || ($emptyAllow && $key == ''))
            {
                return true;
            }else{
                return false;
            }
        }

        $this->currentKey = $key;

        return RequestData::isREQUEST($key);
    }

    /**
     * @param ?string $key
     * @return mixed
     * @throws AyumilaException
     */
    protected function getREQUEST(?string $key): mixed
    {
        $this->currentKey = '';
        if($this->direct)
        {
            return $key;
        }

        $this->currentKey = $key;

        return RequestData::getREQUEST($key);
    }

    /**
     * @param bool $autoLog
     * @return bool
     * @throws AyumilaException
     */
    public function isValid(bool $autoLog = false): bool
    {
        if(!$this->run)
        {
            $this->process();
            $this->run = true;
        }

        if($autoLog && ($this->getErrorlist() || $this->getErrorRequestKeyErrorlist()))
        {
            ApplicationLog::addLog('Validate-Error', implode(', ', $this->getErrorlist()), $this->getErrorRequestKeyErrorlist());
        }

        return $this->validate;
    }

    /**
     * @throws AyumilaException
     */
    private function process(): void
    {
        foreach ($this->rules AS $rule)
        {
            if(!$rule instanceof ValidateRule)
            {
                throw new AyumilaException('The Validate-Rule is not valide');
            }

            $key      = $rule->getValue();
            $keyRules = explode('|', $rule->getRule());

            foreach ($keyRules AS $keyRule)
            {
                preg_match('/(?<method>\w+)(\[(?<argument>[a-zA-Z0-9\-_]+)])?/', $keyRule, $matches);

                $keyRuleMethod   = array_key_exists('method', $matches)   ? $matches['method']   : null;
                $keyRuleArgument = array_key_exists('argument', $matches) ? $matches['argument'] : null;

                if($keyRuleMethod && method_exists(Validate::class, $keyRuleMethod))
                {
                    $arguments = $keyRuleArgument ? [$key, $keyRuleArgument] : [$key];
                    $boolReturn = call_user_func_array(array($this, $keyRuleMethod), $arguments);

                    if(!$boolReturn && $this->isBreakFirstFalse())
                    {
                        $this->validate = false;
                        break 2;
                    }
                }else{
                    throw new AyumilaException('The KeyRuleMethod '.$keyRuleMethod.' is not found');
                }
            }
        }
    }

    /**
     * @param string $error
     * @param null|array|string $replace
     * @return array
     */
    protected function addError(string $error, null|array|string $replace = ''): array
    {
        $this->validate = false;

        $replace = empty($replace) ? array() : (array)$replace;

        $this->errorCount++;

        $replaceArray = array();

        if($this->direct)
        {
            $replaceArray = $replace;
        }

        $replaceKeys  = array_keys($replaceArray);
        $replaceValue = array_values($replaceArray);

        foreach ($replaceKeys AS $key => $value)
        {
            if(!is_string($value))
            {
                $replaceKey = $key === 0 ? '' : $key;
                $replaceKeys[$key] = '#replace'.$replaceKey.'#';
            }
        }

        $error = str_replace($replaceKeys, $replaceValue, $error);
        $error = str_replace('  ', ' ', $error);

        if(!in_array($error, $this->errors))
        {
            $this->errors[] = $error;
        }

        if($this->currentKey)
        {
            $this->errorRequestKeys[$this->currentKey][] = $error;
        }

        return $this->errors;
    }

    /**
     * @param string $error
     * @param string|null $inputName
     * @return void
     */
    public function addExternalError(string $error, ?string $inputName = null): void
    {
        $this->validate = false;

        if(!in_array($error, $this->errors))
        {
            $this->errors[] = $error;
        }

        if($inputName)
        {
            $this->errorRequestKeys[$inputName][] = $error;
        }
    }

    /**
     * @return array
     */
    public function getErrorlist(): array
    {
        return $this->errors;
    }

    /**
     * @param string|null $returnValue
     * @return array
     */
    public function getErrorRequestKeys(?string $returnValue): array
    {
        if($returnValue)
        {
            $return = array();
            foreach ($this->errorRequestKeys AS $key => $error)
            {
                $return[$key] = $returnValue;
            }

            return $return;
        }

        return array_keys($this->errorRequestKeys);
    }

    /**
     * @return array
     */
    public function getErrorRequestKeyErrorlist(): array
    {
        return $this->errorRequestKeys;
    }

    /**
     * @return bool
     */
    public function isBreakFirstFalse(): bool
    {
        return $this->breakFirstFalse;
    }

    /**
     * @param bool $breakFirst
     * @return ValidateProcess
     */
    public function setBreakFirstFalse(bool $breakFirst = true): self
    {
        $this->breakFirstFalse = $breakFirst;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }
}
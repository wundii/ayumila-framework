<?php

namespace Ayumila;

use Ayumila\Traits\SingletonStandard;
use ReflectionException;
use ReflectionFunction;

class ApplicationEvent
{
    use SingletonStandard;

    private array $events = array();

    /**
     * @param string $name
     * @param callable $callback
     * @param int $priority
     * @param bool $unsetAfterCall
     * @throws ReflectionException
     */
    public static function listen(string $name, callable $callback, int $priority = 0, bool $unsetAfterCall = false):void
    {
        $callbackParameters = array();
        $reflection         = new ReflectionFunction($callback);

        foreach ($reflection->getParameters() AS $parameter)
        {
            $callbackParameters[] = $parameter->name;
        }

        $instance = self::create();
        $instance->events[$name][] = [
                'callback'           => $callback,
                'callbackParameters' => $callbackParameters,
                'callCount'          => 0,
                'priority'           => $priority,
                'unsetAfterCall'     => $unsetAfterCall,
                'active'             => true,
            ];
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public static function triggerByName(string $name, array $arguments = array()):void
    {
        $instance = self::create();
        if(array_key_exists($name, $instance->events))
        {
            $events = $instance->sortEventByPriority($instance->events[$name]);

            foreach ($events as $key => $callback)
            {
                if(!$callback['active'])
                {
                    continue;
                }

                $arguments = $instance->processArgumentsToParameters($arguments, $callback['callbackParameters']);

                if($arguments && is_array($arguments))
                {
                    call_user_func_array($callback['callback'], $arguments);

                } elseif ($arguments && !is_array($arguments))
                {
                    call_user_func($callback['callback'], $arguments);

                } else {
                    call_user_func($callback['callback']);
                }

                $instance->processCallbackArray($name, $key);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function triggerAllCallbacksWithoutParameters():void
    {
        $instance = self::create();
        foreach ($instance->events as $name => $events)
        {
            $events = $instance->sortEventByPriority($events);

            foreach ($events as $key => $callback)
            {
                $reflection = new ReflectionFunction($callback['callback']);

                if(!$reflection->getParameters() && $callback['active'])
                {
                    call_user_func($callback['callback']);
                    $instance->processCallbackArray($name, $key);
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function overviewEvents(): array
    {
        $returnEvents = array();
        $instance = self::create();

        foreach ($instance->events as $name => $events)
        {
            $events = $instance->sortEventByPriority($events);

            foreach ($events as $key => $callback)
            {
                $returnEvents[$name][$key] = [
                    'callbackParameters' => $callback['callbackParameters'],
                    'callCount'          => $callback['callCount'],
                    'priority'           => $callback['priority'],
                    'unsetAfterCall'     => $callback['unsetAfterCall'],
                    'active'             => $callback['active'],
                ];
            }
        }

        return $returnEvents;
    }

    /**
     * @param string $name
     * @param int $key
     */
    private function processCallbackArray(string $name, int $key): void
    {
        if(array_key_exists($name, $this->events))
        {
            if(array_key_exists($key, $this->events[$name]))
            {
                $this->events[$name][$key]['callCount']++;
                if($this->events[$name][$key]['unsetAfterCall'])
                {
                    $this->events[$name][$key]['active'] = false;
                }
            }
        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function sortEventByPriority(array $array): array
    {
        usort($array, function($a, $b){return $a['priority'] <=> $b['priority'];});
        return $array;
    }

    /**
     * @param array $callbackParameters
     * @param array $arguments
     * @return array|string
     */
    private function processArgumentsToParameters(array $arguments, array $callbackParameters): array|string
    {
        $returnArguments = array();
        if($arguments)
        {
            if(count($callbackParameters) == 1)
            {
                $returnArguments = current($arguments);
            }else{
                foreach ($callbackParameters AS $parameter)
                {
                    $returnArguments[$parameter] = array_key_exists($parameter, $arguments) ? $arguments[$parameter] : null;
                }
            }
        }
        return $returnArguments;
    }
}
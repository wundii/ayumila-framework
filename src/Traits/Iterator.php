<?php

namespace Ayumila\Traits;

use Exception;

trait Iterator
{
    private array $collection = array();

    /**
     * @return mixed
     * @throws Exception
     */
    public function current(): mixed
    {
        return current($this->collection);
    }

    /**
     * @return mixed
     */
    public function next(): mixed
    {
        return next($this->collection);
    }

    /**
     * @return null|int|string
     */
    public function key(): null|int|string
    {
        return key($this->collection);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->collection) !== null;
    }

    /**
     * @return mixed
     */
    public function rewind(): mixed
    {
        return reset($this->collection);
    }
}
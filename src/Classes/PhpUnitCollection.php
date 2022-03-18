<?php

namespace Ayumila\Classes;

use Ayumila\ApplicationControllerData;
use Ayumila\Traits\Iterator AS TraitIterator;
use Ayumila\Traits\SingletonStandard;
use Iterator;

final class PhpUnitCollection implements Iterator
{
    use SingletonStandard;
    use TraitIterator;

    /**
     * @param string $status
     * @param string $description
     * @return void
     */
    public static function addEntity(string $status, string $description)
    {
        if(ApplicationControllerData::isTestMode())
        {
            $backtrace = debug_backtrace();
            $filePath  = $backtrace[0]['file'] ?? '';
            $fileLine  = $backtrace[0]['line'] ?? null;
            $fileClass = $backtrace[1]['function'] ?? '';
            $fileFunc  = $backtrace[1]['class'] ?? '';

            $entity = new PhpUnitEntity();
            $entity->setStatus($status);
            $entity->setDescription($description);
            $entity->setFilePath($filePath);
            $entity->setFileLine($fileLine);
            $entity->setFileClass($fileClass);
            $entity->setFileFunc($fileFunc);

            $instance = self::create();
            $instance->collection[] = $entity;
        }
    }

    /**
     * @return PhpUnitCollection
     */
    public static function getCollection(): self
    {
        return self::create();
    }

    /**
     * @param string $status
     * @param string $description // str_contains
     * @return bool
     */
    public static function isEntityExists(string $status, string $description): bool
    {
        $instance = self::create();
        foreach($instance AS $entity)
        {
            if(str_contains($entity, $description))
            {
                return true;
            }
        }

        return false;
    }
}

final class PhpUnitEntity
{
    private string $status      = '';
    private string $description = '';
    private string $filePath    = '';
    private ?int   $fileLine    = null;
    private string $fileClass   = '';
    private string $fileFunc    = '';

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * @return int|null
     */
    public function getFileLine(): ?int
    {
        return $this->fileLine;
    }

    /**
     * @param int|null $fileLine
     */
    public function setFileLine(?int $fileLine): void
    {
        $this->fileLine = $fileLine;
    }

    /**
     * @return string
     */
    public function getFileClass(): string
    {
        return $this->fileClass;
    }

    /**
     * @param string $fileClass
     */
    public function setFileClass(string $fileClass): void
    {
        $this->fileClass = $fileClass;
    }

    /**
     * @return string
     */
    public function getFileFunc(): string
    {
        return $this->fileFunc;
    }

    /**
     * @param string $fileFunc
     */
    public function setFileFunc(string $fileFunc): void
    {
        $this->fileFunc = $fileFunc;
    }
}
<?php

namespace Ayumila\Classes;

use Ayumila\Traits\CreateStandard;
use Exception;

class ToastNotification
{
    use CreateStandard;

    private ToastStatus $status;
    private string $title = '';
    private string $content = '';

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->status;
    }

    /**
     * @param ToastStatus $status
     * @return self
     * @throws Exception
     */
    public function setStatus(ToastStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
}
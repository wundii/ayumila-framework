<?php

namespace Ayumila\Classes;

use Ayumila\ApplicationControllerData;
use Ayumila\Http\Session;
use Ayumila\Traits\MultitonStandard;

class ToastCollection
{
    use MultitonStandard;

    private array $collection = array();

    /**
     * @return void
     */
    public function run(): void
    {
        if($this->key === ApplicationControllerData::getFirstApplicationKey())
        {
            $session = Session::create();

            if(isset($session->toasts))
            {
                foreach ($session->toasts AS $toast)
                {
                    if($toast instanceof ToastNotification)
                    {
                        $this->collection[] = $toast;
                    }

                }
                unset($session->toasts);
            }
        }
    }

    /**
     * @return array
     */
    public function getToastsCollection(): array
    {
        return $this->collection;
    }

    /**
     * @return void
     */
    public function forwardToasts(): void
    {
        $session = Session::create();

        foreach ($this->collection AS $toast)
        {
            $toasts          = is_array($session->toasts) ? $session->toasts : array();
            $session->toasts = array_merge($toasts, [$toast]);
        }
    }
}
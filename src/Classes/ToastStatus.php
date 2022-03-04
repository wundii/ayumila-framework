<?php

namespace Ayumila\Classes;

use Ayumila\Traits\ClassEnum;

final class ToastStatus
{
    use ClassEnum;

    public const PRIMARY   = 'primary';
    public const SECONDARY = 'secondary';
    public const SUCCESS   = 'success';
    public const DANGER    = 'danger';
    public const WARNING   = 'warning';
    public const INFO      = 'info';
    public const LIGHT     = 'light';
    public const DARK      = 'dark';
}
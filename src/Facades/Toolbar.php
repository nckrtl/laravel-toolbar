<?php

namespace NckRtl\Toolbar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NckRtl\Toolbar\Toolbar
 */
class Toolbar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NckRtl\Toolbar\Toolbar::class;
    }
}

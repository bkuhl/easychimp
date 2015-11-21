<?php

namespace Easychimp;

use Illuminate\Support\Facades\Facade;

class EasychimpFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Easychimp';
    }
}
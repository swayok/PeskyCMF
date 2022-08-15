<?php

declare(strict_types=1);

namespace PeskyCMF\Facades;

use Illuminate\Support\Facades\Facade;
use PeskyCMF\PeskyCmfManager;

class PeskyCmf extends Facade
{
    
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PeskyCmfManager::class;
    }
}
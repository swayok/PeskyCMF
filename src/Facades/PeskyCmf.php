<?php

declare(strict_types=1);

namespace PeskyCMF\Facades;

use Illuminate\Support\Facades\Facade;
use PeskyCMF\PeskyCmfManager;

class PeskyCmf extends Facade
{
    
    protected static function getFacadeAccessor(): string
    {
        return PeskyCmfManager::class;
    }
}
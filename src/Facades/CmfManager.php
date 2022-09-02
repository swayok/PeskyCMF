<?php

declare(strict_types=1);

namespace PeskyCMF\Facades;

use Illuminate\Support\Facades\Facade;
use PeskyCMF\CmfManager as RealCmfManager;

/**
 * todo: add methods docs
 */
class CmfManager extends Facade
{
    
    protected static function getFacadeAccessor(): string
    {
        return RealCmfManager::class;
    }
}
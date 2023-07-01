<?php

declare(strict_types=1);

namespace PeskyCMF\Facades;

use Illuminate\Support\Facades\Facade;
use PeskyCMF\CmfManager as RealCmfManager;
use PeskyCMF\Config\CmfConfig;

/**
 * @method CmfConfig getCurrentCmfConfig()
 * @method string getCurrentCmfSectionName()
 * @method CmfConfig getCmfConfigForSection(?string $cmfSectionName = null)
 * @method string getDefaultSectionName()
 * @method string[] getAllCmfSectionsNames()
 * @method void setCurrentCmfSection(string $cmfSectionName)
 * @method void onSectionSet(\Closure $callback)
 * @method CmfConfig[] getAvailableCmfConfigs()
 */
class CmfManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RealCmfManager::class;
    }
}

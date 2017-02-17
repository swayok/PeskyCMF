<?php

namespace PeskyCMF;

use PeskyCMF\CMS\Admins\CmsAdminsScaffoldConfig;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\CmsTableStructure;
use PeskyCMF\CMS\Pages\CmsPagesScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Settings\CmsSettingsScaffoldConfig;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\CMS\Texts\CmsTextsScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsTable;

class PeskyCmsServiceProvider extends PeskyCmfServiceProvider {

    public function boot() {
        parent::boot();
        CmsTableStructure::getCmsConfig()->registerDbTableAndScaffoldConfig(CmsSettingsTable::getInstance(), CmsSettingsScaffoldConfig::class);
        CmsTableStructure::getCmsConfig()->registerDbTableAndScaffoldConfig(CmsAdminsTable::getInstance(), CmsAdminsScaffoldConfig::class);
        CmsTableStructure::getCmsConfig()->registerDbTableAndScaffoldConfig(CmsPagesTable::getInstance(), CmsPagesScaffoldConfig::class);
        CmsTableStructure::getCmsConfig()->registerDbTableAndScaffoldConfig(CmsTextsTable::getInstance(), CmsTextsScaffoldConfig::class);
    }

}
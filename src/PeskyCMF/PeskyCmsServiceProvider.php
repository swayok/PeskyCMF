<?php

namespace PeskyCMF;

use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\Admins\CmsAdminsTableStructure;
use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Pages\CmsPagesTableStructure;
use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\CMS\Settings\CmsSettingsTableStructure;
use PeskyCMF\CMS\Texts\CmsText;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\CMS\Texts\CmsTextsTableStructure;

class PeskyCmsServiceProvider extends PeskyCmfServiceProvider {

    public function register() {
        parent::register();
        $this->registerAdminsDbClasses();
        $this->registerSettingsDbClasses();
        $this->registerPagesDbClasses();
        $this->registerTextsDbClasses();
        // note: scaffolds declared in CmsSiteLoader
    }

    public function registerAdminsDbClasses() {
        $this->app->bind(CmsAdmin::class, function () {
            return CmsAdmin::newEmptyRecord();
        });
        $this->app->singleton(CmsAdminsTable::class, function () {
            return CmsAdminsTable::getInstance();
        });
        $this->app->singleton(CmsAdminsTableStructure::class, function () {
            return CmsAdminsTableStructure::getInstance();
        });
    }

    public function registerSettingsDbClasses() {
        $this->app->bind(CmsSetting::class, function () {
            return CmsSetting::newEmptyRecord();
        });
        $this->app->singleton(CmsSettingsTable::class, function () {
            return CmsSettingsTable::getInstance();
        });
        $this->app->singleton(CmsSettingsTableStructure::class, function () {
            return CmsSettingsTableStructure::getInstance();
        });
    }

    public function registerPagesDbClasses() {
        $this->app->bind(CmsPage::class, function () {
            return CmsPage::newEmptyRecord();
        });
        $this->app->singleton(CmsPagesTable::class, function () {
            return CmsPagesTable::getInstance();
        });
        $this->app->singleton(CmsPagesTableStructure::class, function () {
            return CmsPagesTableStructure::getInstance();
        });
    }

    public function registerTextsDbClasses() {
        $this->app->bind(CmsText::class, function () {
            return CmsText::newEmptyRecord();
        });
        $this->app->singleton(CmsTextsTable::class, function () {
            return CmsTextsTable::getInstance();
        });
        $this->app->singleton(CmsTextsTableStructure::class, function () {
            return CmsTextsTableStructure::getInstance();
        });
    }

}
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

    // admins

    public function registerAdminsDbClasses() {
        $this->registerAdminsDbRecord();
        $this->registerAdminsDbTable();
        $this->registerAdminsDbTableStructure();
    }

    public function registerAdminsDbRecord() {
        $this->app->bind(CmsAdmin::class, function () {
            return CmsAdmin::newEmptyRecord();
        });
    }

    public function registerAdminsDbTable() {
        $this->app->singleton(CmsAdminsTable::class, function () {
            return CmsAdminsTable::getInstance();
        });
    }

    public function registerAdminsDbTableStructure() {
        $this->app->singleton(CmsAdminsTableStructure::class, function () {
            return CmsAdminsTableStructure::getInstance();
        });
    }

    // settings

    public function registerSettingsDbClasses() {
        $this->registerSettingsDbRecord();
        $this->registerSettingsDbTable();
        $this->registerSettingsDbTableStructure();
    }

    public function registerSettingsDbRecord() {
        $this->app->bind(CmsSetting::class, function () {
            return CmsSetting::newEmptyRecord();
        });
    }

    public function registerSettingsDbTable() {
        $this->app->singleton(CmsSettingsTable::class, function () {
            return CmsSettingsTable::getInstance();
        });
    }

    public function registerSettingsDbTableStructure() {
        $this->app->singleton(CmsSettingsTableStructure::class, function () {
            return CmsSettingsTableStructure::getInstance();
        });
    }

    // pages

    public function registerPagesDbClasses() {
        $this->registerPagesDbRecord();
        $this->registerPagesDbTable();
        $this->registerPagesDbTableStructure();
    }

    public function registerPagesDbRecord() {
        $this->app->bind(CmsPage::class, function () {
            return CmsPage::newEmptyRecord();
        });
    }

    public function registerPagesDbTable() {
        $this->app->singleton(CmsPagesTable::class, function () {
            return CmsPagesTable::getInstance();
        });
    }

    public function registerPagesDbTableStructure() {
        $this->app->singleton(CmsPagesTableStructure::class, function () {
            return CmsPagesTableStructure::getInstance();
        });
    }

    // texts

    public function registerTextsDbClasses() {
        $this->registerTextsDbRecord();
        $this->registerTextsDbTable();
        $this->registerTextsDbTableStructure();
    }

    public function registerTextsDbRecord() {
        $this->app->bind(CmsText::class, function () {
            return CmsText::newEmptyRecord();
        });
    }

    public function registerTextsDbTable() {
        $this->app->singleton(CmsTextsTable::class, function () {
            return CmsTextsTable::getInstance();
        });
    }

    public function registerTextsDbTableStructure() {
        $this->app->singleton(CmsTextsTableStructure::class, function () {
            return CmsTextsTableStructure::getInstance();
        });
    }

}
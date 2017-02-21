<?php

namespace PeskyCMF\Providers;

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
        $this->registerAdminsDbRecordClassName();
        $this->registerAdminsDbTable();
        $this->registerAdminsDbTableStructure();
    }

    public function registerAdminsDbRecordClassName() {
        $this->app->singleton(CmsAdmin::class, function () {
            // note: do not create record here or you will possibly encounter infinite loop because this class may be
            // used in TableStructure via app(NameTableStructure) (for example to get default value, etc)
            return CmsAdmin::class;
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
        $this->registerSettingsDbRecordClassName();
        $this->registerSettingsDbTable();
        $this->registerSettingsDbTableStructure();
    }

    public function registerSettingsDbRecordClassName() {
        $this->app->singleton(CmsSetting::class, function () {
            // note: do not create record here or you will possibly encounter infinite loop because this class may be
            // used in TableStructure via app(NameTableStructure) (for example to get default value, etc)
            return CmsSetting::class;
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
        $this->registerPagesDbRecordClassName();
        $this->registerPagesDbTable();
        $this->registerPagesDbTableStructure();
    }

    public function registerPagesDbRecordClassName() {
        $this->app->singleton(CmsPage::class, function () {
            // note: do not create record here or you will possibly encounter infinite loop because this class may be
            // used in TableStructure via app(NameTableStructure) (for example to get default value, etc)
            return CmsPage::class;
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
        $this->registerTextsDbRecordClassName();
        $this->registerTextsDbTable();
        $this->registerTextsDbTableStructure();
    }

    public function registerTextsDbRecordClassName() {
        $this->app->singleton(CmsText::class, function () {
            // note: do not create record here or you will possibly encounter infinite loop because this class may be
            // used in TableStructure via app(NameTableStructure) (for example to get default value, etc)
            return CmsText::class;
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
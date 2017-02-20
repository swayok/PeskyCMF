<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Admins\CmsAdminsScaffoldConfig;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\Pages\CmsPagesScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Settings\CmsSettingsScaffoldConfig;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\CMS\Texts\CmsTextsScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\PeskyCmfSiteLoader;

abstract class CmsSiteLoader extends PeskyCmfSiteLoader {

    public function register() {
        parent::register();
        // register default scaffolds
        // admins
        $this->registerAdminsSectionTables();
        $this->registerAdminsSectionScaffolds();
        // settings
        $this->registerSettingsSectionTables();
        $this->registerSettingsSectionScaffolds();
        // pages
        $this->registerPagesSectionTables();
        $this->registerPagesSectionScaffolds();
        // texts
        $this->registerTextsSectionTables();
        $this->registerTextsSectionScaffolds();
    }

    public function registerAdminsSectionTables() {
        $this->app->alias(CmsAdminsTable::class, 'cms.section.admins.table');
    }

    public function registerAdminsSectionScaffolds() {
        $this->app->singleton('cms.section.admins.scaffold', function () {
            return new CmsAdminsScaffoldConfig($this->app->make('cms.section.admins.table'), 'admins');
        });
    }

    public function registerSettingsSectionTables() {
        $this->app->alias(CmsSettingsTable::class, 'cms.section.settings.table');
    }

    public function registerSettingsSectionScaffolds() {
        $this->app->singleton('cms.section.settings.scaffold', function () {
            return new CmsSettingsScaffoldConfig($this->app->make('cms.section.settings.table'), 'settings');
        });
    }

    public function registerPagesSectionTables() {
        $this->app->alias(CmsPagesTable::class, 'cms.section.pages.table');
    }

    public function registerPagesSectionScaffolds() {
        $this->app->singleton('cms.section.pages.scaffold', function () {
            return new CmsPagesScaffoldConfig($this->app->make('cms.section.pages.table'), 'pages');
        });
    }

    public function registerTextsSectionTables() {
        $this->app->alias(CmsTextsTable::class, 'cms.section.texts.table');
    }

    public function registerTextsSectionScaffolds() {
        $this->app->singleton('cms.section.texts.scaffold', function () {
            return new CmsTextsScaffoldConfig($this->app->make('cms.section.texts.table'), 'texts');
        });
    }

}

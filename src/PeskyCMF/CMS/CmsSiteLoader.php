<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Admins\CmsAdminsScaffoldConfig;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\Pages\CmsPagesScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Settings\CmsSettingsScaffoldConfig;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\CMS\Texts\CmsTextsForCategoriesScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsForItemsScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsForNewsScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsForPagesScaffoldConfig;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\Http\PeskyCmfSiteLoader;

abstract class CmsSiteLoader extends PeskyCmfSiteLoader {

    public function register() {
        parent::register();
        // register default scaffolds
        // admins
        $this->registerAdminsTables();
        $this->registerAdminsScaffolds();
        // pages
        $this->registerPagesTables();
        $this->registerPagesScaffolds();
        // texts
        $this->registerTextsTables();
        $this->registerTextsScaffolds();
        // settings
        $this->registerSettingsTables();
        $this->registerSettingsScaffolds();
    }

    public function boot() {
        parent::boot();
        $cmfConfig = static::getCmfConfig();
        $cmfConfig::addMenuItem('admins', [
            'label' => $cmfConfig::transCustom('.admins.menu_title'),
            'url' => routeToCmfItemsTable('admins'),
            'icon' => 'fa fa-group'
        ]);
        $cmfConfig::addMenuItem('pages', [
            'label' => $cmfConfig::transCustom('.pages.menu_title'),
            'url' => routeToCmfItemsTable('pages'),
            'icon' => 'fa fa-files-o'
        ]);
        $cmfConfig::addMenuItem('texts_for_pages', [
            'label' => $cmfConfig::transCustom('.texts_for_pages.menu_title'),
            'url' => routeToCmfItemsTable('texts_for_pages'),
            'icon' => 'fa fa-file-text-o'
        ]);
        $cmfConfig::addMenuItem('texts_for_news', [
            'label' => $cmfConfig::transCustom('.texts_for_news.menu_title'),
            'url' => routeToCmfItemsTable('texts_for_news'),
            'icon' => 'fa fa-newspaper-o'
        ]);
        $cmfConfig::addMenuItem('texts_for_categories', [
            'label' => $cmfConfig::transCustom('.texts_for_categories.menu_title'),
            'url' => routeToCmfItemsTable('texts_for_categories'),
            'icon' => 'fa fa-folder-open-o'
        ]);
        $cmfConfig::addMenuItem('texts_for_items', [
            'label' => $cmfConfig::transCustom('.texts_for_items.menu_title'),
            'url' => routeToCmfItemsTable('texts_for_items'),
            'icon' => 'fa fa-files-o'
        ]);
        $cmfConfig::addMenuItem('settings', [
            'label' => $cmfConfig::transCustom('.settings.menu_title'),
            'url' => routeToCmfItemEditForm('settings', 'all'),
            'icon' => 'glyphicon glyphicon-cog'
        ]);
    }

    public function registerAdminsTables() {
        $this->app->alias(CmsAdminsTable::class, 'cms.section.admins.table');
    }

    public function registerAdminsScaffolds() {
        $this->app->singleton('cms.section.admins.scaffold', function () {
            return new CmsAdminsScaffoldConfig($this->app->make('cms.section.admins.table'), 'admins');
        });
    }

    public function registerSettingsTables() {
        $this->app->alias(CmsSettingsTable::class, 'cms.section.settings.table');
    }

    public function registerSettingsScaffolds() {
        $this->app->singleton('cms.section.settings.scaffold', function () {
            return new CmsSettingsScaffoldConfig($this->app->make('cms.section.settings.table'), 'settings');
        });
    }

    public function registerPagesTables() {
        $this->app->alias(CmsPagesTable::class, 'cms.section.pages.table');
    }

    public function registerPagesScaffolds() {
        $this->app->singleton('cms.section.pages.scaffold', function () {
            return new CmsPagesScaffoldConfig($this->app->make('cms.section.pages.table'), 'pages');
        });
    }

    public function registerTextsTables() {
        $this->app->alias(CmsTextsTable::class, 'cms.section.texts_for_pages.table');
        $this->app->alias(CmsTextsTable::class, 'cms.section.texts_for_news.table');
        $this->app->alias(CmsTextsTable::class, 'cms.section.texts_for_categories.table');
        $this->app->alias(CmsTextsTable::class, 'cms.section.texts_for_items.table');
    }

    public function registerTextsScaffolds() {
        $this->app->singleton('cms.section.texts_for_pages.scaffold', function () {
            return new CmsTextsForPagesScaffoldConfig($this->app->make('cms.section.texts_for_pages.table'), 'texts_for_pages');
        });
        $this->app->singleton('cms.section.texts_for_news.scaffold', function () {
            return new CmsTextsForNewsScaffoldConfig($this->app->make('cms.section.texts_for_news.table'), 'texts_for_news');
        });
        $this->app->singleton('cms.section.texts_for_categories.scaffold', function () {
            return new CmsTextsForCategoriesScaffoldConfig($this->app->make('cms.section.texts_for_categories.table'), 'texts_for_categories');
        });
        $this->app->singleton('cms.section.texts_for_items.scaffold', function () {
            return new CmsTextsForItemsScaffoldConfig($this->app->make('cms.section.texts_for_items.table'), 'texts_for_items');
        });
    }

}

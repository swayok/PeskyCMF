<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Admins\CmsAdminsScaffoldConfig;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\Pages\CmsNewsScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Settings\CmsSettingsScaffoldConfig;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\CMS\Texts\CmsCommonTextsScaffoldConfig;
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
        $this->registerTextsElementsTables();
        $this->registerTextElementsScaffolds();
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
            'icon' => 'fa fa-file-text-o'
        ]);
        $cmfConfig::addMenuItem('news', [
            'label' => $cmfConfig::transCustom('.news.menu_title'),
            'url' => routeToCmfItemsTable('news'),
            'icon' => 'fa fa-newspaper-o'
        ]);
        $cmfConfig::addMenuItem('shop_categories', [
            'label' => $cmfConfig::transCustom('.shop_categories.menu_title'),
            'url' => routeToCmfItemsTable('shop_categories'),
            'icon' => 'fa fa-folder-open-o'
        ]);
        $cmfConfig::addMenuItem('shop_items', [
            'label' => $cmfConfig::transCustom('.shop_items.menu_title'),
            'url' => routeToCmfItemsTable('shop_items'),
            'icon' => 'fa fa-files-o'
        ]);
        $cmfConfig::addMenuItem('text_elements', [
            'label' => $cmfConfig::transCustom('.text_elements.menu_title'),
            'url' => routeToCmfItemsTable('text_elements'),
            'icon' => 'fa fa-file-code-o'
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
        $this->app->alias(CmsPagesTable::class, 'cms.section.news.table');
        $this->app->alias(CmsPagesTable::class, 'cms.section.shop_categories.table');
        $this->app->alias(CmsPagesTable::class, 'cms.section.shop_items.table');
    }

    public function registerPagesScaffolds() {
        $this->app->singleton('cms.section.pages.scaffold', function () {
            return new CmsPagesScaffoldConfig($this->app->make('cms.section.pages.table'), 'pages');
        });
        $this->app->singleton('cms.section.news.scaffold', function () {
            return new CmsNewsScaffoldConfig($this->app->make('cms.section.news.table'), 'news');
        });
//        $this->app->singleton('cms.section.shop_categories.scaffold', function () {
//            return new CmsShopCategoriesScaffoldConfig($this->app->make('cms.section.shop_categories.table'), 'shop_categories');
//        });
//        $this->app->singleton('cms.section.shop_items.scaffold', function () {
//            return new CmsShopItemsScaffoldConfig($this->app->make('cms.section.shop_items.table'), 'shop_items');
//        });
    }

    public function registerTextsElementsTables() {
//        $this->app->alias(CmsTextsTable::class, 'cms.section.common_texts.table');
    }

    public function registerTextElementsScaffolds() {
//        $this->app->singleton('cms.section.common_texts.scaffold', function () {
//            return new CmsCommonTextsScaffoldConfig($this->app->make('cms.section.common_texts.table'), 'common_texts');
//        });
    }

}

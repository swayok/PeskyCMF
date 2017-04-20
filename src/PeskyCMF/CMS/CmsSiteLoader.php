<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Admins\CmsAdminsScaffoldConfig;
use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyCMF\CMS\Pages\CmsTextElementsScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsNewsScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesScaffoldConfig;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Settings\CmsSettingsScaffoldConfig;
use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\Http\PeskyCmfSiteLoader;

abstract class CmsSiteLoader extends PeskyCmfSiteLoader {

    protected $registerSections = [
        'admins',
        'pages',
        'news',
//        'shop_categories',
//        'shop_items',
        'text_elements',
        'settings'
    ];

    public function register() {
        parent::register();
        // register default scaffolds
        if (in_array('admins', $this->registerSections, true)) {
            // admins
            $this->registerAdminsTables();
            $this->registerAdminsScaffolds();
        }
        if (count(array_intersect(['pages', 'news', 'shop_categories', 'shop_items', 'text_elements'], $this->registerSections))) {
            // pages
            $this->registerPagesTables();
            $this->registerPagesScaffolds();
        }
        if (in_array('settings', $this->registerSections, true)) {
            // settings
            $this->registerSettingsTables();
            $this->registerSettingsScaffolds();
        }
    }

    public function boot() {
        parent::boot();
        $cmfConfig = static::getCmfConfig();
        if (in_array('admins', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('admins', [
                'label' => $cmfConfig::transCustom('.admins.menu_title'),
                'url' => routeToCmfItemsTable('admins'),
                'icon' => 'fa fa-group'
            ]);
        }
        if (in_array('pages', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('pages', [
                'label' => $cmfConfig::transCustom('.pages.menu_title'),
                'url' => routeToCmfItemsTable('pages'),
                'icon' => 'fa fa-file-text-o'
            ]);
        }
        if (in_array('news', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('news', [
                'label' => $cmfConfig::transCustom('.news.menu_title'),
                'url' => routeToCmfItemsTable('news'),
                'icon' => 'fa fa-newspaper-o'
            ]);
        }
        if (in_array('shop_categories', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('shop_categories', [
                'label' => $cmfConfig::transCustom('.shop_categories.menu_title'),
                'url' => routeToCmfItemsTable('shop_categories'),
                'icon' => 'fa fa-folder-open-o'
            ]);
        }
        if (in_array('shop_items', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('shop_items', [
                'label' => $cmfConfig::transCustom('.shop_items.menu_title'),
                'url' => routeToCmfItemsTable('shop_items'),
                'icon' => 'fa fa-files-o'
            ]);
        }
        if (in_array('text_elements', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('text_elements', [
                'label' => $cmfConfig::transCustom('.text_elements.menu_title'),
                'url' => routeToCmfItemsTable('text_elements'),
                'icon' => 'fa fa-file-code-o'
            ]);
        }
        if (in_array('settings', $this->registerSections, true)) {
            $cmfConfig::addMenuItem('settings', [
                'label' => $cmfConfig::transCustom('.settings.menu_title'),
                'url' => routeToCmfItemEditForm('settings', 'all'),
                'icon' => 'glyphicon glyphicon-cog'
            ]);
        }
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
        $this->app->alias(CmsPagesTable::class, 'cms.section.text_elements.table');
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
        $this->app->singleton('cms.section.text_elements.scaffold', function () {
            return new CmsTextElementsScaffoldConfig($this->app->make('cms.section.text_elements.table'), 'text_elements');
        });
//        $this->app->singleton('cms.section.shop_categories.scaffold', function () {
//            return new CmsShopCategoriesScaffoldConfig($this->app->make('cms.section.shop_categories.table'), 'shop_categories');
//        });
//        $this->app->singleton('cms.section.shop_items.scaffold', function () {
//            return new CmsShopItemsScaffoldConfig($this->app->make('cms.section.shop_items.table'), 'shop_items');
//        });
    }

}

<?php

namespace PeskyCMF\Console\Commands;

class CmfAddSectionCommand extends CmfInstallCommand {

    protected $description = 'Install new PeskyCMF section along with existing sections';
    protected $signature = 'cmf:add-section 
        {app_subfolder} 
        {url_prefix} 
        {database_classes_app_subfolder=Db}';

    /**
     * Used in CmsInstall
     */
    protected function extender() {

    }

    protected function outro() {

    }

    protected function suggestions($peskyOrmConfigFilePath) {

    }

    protected function copyBaseDbClasses($viewsPath, array $dataForViews) {

    }

    protected function cleanLaravelOrmClassesAndMigrations() {

    }

    protected function createAppSettingsClass() {

    }

    protected function createPeskyOrmConfigFile() {
        return config_path('peskyorm.php');
    }

    protected function createPeskyCmfConfigFile($appSubfolder, array $dataForViews) {

    }

    protected function createMigrations() {

    }
}
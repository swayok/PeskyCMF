<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

class CmfAddSectionCommand extends CmfInstallCommand
{
    protected $description = 'Install new PeskyCMF section along with existing sections';

    protected $signature = 'cmf:add-section
        {app_subfolder}
        {url_prefix}
        {database_classes_app_subfolder=Db}';

    protected function extender(): void
    {
    }

    protected function outro(): void
    {
    }

    protected function suggestions(string $peskyOrmConfigFilePath): void
    {
    }

    protected function copyBaseDbClasses(string $viewsPath, array $dataForViews): void
    {
    }

    protected function cleanLaravelOrmClassesAndMigrations(): void
    {
    }

    protected function createAppSettingsClass(): void
    {
    }

    protected function createPeskyOrmConfigFile(): string
    {
        return config_path('peskyorm.php');
    }

    protected function createPeskyCmfConfigFile(string $appSubfolder, array $dataForViews): void
    {
    }

    protected function createMigrations(): void
    {
    }
}

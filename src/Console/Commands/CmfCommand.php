<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Config\Repository as ConfigsRepostory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\View;
use PeskyCMF\CmfManager;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\Utils\StringUtils;
use Swayok\Utils\File;

abstract class CmfCommand extends Command
{
    protected ?CmfConfig $cmfConfig = null;

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function getCmfConfig(): CmfConfig
    {
        if (!$this->cmfConfig) {
            $class = $this->option('cmf-config-class');
            if ($class) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(
                        "Class $class provided through option --cmf-config-class does not exist"
                    );
                }
                if (
                    !$class instanceof CmfConfig
                    && !is_subclass_of($class, CmfConfig::class)
                ) {
                    throw new \InvalidArgumentException(
                        "Class $class provided through option --cmf-config-class"
                        . ' must be a CmfConfig class or extend it'
                    );
                }
                /** @var CmfConfig $class */
                $this->cmfConfig = new $class();
            } else {
                $this->cmfConfig = $this->getCmfManager()->getCmfConfigForSection(
                    $this->argument('cmf-section') ?: null
                );
            }
            $this->cmfConfig->initSection($this->app);
        }
        return $this->cmfConfig;
    }

    protected function getConfigsRepository(): ConfigsRepostory
    {
        return $this->app->make('config');
    }

    protected function getCmfManager(): CmfManager
    {
        return $this->app->make(CmfManager::class);
    }

    protected function addMigrationForTable(
        string $tableName,
        string $migrationsPath,
        ?int $timestamp = null,
        string $prefix = 'Cmf',
        string $namespace = 'PeskyCMF'
    ): void {
        $filePath = $migrationsPath
            . date('Y_m_d_His', $timestamp ?: time())
            . "_create_{$tableName}_table.php";
        if (File::exist($filePath)) {
            $this->error('- migration ' . $filePath . ' already exist. skipped.');
            return;
        }
        $groupName = StringUtils::toPascalCase($tableName);
        $extendsClass = $prefix . $groupName . 'Migration';
        $dataForViews = [
            'namespace' => $namespace,
            'groupName' => $groupName,
            'extendsClass' => $extendsClass,
        ];
        File::save(
            $filePath,
            $this->renderStubView('create_table_migration', $dataForViews),
            0664,
            0755
        );
        $this->line('Added migration ' . $filePath);
    }

    protected function getStubsPath(): string
    {
        return __DIR__ . '/../../resources/views/stubs/';
    }

    protected function getStubFilePath(string $fileNameWithExtension): string
    {
        return $this->getStubsPath() . $fileNameWithExtension;
    }

    protected function renderStubView(
        string $bladeFileName,
        array $dataForView
    ): string {
        return View::file($this->getStubFilePath($bladeFileName . '.blade.php'), $dataForView)
            ->render();
    }

    protected function getStubFileContents(string $fileName): string
    {
        return File::contents($this->getStubFilePath($fileName . '.stub'));
    }

    /**
     * Keys in $keyValuePairs must look like ':var'.
     * ':' at the start of key is required.
     */
    protected function renderStubFile(string $fileName, array $keyValuePairs): string
    {
        return str_replace(
            array_keys($keyValuePairs),
            array_values($keyValuePairs),
            $this->getStubFileContents($fileName)
        );
    }
}

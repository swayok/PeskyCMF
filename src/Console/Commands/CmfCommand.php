<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\CmfManager;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\File;
use Swayok\Utils\StringUtils;

abstract class CmfCommand extends Command
{
    
    protected ?CmfConfig $cmfConfig = null;
    
    protected function getCmfConfig(): CmfConfig
    {
        if (!$this->cmfConfig) {
            $class = $this->option('cmf-config-class');
            if ($class) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(
                        'Class ' . $class . ' provided through option --cmf-config-class does not exist'
                    );
                }
                if (!is_subclass_of($class, CmfConfig::class)) {
                    throw new \InvalidArgumentException(
                        'Class ' . $class . ' provided through option --cmf-config-class must extend CmfConfig class'
                    );
                }
                /** @var CmfConfig $class */
                $this->cmfConfig = new $class();
            } else {
                /** @var CmfManager $peskyCmfManager */
                $peskyCmfManager = app(CmfManager::class);
                $this->cmfConfig = $peskyCmfManager->getCmfConfigForSection($this->argument('cmf-section') ?: null);
            }
            $this->cmfConfig->initSection(app());
        }
        
        return $this->cmfConfig;
    }
    
    protected function addMigrationForTable(
        string $tableName,
        string $migrationsPath,
        ?int $timestamp = null,
        string $prefix = 'Cmf',
        string $namespace = 'PeskyCMF'
    ): void {
        $filePath = $migrationsPath . date('Y_m_d_His', $timestamp ?: time()) . "_create_{$tableName}_table.php";
        if (File::exist($filePath)) {
            $this->error('- migration ' . $filePath . ' already exist. skipped.');
            return;
        }
        $groupName = StringUtils::classify($tableName);
        $className = 'Create' . $groupName . 'Table';
        if (class_exists($className)) {
            $this->error('- class ' . $className . ' already exists. Probably migration already exists. skipped.');
            return;
        }
        $extendsClass = $prefix . $groupName . 'Migration';
        $fileContents = <<<FILE
<?php

use {$namespace}\\Db\\{$groupName}\\{$extendsClass};

class {$className} extends {$extendsClass} {

}

FILE;
        File::save($filePath, $fileContents, 0664, 0755);
        $this->line('Added migration ' . $filePath);
    }
}
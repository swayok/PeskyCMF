<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use Swayok\Utils\File;
use Swayok\Utils\StringUtils;

abstract class CmfCommand extends Command {

    protected function addMigrationForTable($tableName, $migrationsPath, $timestamp = null, $prefix = 'Cmf', $namespace = 'PeskyCMF') {
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
        $this->line('Added migration ' . $migrationsPath);
    }
}
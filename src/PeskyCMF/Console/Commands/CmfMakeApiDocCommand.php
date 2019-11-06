<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\ApiDocs\CmfApiDocumentation;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\PeskyCmfManager;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeApiDocCommand extends Command {

    protected $description = 'Create class that extends CmfApiDocumentation class';

    protected $signature = 'cmf:make-api-doc
        {class_name}
        {docs_group}
        {cmf-section? : cmf section name (key) that exists in config(\'peskycmf.cmf_configs\') and accessiblr by PeskyCmfManager}
        {--folder= : folder path relative to app_path(); default = CmfConfig::getPrimary()->api_documentation_classes_folder()}
        {--cmf-config-class= : full class name to a class that extends CmfConfig}
        ';

    /** @var CmfConfig */
    protected $cmfConfig;

    public function fire() {
        // compatibility with Laravel <= 5.4
        $this->handle();
    }

    /**
     * @return CmfConfig
     */
    protected function getCmfConfig() {
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
                $this->cmfConfig = $class::getInstance();
            } else {
                $sectionName = $this->argument('cmf-section');
                if (!empty($sectionName)) {
                    /** @var PeskyCmfManager $peskyCmfManager */
                    $peskyCmfManager = app(PeskyCmfManager::class);
                    $this->cmfConfig = $peskyCmfManager->getCmfConfigForSection($sectionName);
                } else {
                    $this->cmfConfig = CmfConfig::getDefault();
                    if (get_class($this->cmfConfig) === CmfConfig::class) {
                        throw new \InvalidArgumentException(
                            'Child class for CmfConfig was not found. You need to provide it through --cmf-config-class option '
                        );
                    }
                }
            }
            $this->cmfConfig->initSection(app());
        }

        return $this->cmfConfig;
    }

    public function handle() {
        $classSuffix = $this->getCmfConfig()->getApiDocumentationModule()->getClassNameSuffix();
        $className = preg_replace('%' . preg_quote($classSuffix, '%') . '$%', '', $this->argument('class_name')) . $classSuffix;
        $folder = $this->option('folder');
        if (trim($folder) === '') {
            $folder = $this->getCmfConfig()->getApiDocumentationModule()->getClassesFolderPath();
        } else {
            $folder = app_path($folder);
        }
        $folder .= DIRECTORY_SEPARATOR . $this->argument('docs_group');
        $namespace = '\\App' . rtrim(str_ireplace([app_path(), '/'], ['', '\\'], $folder), '\\ ');
        $folder = Folder::load($folder, true, 0755);
        $filePath = $folder->pwd() . DIRECTORY_SEPARATOR . $className . '.php';
        if (File::exist($filePath) && !$this->confirm("File $filePath already exists. Overwrite?")) {
            $this->line('Cancelled');
            return;
        }
        $this->makeClass($className, $namespace, $filePath);
    }

    protected function makeClass($className, $namespace, $filePath) {
        $this->line('Writing class ' .  $namespace . '\\' . $className . ' to file ' . $filePath);
        $namespace = ltrim($namespace, '\\');
        $baseClass = CmfApiDocumentation::class;
        $baseClassName = class_basename($baseClass);
        $classSuffix = $this->getCmfConfig()->getApiDocumentationModule()->getClassNameSuffix();
        $translationSubGroup = snake_case(
            preg_replace(
                '%(ApiDocs?|(Method)?(Doc(umentation)?)?|' . preg_quote($classSuffix, '%') . '$)%',
                '',
                $className
            )
        );
        $docsGroup = $this->argument('docs_group');
        $translationGroup = empty($docsGroup) ? 'method' : snake_case($docsGroup);
        $translationGroup .= '.' . $translationSubGroup;
        $fileContents = <<<CLASS
<?php

namespace {$namespace};

use {$baseClass};

class {$className} extends {$baseClassName} {

    protected \$translationsBasePath = '{$translationGroup}';

    //protected \$title = '{{$translationGroup}.title}';
    //protected \$description = '{{$translationGroup}.description}';

}
CLASS;
        File::save($filePath, $fileContents, 0644, 0755);
        $this->line("File $filePath created");
        $this->line('Add next translations to you dictionaries:');
        $translations = [];
        array_set($translations, $translationGroup . '.title', '');
        array_set($translations, $translationGroup . '.description', '');
        $this->line($this->arrayToString($translations));
    }

    protected function arrayToString(array $array, $depth = 0) {
        $ret = "[\n";
        foreach ($array as $key => $value) {
            $ret .= str_pad('', $depth * 4 + 4, ' ');
            if (!is_int($key)) {
                $ret .= "'$key' => ";
            }
            if (is_array($value)) {
                $ret .= $this->arrayToString($value, $depth + 1);
            } else {
                $ret .= "'$value',\n";
            }
        }
        return $ret . str_pad('', $depth * 4, ' ') . "],\n";
    }

}

<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PeskyCMF\ApiDocs\CmfApiDocumentation;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeApiDocCommand extends CmfCommand
{
    
    protected $description = 'Create class that extends CmfApiDocumentation class';
    
    protected $signature = 'cmf:make-api-doc
        {class_name}
        {docs_group}
        {cmf-section? : cmf section name (key) that exists in config(\'peskycmf.cmf_configs\') and accessiblr by PeskyCmfManager}
        {--folder= : folder path relative to app_path(); default = CmfConfig::getPrimary()->api_documentation_classes_folder()}
        {--cmf-config-class= : full class name to a class that extends CmfConfig}
        ';
    
    public function handle(): int
    {
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
            return 0;
        }
        $this->makeClass($className, $namespace, $filePath);
        return 0;
    }
    
    protected function makeClass(string $className, string $namespace, string $filePath): void
    {
        $this->line('Writing class ' . $namespace . '\\' . $className . ' to file ' . $filePath);
        $namespace = ltrim($namespace, '\\');
        $baseClass = CmfApiDocumentation::class;
        /** @noinspection DuplicatedCode */
        $baseClassName = class_basename($baseClass);
        $classSuffix = $this->getCmfConfig()->getApiDocumentationModule()->getClassNameSuffix();
        $translationSubGroup = Str::snake(
            preg_replace(
                '%(ApiDocs?|(Method)?(Doc(umentation)?)?|' . preg_quote($classSuffix, '%') . '$)%',
                '',
                $className
            )
        );
        $docsGroup = $this->argument('docs_group');
        $translationGroup = empty($docsGroup) ? 'method' : Str::snake($docsGroup);
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
        Arr::set($translations, $translationGroup . '.title', '');
        Arr::set($translations, $translationGroup . '.description', '');
        $this->line($this->arrayToString($translations));
    }
    
    protected function arrayToString(array $array, int $depth = 0): string
    {
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

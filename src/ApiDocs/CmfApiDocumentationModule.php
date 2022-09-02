<?php

declare(strict_types=1);

namespace PeskyCMF\ApiDocs;

use Illuminate\Support\Str;
use PeskyCMF\CmfUrl;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\Folder;

class CmfApiDocumentationModule
{
    
    protected CmfConfig $cmfConfig;
    
    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
    }
    
    /**
     * @return CmfConfig
     */
    public function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }
    
    /**
     * Menu item for api logs page.
     * Note: it is not added automatically to menu items - you need to add it manually to static::menu()
     */
    public function getMenuItem(): array
    {
        return [
            'label' => $this->getCmfConfig()->transCustom('api_docs.menu_title'),
            'icon' => 'glyphicon glyphicon-book',
            'url' => CmfUrl::toPage('api_docs'),
        ];
    }
    
    /**
     * Provides sections with list of objects of classes that extend CmfApiMethodDocumentation class to be displayed in api docs section
     * @return array - key - section name, value - array that contains names of classes that extend CmfApiDocumentation class
     */
    public function getDocumentationClassesList(): array
    {
        $classNames = $this->getCmfConfig()->config('api_documentation.classes', []);
        if (empty($classNames)) {
            $classNames = $this->loadClassesFromFileSystem();
        }
        return $classNames;
    }
    
    /**
     * Load api dosc sections from files in static::api_methods_documentation_classes_folder() and its subfolders.
     * Should be used only when static::config('api_docs_class_names') not provided.
     * Subfolders names used as API sections.
     * Collects only classes that extend next classes:
     *  - ApiDocumentation
     *  - V1ApiMethodDocumentation
     *  - static::api_method_documentation_base_class()
     * @return array
     */
    protected function loadClassesFromFileSystem(): array
    {
        $rootFolderPath = $this->getClassesFolderPath();
        $folder = Folder::load($rootFolderPath);
        if (!$folder->exists()) {
            return [];
        }
        $ret = [];
        $classFinder = function ($folderPath, array $files) {
            $classes = [];
            foreach ($files as $fileName) {
                if (preg_match('%\.php$%i', $fileName)) {
                    $file = fopen($folderPath . DIRECTORY_SEPARATOR . $fileName, 'rb');
                    $buffer = fread($file, 512);
                    $parentClassName = class_basename($this->getMethodBaseClass()) . '|[a-zA-Z0-9_-]+V1ApiMethodDocumentation|CmfApiDocumentation';
                    if (preg_match('%^\s*class\s+(\w+)\s+extends\s+(' . $parentClassName . ')%im', $buffer, $classMatches)) {
                        $class = $classMatches[1];
                        if (preg_match("%[^w]namespace\s+([\w\\\]+).*?class\s+{$class}\s+%is", $buffer, $nsMatches)) {
                            $namespace = $nsMatches[1];
                            $classes[] = '\\' . $namespace . '\\' . $class;
                        }
                    }
                }
            }
            // sort classes
            usort($classes, function ($class1, $class2) {
                /** @var CmfApiDocumentation $class1 */
                /** @var CmfApiDocumentation $class2 */
                $pos1 = $class1::getPosition();
                $pos2 = $class2::getPosition();
                if ($pos1 === null) {
                    return $pos2 === null ? 0 : 1;
                } elseif ($pos2 === null) {
                    return -1;
                } elseif ($pos1 === $pos2) {
                    return 0;
                } else {
                    return $pos1 > $pos2;
                }
            });
            return $classes;
        };
        [$subFolders, $files] = $folder->read();
        $withoutSection = $classFinder($folder->pwd(), $files);
        if (!empty($withoutSection)) {
            $ret[(string)$this->getCmfConfig()->transCustom('api_docs.section.no_section')] = $withoutSection;
        }
        foreach ($subFolders as $subFolderName) {
            if ($subFolderName[0] === '.') {
                // ignore folders starting with '.' - nothing useful there
                continue;
            }
            $subFolder = Folder::load($folder->pwd() . DIRECTORY_SEPARATOR . $subFolderName);
            $files = $subFolder->find('.*\.php');
            $classes = $classFinder($subFolder->pwd(), $files);
            if (!empty($classes)) {
                $ret[(string)$this->getCmfConfig()->transApiDoc('section.' . Str::snake($subFolderName))] = $classes;
            }
        }
        return $ret;
    }
    
    public function getClassesFolderPath(): string
    {
        return $this->getCmfConfig()->config('api_documentation.folder') ?: app_path('Api/Docs');
    }
    
    public function getMethodBaseClass(): string
    {
        return $this->getCmfConfig()->config('api_documentation.base_class_for_method') ?: CmfApiMethodDocumentation::class;
    }
    
    public function getClassNameSuffix(): string
    {
        return $this->getCmfConfig()->config('api_documentation.class_suffix', 'Documentation');
    }
}

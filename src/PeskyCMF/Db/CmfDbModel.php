<?php

namespace PeskyCMF\Db;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\DbModel;
use Swayok\Utils\StringUtils;

abstract class CmfDbModel extends DbModel {

    /** @var null|ScaffoldSectionConfig */
    protected $scaffoldConfig = null;

    static public function getScaffoldConfigClassSuffix() {
        /** @var CmfDbModel $calledClass */
        $calledClass = get_called_class();
        return CmfConfig::getInstance()->scaffold_config_class_suffix();
    }

    static public function getScaffoldConfigNameByTableName($tableName) {
        $calledClass = get_called_class();
        $objectName = call_user_func([$calledClass, 'getObjectNameByTableName'], $tableName);
        return $objectName . call_user_func([$calledClass, 'getScaffoldConfigClassSuffix']);
    }

    static public function getModelsNamespace() {
        return call_user_func([get_called_class(), 'getRootNamespace']) . '\\';
    }

    static public function getObjectsNamespace() {
        return call_user_func([get_called_class(), 'getRootNamespace']) . '\\';
    }

    protected function getConfigsNamespace() {
        return $this->getNamespace();
    }

    /**
     * MUST BE OVERRIDEN
     * @return string
     */
    static public function getRootNamespace() {
        return __NAMESPACE__;
    }

    static public function getFullModelClassNameByName($modelNameOrObjectName) {
        /** @var CmfDbModel $calledClass */
        $calledClass = get_called_class();
        $subfolder = preg_replace('%' . $calledClass::$modelClassSuffix . '$%i', '', $modelNameOrObjectName);
        $modelName = $subfolder . $calledClass::$modelClassSuffix;
        $rootNs = call_user_func([$calledClass, 'getRootNamespace']);
        return $rootNs . '\\' . $subfolder . '\\' . $modelName;
    }

    static public function getFullModelClassByTableName($tableName) {
        $rootNs = call_user_func([get_called_class(), 'getRootNamespace']);
        $subfolder = StringUtils::modelize($tableName);
        $modelClassName = call_user_func([get_called_class() ,'getModelNameByTableName'], $tableName);
        return  $rootNs . '\\' . $subfolder . '\\' .$modelClassName;
    }

    static public function getFullDbObjectClass($dbObjectNameOrTableName) {
        /** @var CmfDbModel $calledClass */
        $calledClass = get_called_class();
        $modelClassName = call_user_func(
            [$calledClass, 'getFullModelClassNameByName'],
            StringUtils::modelize($dbObjectNameOrTableName)
        );
        return preg_replace('%' . $calledClass::$modelClassSuffix . '$%', '', $modelClassName);
    }

    /**
     * @return ScaffoldSectionConfig
     */
    public function getScaffoldConfig() {
        /** @var CmfDbModel $calledClass */
        $calledClass = get_called_class();
        if (empty($this->scaffoldConfig)) {
            $className = $this->getNamespace() . $this->getAlias() . $this->getScaffoldConfigClassSuffix();
            $this->scaffoldConfig = new $className($this);
        }
        return $this->scaffoldConfig;
    }

}
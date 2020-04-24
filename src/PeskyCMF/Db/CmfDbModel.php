<?php

namespace PeskyCMF\Db;

use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\DbModel;
use Swayok\Utils\StringUtils;

abstract class CmfDbModel extends DbModel {

    /** @var null|ScaffoldSectionConfig */
    protected $scaffoldConfig = null;
    /** @var array */
    static private $timeZonesList = null;
    /** @var array */
    static private $timeZonesOptions = null;

    static public function getTimezonesList($asOptions = false) {
        if (self::$timeZonesList === null) {
            $ds = DbConnectionsManager::getConnection('default');
            $query = $ds->quoteDbExpr(DbExpr::create('SELECT * from `pg_timezone_names` ORDER BY `utc_offset` ASC'));
            self::$timeZonesList = Utils::getDataFromStatement($ds->query($query), Utils::FETCH_ALL);
        }
        if ($asOptions) {
            if (self::$timeZonesOptions === null) {
                self::$timeZonesOptions = [];
                foreach (self::$timeZonesList as $tzInfo) {
                    $offset = preg_replace('%:\d\d$%', '', $tzInfo['utc_offset']);
                    $offsetPrefix = $offset[0] === '-' ? '' : '+';
                    self::$timeZonesOptions[$tzInfo['name']] = "({$offsetPrefix}{$offset}) {$tzInfo['name']}";
                }
            }
            return self::$timeZonesOptions;
        } else {
            return self::$timeZonesList;
        }
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
     * @deprecated
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
        /** @var DbModel $class */
        $class = static::class;
        $rootNs = $class::getRootNamespace();
        $subfolder = StringUtils::modelize($tableName);
        
        $modelClassName = $class::getModelNameByTableName($tableName);
        return  $rootNs . '\\' . $subfolder . '\\' .$modelClassName;
    }

    static public function getFullDbObjectClass($dbObjectNameOrTableName) {
        /** @var CmfDbModel $calledClass */
        $calledClass = static::class;
        $modelClassName = $calledClass::getFullModelClassNameByName(StringUtils::modelize($dbObjectNameOrTableName));
        return preg_replace('%' . $calledClass::$modelClassSuffix . '$%', '', $modelClassName);
    }

}
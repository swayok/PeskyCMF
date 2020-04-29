<?php

namespace PeskyCMF\Db;

use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\StringUtils;

abstract class CmfTable extends Table {

    /** @var array */
    static private $timeZonesList;
    /** @var array */
    static private $timeZonesOptions;
    
    /** @var CmfTable[] */
    static protected $loadedModels = [];    //< Model objects
    
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';
    
    /** @var TableStructure */
    protected $tableConfig;
    /** @var string|null */
    protected $defaultOrderByColumn;
    /** @var string */
    protected $orderDirectionForDefaultOrderByColumn = self::ORDER_ASCENDING;
    
    static protected $tableConfigClassSuffix = 'TableStructure';
    static protected $modelClassSuffix = 'Table';

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
    
    /**
     * @deprecated
     * @return string
     */
    static public function getRootNamespace() {
        return config('peskyorm.classes_namespace') . '\\';
    }
    
    /**
     * @deprecated
     * @param $modelNameOrObjectName
     * @return string
     */
    static public function getFullModelClassNameByName($modelNameOrObjectName) {
        $subfolder = preg_replace('%' . static::$modelClassSuffix . '$%i', '', $modelNameOrObjectName);
        $modelName = $subfolder . static::$modelClassSuffix;
        $rootNs = static::getRootNamespace();
        return $rootNs . $subfolder . '\\' . $modelName;
    }
    
    /**
     * @deprecated
     * @param $tableName
     * @return string
     */
    static public function getFullModelClassByTableName($tableName) {
        $rootNs = static::getRootNamespace();
        $subfolder = StringUtils::modelize($tableName);
        $modelClassName = StringUtils::modelize($tableName) . static::$modelClassSuffix;
        return  $rootNs . $subfolder . '\\' .$modelClassName;
    }
    
    /**
     * @deprecated
     * @param $dbObjectNameOrTableName
     * @return string|string[]|null
     */
    static public function getFullDbObjectClass($dbObjectNameOrTableName) {
        /** @var CmfTable $calledClass */
        $calledClass = static::class;
        $modelClassName = $calledClass::getFullModelClassNameByName(StringUtils::modelize($dbObjectNameOrTableName));
        return preg_replace('%' . $calledClass::$modelClassSuffix . '$%', '', $modelClassName);
    }
    
    public function getTableStructure() {
        if (!$this->tableConfig) {
            if (!preg_match('%^(.*?\\\?)([a-zA-Z0-9]+)' . static::$modelClassSuffix . '$%is', static::class, $classNameParts)) {
                $className = static::class;
                throw new \UnexpectedValueException("Invalid Model class name [{$className}]. Required name is like NameSpace\\SomeModel.");
            }
            /** @var TableStructure $tableConfigClass */
            $tableConfigClass = $classNameParts[1] . $classNameParts[2] . static::$tableConfigClassSuffix;
            if (!class_exists($tableConfigClass)) {
                throw new \UnexpectedValueException("Db table config class [{$tableConfigClass}] not found");
            }
            $this->tableConfig = $tableConfigClass::getInstance();
        }
        return $this->tableConfig;
    }
    
    /**
     * @return null|string
     */
    public function getDefaultOrderByColumn() {
        return $this->defaultOrderByColumn;
    }

    /**
     * @return string
     */
    public function getOrderDirectionForDefaultOrderByColumn() {
        return $this->orderDirectionForDefaultOrderByColumn;
    }

    /**
     * @param string $modelNameOrObjectName - base class name (UserToken or UserTokenModel or User)
     * @return CmfTable
     *@deprecated
     * Load and return requested Model
     */
    static public function getModel($modelNameOrObjectName) {
        /** @var CmfTable $calledClass */
        $calledClass = static::class;
        $modelClass = $calledClass::getFullModelClassNameByName($modelNameOrObjectName);
        return $calledClass::getModelByClassName($modelClass);
    }

    /**
     * @deprecated
     * @param string $modelClass
     * @return $this
     */
    static public function getModelByClassName($modelClass) {
        if (empty(self::$loadedModels[$modelClass])) {
            if (!class_exists($modelClass)) {
                throw new \InvalidArgumentException("Class $modelClass was not found");
            }
            self::$loadedModels[$modelClass] = new $modelClass();
        }
        return self::$loadedModels[$modelClass];
    }

    /**
     * @param string $tableName
     * @return $this
     */
    static public function getModelByTableName($tableName) {
        $modelClass = static::getFullModelClassByTableName($tableName);
        return static::getModelByClassName($modelClass);
    }

    /**
     * @param string $dbObjectNameOrTableName - class name or table name (UserToken or user_tokens)
     * @param null|array|string|int $data - null: do nothing | int and string: is primary key (read db) | array: object data
     * @param bool $filter - used only when $data not empty and is array
     *      true: filters $data that does not belong to this object
     *      false: $data that does not belong to this object will trigger exceptions
     * @param bool $isDbValues
     * @return Record|CmfRecord
     *@deprecated
     * Load DbObject class and create new instance of it
     */
    static private function createDbObject($dbObjectNameOrTableName, $data = null, $filter = false, $isDbValues = false) {
        $dbObjectClass = static::getFullDbObjectClass($dbObjectNameOrTableName);
        if (!class_exists($dbObjectClass)) {
            throw new \InvalidArgumentException("Class $dbObjectClass was not found");
        }
        $model = static::getModel(StringUtils::modelize($dbObjectNameOrTableName));
        return new $dbObjectClass($data, $filter, $isDbValues, $model);
    }
    
    /**
     * @return Record|CmfRecord
     */
    public function newRecord() {
        $objectName = preg_replace(
            [
                '%^.*[\\\]%is',
                '%' . static::$modelClassSuffix . '$%',
                '%^' . preg_quote(addslashes(static::getRootNamespace()), '%') . '/%'
            ],
            [
                '',
                '',
                static::getRootNamespace() . '/'
            ],
            static::class
        );
        return static::createDbObject($objectName);
    }

    /**
     * Build valid 'JOIN' settings from 'CONTAIN' table aliases
     * @param array $columnsToSelect
     * @param array $conditionsAndOptions
     * @param string|null $aliasForSubContains
     * @return array = [$columnsToSelect, $conditionsAndOptions]
     */
    static public function normalizeConditionsAndOptions(array $columnsToSelect, array $conditionsAndOptions, $aliasForSubContains = null): array {
        if (!empty($conditionsAndOptions['CONTAIN'])) {
            if (!is_array($conditionsAndOptions['CONTAIN'])) {
                $conditionsAndOptions['CONTAIN'] = [$conditionsAndOptions['CONTAIN']];
            }
            if (empty($conditionsAndOptions['JOIN']) || !is_array($conditionsAndOptions['JOIN'])) {
                $conditionsAndOptions['JOIN'] = [];
            }

            foreach ($conditionsAndOptions['CONTAIN'] as $alias => $columnsToSelectForRelation) {
                if (is_int($alias)) {
                    $alias = $columnsToSelectForRelation;
                    $columnsToSelectForRelation = ['*'];
                } else {
                    $columnsToSelectForRelation = [];
                }
                $relationConfig = static::getStructure()->getRelation($alias);
                if ($relationConfig->getType() === Relation::HAS_MANY) {
                    throw new \UnexpectedValueException("Queries with one-to-many joins are not allowed via 'CONTAIN' key");
                } else {
                    $model = $relationConfig->getForeignTable();
                    $joinType = $relationConfig->getJoinType();
                    if (is_array($columnsToSelectForRelation)) {
                        if (isset($columnsToSelectForRelation['TYPE'])) {
                            $joinType = $columnsToSelectForRelation['TYPE'];
                        }
                        unset($columnsToSelectForRelation['TYPE']);
                        if (isset($columnsToSelectForRelation['CONDITIONS'])) {
                            throw new \UnexpectedValueException('CONDITIONS key is not supported in CONTAIN');
                        }
                        unset($columnsToSelectForRelation['CONDITIONS']);
                        if (!empty($columnsToSelectForRelation['CONTAIN'])) {
                            $subContains = $columnsToSelectForRelation['CONTAIN'];
                        }
                        unset($columnsToSelectForRelation['CONTAIN']);
                        if (empty($columnsToSelectForRelation)) {
                            $columnsToSelectForRelation = [];
                        }
                    }

                    $conditionsAndOptions['JOIN'][$alias] = $relationConfig->toOrmJoinConfig(
                        static::getInstance(),
                        $aliasForSubContains,
                        $alias,
                        $joinType
                    )->setForeignColumnsToSelect($columnsToSelectForRelation);

                    if (!empty($subContains)) {
                        [, $subJoins] = $model::normalizeConditionsAndOptions(
                            [],
                            ['CONTAIN' => $subContains],
                            $alias
                        );
                        $conditionsAndOptions['JOIN'] = array_merge($conditionsAndOptions['JOIN'], $subJoins['JOIN']);
                    }
                }
            }
            if (empty($conditionsAndOptions['JOIN'])) {
                unset($conditionsAndOptions['JOIN']);
            }
        }
        unset($conditionsAndOptions['CONTAIN']);
        if (!empty($conditionsAndOptions['ORDER'])) {
            if (is_string($conditionsAndOptions['ORDER'])) {
                $conditionsAndOptions['ORDER'] = explode(',', $conditionsAndOptions['ORDER']);
            }
            $orderBy = [];
            $orderRegexp = '%^([^\s]+)\s+((?:asc|desc)(?:\s+nulls\s+(?:first|last))?)$%i';
            foreach ($conditionsAndOptions['ORDER'] as $key => $value) {
                if (!is_int($key) || $value instanceof DbExpr) {
                    $orderBy[$key] = $value;
                } else if (is_string($value) && preg_match($orderRegexp, trim($value), $matches)) {
                    $orderBy[$matches[1]] = $matches[2];
                } else {
                    throw new \UnexpectedValueException('ORDER key is invalid: ' . json_encode(['key' => $key, 'value' => $value], JSON_UNESCAPED_UNICODE));
                }
            }
            if (!empty($orderBy)) {
                $conditionsAndOptions['ORDER'] = $orderBy;
            }
        }
        return [$columnsToSelect, $conditionsAndOptions];
    }

    /* Queries */
    
    static public function selectAsArray($columns = '*', array $conditions = [], ?\Closure $configurator = null): array {
        [$columns, $conditions] = static::normalizeConditionsAndOptions((array)$columns, $conditions);
        return parent::select($columns, $conditions, $configurator)->toArrays();
    }
    
    static public function select($columns = '*', array $conditions = [], ?\Closure $configurator = null) {
        [$columns, $conditionsAndOptions] = static::normalizeConditionsAndOptions((array)$columns, $conditions);
        return parent::select($columns, $conditionsAndOptions, $configurator);
    }

    static public function selectColumn($column, array $conditions = [], ?\Closure $configurator = null): array {
        [, $conditions] = static::normalizeConditionsAndOptions([], $conditions);
        return parent::selectColumn($column, $conditions);
    }

    static public function selectAssoc(string $keysColumn, string $valuesColumn, array $conditions = [], ?\Closure $configurator = null): array {
        [, $conditions] = static::normalizeConditionsAndOptions([], $conditions);
        return parent::selectAssoc($keysColumn, $valuesColumn, $conditions);
    }
    
    static public function count(array $conditions = [], ?\Closure $configurator = null, bool $removeNotInnerJoins = false): int {
        [, $conditions] = static::normalizeConditionsAndOptions([], $conditions);
        return parent::count($conditions, $configurator, $removeNotInnerJoins);
    }
    
    static public function selectOne($columns, array $conditions, ?\Closure $configurator = null): array {
        [$columns, $conditions] = static::normalizeConditionsAndOptions((array)$columns, $conditions);
        return parent::selectOne($columns, $conditions, $configurator);
    }
    
    static public function selectOneAsDbRecord($columns, array $conditions, ?\Closure $configurator = null): RecordInterface {
        [$columns, $conditions] = static::normalizeConditionsAndOptions((array)$columns, $conditions);
        return parent::selectOneAsDbRecord($columns, $conditions, $configurator);
    }

    static public function selectValue(DbExpr $expression, array $conditions = [], ?\Closure $configurator = null): ?string {
        [, $conditions] = static::normalizeConditionsAndOptions([], $conditions);
        return parent::selectValue($expression, $conditions, $configurator);
    }
    
}
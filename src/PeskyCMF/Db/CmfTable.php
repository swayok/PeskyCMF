<?php

namespace PeskyCMF\Db;

use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\Table;

abstract class CmfTable extends Table {

    /** @var array */
    static private $timeZonesList;
    /** @var array */
    static private $timeZonesOptions;
    
    public const ORDER_ASCENDING = 'ASC';
    public const ORDER_DESCENDING = 'DESC';
    
    /** @var string|null */
    protected $defaultOrderByColumn;
    /** @var string */
    protected $orderDirectionForDefaultOrderByColumn = self::ORDER_ASCENDING;
    
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
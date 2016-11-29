<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\RecordsArray;
use PeskyORM\ORM\RecordsSet;
use Swayok\Utils\Set;

trait CacheForDbSelects {

    /**
     * @var bool|null
     */
    static protected $_cachingIsPossible;

    /**
     * @var null|int
     */
    private $_cacheTimeoutForNextSelect;

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select one" queries
     * @return int
     */
    abstract public function getDefaultCacheDurationForSelectOneInMinutes();

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select many" queries
     * @return int
     */
    abstract public function getDefaultCacheDurationForSelectManyInMinutes();

    /**
     * Override to change default value
     * @return boolean
     */
    abstract public function canCleanRelationsCache();

    /**
     * Override to change default value
     * @return boolean
     */
    abstract public function canAutoCacheSelectOneQueries();

    /**
     * Allow/disallow cache for DbModel->expression().
     * Cache timeout provided by $this->getDefaultCacheDurationForSelectOneInMinutes()
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheExpressionQueries() {
        return $this->canAutoCacheSelectOneQueries();
    }

    /**
     * Override to change default value
     * @return boolean
     */
    abstract public function canAutoCacheSelectManyQueries();

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    abstract public function getAutoCacheTimeoutForSelectOneInMinutes();

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    abstract public function getAutoCacheTimeoutForSelectManyInMinutes();

    /**
     * Detect if caching is possible
     * @return bool
     */
    abstract protected function cachingIsPossible();

    /**
     * Cache key builder
     * Override if you wish to change algorythm
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     * @return string
     * @throws \InvalidArgumentException
     */
    static public function buildCacheKey($columns = '*', array $conditionsAndOptions = []) {
        foreach ($conditionsAndOptions as &$value) {
            if ($value instanceof DbExpr) {
                $value = $value->get();
            } else if (is_object($value)) {
                throw new \InvalidArgumentException(
                    '$conditionsAndOptions argument may contain only strings and objects of class \PeskyORM\Core\DbExpr.'
                    . ' Object of class ' . get_class($value) . ' detected'
                );
            }
        }
        unset($value);
        if (is_array($columns)) {
            foreach ($columns as &$value) {
                if ($value instanceof DbExpr) {
                    $value = $value->get();
                } else if (is_object($value)) {
                    throw new \InvalidArgumentException(
                        '$columns argument may contain only strings and objects of class \PeskyORM\Core\DbExpr.'
                        . ' Object of class ' . get_class($value) . ' detected'
                    );
                }
            }
            unset($value);
        } else if ($columns instanceof DbExpr) {
            $columns = $columns->get();
        }
        return hash('sha256', json_encode(array($columns, $conditionsAndOptions)));
    }

/** ============ Next methods does not contain any setting or normally overridable content ============ */

    /**
     * Set custom cache timeout for next query in this model
     * @param int|\DateTime|bool $minutes
     * @return $this
     */
    public function withCacheTimeout($minutes) {
        /** @var CmfDbTable|CacheForDbSelects $this */
        $this->_cacheTimeoutForNextSelect = $minutes;
        return $this;
    }

    /**
     * Removes custom cache timeout for next query
     * @return $this
     */
    public function cleanCacheTimeout() {
        $this->_cacheTimeoutForNextSelect = null;
        return $this;
    }

    /**
     * @return int
     */
    private function _getCacheDurationForSelectOneInMinutes() {
        /** @var CmfDbTable|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectOneInMinutes()
            : $this->_cacheTimeoutForNextSelect;
    }

    /**
     * Override this to change default value
     * @return int
     */
    private function _getCacheDurationForSelectManyInMinutes() {
        /** @var CmfDbTable|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectManyInMinutes()
            : $this->_cacheTimeoutForNextSelect;
    }

    /**
     * @param bool|true $cleanRelatedModelsCache - true: clean cache for all related models
     */
    abstract public function cleanModelCache($cleanRelatedModelsCache = null);

    /**
     * Clean cache for all related models
     * @param bool|null $cleanRelatedModelsCache
     */
    abstract public function cleanRelatedModelsCache($cleanRelatedModelsCache = true);

    /**
     * @param bool $cleanRelatedModelsCache
     */
    abstract public function cleanSelectManyCache($cleanRelatedModelsCache = null);

    /**
     * @param bool $cleanRelatedModelsCache
     */
    abstract public function cleanSelectOneCache($cleanRelatedModelsCache = null);

    /**
     * @param int|string|array|Object $record
     * @param bool $cleanRelatedModelsCache
     */
    abstract public function cleanRecordCache($record, $cleanRelatedModelsCache = null);

    /**
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getModelCachePrefix() {
        /** @var CmfDbTable|CacheForDbSelects $this */
        $structure = $this::getStructure();
        $parts = [
            $structure::getConnectionName(),
            $structure::getSchema(),
            $this::getConnection()->getConnectionConfig()->getDbName(),
            $this::getAlias()
        ];
        return implode('.', $parts) . '.';
    }

    /**
     * Get data from cache or put data from $callback to cache (for external use)
     * @param bool $affectsSingleRecord
     * @param array|string $cacheSettings - array: settings; string: cache key
     *      array: [
     *          'key' => 'string, cache key',
     *          'timeout' => 'int (minutes or unix timestamp), \DateTime, null (infinite)',
     *          'tags' => ['custom', 'cache', 'tags'],
     *          'recache' => 'bool, ignore cached data and replace it with fresh data'
     *      ]
     * @param callable $callback
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getCachedData($affectsSingleRecord, $cacheSettings, callable $callback) {
        $defaultTimeout = $affectsSingleRecord
                ? $this->_getCacheDurationForSelectOneInMinutes()
                : $this->_getCacheDurationForSelectManyInMinutes();
        $resolvedCacheSettings = $this->resolveCacheSettings(
            $cacheSettings,
            $defaultTimeout,
            function () {
                throw new \InvalidArgumentException('$cacheSettings must contain a "key" key (if array) or be the cache key (if string)');
            }
        );
        if (is_array($cacheSettings)) {
            return $this->_getCachedData($affectsSingleRecord, $resolvedCacheSettings, $callback);
        } else {
            return $callback();
        }
    }

    /**
     * Get data from cache or put data from $callback to cache (for internal use)
     * @param bool $affectsSingleRecord
     * @param array $cacheSettings - prepared cache settings. Always contains 'key' key
     * @param callable $callback
     * @return array
     */
    abstract protected function _getCachedData($affectsSingleRecord, array $cacheSettings, callable $callback);

    /**
     * @param bool $affectsSingleRecord
     * @param null|string|array $columns
     * @param null|array $conditionsAndOptions
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function buildDefaultCacheKey($affectsSingleRecord, $columns, $conditionsAndOptions) {
        $mid = $affectsSingleRecord ? 'one.' : 'many.';
        return $this->getModelCachePrefix() . $mid . static::buildCacheKey($columns, $conditionsAndOptions);
    }

    /**
     * @param mixed $cacheSettings
     * @param int|bool|\DateTime $defaultTimeout
     * @param callable $defaultCacheKey
     * @return array|bool - bool: false - when caching is not possible
     */
    protected function resolveCacheSettings($cacheSettings, $defaultTimeout, callable $defaultCacheKey) {
        if (!is_array($cacheSettings)) {
            $tmp = [
                'timeout' => $defaultTimeout,
            ];
            if (is_numeric($cacheSettings) || $cacheSettings instanceof \DateTime) {
                $tmp['timeout'] = $cacheSettings;
            } else if (is_string($cacheSettings)) {
                $tmp['key'] = $cacheSettings;
            }
            $cacheSettings = $tmp;
        }
        if (empty($cacheSettings['key'])) {
            $cacheSettings['key'] = $defaultCacheKey();
        }
        if (empty($cacheSettings['tags'])) {
            $cacheSettings['tags'] = [];
        } else if (!is_array($cacheSettings['tags'])) {
            $cacheSettings['tags'] = [$cacheSettings['tags']];
        }
        // prevent possible cache-related problems in post/put/delete requests
        if (in_array(request()->method(), ['POST', 'PUT', 'DELETE'], true)) {
            $cacheSettings['recache'] = true;
        }
        $this->cleanCacheTimeout();
        return $cacheSettings;
    }

    /**
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     *  special key: 'CACHE'. Accepted values:
     *  - array: cache settings, accepted keys:
     *      'key' => optional
     *              string - cache key
     *              Default: $this->buildDefaultCacheKey(false, $columns, $conditionsAndOptions)
     *      'timeout' => optional
     *              int|DateTime - cache timeout
     *              false|null - cache for forever
     *              Default: $this->getCacheDurationForSelectAllInMinutes()
     *      'tags' => optional
     *              string|array - cache tags.
     *              Default: $this->alias
     *  - true: cache with default settings
     *  - numeric or \DateTime: cache timeout, key and tags are set to default values
     *  - string: cache tag, timeout and tags are set to default
     * @param \Closure $configurator
     * @return RecordsSet|RecordsArray
     * @throws \InvalidArgumentException
     */
    public function select($columns = '*', array $conditionsAndOptions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $this->canAutoCacheSelectManyQueries()
                    && $this->getAutoCacheTimeoutForSelectManyInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $this->getAutoCacheTimeoutForSelectManyInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = $this->resolveCacheSettings(
                        $cacheSettings,
                        $this->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($columns, $conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(false, $columns, $conditionsAndOptions);
                        }
                    );
                    $records = $this->_getCachedData(false, $cacheSettings, function () use ($columns, $conditionsAndOptions, $configurator) {
                        return parent::select($columns, $conditionsAndOptions, $configurator);
                    });
                    return RecordsSet::createFromArray($this, $records, null);
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::select($columns, $conditionsAndOptions, $configurator);
    }

    /**
     * @param string|array $columns
     * @param array $conditions
     * @param \Closure|null $configurator
     * @return RecordsSet|RecordsArray
     */
    public function selectFromCache($columns = '*', array $conditions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return $this->select($columns, $conditions, $configurator);
    }

    /**
     * @param string $column
     * @param array $conditions
     * @param \Closure|null $configurator
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function selectColumnFromCache($column, array $conditions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return $this->selectColumn($column, $conditions, $configurator);
    }

    /**
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param array $conditions
     * @param \Closure $configurator
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function selectAssocFromCache($keysColumn, $valuesColumn, array $conditions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return $this->selectAssoc($keysColumn, $valuesColumn, $conditions, $configurator);
    }

    /**
     * @param null|array|string $conditionsAndOptions
     * @param bool $cacheSettings
     */
    static private function addCacheOptionToConditionsAndOptions(array &$conditionsAndOptions, $cacheSettings = true) {
        $conditionsAndOptions['CACHE'] = $cacheSettings;
    }

    /**
     * @param array $conditions
     * @param \Closure $configurator
     * @param bool $removeNotInnerJoins
     * @return int
     */
    public function count(array $conditions = [], \Closure $configurator = null, $removeNotInnerJoins = false) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            if (
                $hasCacheOption
                || (
                    $this->canAutoCacheSelectManyQueries()
                    && $this->getAutoCacheTimeoutForSelectManyInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $this->getAutoCacheTimeoutForSelectManyInMinutes()];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = $this->resolveCacheSettings(
                        $cacheSettings,
                        $this->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($conditions) {
                            return $this->buildDefaultCacheKey(false, '__COUNT__', $conditions);
                        }
                    );
                    $cacheSettings['key'] .= '_count'; //< for DbModel->selectWithCount() method when cache key provided by user
                    $count = $this->_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($conditions, $configurator, $removeNotInnerJoins) {
                            return parent::count($conditions, $configurator, $removeNotInnerJoins);
                        }
                    );
                    return $count;
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::count($conditions, $configurator, $removeNotInnerJoins);
    }

    /**
     * @param array $conditions
     * @param \Closure $configurator
     * @param bool $removeNotInnerJoins
     * @return int
     */
    public function countFromCache(array $conditions = [], \Closure $configurator = null, $removeNotInnerJoins = false) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return $this->count($conditions, $configurator, $removeNotInnerJoins);
    }

    /**
     * @param DbExpr $expression
     * @param array $conditions
     * @param \Closure|null $configurator
     * @return array
     */
    public function selectValue(DbExpr $expression, array $conditions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            if (
                $hasCacheOption
                || (
                    $this->canAutoCacheExpressionQueries()
                    && $this->getAutoCacheTimeoutForSelectOneInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $this->getAutoCacheTimeoutForSelectOneInMinutes()];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = $this->resolveCacheSettings(
                        $cacheSettings,
                        $this->getAutoCacheTimeoutForSelectOneInMinutes(),
                        function () use ($expression, $conditions) {
                            return $this->buildDefaultCacheKey(false, $expression, $conditions);
                        }
                    );
                    $result = $this->_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($expression, $conditions, $configurator) {
                            return parent::expression($expression, $conditions, $configurator);
                        }
                    );
                    return $result;
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::expression($expression, $conditions, $configurator);
    }

    /**
     * @param DbExpr $expression
     * @param array $conditions
     * @param \Closure|null $configurator
     * @return mixed
     */
    public function selectValueFromCache(DbExpr $expression, array $conditions = [], \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return $this->selectValue($expression, $conditions, $configurator);
    }

    /**
     * Get 1 record from DB
     * @param string|array $columns
     * @param null|array|string|int $conditions -
     *      array|string: conditions,
     *      numeric|int: record's pk value, automatically converted to array($this->primaryKey => $where)
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function selectOneFromCache($columns, array $conditions, \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            if (is_numeric($conditions) || is_int($conditions)) {
                $conditions = array(static::getPkColumnName() => $conditions);
            }
            static::addCacheOptionToConditionsAndOptions($conditions, true);
        }
        return $this->selectOne($columns, $conditions, $configurator);
    }

    /**
     * Get 1 record from DB
     * @param string|array $columns
     * @param null|array|string|int $conditions -
     *      array|string: conditions,
     *      numeric|int: record's pk value, automatically converted to array($this->primaryKey => $where)
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function selectOneAsDbRecordFromCache($columns, array $conditions, \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            if (is_numeric($conditions) || is_int($conditions)) {
                $conditions = array(static::getPkColumnName() => $conditions);
            }
            static::addCacheOptionToConditionsAndOptions($conditions, true);
        }
        return $this->selectOneAsDbRecord($columns, $conditions, $configurator);
    }

    /**
     * @inheritdoc
     * Also you can use 'CACHE' option. See description of select() method
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws \InvalidArgumentException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     */
    public function selectOne($columns, array $conditions, \Closure $configurator = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbTable|CacheForDbSelects $this */
            if (empty($conditionsAndOptions)) {
                throw new DbModelException($this, 'Selecting one record without conditions is not allowed');
            }
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $this->canAutoCacheSelectOneQueries()
                    && $this->getAutoCacheTimeoutForSelectOneInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $this->getAutoCacheTimeoutForSelectOneInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = $this->resolveCacheSettings(
                        $cacheSettings,
                        $this->_getCacheDurationForSelectOneInMinutes(),
                        function () use ($columns, $conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(true, $columns, $conditionsAndOptions);
                        }
                    );
                    $record = $this->_getCachedData(true, $cacheSettings, function () use ($columns, $conditionsAndOptions) {
                        return parent::selectOne($columns, $conditionsAndOptions, false, false);
                    });
                    if ($asObject) {
                        $record = static::getOwnDbObject($record, false, true);
                    } else if ($withRootAlias) {
                        $record = array($this->getAlias() => $record);
                    }
                    return $record;
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::selectOne($columns, $conditionsAndOptions, $asObject, $withRootAlias);
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function insert($data, $returning = null) {
        $ret = parent::insert($data, $returning);
        if ($this->cachingIsPossible()) {
            $this->cleanSelectManyCache();
        }
        return $ret;
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function insertMany($columnsNames, $rows, $returning = false) {
        $ret = parent::insertMany($columnsNames, $rows, $returning);
        if ($this->cachingIsPossible()) {
            $this->cleanSelectManyCache();
        }
        return $ret;
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws \InvalidArgumentException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     */
    public function update($data, $conditionsAndOptions = null, $returning = false) {
        /** @var CmfDbTable|CacheForDbSelects $this */
        $ret = parent::update($data, $conditionsAndOptions, $returning);
        if ($this->cachingIsPossible()) {
            if (!empty($ret[$this->getPkColumnName()])) {
                $ids = $ret[$this->getPkColumnName()];
            } else if (!empty($ret[0]) && !empty($ret[0][$this->getPkColumnName()])) {
                $ids = Set::extract('/' . $this->getPkColumnName());
            } else if (!empty($data[$this->getPkColumnName()])) {
                $ids = $data[$this->getPkColumnName()];
            } else if (!empty($conditionsAndOptions[$this->getPkColumnName()])) {
                $ids = $conditionsAndOptions[$this->getPkColumnName()];
            }
            if (!empty($ids)) {
                if (!is_array($ids)) {
                    $ids = array($ids);
                }
                foreach ($ids as $id) {
                    $this->cleanRecordCache($id);
                }
                $this->cleanSelectManyCache();
            } else {
                $this->cleanModelCache();
            }
        }
        return $ret;
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws \InvalidArgumentException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     */
    public function delete($conditionsAndOptions = null, $returning = false) {
        /** @var CmfDbTable|CacheForDbSelects $this */
        $ret = parent::delete($conditionsAndOptions, $returning);
        if ($this->cachingIsPossible()) {
            if (!empty($ret[$this->getPkColumnName()])) {
                $ids = $ret[$this->getPkColumnName()];
            } else if (!empty($ret[0]) && !empty($ret[0][$this->getPkColumnName()])) {
                $ids = Set::extract('/' . $ret[$this->getPkColumnName()]);
            } else if (!empty($conditionsAndOptions[$this->getPkColumnName()])) {
                $ids = $conditionsAndOptions[$this->getPkColumnName()];
            }
            if (!empty($ids)) {
                if (!is_array($ids)) {
                    $ids = array($ids);
                }
                foreach ($ids as $id) {
                    $this->cleanRecordCache($id);
                }
                $this->cleanSelectManyCache();
            } else {
                $this->cleanModelCache();
            }
        }
        return $ret;
    }
}
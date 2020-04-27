<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyORM\DbExpr;
use PeskyORM\DbModel;
use PeskyORM\ORM\RecordsArray;
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
    static public function buildCacheKey($columns = '*', $conditionsAndOptions = null) {
        if (is_array($conditionsAndOptions)) {
            foreach ($conditionsAndOptions as &$value) {
                if ($value instanceof DbExpr) {
                    $value = $value->get();
                } else if (is_object($value)) {
                    throw new \InvalidArgumentException(
                        '$conditionsAndOptions argument may contain only strings and objects of class \PeskyORM\DbExpr.'
                        . ' Object of class ' . get_class($value) . ' detected'
                    );
                }
            }
            unset($value);
        } else if ($conditionsAndOptions instanceof DbExpr) {
            $conditionsAndOptions = $conditionsAndOptions->get();
        }
        if (is_array($columns)) {
            foreach ($columns as &$value) {
                if ($value instanceof DbExpr) {
                    $value = $value->get();
                } else if (is_object($value)) {
                    throw new \InvalidArgumentException(
                        '$columns argument may contain only strings and objects of class \PeskyORM\DbExpr.'
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
     * @param int|\DateTime|bool $seconds
     * @return $this
     */
    public function withCacheTimeout($seconds) {
        /** @var CmfDbModel|CacheForDbSelects $this */
        $this->_cacheTimeoutForNextSelect = $seconds;
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
        /** @var CmfDbModel|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectOneInMinutes() * 60
            : $this->_cacheTimeoutForNextSelect;
    }

    /**
     * Override this to change default value
     * @return int
     */
    private function _getCacheDurationForSelectManyInMinutes() {
        /** @var CmfDbModel|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectManyInMinutes() * 60
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
     */
    public function getModelCachePrefix() {
        /** @var CmfDbModel|CacheForDbSelects $this */
        $parts = [
            static::getConnection()->getConnectionConfig()->getName(),
            $this->getTableConfig()->getSchema(),
            static::getConnection()->getConnectionConfig()->getDbName(),
            static::getAlias()
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
                ? $this->_getCacheDurationForSelectOneInMinutes() * 60
                : $this->_getCacheDurationForSelectManyInMinutes() * 60;
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
     * @throws \InvalidArgumentException
     */
    public function buildDefaultCacheKey($affectsSingleRecord, $columns, $conditionsAndOptions) {
        $mid = $affectsSingleRecord ? 'one.' : 'many.';
        return $this->getModelCachePrefix() . $mid . static::buildCacheKey($columns, $conditionsAndOptions);
    }

    /**
     * @param mixed $cacheSettings
     * @param int|bool|\DateTime $defaultTimeout - int: seconds
     * @param callable $defaultCacheKey
     * @param bool $isAutoCache
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
     * @param bool $asObjects - true: return DbObject | false: return array
     * @param bool $withRootAlias
     * @return array|Object[]
     * @throws \BadMethodCallException
     */
    static public function select($columns = '*', array $conditionsAndOptions = [], \Closure $configurator = null, bool $asRecordSet = false) {
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $model->canAutoCacheSelectManyQueries()
                    && $model->getAutoCacheTimeoutForSelectManyInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $model->getAutoCacheTimeoutForSelectManyInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = $model->resolveCacheSettings(
                        $cacheSettings,
                        $model->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($columns, $conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(false, $columns, $conditionsAndOptions);
                        }
                    );
                    $records = $model->_getCachedData(false, $cacheSettings, function () use ($columns, $conditionsAndOptions) {
                        return parent::select($columns, $conditionsAndOptions, false, false);
                    });
                    if ($asRecordSet) {
                        $records = new RecordsArray($model, $records, true, true);
                    }
                    return $records;
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::select($columns, $conditionsAndOptions, $configurator, $asRecordSet);
    }

    /**
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     * @param bool $asObjects - true: return DbObject | false: return array
     * @param bool $withRootAlias
     * @return array|Object[]
     */
    public function selectFromCache($columns = '*', $conditionsAndOptions = null, \Closure $configurator = null, bool $asRecordSet = false) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::select($columns, $conditionsAndOptions, $configurator, $asRecordSet);
    }

    /**
     * Selects only 1 column
     * @param string $column
     * @param null|array|string $conditionsAndOptions
     * @return array
     */
    public function selectColumnFromCache($column, $conditionsAndOptions = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::selectColumn($column, $conditionsAndOptions);
    }

    /**
     * Select associative array
     * Note: does not support columns from foreign models
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param null|array|string $conditionsAndOptions
     * @return array
     */
    public function selectAssocFromCache($keysColumn, $valuesColumn, $conditionsAndOptions = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::selectAssoc($keysColumn, $valuesColumn, $conditionsAndOptions);
    }

    /**
     * @param null|array|string $conditionsAndOptions
     * @param bool $cacheSettings
     */
    static private function addCacheOptionToConditionsAndOptions(&$conditionsAndOptions, $cacheSettings = true) {
        if (empty($conditionsAndOptions)) {
            $conditionsAndOptions = array();
        } else if (is_string($conditionsAndOptions)) {
            $conditionsAndOptions = array($conditionsAndOptions);
        }
        $conditionsAndOptions['CACHE'] = $cacheSettings;
    }

    /**
     * @param null|array $conditionsAndOptions
     * @param bool $removeNotInnerJoins
     * @return int
     */
    static public function count(array $conditionsAndOptions = [], \Closure $configurator = null, $removeNotInnerJoins = false) {
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $model->canAutoCacheSelectManyQueries()
                    && $model->getAutoCacheTimeoutForSelectManyInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $model->getAutoCacheTimeoutForSelectManyInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = $model->resolveCacheSettings(
                        $cacheSettings,
                        $model->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(false, '__COUNT__', $conditionsAndOptions);
                        }
                    );
                    $cacheSettings['key'] .= '_count'; //< for DbModel->selectWithCount() method when cache key provided by user
                    $count = $model->_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($configurator, $conditionsAndOptions, $removeNotInnerJoins) {
                            return parent::count($conditionsAndOptions, $configurator, $removeNotInnerJoins);
                        }
                    );
                    return $count;
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::count($conditionsAndOptions, $configurator, $removeNotInnerJoins);
    }

    /**
     * @param null|array $conditionsAndOptions
     * @param bool $removeNotInnerJoins
     * @return int
     */
    public function countFromCache($conditionsAndOptions = null, \Closure $configurator = null, $removeNotInnerJoins = false) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::count($conditionsAndOptions, $configurator, $removeNotInnerJoins);
    }

    /**
     * @param string $expression - example: 'COUNT(*)', 'SUM(`field`)'
     * @param array|string|null $conditionsAndOptions
     * @return string|int|float|bool
     */
    static public function expression($expression, array $conditionsAndOptions = []) {
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $model->canAutoCacheExpressionQueries()
                    && $model->getAutoCacheTimeoutForSelectOneInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $model->getAutoCacheTimeoutForSelectOneInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = $model->resolveCacheSettings(
                        $cacheSettings,
                        $model->getAutoCacheTimeoutForSelectOneInMinutes(),
                        function () use ($expression, $conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(false, $expression, $conditionsAndOptions);
                        }
                    );
                    $result = $model->_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($expression, $conditionsAndOptions) {
                            return parent::expression($expression, $conditionsAndOptions);
                        }
                    );
                    return $result;
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::expression($expression, $conditionsAndOptions);
    }

    /**
     * @param string $expression - example: 'COUNT(*)', 'SUM(`field`)'
     * @param array|string|null $conditionsAndOptions
     * @return string|int|float|bool
     */
    public function expressionFromCache($expression, array $conditionsAndOptions = []) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::expression($expression, $conditionsAndOptions);
    }

    /**
     * Get 1 record from DB
     * @param string|array $columns
     * @param null|array|string|int $conditionsAndOptions -
     *      array|string: conditions,
     *      numeric|int: record's pk value, automatically converted to array($this->primaryKey => $where)
     * @param bool $asObject - true: return DbObject | false: return array
     * @param bool $withRootAlias
     * @return array|bool|Object
     */
    public function selectOneFromCache($columns, $conditionsAndOptions = null) {
        if ($this->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            if (is_numeric($conditionsAndOptions) || is_int($conditionsAndOptions)) {
                $conditionsAndOptions = array(static::getPkColumnName() => $conditionsAndOptions);
            }
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions, true);
        }
        return static::selectOne($columns, $conditionsAndOptions);
    }

    /**
     * @inheritdoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    static public function selectOne($columns, array $conditionsAndOptions, ?\Closure $configurator = null) {
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            /** @var CmfDbModel|CacheForDbSelects $this */
            if (empty($conditionsAndOptions)) {
                throw new \InvalidArgumentException('Selecting one record without conditions is not allowed');
            }
            $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
            if (
                $hasCacheOption
                || (
                    $model->canAutoCacheSelectOneQueries()
                    && $model->getAutoCacheTimeoutForSelectOneInMinutes() > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditionsAndOptions['CACHE']
                    : ['timeout' => $model->getAutoCacheTimeoutForSelectOneInMinutes()];
                unset($conditionsAndOptions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = $model->resolveCacheSettings(
                        $cacheSettings,
                        $model->_getCacheDurationForSelectOneInMinutes(),
                        function () use ($columns, $conditionsAndOptions) {
                            return $this->buildDefaultCacheKey(true, $columns, $conditionsAndOptions);
                        }
                    );
                    $record = $model->_getCachedData(true, $cacheSettings, function () use ($columns, $conditionsAndOptions) {
                        return parent::selectOne($columns, $conditionsAndOptions, false, false);
                    });
                    return $record;
                }
            }
        }
        unset($conditionsAndOptions['CACHE']);
        return parent::selectOne($columns, $conditionsAndOptions, $configurator);
    }
    
    static public function selectOneAsDbRecord($columns, array $conditionsAndOptions, ?\Closure $configurator = null) {
        /** @var DbModel|CacheForDbSelects $model */
        $data = static::selectOne($columns, $conditionsAndOptions, $configurator);
        return static::getInstance()->newRecord()->fromData($data, true, false);
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     */
    static public function insert(array $data, $returning = false) {
        $ret = parent::insert($data, $returning);
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            $model->cleanSelectManyCache();
        }
        return $ret;
    }

    /**
     * Insert many records at once
     * @param array $fieldNames - field names use
     * @param array[] $rows - arrays of values for $fieldNames
     * @param bool|string $returning - string: something compatible with RETURNING for postgresql query | false: do not return
     * @return int|array - int: amount of rows created | array: records (when $returning !== false)
     * @throws \BadMethodCallException
     */
    static public function insertMany(array $columns, array $rows, $returning = false) {
        $ret = parent::insertMany($columns, $rows, $returning);
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            $model->cleanSelectManyCache();
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    static public function update(array $data, array $conditionsAndOptions, $returning = false) {
        /** @var CmfDbModel|CacheForDbSelects $this */
        $ret = parent::update($data, $conditionsAndOptions, $returning);
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            $pkColumnName = $model::getPkColumnName();
            if (!empty($ret[$pkColumnName])) {
                $ids = $ret[$pkColumnName];
            } else if (!empty($ret[0]) && !empty($ret[0][$pkColumnName])) {
                $ids = Set::extract('/' . $pkColumnName);
            } else if (!empty($data[$pkColumnName])) {
                $ids = $data[$pkColumnName];
            } else if (!empty($conditionsAndOptions[$pkColumnName])) {
                $ids = $conditionsAndOptions[$pkColumnName];
            }
            if (!empty($ids)) {
                if (!is_array($ids)) {
                    $ids = array($ids);
                }
                foreach ($ids as $id) {
                    $model->cleanRecordCache($id);
                }
                $model->cleanSelectManyCache();
            } else {
                $model->cleanModelCache();
            }
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    static public function delete(array $conditionsAndOptions = [], $returning = false) {
        /** @var CmfDbModel|CacheForDbSelects $this */
        $ret = parent::delete($conditionsAndOptions, $returning);
        /** @var DbModel|CacheForDbSelects $model */
        $model = static::getInstance();
        if ($model->cachingIsPossible()) {
            $pkColumnName = $model::getPkColumnName();
            if (!empty($ret[$pkColumnName])) {
                $ids = $ret[$pkColumnName];
            } else if (!empty($ret[0]) && !empty($ret[0][$pkColumnName])) {
                $ids = Set::extract('/' . $pkColumnName, $ret);
            } else if (!empty($conditionsAndOptions[$pkColumnName])) {
                $ids = $conditionsAndOptions[$pkColumnName];
            }
            if (!empty($ids)) {
                if (!is_array($ids)) {
                    $ids = array($ids);
                }
                foreach ($ids as $id) {
                    $model->cleanRecordCache($id);
                }
                $model->cleanSelectManyCache();
            } else {
                $model->cleanModelCache();
            }
        }
        return $ret;
    }
}
<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\Set;

/**
 * @method static Table|TableInterface|CacheForDbSelects|self getInstance()
 */
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
    abstract static public function getDefaultCacheDurationForSelectOneInMinutes();
    
    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select many" queries
     * @return int
     */
    abstract static public function getDefaultCacheDurationForSelectManyInMinutes();
    
    /**
     * Override to change default value
     * @return boolean
     */
    abstract static public function canCleanRelationsCache();
    
    /**
     * Override to change default value
     * @return boolean
     */
    abstract static public function canAutoCacheSelectOneQueries();
    
    /**
     * Allow/disallow cache for Table::expression().
     * Cache timeout provided by static::getDefaultCacheDurationForSelectOneInMinutes()
     * Override to change default value
     * @return boolean
     */
    static public function canAutoCacheExpressionQueries() {
        return static::canAutoCacheSelectOneQueries();
    }
    
    /**
     * Override to change default value
     * @return boolean
     */
    abstract static public function canAutoCacheSelectManyQueries();
    
    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    abstract static public function getAutoCacheTimeoutForSelectOneInMinutes();
    
    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    abstract static public function getAutoCacheTimeoutForSelectManyInMinutes();
    
    /**
     * Detect if caching is possible
     * @return bool
     */
    abstract static protected function cachingIsPossible();
    
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
        return hash('sha256', json_encode([$columns, $conditionsAndOptions]));
    }
    
    /** ============ Next methods does not contain any setting or normally overridable content ============ */
    
    /**
     * Set custom cache timeout for next query in this model
     * @param int|\DateTime|bool $seconds
     * @return $this
     */
    public function withCacheTimeout($seconds) {
        /** @var CmfDbTable|CacheForDbSelects $this */
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
        /** @var CmfDbTable|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect ?: static::getDefaultCacheDurationForSelectOneInMinutes() * 60;
    }
    
    /**
     * Override this to change default value
     * @return int
     */
    private function _getCacheDurationForSelectManyInMinutes() {
        /** @var CmfDbTable|CacheForDbSelects $this */
        return $this->_cacheTimeoutForNextSelect ?: static::getDefaultCacheDurationForSelectManyInMinutes() * 60;
    }
    
    /**
     * @param bool|true $cleanRelatedModelsCache - true: clean cache for all related models
     */
    abstract static public function cleanModelCache($cleanRelatedModelsCache = null);
    
    /**
     * Clean cache for all related models
     * @param bool|null $cleanRelatedModelsCache
     */
    abstract static public function cleanRelatedModelsCache($cleanRelatedModelsCache = true);
    
    /**
     * @param bool $cleanRelatedModelsCache
     */
    abstract static public function cleanSelectManyCache($cleanRelatedModelsCache = null);
    
    /**
     * @param bool $cleanRelatedModelsCache
     */
    abstract static public function cleanSelectOneCache($cleanRelatedModelsCache = null);
    
    /**
     * @param int|string|array|Object $record
     * @param bool $cleanRelatedModelsCache
     */
    abstract static public function cleanRecordCache($record, $cleanRelatedModelsCache = null);
    
    static public function getModelCachePrefix(): string {
        return static::getCacheTag();
    }
    
    static public function getCacheTag(): string {
        $parts = [
            static::getConnection()->getConnectionConfig()->getName(),
            static::getStructure()->getSchema(),
            static::getConnection()->getConnectionConfig()->getDbName(),
            static::getAlias()
        ];
        return implode('.', $parts) . '.';
    }
    
    static public function makeCacheKeyFromConditions(string $prefix, array $conditions): string {
        ksort($conditions);
        return $prefix . '_' . sha1(json_encode($conditions, JSON_UNESCAPED_UNICODE));
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
    static public function getCachedData($affectsSingleRecord, $cacheSettings, callable $callback) {
        $defaultTimeout = $affectsSingleRecord
            ? static::getInstance()->_getCacheDurationForSelectOneInMinutes() * 60
            : static::getInstance()->_getCacheDurationForSelectManyInMinutes() * 60;
        $resolvedCacheSettings = static::resolveCacheSettings(
            $cacheSettings,
            $defaultTimeout,
            function () {
                throw new \InvalidArgumentException('$cacheSettings must contain a "key" key (if array) or be the cache key (if string)');
            }
        );
        if (is_array($cacheSettings)) {
            return static::_getCachedData($affectsSingleRecord, $resolvedCacheSettings, $callback);
        } else {
            return $callback();
        }
    }
    
    /**
     * Get data from cache or put data from $callback to cache (for internal use)
     * @param bool $affectsSingleRecord
     * @param array $cacheSettings - prepared cache settings. Always contains 'key' key
     * @param callable $callback
     * @return mixed
     */
    abstract static protected function _getCachedData($affectsSingleRecord, array $cacheSettings, callable $callback);
    
    /**
     * @param bool $affectsSingleRecord
     * @param null|string|array $columns
     * @param null|array $conditionsAndOptions
     * @return string
     * @throws \InvalidArgumentException
     */
    static public function buildDefaultCacheKey($affectsSingleRecord, $columns, $conditionsAndOptions) {
        $mid = $affectsSingleRecord ? 'one.' : 'many.';
        return static::getModelCachePrefix() . $mid . static::buildCacheKey($columns, $conditionsAndOptions);
    }
    
    /**
     * @param mixed $cacheSettings
     * @param int|bool|\DateTime $defaultTimeout - int: seconds
     * @param callable $defaultCacheKey
     * @param bool $isAutoCache
     * @return array|bool - bool: false - when caching is not possible
     */
    static protected function resolveCacheSettings($cacheSettings, $defaultTimeout, callable $defaultCacheKey) {
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
        static::getInstance()->cleanCacheTimeout();
        return $cacheSettings;
    }
    
    /**
     * @inheritDoc
     *
     * Special key for $conditions: 'CACHE'.
     * Accepted values: array|true|numeric|string
     *  - array
     *      'key' => optional
     *              string - cache key
     *              Default: static::buildDefaultCacheKey(false, $columns, $conditionsAndOptions)
     *      'timeout' => optional
     *              int|DateTime - cache timeout
     *              false|null - cache for forever
     *              Default: $this->getCacheDurationForSelectAllInMinutes()
     *      'tags' => optional
     *              string|array - cache tags.
     *  - true: cache with default settings
     *  - numeric or \DateTime: cache timeout, key and tags are set to default values
     *  - string: cache tag, timeout and tags are set to default
     */
    static public function selectAsArray($columns = '*', array $conditions = [], ?\Closure $configurator = null): array {
        if (static::cachingIsPossible()) {
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            $timeout = static::getAutoCacheTimeoutForSelectManyInMinutes();
            if (
                $hasCacheOption
                || (
                    static::canAutoCacheSelectManyQueries()
                    && $timeout > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $timeout];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = static::resolveCacheSettings(
                        $cacheSettings,
                        static::getInstance()->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($columns, $conditions) {
                            return static::buildDefaultCacheKey(false, $columns, $conditions);
                        }
                    );
                    return static::_getCachedData(false, $cacheSettings, function () use ($configurator, $columns, $conditions) {
                        return parent::selectAsArray($columns, $conditions, $configurator);
                    });
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::selectAsArray($columns, $conditions, $configurator);
    }
    
    /**
     * @see selectAsArray()
     */
    public function selectFromCache($columns = '*', $conditionsAndOptions = null, ?\Closure $configurator = null) {
        if (static::cachingIsPossible()) {
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::selectAsArray($columns, $conditionsAndOptions, $configurator);
    }
    
    /**
     * @see selectColumn()
     */
    public function selectColumnFromCache($column, $conditionsAndOptions = null) {
        if (static::cachingIsPossible()) {
            static::addCacheOptionToConditionsAndOptions($conditionsAndOptions);
        }
        return static::selectColumn($column, $conditionsAndOptions);
    }
    
    /**
     * @see selectAssoc()
     */
    public function selectAssocFromCache($keysColumn, $valuesColumn, $conditionsAndOptions = null) {
        if (static::cachingIsPossible()) {
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
            $conditionsAndOptions = [];
        } else if (is_string($conditionsAndOptions)) {
            $conditionsAndOptions = [$conditionsAndOptions];
        }
        $conditionsAndOptions['CACHE'] = $cacheSettings;
    }
    
    /**
     * @inheritDoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    static public function count(array $conditions = [], ?\Closure $configurator = null, bool $removeNotInnerJoins = false): int {
        if (static::cachingIsPossible()) {
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            $timeout = static::getAutoCacheTimeoutForSelectManyInMinutes();
            if (
                $hasCacheOption
                || (
                    static::canAutoCacheSelectManyQueries()
                    && $timeout > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $timeout];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = static::resolveCacheSettings(
                        $cacheSettings,
                        static::getInstance()->_getCacheDurationForSelectManyInMinutes(),
                        function () use ($conditions) {
                            return static::buildDefaultCacheKey(false, '__COUNT__', $conditions);
                        }
                    );
                    $cacheSettings['key'] .= '_count'; //< for DbModel->selectWithCount() method when cache key provided by user
                    return (int)static::_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($configurator, $conditions, $removeNotInnerJoins) {
                            return parent::count($conditions, $configurator, $removeNotInnerJoins);
                        }
                    );
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::count($conditions, $configurator, $removeNotInnerJoins);
    }
    
    /**
     * @see count()
     */
    static public function countFromCache(array $conditions = [], ?\Closure $configurator = null, $removeNotInnerJoins = false) {
        if (static::cachingIsPossible()) {
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return static::count($conditions, $configurator, $removeNotInnerJoins);
    }
    
    /**
     * @inheritDoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    static public function selectValue(DbExpr $expression, array $conditions = [], ?\Closure $configurator = null): ?string {
        if (static::cachingIsPossible()) {
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            $timeout = static::getAutoCacheTimeoutForSelectOneInMinutes();
            if (
                $hasCacheOption
                || (
                    static::canAutoCacheExpressionQueries()
                    && $timeout > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $timeout];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    /** @var array $cacheSettings */
                    $cacheSettings = static::resolveCacheSettings(
                        $cacheSettings,
                        $timeout,
                        function () use ($expression, $conditions) {
                            return static::buildDefaultCacheKey(false, $expression, $conditions);
                        }
                    );
                    return static::_getCachedData(
                        false,
                        $cacheSettings,
                        function () use ($configurator, $expression, $conditions) {
                            return parent::selectValue($expression, $conditions, $configurator);
                        }
                    );
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::selectValue($expression, $conditions, $configurator);
    }
    
    /**
     * @see selectValue()
     */
    static public function selectValueFromCache(DbExpr $expression, array $conditions = [], ?\Closure $configurator = null): ?string {
        if (static::cachingIsPossible()) {
            static::addCacheOptionToConditionsAndOptions($conditions);
        }
        return static::selectValue($expression, $conditions, $configurator);
    }
    
    /**
     * @inheritDoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    static public function selectOne($columns, array $conditions, ?\Closure $configurator = null): array {
        if (static::cachingIsPossible()) {
            if (empty($conditions)) {
                throw new \InvalidArgumentException('Selecting one record without conditions is not allowed');
            }
            $hasCacheOption = is_array($conditions) && array_key_exists('CACHE', $conditions);
            $timeout = static::getAutoCacheTimeoutForSelectOneInMinutes();
            if (
                $hasCacheOption
                || (
                    static::canAutoCacheSelectOneQueries()
                    && $timeout > 0
                )
            ) {
                $cacheSettings = $hasCacheOption
                    ? $conditions['CACHE']
                    : ['timeout' => $timeout];
                unset($conditions['CACHE']);
                if ($cacheSettings !== false) {
                    $cacheSettings = static::resolveCacheSettings(
                        $cacheSettings,
                        static::getInstance()->_getCacheDurationForSelectOneInMinutes(),
                        function () use ($columns, $conditions) {
                            return static::buildDefaultCacheKey(true, $columns, $conditions);
                        }
                    );
                    return static::_getCachedData(true, $cacheSettings, function () use ($columns, $conditions) {
                        return parent::selectOne($columns, $conditions, false, false);
                    });
                }
            }
        }
        unset($conditions['CACHE']);
        return parent::selectOne($columns, $conditions, $configurator);
    }
    
    /**
     * @see selectOne()
     */
    static public function selectOneFromCache($columns, array $conditions, ?\Closure $configurator = null) {
        if (static::cachingIsPossible()) {
            static::addCacheOptionToConditionsAndOptions($conditions, true);
        }
        return static::selectOne($columns, $conditions, $configurator);
    }
    
    /**
     * @inheritDoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    static public function selectOneAsDbRecord($columns, array $conditions, ?\Closure $configurator = null): RecordInterface {
        $data = static::selectOne($columns, $conditions, $configurator);
        return static::getInstance()->newRecord()->fromData($data, true, false);
    }
    
    /**
     * @inheritDoc
     */
    static public function insert(array $data, $returning = false) {
        $ret = parent::insert($data, $returning);
        if (static::cachingIsPossible()) {
            static::cleanSelectManyCache();
        }
        return $ret;
    }
    
    /**
     * @inheritDoc
     */
    static public function insertMany(array $columns, array $rows, $returning = false) {
        $ret = parent::insertMany($columns, $rows, $returning);
        if (static::cachingIsPossible()) {
            static::cleanSelectManyCache();
        }
        return $ret;
    }
    
    /**
     * @inheritDoc
     */
    static public function insertManyAsIs(array $columns, array $rows, $returning = false) {
        $ret = parent::insertManyAsIs($columns, $rows, $returning);
        if (static::cachingIsPossible()) {
            static::cleanSelectManyCache();
        }
        return $ret;
    }
    
    /**
     * @inheritDoc
     */
    static public function update(array $data, array $conditionsAndOptions, $returning = false) {
        $ret = parent::update($data, $conditionsAndOptions, $returning);
        if (static::cachingIsPossible()) {
            $pkColumnName = static::getPkColumnName();
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
                    $ids = [$ids];
                }
                foreach ($ids as $id) {
                    static::cleanRecordCache($id);
                }
                static::cleanSelectManyCache();
            } else {
                static::cleanModelCache();
            }
        }
        return $ret;
    }
    
    /**
     * @inheritDoc
     */
    static public function delete(array $conditionsAndOptions = [], $returning = false) {
        $ret = parent::delete($conditionsAndOptions, $returning);
        if (static::cachingIsPossible()) {
            $pkColumnName = static::getPkColumnName();
            if (!empty($ret[$pkColumnName])) {
                $ids = $ret[$pkColumnName];
            } else if (!empty($ret[0]) && !empty($ret[0][$pkColumnName])) {
                $ids = Set::extract('/' . $pkColumnName, $ret);
            } else if (!empty($conditionsAndOptions[$pkColumnName])) {
                $ids = $conditionsAndOptions[$pkColumnName];
            }
            if (!empty($ids)) {
                if (!is_array($ids)) {
                    $ids = [$ids];
                }
                foreach ($ids as $id) {
                    static::cleanRecordCache($id);
                }
                static::cleanSelectManyCache();
            } else {
                static::cleanModelCache();
            }
        }
        return $ret;
    }
}
<?php


namespace PeskyCMF\Db\Traits;


use PeskyCMF\Db\CmfDbModel;
use PeskyORM\DbObject;
use PeskyORM\Exception\DbModelException;
use Swayok\Utils\Set;

trait CacheableDbModel {

    private $_cacheTimeoutForNextSelect = null;

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select one" queries
     * @return int
     */
    public function getDefaultCacheDurationForSelectOneInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select many" queries
     * @return int
     */
    public function getDefaultCacheDurationForSelectManyInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canCleanRelationsCache() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectOneQueries() {
        return false;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectManyQueries() {
        return false;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    public function getAutoCacheTimeoutForSelectOneInMinutes() {
        return 10;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    public function getAutoCacheTimeoutForSelectManyInMinutes() {
        return 10;
    }

    /**
     * Cache key builder
     * Override if you wish to change algorythm
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     * @return string
     */
    static public function buildCacheKey($columns = '*', $conditionsAndOptions = null) {
        return hash('sha256', json_encode(array($columns, $conditionsAndOptions)));
    }

/** ============ Next methods does not contain any setting or normally overridable content ============ */

    /**
     * Set custom cache timeout for next query in this model
     * @param int|\DateTime|bool $minutes
     * @return $this
     */
    public function withCacheTimeout($minutes) {
        /** @var CmfDbModel|CacheableDbModel $this */
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
        /** @var CmfDbModel|CacheableDbModel $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectOneInMinutes()
            : $this->_cacheTimeoutForNextSelect;
    }

    /**
     * Override this to change default value
     * @return int
     */
    private function _getCacheDurationForSelectManyInMinutes() {
        /** @var CmfDbModel|CacheableDbModel $this */
        return $this->_cacheTimeoutForNextSelect === null
            ? $this->getDefaultCacheDurationForSelectManyInMinutes()
            : $this->_cacheTimeoutForNextSelect;
    }

    /**
     * @param bool|true $cleanRelatedModelsCache - true: clean cache for all related models
     */
    public function cleanModelCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|CacheableDbModel $this */
        \Cache::tags($this->getModelCachePrefix())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * Clean cache for all related models
     * @param bool|null $cleanRelatedModelsCache
     */
    public function cleanRelatedModelsCache($cleanRelatedModelsCache = true) {
        /** @var CmfDbModel|CacheableDbModel $this */
        if ($cleanRelatedModelsCache === null) {
            $cleanRelatedModelsCache = $this->canCleanRelationsCache();
        }
        if ($cleanRelatedModelsCache) {
            \Cache::tags(array_keys($this->getTableRealtaions()))->flush();
        }
    }

    /**
     * @param bool $cleanRelatedModelsCache
     */
    public function cleanSelectCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|CacheableDbModel $this */
        \Cache::tags($this->getSelectCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param bool $cleanRelatedModelsCache
     */
    public function cleanSelectOneCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|CacheableDbModel $this */
        \Cache::tags($this->getSelectOneCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param int|string|array|Object $record
     * @param bool $cleanRelatedModelsCache
     * @throws DbModelException
     */
    public function cleanRecordCache($record, $cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|CacheableDbModel $this */
        \Cache::tags($this->getRecordCacheTag($record))->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @return string
     */
    public function getSelectCacheTag() {
        /** @var CmfDbModel|CacheableDbModel $this */
        return $this->getModelCachePrefix() . 'many';
    }

    /**
     * @return string
     */
    public function getSelectOneCacheTag() {
        /** @var CmfDbModel|CacheableDbModel $this */
        return $this->getModelCachePrefix() . 'one';
    }

    /**
     * @return string
     * @throws DbModelException
     */
    public function getModelCachePrefix() {
        /** @var CmfDbModel|CacheableDbModel $this */
        return "{$this->getDataSource()->getDbName()}.{$this->getConnectionAlias()}.{$this->getAlias()}.";
    }

    /**
     * @param int|string|array|Object $record
     * @return string
     * @throws DbModelException
     */
    public function getRecordCacheTag($record) {
        /** @var CmfDbModel|CacheableDbModel $this */
        if ($record instanceof DbObject) {
            $id = $record->exists() ? $record->_getPkValue() : null;
        } else if (!is_array($record)) {
            $id = $record;
        } else if (!empty($record[$this->getPkColumnName()])) {
            $id = $record[$this->getPkColumnName()];
        } else if (!empty($record[$this->getAlias()]) && !empty($record[$this->getAlias()][$this->getPkColumnName()])) {
            $id = $record[$this->getAlias()][$this->getPkColumnName()];
        }
        if (empty($id)) {
            throw new DbModelException($this, 'Data passed to getRecordCacheTag() has no value for primary key');
        }
        return $this->getModelCachePrefix() . 'id_' . $id;
    }

    /**
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     *  special key: 'CACHE'. Accepted values:
     *  - array: cache settings, accepted keys:
     *      'key' => optional
     *              string - cache key
     *              Default: $this->alias . '_many_' . self::buildCacheKey($columns, $conditionsAndOptions)
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
     */
    public function select($columns = '*', $conditionsAndOptions = null, $asObjects = false, $withRootAlias = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
        if (
            $hasCacheOption
            || (
                $this->canAutoCacheSelectManyQueries()
                && $this->getAutoCacheTimeoutForSelectManyInMinutes() > 0
                //&& \Request::getMethod() === 'GET'
            )
        ) {
            if ($hasCacheOption) {
                $cacheSettings = $conditionsAndOptions['CACHE'];
            } else {
                $cacheSettings = [
                    'timeout' => $this->getAutoCacheTimeoutForSelectManyInMinutes()
                ];
            }
            unset($conditionsAndOptions['CACHE']);
            if ($cacheSettings !== false) {
                $cacheTag = $this->getSelectCacheTag();
                $cacheSettings = $this->resolveCacheSettings(
                    $cacheSettings,
                    $this->_getCacheDurationForSelectManyInMinutes(),
                    function () use($columns, $conditionsAndOptions, $cacheTag) {
                        return $cacheTag . '_' . $this->buildCacheKey($columns, $conditionsAndOptions);
                    }
                );
                $cacheSettings['tags'][] = $this->getModelCachePrefix();
                $cacheSettings['tags'][] = $cacheTag;
                $cacher = \Cache::tags(array_unique($cacheSettings['tags']));
                $callback = function () use ($columns, $conditionsAndOptions) {
                    return parent::select($columns, $conditionsAndOptions, false, false);
                };
                if (empty($cacheSettings['timeout'])) {
                    $records = $cacher->rememberForever($cacheSettings['key'], $callback);
                } else {
                    $records = $cacher->remember($cacheSettings['key'], $cacheSettings['timeout'], $callback);
                }
                if ($asObjects) {
                    $records = $this->recordsToObjects($records, true);
                } else if ($withRootAlias) {
                    $records = array($this->getAlias() => $records);
                }
                return $records;
            }
        }
        return parent::select($columns, $conditionsAndOptions, $asObjects, $withRootAlias);
    }

    /**
     * @param array|int|string|\DateTime $cacheSettings
     * @param int|bool|\DateTime $defaultTimeout
     * @param callable $defaultCacheKey
     * @return array
     */
    private function resolveCacheSettings($cacheSettings, $defaultTimeout, callable $defaultCacheKey) {
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
        $this->cleanCacheTimeout();
        return $cacheSettings;
    }

    /**
     * @param string|array $columns
     * @param null|array|string $conditionsAndOptions
     * @param bool $asObjects - true: return DbObject | false: return array
     * @param bool $withRootAlias
     * @return array|Object[]
     */
    public function selectFromCache($columns = '*', $conditionsAndOptions = null, $asObjects = false, $withRootAlias = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        self::addCacheKeyToConditionsAndOptions($conditionsAndOptions);
        return $this->select($columns, $conditionsAndOptions, $asObjects, $withRootAlias);
    }

    /**
     * Selects only 1 column
     * @param string $column
     * @param null|array|string $conditionsAndOptions
     * @return array
     */
    public function selectColumnFromCache($column, $conditionsAndOptions = null) {
        /** @var CmfDbModel|CacheableDbModel $this */
        self::addCacheKeyToConditionsAndOptions($conditionsAndOptions);
        return $this->selectColumn($column, $conditionsAndOptions);
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
        /** @var CmfDbModel|CacheableDbModel $this */
        self::addCacheKeyToConditionsAndOptions($conditionsAndOptions);
        return $this->selectAssoc($keysColumn, $valuesColumn, $conditionsAndOptions);
    }

    /**
     * @param null|array|string $conditionsAndOptions
     * @param bool $cacheSettings
     */
    static private function addCacheKeyToConditionsAndOptions(&$conditionsAndOptions, $cacheSettings = true) {
        if (empty($conditionsAndOptions)) {
            $conditionsAndOptions = array();
        } else if (is_string($conditionsAndOptions)) {
            $conditionsAndOptions = array($conditionsAndOptions);
        }
        $conditionsAndOptions['CACHE'] = $cacheSettings;
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
    public function selectOneFromCache($columns, $conditionsAndOptions = null, $asObject = true, $withRootAlias = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        if (is_numeric($conditionsAndOptions) || is_int($conditionsAndOptions)) {
            $conditionsAndOptions = array($this->getPkColumnName() => $conditionsAndOptions);
        }
        $this->addCacheKeyToConditionsAndOptions($conditionsAndOptions, true);
        return $this->selectOne($columns, $conditionsAndOptions, $asObject, $withRootAlias);
    }

    /**
     * @inheritdoc
     * Also you can use 'CACHE' option. See description of select() method
     */
    public function selectOne($columns, $conditionsAndOptions, $asObject = true, $withRootAlias = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        if (empty($conditionsAndOptions)){
            throw new DbModelException($this, 'Selecting one record without conditions is not allowed');
        }
        $hasCacheOption = is_array($conditionsAndOptions) && array_key_exists('CACHE', $conditionsAndOptions);
        if (
            $hasCacheOption
            || (
                $this->canAutoCacheSelectOneQueries()
                && $this->getAutoCacheTimeoutForSelectOneInMinutes() > 0
                //&& \Request::getMethod() === 'GET'
            )
        ) {
            if ($hasCacheOption) {
                $cacheSettings = $conditionsAndOptions['CACHE'];
            } else {
                $cacheSettings = [
                    'timeout' => $this->getAutoCacheTimeoutForSelectOneInMinutes()
                ];
            }
            unset($conditionsAndOptions['CACHE']);
            if ($cacheSettings !== false) {
                $cacheTag = $this->getSelectOneCacheTag();
                $cacheSettings = $this->resolveCacheSettings(
                    $cacheSettings,
                    $this->_getCacheDurationForSelectOneInMinutes(),
                    function () use($columns, $conditionsAndOptions, $cacheTag) {
                        return $cacheTag . '_' . $this->buildCacheKey($columns, $conditionsAndOptions);
                    }
                );
                $record = \Cache::get($cacheSettings['key'], false);
                if ($record === false) {
                    $record = parent::selectOne($columns, $conditionsAndOptions, false, false);
                }
                if (!empty($record)) {
                    $cacheSettings['tags'][] = $this->getModelCachePrefix();
                    $cacheSettings['tags'][] = $cacheTag;
                    $cacheSettings['tags'][] = $this->getRecordCacheTag($record);
                    $cacher = \Cache::tags(array_unique($cacheSettings['tags']));
                    if (empty($cacheSettings['timeout'])) {
                        $cacher->forever($cacheSettings['key'], $record);
                    } else {
                        $cacher->put($cacheSettings['key'], $record, $cacheSettings['timeout']);
                    }
                }
                if ($asObject) {
                    $record = self::getOwnDbObject($record, false, true);
                } else if ($withRootAlias) {
                    $record = array($this->getAlias() => $record);
                }
                return $record;
            }
        }
        return parent::selectOne($columns, $conditionsAndOptions, $asObject, $withRootAlias);
    }

    /**
     * @inheritdoc
     */
    public function insert($data, $returning = null) {
        $ret = parent::insert($data, $returning);
        $this->cleanSelectCache();
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function insertMany($fieldNames, $rows, $returning = false) {
        $ret = parent::insertMany($fieldNames, $rows, $returning);
        $this->cleanSelectCache();
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function update($data, $conditionsAndOptions = null, $returning = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        $ret = parent::update($data, $conditionsAndOptions, $returning);
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
            $this->cleanSelectCache();
        } else {
            $this->cleanModelCache();
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete($conditionsAndOptions = null, $returning = false) {
        /** @var CmfDbModel|CacheableDbModel $this */
        $ret = parent::delete($conditionsAndOptions, $returning);
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
            $this->cleanSelectCache();
        } else {
            $this->cleanModelCache();
        }
        return $ret;
    }
}
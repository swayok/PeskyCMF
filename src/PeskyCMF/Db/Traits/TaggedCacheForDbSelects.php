<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\Record;

trait TaggedCacheForDbSelects {
    
    use CacheForDbSelects;
    
    /**
     * Detect if caching is possible
     * @return bool
     */
    static protected function cachingIsPossible() {
        if (static::$_cachingIsPossible === null) {
            /** @var \AlternativeLaravelCache\Core\AlternativeCacheStore $cache */
            $storeClass = '\AlternativeLaravelCache\Core\AlternativeCacheStore';
            $poolInterface = '\Cache\Taggable\TaggablePoolInterface';
            $cache = app('cache.store')->getStore();
            static::$_cachingIsPossible = (
                $cache instanceof $storeClass
                && $cache->getWrappedConnection() instanceof $poolInterface
            );
        }
        return static::$_cachingIsPossible;
    }
    
    /**
     * @param bool|true $cleanRelatedModelsCache - true: clean cache for all related models
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function cleanModelCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbTable|TaggedCacheForDbSelects $this */
        \Cache::tags(static::getModelCachePrefix())->flush();
        static::cleanRelatedModelsCache($cleanRelatedModelsCache);
    }
    
    /**
     * Clean cache for all related models
     * @param bool|null|array|string $cleanRelatedModelsCache -
     *      - array: list of relations to clean
     *      - string: single relation to clean
     *      - bool: true = clean all relations provided by static::getDefaultRelationsForCacheCleaner(); false - don't clean relations
     *      - null: if (static::canCleanRelationsCache() === true) then clean static::getDefaultRelationsForCacheCleaner()
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function cleanRelatedModelsCache($cleanRelatedModelsCache = true) {
        /** @var CmfDbTable|TaggedCacheForDbSelects $this */
        if ($cleanRelatedModelsCache === null) {
            $cleanRelatedModelsCache = static::canCleanRelationsCache();
        }
        if ($cleanRelatedModelsCache) {
            if (is_array($cleanRelatedModelsCache)) {
                $relationsToClean = $cleanRelatedModelsCache;
            } else if (is_string($cleanRelatedModelsCache)) {
                $relationsToClean = [$cleanRelatedModelsCache];
            } else {
                $relationsToClean = static::getDefaultRelationsForCacheCleaner();
            }
            $tags = [];
            foreach ($relationsToClean as $relationKey) {
                if (!static::getStructure()->hasRelation($relationKey)) {
                    throw new \InvalidArgumentException("Model has no relation named $relationKey");
                }
                /** @var CmfDbTable|TaggedCacheForDbSelects $model */
                $model = static::getStructure()->getRelation($relationKey)->getForeignTable();
                $tags[] = $model::getModelCachePrefix();
            }
            if (!empty($tags)) {
                \Cache::tags($tags)->flush();
            }
        }
    }
    
    /**
     * @return array - relations names
     */
    static protected function getDefaultRelationsForCacheCleaner() {
        return array_keys(static::getStructure()->getRelations());
    }
    
    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function cleanSelectManyCache($cleanRelatedModelsCache = null) {
        \Cache::tags(static::getSelectManyCacheTag())->flush();
        static::cleanRelatedModelsCache($cleanRelatedModelsCache);
    }
    
    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function cleanSelectOneCache($cleanRelatedModelsCache = null) {
        \Cache::tags(static::getSelectOneCacheTag())->flush();
        static::cleanRelatedModelsCache($cleanRelatedModelsCache);
    }
    
    /**
     * @param int|string|array|CmfDbRecord|Record $record
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function cleanRecordCache($record, $cleanRelatedModelsCache = null) {
        if (!($record instanceof Record) || $record->existsInDb()) {
            \Cache::tags(static::getRecordCacheTag($record))->flush();
        }
        static::cleanRelatedModelsCache($cleanRelatedModelsCache);
    }
    
    /**
     * @return string
     */
    static public function getSelectManyCacheTag() {
        return static::getModelCachePrefix() . 'many';
    }
    
    /**
     * @return string
     */
    static public function getSelectOneCacheTag() {
        return static::getModelCachePrefix() . 'one';
    }
    
    /**
     * @param mixed $record
     * @return string
     */
    static public function getRecordCacheTag($record) {
        /** @var CmfDbTable|TaggedCacheForDbSelects $this */
        if ($record instanceof Record) {
            $id = $record->existsInDb() ? $record->getPrimaryKeyValue() : null;
        } else if (!is_array($record)) {
            $id = $record;
        } else if (!empty($record[static::getPkColumnName()])) {
            $id = $record[static::getPkColumnName()];
        } else if (!empty($record[static::getAlias()]) && !empty($record[static::getAlias()][static::getPkColumnName()])) {
            $id = $record[static::getAlias()][static::getPkColumnName()];
        }
        if (empty($id)) {
            throw new \UnexpectedValueException('Data passed to getRecordCacheTag() has no value for primary key');
        }
        return static::getModelCachePrefix() . 'id=' . $id;
    }
    
    /**
     * Get data from cache or put data from $callback to cache
     * @param bool $affectsSingleRecord
     * @param array $cacheSettings - [
     *      'key' => 'string, cache key',
     *      'timeout' => 'int (minutes or unix timestamp), \DateTime, null (infinite)',
     *      'tags' => ['custom', 'cache', 'tags'],
     *      'recache' => 'bool, ignore cached data and replace it with fresh data'
     * ]
     * @param callable $callback
     * @return array
     * @throws \BadMethodCallException
     */
    static protected function _getCachedData($affectsSingleRecord, array $cacheSettings, callable $callback) {
        $data = empty($cacheSettings['recache']) ? \Cache::get($cacheSettings['key'], '{!404!}') : '{!404!}';
        if ($data === '{!404!}') {
            $data = $callback();
            if ($data instanceof Record) {
                $data = $data->existsInDb() ? $data->toArray() : [];
            }
            $tags = $cacheSettings['tags'];
            $tags[] = static::getModelCachePrefix();
            if ($affectsSingleRecord) {
                $tags[] = static::getSelectOneCacheTag();
                if (!empty($data) && is_array($data) && !empty($data[static::getPkColumnName()])) {
                    // create tag only for record with primary key value
                    $tags[] = static::getRecordCacheTag($data);
                }
            } else {
                $tags[] = static::getSelectManyCacheTag();
            }
            $cacher = \Cache::tags($tags);
            if (empty($cacheSettings['timeout'])) {
                $cacher->forever($cacheSettings['key'], $data);
            } else {
                $cacher->put($cacheSettings['key'], $data, $cacheSettings['timeout']);
            }
        }
        return $data;
    }
    
    /**
     * @param bool $affectsSingleRecord
     * @param null|string|array $columns
     * @param null|array $conditionsAndOptions
     * @return string
     */
    static public function buildDefaultCacheKey($affectsSingleRecord, $columns, $conditionsAndOptions) {
        $prefix = $affectsSingleRecord ? static::getSelectOneCacheTag() : static::getSelectManyCacheTag();
        return $prefix . '.' . static::buildCacheKey($columns, $conditionsAndOptions);
    }
    
    
}
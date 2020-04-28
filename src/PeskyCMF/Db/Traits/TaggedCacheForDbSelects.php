<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Db\CmfDbObject;
use PeskyORM\ORM\Record;

trait TaggedCacheForDbSelects {

    use CacheForDbSelects;

    /**
     * Detect if caching is possible
     * @return bool
     */
    protected function cachingIsPossible() {
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
    public function cleanModelCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getModelCachePrefix())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * Clean cache for all related models
     * @param bool|null|array|string $cleanRelatedModelsCache -
     *      - array: list of relations to clean
     *      - string: single relation to clean
     *      - bool: true = clean all relations provided by $this->getDefaultRelationsForCacheCleaner(); false - don't clean relations
     *      - null: if ($this->canCleanRelationsCache() === true) then clean $this->getDefaultRelationsForCacheCleaner()
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function cleanRelatedModelsCache($cleanRelatedModelsCache = true) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        if ($cleanRelatedModelsCache === null) {
            $cleanRelatedModelsCache = $this->canCleanRelationsCache();
        }
        if ($cleanRelatedModelsCache) {
            if (is_array($cleanRelatedModelsCache)) {
                $relationsToClean = $cleanRelatedModelsCache;
            } else if (is_string($cleanRelatedModelsCache)) {
                $relationsToClean = [$cleanRelatedModelsCache];
            } else {
                $relationsToClean = $this->getDefaultRelationsForCacheCleaner();
            }
            $tags = [];
            foreach ($relationsToClean as $relationKey) {
                if (!$this->hasTableRelation($relationKey)) {
                    throw new \InvalidArgumentException("Model has no relation named $relationKey");
                }
                /** @var CmfDbModel|TaggedCacheForDbSelects $model */
                $model = $this->getTableRealtaion($relationKey)->getForeignTable();
                $tags[] = $model->getModelCachePrefix();
            }
            if (!empty($tags)) {
                \Cache::tags($tags)->flush();
            }
        }
    }

    /**
     * @return array - relations names
     */
    protected function getDefaultRelationsForCacheCleaner() {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        return array_keys($this->getTableRealtaions());
    }

    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function cleanSelectManyCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getSelectManyCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function cleanSelectOneCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getSelectOneCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param int|string|array|CmfDbObject|Record $record
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function cleanRecordCache($record, $cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        if (!($record instanceof Record) || $record->existsInDb()) {
            \Cache::tags($this->getRecordCacheTag($record))->flush();
        }
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @return string
     */
    public function getSelectManyCacheTag() {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        return $this->getModelCachePrefix() . 'many';
    }

    /**
     * @return string
     */
    public function getSelectOneCacheTag() {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        return $this->getModelCachePrefix() . 'one';
    }

    /**
     * @param mixed $record
     * @return string
     */
    public function getRecordCacheTag($record) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        if ($record instanceof Record) {
            $id = $record->existsInDb() ? $record->getPrimaryKeyValue() : null;
        } else if (!is_array($record)) {
            $id = $record;
        } else if (!empty($record[static::getPkColumnName()])) {
            $id = $record[static::getPkColumnName()];
        } else if (!empty($record[$this->getTableAlias()]) && !empty($record[$this->getTableAlias()][static::getPkColumnName()])) {
            $id = $record[$this->getTableAlias()][static::getPkColumnName()];
        }
        if (empty($id)) {
            throw new \UnexpectedValueException('Data passed to getRecordCacheTag() has no value for primary key');
        }
        return $this->getModelCachePrefix() . 'id=' . $id;
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
     *
     * @return array
     * @throws \BadMethodCallException
     */
    protected function _getCachedData($affectsSingleRecord, array $cacheSettings, callable $callback) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        $data = empty($cacheSettings['recache']) ? \Cache::get($cacheSettings['key'], '{!404!}') : '{!404!}';
        if ($data === '{!404!}') {
            $data = $callback();
            if ($data instanceof Record) {
                $data = $data->existsInDb() ? $data->toArray() : [];
            }
            $tags = $cacheSettings['tags'];
            $tags[] = $this->getModelCachePrefix();
            if ($affectsSingleRecord) {
                $tags[] = $this->getSelectOneCacheTag();
                if (!empty($data) && is_array($data) && !empty($data[static::getPkColumnName()])) {
                    // create tag only for record with primary key value
                    $tags[] = $this->getRecordCacheTag($data);
                }
            } else {
                $tags[] = $this->getSelectManyCacheTag();
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
    public function buildDefaultCacheKey($affectsSingleRecord, $columns, $conditionsAndOptions) {
        $prefix = $affectsSingleRecord ? $this->getSelectOneCacheTag() : $this->getSelectManyCacheTag();
        return $prefix . '.' . static::buildCacheKey($columns, $conditionsAndOptions);
    }


}
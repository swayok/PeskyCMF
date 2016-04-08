<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyORM\DbObject;
use PeskyORM\Exception\DbModelException;

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
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function cleanModelCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getModelCachePrefix())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * Clean cache for all related models
     * @param bool|null $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     */
    public function cleanRelatedModelsCache($cleanRelatedModelsCache = true) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        if ($cleanRelatedModelsCache === null) {
            $cleanRelatedModelsCache = $this->canCleanRelationsCache();
        }
        if ($cleanRelatedModelsCache) {
            \Cache::tags(array_keys($this->getTableRealtaions()))->flush();
        }
    }

    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function cleanSelectManyCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getSelectManyCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param bool $cleanRelatedModelsCache
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function cleanSelectOneCache($cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getSelectOneCacheTag())->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @param int|string|array|Object $record
     * @param bool $cleanRelatedModelsCache
     * @throws DbModelException
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \PeskyORM\Exception\DbObjectException
     */
    public function cleanRecordCache($record, $cleanRelatedModelsCache = null) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        \Cache::tags($this->getRecordCacheTag($record))->flush();
        $this->cleanRelatedModelsCache($cleanRelatedModelsCache);
    }

    /**
     * @return string
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     */
    public function getSelectManyCacheTag() {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        return $this->getModelCachePrefix() . 'many';
    }

    /**
     * @return string
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     */
    public function getSelectOneCacheTag() {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
        return $this->getModelCachePrefix() . 'one';
    }

    /**
     * @param mixed $record
     * @return string
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws DbModelException
     */
    public function getRecordCacheTag($record) {
        /** @var CmfDbModel|TaggedCacheForDbSelects $this */
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
        return $this->getModelCachePrefix() . 'id=' . $id;
    }

    /**
     * Get data from cache or put data from $callback to cache
     * @param bool $isSingleRecord
     * @param array $cacheSettings
     * @param callable $callback
     * @return array
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     * @throws \BadMethodCallException
     */
    protected function _getCachedData($isSingleRecord, array $cacheSettings, callable $callback) {
        $data = \Cache::get($cacheSettings['key'], '{!404!}');
        if ($data === '{!404!}') {
            $data = $callback();
            $tags = $cacheSettings['tags'];
            $tags[] = $this->getModelCachePrefix();
            if ($isSingleRecord) {
                $tags[] = $this->getSelectOneCacheTag();
                $tags[] = $this->getRecordCacheTag($data);
            } else {
                $tags[] = $this->getSelectManyCacheTag();
            }
            $cacher = \Cache::tags($tags);
            if ($data instanceof DbObject) {
                $data = $data->toPublicArray();
            }
            if (empty($cacheSettings['timeout'])) {
                $cacher->forever($cacheSettings['key'], $data);
            } else {
                $cacher->put($cacheSettings['key'], $data, $cacheSettings['timeout']);
            }
        }
        return $data;
    }

    /**
     * @param bool $isSingleRecord
     * @param null|string|array $columns
     * @param null|array $conditionsAndOptions
     * @return string
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     */
    public function buildDefaultCacheKey($isSingleRecord, $columns, $conditionsAndOptions) {
        $prefix = $isSingleRecord ? $this->getSelectOneCacheTag() : $this->getSelectManyCacheTag();
        return $prefix . '.' . static::buildCacheKey($columns, $conditionsAndOptions);
    }


}
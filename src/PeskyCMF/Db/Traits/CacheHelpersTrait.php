<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\CmfDbTable;

trait CacheHelpersTrait {

    /**
     * Generate cache key from $baseKey for current class and DB Object id (if current class instance of \App\Db\BaseDbObject)
     * @param string|array $baseKey - use method name; array - will be jsoned and hashed
     * @param string|array|null $suffix
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public function generateCacheKey($baseKey, $suffix = null) {
        if (is_array($baseKey)) {
            asort($baseKey);
            $baseKey = \Hash::make(json_encode($baseKey, JSON_UNESCAPED_UNICODE));
        }
        $key = get_class($this) . '->' . $baseKey;
        if ($this instanceof CmfDbRecord) {
            $key .= '-' . $this::getTableStructure()->getSchema();
            $key .= '-id-' . $this->getPrimaryKeyValue();
        } else if ($this instanceof CmfDbTable) {
            $key .= '-' . $this::getTableStructure()->getSchema();
        }
        if (!empty($suffix)) {
            if (is_array($suffix)) {
                asort($suffix);
                $suffix = \Hash::make(json_encode($suffix, JSON_UNESCAPED_UNICODE));
            }
            $key .= '-' . $suffix;
        }
        return $key;
    }

    /**
     * Return data cached via $callback call
     * @param string|array $baseKey - use method name; array - will be jsoned and hashed
     * @param int|\DateTime $minutes
     * @param \Closure $dataCallback
     * @param string|array|null $cacheKeySuffix
     * @param bool $recache - true: update cache forcefully
     * @return mixed
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function cachedData($baseKey, $minutes, \Closure $dataCallback, $cacheKeySuffix = '', $recache = false) {
        $cacheKay = $this->generateCacheKey($baseKey, $cacheKeySuffix);
        if ($recache) {
            \Cache::forget($cacheKay);
        }
        return \Cache::remember($cacheKay, $minutes, $dataCallback);
    }

    /**
     * @param string|array $baseKey
     * @param string|array|null $cacheKeySuffix
     * @return bool
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function removeCachedData($baseKey, $cacheKeySuffix = '') {
        return \Cache::forget($this->generateCacheKey($baseKey, $cacheKeySuffix));
    }

    /**
     * @param string|array $baseKey - use method name; array - will be jsoned and hashed
     * @param string|array|null $cacheKeySuffix
     * @param mixed $default
     * @return mixed
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getDataFromCache($baseKey, $cacheKeySuffix = '', $default = null) {
        $cacheKay = $this->generateCacheKey($baseKey, $cacheKeySuffix);
        return \Cache::get($cacheKay, $default);
    }

}
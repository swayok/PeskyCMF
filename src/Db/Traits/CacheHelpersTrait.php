<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Db\CmfDbObject;

trait CacheHelpersTrait {

    /**
     * Generate cache key from $baseKey for current class and DB Object id (if current class instance of \App\Db\BaseDbObject)
     * @param string|array $baseKey - use method name; array - will be jsoned and hashed
     * @param string|array|null $suffix
     * @return string
     */
    public function generateCacheKey($baseKey, $suffix = null) {
        if (is_array($baseKey)) {
            asort($baseKey);
            $baseKey = \Hash::make(json_encode($baseKey, JSON_UNESCAPED_UNICODE));
        }
        $key = get_class($this) . '->' . $baseKey;
        if ($this instanceof CmfDbObject) {
            $key .= '-id-' . $this->_getPkValue();
            $key .= '-' . $this->_getTableConfig()->getSchema();
        } else if ($this instanceof CmfDbModel) {
            $key .= '-' . $this->getTableConfig()->getSchema();
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
     * @param callable $callback
     * @param string|array|null $cacheKeySuffix
     * @param bool $recache - true: update cache forcefully
     * @return mixed
     */
    public function cacheData($baseKey, $minutes, callable $callback, $cacheKeySuffix = '', $recache = false) {
        $cacheKay = $this->generateCacheKey($baseKey, $cacheKeySuffix);
        if ($recache) {
            \Cache::forget($cacheKay);
        }
        return \Cache::remember($cacheKay, $minutes, $callback);
    }

    /**
     * @param string|array $baseKey
     * @param string|array|null $cacheKeySuffix
     * @return bool
     */
    public function removeCache($baseKey, $cacheKeySuffix) {
        return \Cache::forget($this->generateCacheKey($baseKey, $cacheKeySuffix));
    }

}
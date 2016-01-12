<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;

trait CacheableDbObject {

    protected $_cacheOnceTimeout = false;

    /**
     * Set cache timeout for next read() or find() method call
     * @param null|int|\DateTime $timeout - int: minutes
     * @return $this
     */
    public function withCacheTimeout($timeout = null) {
        /** @var CmfDbObject|CacheableDbObject $this */
        $this->_cacheOnceTimeout = empty($timeout)
            ? $this->_getModel()->getDefaultCacheDurationForSelectOneInMinutes()
            : $timeout;
        return $this;
    }

    /**
     * @return $this
     */
    public function find($conditions, $fieldNames = '*', $relations = array()) {
        if ($this->_cacheOnceTimeout !== false) {
            if (!is_array($conditions)) {
                $conditions = empty($conditions) ? [] : [$conditions];
            }
            $conditions['CACHE'] = ['timeout' => $this->_cacheOnceTimeout];
            $this->_cacheOnceTimeout = false;
        }
        return parent::find($conditions, $fieldNames, $relations);
    }

    /**
     * @return $this
     */
    public function reload($fieldNames = '*', $relations = null) {
        $this->_cacheOnceTimeout = false;
        return parent::reload($fieldNames, $relations);
    }
}
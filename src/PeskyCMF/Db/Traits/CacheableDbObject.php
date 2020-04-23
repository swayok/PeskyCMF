<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;

trait CacheableDbObject {

    protected $_cacheOnceTimeout = false;

    /**
     * Set cache timeout for next read() or find() method call
     * @param null|int|\DateTime $timeout - int: seconds
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
     * @param $conditions
     * @param string $fieldNames
     * @param array $relations
     * @return $this
     */
    public function fromDb($conditions, $fieldNames = '*', $relations = array()) {
        if ($this->_cacheOnceTimeout !== false) {
            if (!is_array($conditions)) {
                $conditions = empty($conditions) ? [] : [$conditions];
            }
            $conditions['CACHE'] = ['timeout' => $this->_cacheOnceTimeout];
            $this->_cacheOnceTimeout = false;
        }
        return parent::fromDb($conditions, $fieldNames === '*' ? [] : (array)$fieldNames, $relations);
    }

    /**
     * @param string $fieldNames
     * @param null $relations
     * @return $this
     */
    public function reload($fieldNames = '*', $relations = null) {
        $this->_cacheOnceTimeout = false;
        return parent::reload($fieldNames, $relations);
    }
}
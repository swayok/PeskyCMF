<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbRecord;

trait CacheableRecord {

    protected $_cacheOnceTimeout = false;

    /**
     * Set cache timeout for next read() or find() method call
     * @param null|int|\DateTime $timeout - int: minutes
     * @return $this
     */
    public function withCacheTimeout($timeout = null) {
        /** @var CmfDbRecord|CacheableRecord $this */
        $this->_cacheOnceTimeout = empty($timeout)
            ? $this->getTable()->getDefaultCacheDurationForSelectOneInMinutes()
            : $timeout;
        return $this;
    }

    /**
     * @param array $conditionsAndOptions
     * @param array $columns
     * @param array $readRelatedRecords
     * @return $this
     */
    public function fromDb(array $conditionsAndOptions, array $columns = [], array $readRelatedRecords = []) {
        if ($this->_cacheOnceTimeout !== false) {
            $conditionsAndOptions['CACHE'] = ['timeout' => $this->_cacheOnceTimeout];
            $this->_cacheOnceTimeout = false;
        }
        return parent::fromDb($conditionsAndOptions, $columns, $readRelatedRecords);
    }

    /**
     * @param array $columns
     * @param array $readRelatedRecords
     * @return $this
     */
    public function reload(array $columns = [], array $readRelatedRecords = []) {
        $this->_cacheOnceTimeout = false;
        return parent::reload($columns, $readRelatedRecords);
    }
}
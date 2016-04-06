<?php

namespace PeskyCMF\Db\Traits;

trait AutoCacheableDbModel {

    use CacheForDbSelects;

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectOneQueries() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectManyQueries() {
        return true;
    }

}
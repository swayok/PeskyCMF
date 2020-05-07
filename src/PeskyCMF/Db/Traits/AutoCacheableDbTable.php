<?php

namespace PeskyCMF\Db\Traits;

trait AutoCacheableDbTable {

    use CacheForDbSelects;

    /**
     * Override to change default value
     * @return boolean
     */
    static public function canAutoCacheSelectOneQueries() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    static public function canAutoCacheSelectManyQueries() {
        return true;
    }

}
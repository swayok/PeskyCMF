<?php

namespace PeskyCMF\Db\Traits;

trait AutoCacheableDbTable {

    use CacheForDbSelects;

    /**
     * Override to change default value
     * @return boolean
     */
    public static function canAutoCacheSelectOneQueries() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public static function canAutoCacheSelectManyQueries() {
        return true;
    }

}
<?php

namespace PeskyCMF\Db\Traits;

trait AutoCacheableDbModel {

    use CacheableDbModel;

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
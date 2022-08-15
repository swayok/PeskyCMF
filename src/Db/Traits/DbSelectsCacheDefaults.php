<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsCacheDefaults {

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select one" queries
     * @return int
     */
    public static function getDefaultCacheDurationForSelectOneInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select many" queries
     * @return int
     */
    public static function getDefaultCacheDurationForSelectManyInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public static function canCleanRelationsCache() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public static function canAutoCacheSelectOneQueries() {
        return false;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public static function canAutoCacheSelectManyQueries() {
        return false;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    public static function getAutoCacheTimeoutForSelectOneInMinutes() {
        return 10;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    public static function getAutoCacheTimeoutForSelectManyInMinutes() {
        return 10;
    }

}
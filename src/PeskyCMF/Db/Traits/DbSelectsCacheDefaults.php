<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsCacheDefaults {

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select one" queries
     * @return int
     */
    public function getDefaultCacheDurationForSelectOneInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * (NOT FOR AUTO CACHEING)
     * Default cache timeout for "select many" queries
     * @return int
     */
    public function getDefaultCacheDurationForSelectManyInMinutes() {
        return 1440;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canCleanRelationsCache() {
        return true;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectOneQueries() {
        return false;
    }

    /**
     * Override to change default value
     * @return boolean
     */
    public function canAutoCacheSelectManyQueries() {
        return false;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    public function getAutoCacheTimeoutForSelectOneInMinutes() {
        return 10;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    public function getAutoCacheTimeoutForSelectManyInMinutes() {
        return 10;
    }

}
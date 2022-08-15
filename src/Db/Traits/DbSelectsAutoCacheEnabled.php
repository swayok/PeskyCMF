<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsAutoCacheEnabled {

    use DbSelectsCacheDefaults;

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
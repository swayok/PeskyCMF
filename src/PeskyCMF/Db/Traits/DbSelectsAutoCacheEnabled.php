<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsAutoCacheEnabled {

    use DbSelectsCacheDefaults;

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

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    static public function getAutoCacheTimeoutForSelectOneInMinutes() {
        return 10;
    }

    /**
     * Override to change default value
     * This is used only when $this->isAutoCacheForSelectAllAllowed() === true
     * @return int
     */
    static public function getAutoCacheTimeoutForSelectManyInMinutes() {
        return 10;
    }

}
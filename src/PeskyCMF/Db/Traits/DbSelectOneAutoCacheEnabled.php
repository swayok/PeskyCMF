<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectOneAutoCacheEnabled {

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
     * This is used only when $this->isAutoCacheForSelectOneAllowed() === true
     * @return int
     */
    static public function getAutoCacheTimeoutForSelectOneInMinutes() {
        return 10;
    }

}
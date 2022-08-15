<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsAutoCacheDisabled {

    use DbSelectsCacheDefaults;

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

}
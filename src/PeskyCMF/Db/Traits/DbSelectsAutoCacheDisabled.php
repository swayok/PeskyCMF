<?php

namespace PeskyCMF\Db\Traits;

trait DbSelectsAutoCacheDisabled {

    use DbSelectsCacheDefaults;

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

}
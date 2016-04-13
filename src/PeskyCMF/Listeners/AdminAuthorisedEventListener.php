<?php

namespace PeskyCMF\Listeners;

use PeskyCMF\Event\AdminAuthorised;
use PeskyORM\Db;
use PeskyORM\DbObject;

class AdminAuthorisedEventListener {

    public function handle(AdminAuthorised $event) {
        /** @var DbObject $user */
        $user = $event->user;
        if ($user->_hasField('timezone')) {
            $user->_getModel()->getDataSource()->onConnect(function (Db $db) use ($user) {
                $db->setTimezone($user->timezone);
            });
            date_default_timezone_set($user->timezone);
        }
    }
}

<?php

namespace PeskyCMF\Listeners;

use PeskyCMF\Event\AdminAuthorised;
use PeskyORM\Core\DbAdapterInterface;
use PeskyORM\ORM\Record;

class AdminAuthorisedEventListener {

    public function handle(AdminAuthorised $event) {
        /** @var Record $user */
        $user = $event->user;
        if ($user::hasColumn('timezone') && $user->hasValue('timezone')) {
            $timezone = $user->getValue('timezone');
            if (!empty($timezone)) {
                $user::getTable()->getConnection()->onConnect(function (DbAdapterInterface $adapter) use ($timezone) {
                    $adapter->setTimezone($timezone);
                });
                date_default_timezone_set($timezone);
            }
        }
    }
}

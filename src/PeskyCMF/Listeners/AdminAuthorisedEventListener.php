<?php

namespace PeskyCMF\Listeners;

use App\Db\Admin\Admin;
use PeskyCMF\Event\AdminAuthorised;
use PeskyORM\Core\DbAdapterInterface;

class AdminAuthorisedEventListener {

    public function handle(AdminAuthorised $event) {
        /** @var Admin $user */
        $user = $event->user;
        if ($user::hasColumn('timezone')) {
            $user::getTable()->getConnection(true)->onConnect(function (DbAdapterInterface $db) use ($user) {
                $db->setTimezone($user->timezone);
            });
            date_default_timezone_set($user->timezone);
        }
    }
}

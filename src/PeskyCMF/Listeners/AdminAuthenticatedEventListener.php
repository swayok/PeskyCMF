<?php

namespace PeskyCMF\Listeners;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthenticated;
use PeskyORM\Core\DbAdapterInterface;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\ORM\Record;

class AdminAuthenticatedEventListener {

    public function handle(AdminAuthenticated $event) {
        /** @var Record $user */
        $user = $event->user;
        if ($user::hasColumn('language') && $user->hasValue('language')) {
            CmfConfig::getPrimary()->setLocale($user->getValue('language'));
        }
        if ($user::hasColumn('timezone') && $user->hasValue('timezone')) {
            $timezone = $user->getValue('timezone');
            if (!empty($timezone)) {
                $fn = function (DbAdapterInterface $adapter) use ($timezone) {
                    $adapter->setTimezone($timezone);
                };
                foreach (DbConnectionsManager::getAll() as $connection) {
                    $connection->onConnect($fn);
                }
                date_default_timezone_set($timezone);
            }
        }
    }
}

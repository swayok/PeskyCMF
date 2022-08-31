<?php

declare(strict_types=1);

namespace PeskyCMF\Listeners;

use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyORM\Core\DbAdapterInterface;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\ORM\Record;

class CmfUserAuthenticatedEventListener
{
    
    public function handle(CmfUserAuthenticated $event): void
    {
        /** @var Record $user */
        $user = $event->user;
        if ($user::hasColumn('language') && $user->hasValue('language')) {
            $event->cmfConfig->setLocale($user->getValue('language'));
        }
        if ($user::hasColumn('timezone') && $user->hasValue('timezone')) {
            $timezone = $user->getValue('timezone');
        } elseif ($user::hasColumn('time_zone') && $user->hasValue('time_zone')) {
            $timezone = $user->getValue('time_zone');
        }
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

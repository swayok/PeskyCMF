<?php

declare(strict_types=1);

namespace PeskyCMF\Listeners;

use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyORM\Adapter\DbAdapterInterface;
use PeskyORM\Config\Connection\DbConnectionsFacade;

class CmfUserAuthenticatedEventListener
{
    public function handle(CmfUserAuthenticated $event): void
    {
        $user = $event->user;
        $tableStructure = $user->getTable()->getTableStructure();
        if ($tableStructure->hasColumn('language') && $user->hasValue('language')) {
            $event->cmfConfig->setLocale($user->getValue('language'));
        }
        if ($tableStructure->hasColumn('timezone') && $user->hasValue('timezone')) {
            $timezone = $user->getValue('timezone');
        } elseif ($tableStructure->hasColumn('time_zone') && $user->hasValue('time_zone')) {
            $timezone = $user->getValue('time_zone');
        }
        if (!empty($timezone)) {
            $fn = function (DbAdapterInterface $adapter) use ($timezone) {
                $adapter->setTimezone($timezone);
            };
            foreach (DbConnectionsFacade::getRegisteredConnectionsNames() as $connectionName) {
                DbConnectionsFacade::getConnection($connectionName)->onConnect($fn);
            }
            date_default_timezone_set($timezone);
        }
    }
}

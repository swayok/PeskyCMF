<?php

namespace PeskyCMF\Listeners;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthorised;
use PeskyCMF\Http\Controllers\CmfGeneralController;
use PeskyORM\Core\DbAdapterInterface;
use PeskyORM\ORM\Record;

class AdminAuthorisedEventListener {

    public function handle(AdminAuthorised $event) {
        /** @var Record $user */
        $user = $event->user;
        if ($user::hasColumn('language') && $user->hasValue('language')) {
            CmfConfig::getPrimary()->setLocale($user->getValue('language'));
        }
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

<?php

namespace PeskyCMF\Console\Commands;

use PeskyCMF\Console\BaseDbClassesInstallCommand;

class CmfInstallSettings extends BaseDbClassesInstallCommand {

    /**
     * Suffix for command name and templates dir name.
     * Command name is "cmf::install-{suffix}"
     * Default templates path is "{cmf_path}/resources/views/install/db/{suffix}"
     * For example if suffix is "admins" command name will be "cmf::install-admins" and
     * templates path will be "{cmf_path}/resources/views/install/db/admins"
     * @return string
     */
    protected function getCommandSuffix() {
        return 'settings';
    }
}
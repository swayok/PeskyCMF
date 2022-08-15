<?php

namespace PeskyCMF\Console\Commands;

use PeskyCMF\Db\HttpRequestStats\CmfHttpRequestStatsScaffoldConfig;
use PeskyCMF\Db\HttpRequestStats\CmfHttpRequestStatsTableStructure;
use PeskyCMF\Http\Middleware\RequestProfiling;

class CmfInstallHttpRequestsProfilingCommand  extends CmfCommand {

    protected $description = 'Install HTTP requests profiling';

    protected $signature = 'cmf:install-http-requests-profiling';

    public function fire() {
        // compatibility with Laravel <= 5.4
        $this->handle();
    }

    public function handle() {
        $this->addMigrationForTable(
            CmfHttpRequestStatsTableStructure::getTableName(),
            database_path('migrations') . DIRECTORY_SEPARATOR
        );

        $this->line('Next steps:');
        $this->line('1. Run "php artisan migrate" to create table in you database to store logs there;');
        $this->line('2. Add "' . RequestProfiling::class . '" middleware to the routes you want to profile;');
        $this->line('3. Add "' . CmfHttpRequestStatsScaffoldConfig::class . '" to "resources" key in your cmf config (by default: config/peskycmf.php).');
        $this->line('Note: In order to profile all sql queries you may need to place middleware 1st in the list or call PeskyOrmPdoProfiler::init() in AppServiceProvider->register().');
        $this->line('Review middleware class to find out what arguments it accepts for custom configuration of profiling');
    }


}
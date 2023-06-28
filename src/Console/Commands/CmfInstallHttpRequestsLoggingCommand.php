<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsScaffoldConfig;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsTableStructure;
use PeskyCMF\Http\Middleware\LogHttpRequest;

class CmfInstallHttpRequestsLoggingCommand extends CmfCommand
{
    protected $description = 'Install HTTP requests logging';

    protected $signature = 'cmf:install-http-requests-logging';

    public function handle(): int
    {
        $this->addMigrationForTable(
            CmfHttpRequestLogsTableStructure::getTableName(),
            database_path('migrations') . DIRECTORY_SEPARATOR
        );

        $this->line('Next steps:');
        $this->line('1. Run "php artisan migrate" to create table in you database to store logs there;');
        $this->line('2. Add "' . LogHttpRequest::class . '" middleware to the routes you want to log;');
        $this->line(
            '3. Add "' . CmfHttpRequestLogsScaffoldConfig::class
            . '" to "resources" key in your cmf config (by default: config/peskycmf.php).'
        );
        $this->line(
            'Review middleware class to find out what arguments it accepts for custom configuration of logging'
        );
        return 0;
    }
}

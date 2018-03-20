<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PeskyCMF\Db\Admins\CmfAdminsTable;

class CmfHttpRequestLogsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmfHttpRequestLogsTableStructure::getTableName())) {
            \Schema::create(CmfHttpRequestLogsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('requester_id')->unsigned()->nullable();
                $table->string('requester_table')->nullable();
                $table->string('requester_info')->nullable();
                $table->string('url', 500);
                $table->string('http_method', 10);
                $table->ipAddress('ip');
                $table->string('filter', 200);
                $table->string('section', 50);
                $table->integer('response_code')->unsigned()->nullable();
                $table->string('response_type', 200)->nullable();
                $table->json('request');
                $table->text('response')->nullable();
                $table->text('debug')->nullable();
                $table->string('table', 150)->nullable();
                $table->integer('item_id')->unsigned()->nullable();
                $table->json('data_before')->nullable();
                $table->json('data_after')->nullable();
                $currentTimestamp = \DB::raw(CmfAdminsTable::quoteDbExpr(CmfAdminsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp);
                $table->timestampTz('responded_at')->nullable();

                $table->index('response_code');
                $table->index('section');
                $table->index('filter');
                $table->index('requester_table');
                $table->index('requester_info');
                $table->index('requester_id');

            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmfHttpRequestLogsTableStructure::getTableName());
    }
}

<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CmfHttpRequestLogsMigration extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable($this->getTableName())) {
            Schema::create($this->getTableName(), static function (Blueprint $table) {
                $table->bigIncrements('id');
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
                $table->date('creation_date')->default(DB::raw('NOW()'));
                $table->timestampTz('created_at')->default(DB::raw('NOW()'));
                $table->timestampTz('responded_at')->nullable();

                $table->index('response_code');
                $table->index('section');
                $table->index('filter');
                $table->index('requester_table');
                $table->index('requester_info');
                $table->index('requester_id');
                $table->index('creation_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }

    protected function getTableName(): string
    {
        return (new CmfHttpRequestLogsTableStructure())->getTableName();
    }
}

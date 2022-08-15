<?php

namespace PeskyCMF\Db\HttpRequestStats;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmfHttpRequestStatsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmfHttpRequestStatsTableStructure::getTableName())) {
            \Schema::create(CmfHttpRequestStatsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('http_method');
                $table->string('url');
                $table->string('route');
                $table->timestampTz('created_at')->default(\DB::raw('NOW()'));
                $table->float('duration', 10, 6);
                $table->float('duration_sql', 10, 6);
                $table->float('duration_error', 10, 6);
                $table->float('memory_usage_mb', 10, 6);
                $table->integer('http_code');
                $table->boolean('is_cache')->default(false);

                if (config('database.connections.' . ($this->getConnection() ?: config('database.default'))  . '.driver') === 'pgsql') {
                    $table->jsonb('url_params')->default('{}');
                    $table->jsonb('sql')->default('{}');
                    $table->jsonb('request_data')->default('{}');
                    $table->jsonb('checkpoints')->default('{}');
                    $table->jsonb('counters')->default('{}');
                } else {
                    $table->mediumText('url_params')->default('{}');
                    $table->text('sql')->default('{}');
                    $table->text('request_data')->default('{}');
                    $table->text('checkpoints')->default('{}');
                    $table->text('counters')->default('{}');
                }

                $table->index('url');
                $table->index('route');
                $table->index('is_cache');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmfHttpRequestStatsTableStructure::getTableName());
    }
}

<?php

namespace PeskyCMF\Db\HttpRequestStats;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class HttpRequestStatsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(HttpRequestStatsTableStructure::getTableName())) {
            \Schema::create(HttpRequestStatsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('http_method');
                $table->string('url');
                $table->string('route');
                $table->timestampTz('created_at')->default(\DB::raw('NOW()'));
                $table->float('duration', 8, 3);
                $table->float('duration_sql', 8, 3);
                $table->float('memory_usage_mb', 8, 3);
                $table->integer('http_code');
                $table->boolean('is_cache')->default(false);

                if (config('database.connections.' . ($this->getConnection() ?: config('database.default'))  . '.driver') === 'pgsql') {
                    $table->jsonb('url_params')->default('{}');
                    $table->jsonb('sql')->default('{}');
                } else {
                    $table->mediumText('url_params')->default('{}');
                    $table->text('sql')->default('{}');
                }

                $table->index('url');
                $table->index('route');
                $table->index('is_cache');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(HttpRequestStatsTableStructure::getTableName());
    }
}

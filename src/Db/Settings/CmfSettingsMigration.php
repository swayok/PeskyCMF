<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CmfSettingsMigration extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable($this->getTableName())) {
            Schema::create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $connectionName = $this->getConnection() ?: config('database.default');
                $dbDriver = config("database.connections.{$connectionName}.driver");
                if ($dbDriver === 'pgsql') {
                    $table->jsonb('value')->nullable();
                } else {
                    $table->mediumText('value')->nullable();
                }

                $table->unique('key');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }

    protected function getTableName(): string
    {
        return (new CmfSettingsTableStructure())->getTableName();
    }
}

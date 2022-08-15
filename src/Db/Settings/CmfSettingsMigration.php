<?php

namespace PeskyCMF\Db\Settings;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmfSettingsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmfSettingsTableStructure::getTableName())) {
            \Schema::create(CmfSettingsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');

                if (config('database.connections.' . ($this->getConnection() ?: config('database.default')) . '.driver') === 'pgsql') {
                    $table->jsonb('value')->nullable();
                } else {
                    $table->mediumText('value')->nullable();
                }

                $table->unique('key');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmfSettingsTableStructure::getTableName());
    }
}

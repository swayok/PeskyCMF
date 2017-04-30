<?php

namespace PeskyCMF\CMS\Settings;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmsSettingsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmsSettingsTableStructure::getTableName())) {
            \Schema::create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');

                if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
                    $table->jsonb('value');
                } else {
                    $table->mediumText('value');
                }

                $table->unique('key');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmsSettingsTableStructure::getTableName());
    }
}

<?php

namespace PeskyCMF\CMS\Settings;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PeskyCMF\CMS\Admins\CmsAdminsTableStructure;

class CmsSettingsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmsSettingsTableStructure::getTableName())) {
            \Schema::create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->integer('admin_id')->nullable()->unsigned();
                if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
                    $table->jsonb('value');
                } else {
                    $table->mediumText('value');
                }

                $table->unique('key');

                $table->foreign('admin_id')
                    ->references('id')
                    ->on(CmsAdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmsSettingsTableStructure::getTableName());
    }
}

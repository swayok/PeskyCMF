<?php

namespace PeskyCMF\CMS\Admins;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmsAdminsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmsAdminsTableStructure::getTableName())) {
            \Schema::create(CmsAdminsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable()->unsigned();
                $table->string('name')->default('');
                $table->string('email')->nullable();
                $table->string('login')->nullable();
                $table->string('password');
                $table->string('ip', 40)->nullable();
                $table->boolean('is_superadmin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('role', 50)->default(CmsAdminsTableStructure::getColumn('role')->getDefaultValueAsIs());
                $table->char('language', 2)->default(CmsAdminsTableStructure::getColumn('language')->getDefaultValueAsIs());
                $currentTimestamp = \DB::raw(CmsAdminsTable::quoteDbExpr(CmsAdminsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp);
                $table->timestampTz('updated_at')->default($currentTimestamp);
                $table->string('timezone')->nullable();
                $table->rememberToken();

                $table->index('parent_id');
                $table->index('password');
                $table->index('created_at');
                $table->index('updated_at');
                $table->unique('email');
                $table->unique('login');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(CmsAdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmsAdminsTableStructure::getTableName());
    }
}
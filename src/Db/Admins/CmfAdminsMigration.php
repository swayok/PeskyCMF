<?php

namespace PeskyCMF\Db\Admins;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmfAdminsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmfAdminsTableStructure::getTableName())) {
            \Schema::create(CmfAdminsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable()->unsigned();
                $table->string('name')->default('');
                $table->string('email')->nullable();
                $table->string('login')->nullable();
                $table->string('password');
                $table->string('ip', 40)->nullable();
                $table->boolean('is_superadmin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('role', 50)->default(CmfAdminsTableStructure::getColumn('role')->getDefaultValueAsIs());
                $table->char('language', 2)->default(CmfAdminsTableStructure::getColumn('language')->getDefaultValueAsIs());
                $table->timestampTz('created_at')->default(\DB::raw('NOW()'));
                $table->timestampTz('updated_at')->default(\DB::raw('NOW()'));
                $table->string('timezone')->nullable();
                $table->rememberToken();

                $table->index('parent_id');
                $table->index('is_active');
                $table->unique('email');
                $table->unique('login');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(CmfAdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmfAdminsTableStructure::getTableName());
    }
}
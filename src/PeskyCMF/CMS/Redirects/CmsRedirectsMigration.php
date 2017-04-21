<?php

namespace PeskyCMF\CMS\Redirects;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PeskyCMF\CMS\Admins\CmsAdminsTableStructure;
use PeskyCMF\CMS\Pages\CmsPagesTableStructure;

class CmsRedirectsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmsRedirectsTableStructure::getTableName())) {
            \Schema::create(CmsRedirectsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('page_id')->nullable()->unsigned();
                $table->integer('admin_id')->nullable()->unsigned();
                $table->string('relative_url', 500);
                $table->boolean('is_permanent')->default(true);

                $currentTimestamp = \DB::raw(CmsRedirectsTable::quoteDbExpr(CmsRedirectsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp);
                $table->timestampTz('updated_at')->default($currentTimestamp);

                $table->index('created_at');
                $table->index('updated_at');
                $table->unique('relative_url');

                $table->foreign('page_id')
                    ->references('id')
                    ->on(CmsPagesTableStructure::getTableName())
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('admin_id')
                    ->references('id')
                    ->on(CmsAdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        \Schema::dropIfExists(CmsRedirectsTableStructure::getTableName());
    }
}

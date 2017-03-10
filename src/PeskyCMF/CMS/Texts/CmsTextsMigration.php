<?php

namespace PeskyCMF\CMS\Texts;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PeskyCMF\CMS\Admins\CmsAdminsTableStructure;
use PeskyCMF\CMS\Pages\CmsPagesTableStructure;

class CmsTextsMigration extends Migration {

    public function up() {
        if (!\Schema::hasTable(CmsTextsTableStructure::getTableName())) {
            \Schema::create(CmsTextsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('page_id')->nullable()->unsigned();
                $table->integer('admin_id')->nullable()->unsigned();
                $table->char('language', 2);
                $table->string('title')->default('');
                $table->string('browser_title')->default('');
                $table->string('menu_title')->default('');
                $table->string('comment', 1000)->default('');
                $table->mediumText('content')->nullable();
                $table->string('meta_description', 1000)->default('');
                $table->string('meta_keywords', 500)->default('');
                $currentTimestamp = \DB::raw(CmsTextsTable::quoteDbExpr(CmsTextsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp);
                $table->timestampTz('updated_at')->default($currentTimestamp);

                if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
                    $table->jsonb('custom_info');
                } else {
                    $table->text('custom_info');
                }

                $table->index('parent_id');
                $table->index('created_at');
                $table->index('updated_at');
                $table->unique(['page_id', 'language']);

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
        \Schema::dropIfExists(CmsTextsTableStructure::getTableName());
    }
}
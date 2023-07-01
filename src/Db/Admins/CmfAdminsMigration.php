<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PeskyORM\ORM\TableStructure\TableStructureInterface;

class CmfAdminsMigration extends Migration
{
    public function up(): void
    {
        $tableStructure = $this->getTableStructure();
        if (!Schema::hasTable($tableStructure->getTableName())) {
            Schema::create(
                $tableStructure->getTableName(),
                function (Blueprint $table) use ($tableStructure) {
                    $table->increments('id');
                    $table->integer('parent_id')->nullable()->unsigned();
                    $table->string('name')->default('');
                    $table->string('email')->nullable();
                    $table->string('login')->nullable();
                    $table->string('password');
                    $table->string('ip', 40)->nullable();
                    $table->boolean('is_superadmin')->default(false);
                    $table->boolean('is_active')->default(true);
                    $table->string('role', 50)->default(
                        $tableStructure->getColumn('role')->getDefaultValue()
                    );
                    $table->char('language', 2)->default(
                        $tableStructure->getColumn('language')->getDefaultValue()
                    );
                    $table->timestampTz('created_at')->default(DB::raw('NOW()'));
                    $table->timestampTz('updated_at')->default(DB::raw('NOW()'));
                    $table->string('timezone')->nullable();
                    $table->rememberToken();

                    $table->index('parent_id');
                    $table->index('is_active');
                    $table->unique('email');
                    $table->unique('login');

                    $table->foreign('parent_id')
                        ->references('id')
                        ->on($tableStructure->getTableName())
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableStructure()->getTableName());
    }

    protected function getTableStructure(): TableStructureInterface
    {
        return new CmfAdminsTableStructure();
    }
}

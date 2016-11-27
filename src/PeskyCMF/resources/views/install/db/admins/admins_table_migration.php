<?php echo "<?php\n"; ?>

use App\<?php echo $dbClassesAppSubfolder ?>\Admins\AdminsTable;
use App\<?php echo $dbClassesAppSubfolder ?>\Admins\AdminsTableStructure;
use App\<?php echo $sectionName; ?>\AdminConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration {

    public function up() {
        if (!Schema::hasTable(AdminsTableStructure::getTableName())) {
            Schema::create(AdminsTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable()->unsigned();
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->string('ip', 40)->nullable();
                $table->boolean('is_superadmin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('role', 50)->default(AdminConfig::default_role());
                $table->char('language', 2)->default(AdminConfig::default_locale());
                $currentTimestamp = DB::raw(AdminsTable::quoteDbExpr(AdminsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp);
                $table->timestampTz('updated_at')->default($currentTimestamp);
                $table->string('timezone')->nullable();
                $table->rememberToken();

                $table->index('parent_id');
                $table->index('password');
                $table->index('created_at');
                $table->index('updated_at');
                $table->unique('email');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(AdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(AdminsTableStructure::getTableName());
    }
}

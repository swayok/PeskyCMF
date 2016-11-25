<?php echo "<?php\n"; ?>

use App\<?php echo $dbClassesAppSubfolder ?>\Admins\AdminsTable;
use App\<?php echo $sectionName; ?>\AdminConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration {

    public function up() {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('parent_id')
                    ->nullable()
                    ->index()
                    ->foreign()
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password')->index();
                $table->string('ip', 40);
                $table->boolean('is_superadmin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('role', 50)->default(AdminConfig::default_role());
                $table->char('language', 2)->default(AdminConfig::default_locale());
                $currentTimestamp = DB::raw(AdminsTable::quoteDbExpr(AdminsTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
                $table->timestampTz('created_at')->default($currentTimestamp)->index();
                $table->timestampTz('updated_at')->default($currentTimestamp)->index();
                $table->string('timezone')->nullable();
                $table->rememberToken();
            });
        }
    }

    public function down() {
        Schema::dropIfExists('admins');
    }
}

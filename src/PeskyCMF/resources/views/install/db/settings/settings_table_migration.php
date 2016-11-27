<?php echo "<?php\n"; ?>

use App\<?php echo $dbClassesAppSubfolder ?>\Settings\SettingsTableStructure;
use App\<?php echo $dbClassesAppSubfolder ?>\Admins\AdminsTableStructure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingsTable extends Migration {

    public function up() {
        if (!Schema::hasTable(SettingsTableStructure::getTableName())) {
            Schema::create('settings', function (Blueprint $table) {
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
                    ->on(AdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(SettingsTableStructure::getTableName());
    }
}
<?php echo "<?php\n"; ?>

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

    public function up() {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
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
        Schema::dropIfExists('settings');
    }
}
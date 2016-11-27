<?php echo "<?php\n"; ?>

use App\<?php echo $dbClassesAppSubfolder ?>\Pages\PagesTable;
use App\<?php echo $dbClassesAppSubfolder ?>\Pages\PagesTableStructure;
use App\<?php echo $dbClassesAppSubfolder ?>\Admins\AdminsTableStructure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration {

    public function up() {
        if (!Schema::hasTable(PagesTableStructure::getTableName())) {
            Schema::create(PagesTableStructure::getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable()->unsigned();
                $table->integer('admin_id')->nullable()->unsigned();
                $table->string('title');
                $table->string('browser_title')->default('');
                $table->string('menu_title')->default('');
                $table->string('type', 50)->default('page');
                $table->string('comment', 1000)->default('');
                $table->string('url_alias')->nullable();
                $table->string('page_code')->nullable();
                $table->text('content')->nullable();
                if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
                    $table->jsonb('images')->default('{}');
                } else {
                    $table->mediumText('images')->nullable();
                }

                $table->string('meta_description', 1000)->default('');
                $table->string('meta_keywords', 500)->default('');
                $table->integer('order')->nullable();
                $table->boolean('with_contact_form')->default(false);
                $table->boolean('is_published')->default(true);
                $currentTimestamp = DB::raw(PagesTable::quoteDbExpr(PagesTable::getCurrentTimeDbExpr()->setWrapInBrackets(false)));
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
                $table->index('order');
                $table->index('is_published');
                $table->unique('url_alias');
                $table->unique('page_code');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on(PagesTableStructure::getTableName())
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('admin_id')
                    ->references('id')
                    ->on(AdminsTableStructure::getTableName())
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(PagesTableStructure::getTableName());
    }
}

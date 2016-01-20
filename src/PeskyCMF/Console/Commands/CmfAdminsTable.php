<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfAdminsTable extends BaseCommand {

    protected $description = 'Create migration class that will create admins table in DB';
    protected $signature = 'cmf:admins_table {--without-trigger}';

    public function fire() {
        $folder = Folder::load(database_path('/migrations'), true, 0755);
        $dontRenderTrigger = !!$this->input->getOption('without-trigger');
        if (!$dontRenderTrigger) {
            File::load($folder->pwd() . '/' . date('Y_m_d_His', time()) . '_create_timestsmp_renew_trigger_function.php', true, 0755, 0644)
                ->write(view('cmf::install.db.create_timestsmp_renew_trigger_function_migration')->render());
        }
        File::load($folder->pwd() . '/' . date('Y_m_d_His', time() + 1) . '_create_admins_table.php', true, 0755, 0644)
            ->write(view('cmf::install.db.create_admins_table_migration', ['withoutTrigger' => $dontRenderTrigger])->render());
        $this->line('Done');
        $this->line('Run [artisan migrate] (and [composer dump-autoload] if migration classes not found)');
    }
}
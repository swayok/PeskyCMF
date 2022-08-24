<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyORMLaravel\Db\LaravelUploadedTempFileInfo;
use Swayok\Utils\Folder;

class CmfCleanUploadedTempFilesCommand extends Command {

    protected $description = 'Clean old uploaded temp files';

    protected $signature = 'cmf:clean-uploaded-temp-files';

    public function handle() {
        $this->line('> Deleting old uploaded temp files');
        $dir = Folder::load(LaravelUploadedTempFileInfo::getUploadsTempFolder());
        if ($dir->exists()) {
            /** @var array $subdirs */
            [$subdirs] = $dir->read(true, ['.', '..'], false);
            $currentFolder = LaravelUploadedTempFileInfo::getSubfolderName();
            foreach ($subdirs as $name) {
                if ($name !== $currentFolder) {
                    Folder::remove($dir->pwd() . '/' . $name);
                    $this->line('+ Removed folder: ' . $dir->pwd() . '/' . $name);
                }
            }
        }
    }
}

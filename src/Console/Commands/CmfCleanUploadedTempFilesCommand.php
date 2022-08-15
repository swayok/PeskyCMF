<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Scaffold\Form\UploadedTempFileInfo;
use Swayok\Utils\Folder;

class CmfCleanUploadedTempFilesCommand extends Command {

    protected $description = 'Clean old uploaded temp files';

    protected $signature = 'cmf:clean-uploaded-temp-files';

    public function handle() {
        $this->line('> Deleting old uploaded temp files');
        $dir = Folder::load(UploadedTempFileInfo::getUploadsTempFolder());
        if ($dir->exists()) {
            /** @var array $subdirs */
            [$subdirs] = $dir->read(true, ['.', '..'], false);
            $currentFolder = UploadedTempFileInfo::getSubfolderName();
            foreach ($subdirs as $name) {
                if ($name !== $currentFolder) {
                    Folder::remove($dir->pwd() . '/' . $name);
                    $this->line('+ Removed folder: ' . $dir->pwd() . '/' . $name);
                }
            }
        }
    }
}

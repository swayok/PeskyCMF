<?php

namespace PeskyCMF\Console\Commands;

use Symfony\Component\Process\Process;

trait OpenFileInPhpStormTrait {

    protected function openFileInPhpStorm($filePath) {
        // make sure path to phpstorm binary is present in Windows' PATH environment variable
        (new Process('', base_path()))
            ->setTimeout(null)
            ->setCommandLine('PhpStorm.exe ' . base_path() . ' ' . $filePath)
            ->run();
    }
}
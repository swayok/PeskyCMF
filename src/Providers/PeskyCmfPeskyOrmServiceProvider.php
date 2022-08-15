<?php

namespace PeskyCMF\Providers;

use PeskyORMLaravel\Providers\PeskyOrmServiceProvider;

class PeskyCmfPeskyOrmServiceProvider extends PeskyOrmServiceProvider {

    protected function configurePublishes() {
        // configs file is publised by PeskyCmfServiceProvider or PeskyCmsServiceProvider
    }
}
<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use PeskyORMLaravel\Providers\PeskyOrmServiceProvider;

class PeskyCmfPeskyOrmServiceProvider extends PeskyOrmServiceProvider
{
    
    protected function configurePublishes(): void
    {
        // configs file is publised by PeskyCmfServiceProvider or PeskyCmsServiceProvider
    }
}
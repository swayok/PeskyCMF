<?php

namespace PeskyCMF\Providers;

use Illuminate\Database\DatabaseServiceProvider;

class SuppressLaravelDatabaseServiceProvider extends DatabaseServiceProvider {

    public function boot() {
        // skip
    }

    protected function registerEloquentFactory() {
        // skip
    }

}
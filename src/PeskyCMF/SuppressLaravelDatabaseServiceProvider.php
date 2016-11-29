<?php

namespace PeskyCMF;

use Illuminate\Database\DatabaseServiceProvider;

class SuppressLaravelDatabaseServiceProvider extends DatabaseServiceProvider {

    public function boot() {
        // skip
    }

    protected function registerEloquentFactory() {
        // skip
    }

}
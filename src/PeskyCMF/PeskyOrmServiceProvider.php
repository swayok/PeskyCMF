<?php

namespace PeskyCMF;

use Auth;
use DebugBar\DataCollector\PDO\PDOCollector;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Db\Field\PasswordField;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbConnectionConfig;

class PeskyOrmServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $connection = config('database.default');
        CmfDbModel::setDbConnectionConfig(
            DbConnectionConfig::create()
                ->setDriver(config("database.connections.$connection.driver"))
                ->setHost(config("database.connections.$connection.host"))
                ->setPort(config("database.connections.$connection.port"))
                ->setDbName(config("database.connections.$connection.database"))
                ->setUserName(config("database.connections.$connection.username"))
                ->setPassword(config("database.connections.$connection.password"))
        );
        DbColumnConfig::registerType('password', DbColumnConfig::DB_TYPE_VARCHAR, PasswordField::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        Auth::provider('peskyorm', function($app, array $config) {
            return new PeskyOrmUserProvider($config['model']);
        });
    }
}

<?php

namespace PeskyCMF;

use Auth;
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
     * @throws \PeskyORM\Exception\DbConnectionConfigException
     */
    public function boot() {
        $driver = config('database.default');
        CmfDbModel::setDbConnectionConfig(
            DbConnectionConfig::create()
                ->setDriver($driver)
                ->setHost(config("database.connections.$driver.host"))
                ->setDbName(config("database.connections.$driver.database"))
                ->setUserName(config("database.connections.$driver.username"))
                ->setPassword(config("database.connections.$driver.password"))
        );
        DbColumnConfig::registerType('password', DbColumnConfig::DB_TYPE_VARCHAR, PasswordField::class);

        /*if (app()->offsetExists('debugbar') && debugbar()->isEnabled()) {
            $timeCollector = (debugbar()->hasCollector('time')) ? debugbar()->getCollector('time') : null;
            $pdoCollector = new PDOCollector(null, $timeCollector);
            $pdoCollector->setRenderSqlWithParams(true);
            debugbar()->addCollector($pdoCollector);
            Db::setConnectionWrapper(function (Db $db, \PDO $pdo) {
                $pdoTracer = new TraceablePDO($pdo);
                if (debugbar()->hasCollector('pdo')) {
                    debugbar()->getCollector('pdo')->addConnection($pdoTracer, $db->getDbName());
                }
                return $pdoTracer;
            });
        }*/
    }

    /**
     * Register any application services.
     *
     * @return void
     * @throws \PeskyORM\Exception\DbUtilsException
     */
    public function register() {
        Auth::provider('peskyorm', function($app, array $config) {
            return new PeskyOrmUserProvider($config['model']);
        });
    }
}

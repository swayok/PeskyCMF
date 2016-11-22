<?php

namespace PeskyCMF;

use Auth;
use Illuminate\Support\ServiceProvider;
use PeskyORM\Config\Connection\MysqlConfig;
use PeskyORM\Config\Connection\PostgresConfig;
use PeskyORM\Core\DbAdapter;
use PeskyORM\Core\DbAdapterInterface;
use PeskyORM\Core\DbConnectionsManager;

class PeskyOrmServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function boot() {
        $driver = config('database.default');
        switch ($driver) {
            case 'pgsql':
                $config = new PostgresConfig(
                    config("database.connections.$driver.database"),
                    config("database.connections.$driver.username"),
                    config("database.connections.$driver.password")
                );
                break;
            case 'mysql':
                $config = new MysqlConfig(
                    config("database.connections.$driver.database"),
                    config("database.connections.$driver.username"),
                    config("database.connections.$driver.password")
                );
                break;
            default:
                return;
        }
        $host = config("database.connections.$driver.host");
        if ($host) {
            $config->setDbHost($host);
        }
        $port = config("database.connections.$driver.port");
        if ($port) {
            $config->setDbPort($port);
        }
        DbConnectionsManager::createConnection('default', $driver, $config);

        $this->addPdoCollectorForDebugbar();
    }

    protected function addPdoCollectorForDebugbar() {
        if (config('app.debug', false) && app()->offsetExists('debugbar') && debugbar()->isEnabled()) {
            $timeCollector = debugbar()->hasCollector('time') ? debugbar()->getCollector('time') : null;
            $pdoCollector = new DebugBar\DataCollector\PDO\PDOCollector(null, $timeCollector);
            $pdoCollector->setRenderSqlWithParams(true);
            debugbar()->addCollector($pdoCollector);
            DbAdapter::setConnectionWrapper(function (DbAdapterInterface $adapter, \PDO $pdo) {
                $pdoTracer = new PeskyOrmPdoTracer($pdo);
                if (debugbar()->hasCollector('pdo')) {
                    debugbar()->getCollector('pdo')->addConnection(
                        $pdoTracer,
                        $adapter->getConnectionConfig()->getDbName()
                    );
                }
                return $pdoTracer;
            });
        }
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

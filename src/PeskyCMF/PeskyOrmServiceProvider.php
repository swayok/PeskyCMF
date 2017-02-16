<?php

namespace PeskyCMF;

use Auth;
use Illuminate\Support\ServiceProvider;
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
        $connectionConfig = config("database.connections.$driver");
        if (!empty($connectionConfig['password'])) {
            DbConnectionsManager::createConnectionFromArray($driver, $connectionConfig);
            DbConnectionsManager::addAlternativeNameForConnection($driver, 'default');
        }
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

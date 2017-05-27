<?php

namespace PeskyCMF\Providers;

use Auth;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
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
                $pdoTracer = new \PeskyCMF\Db\PeskyOrmPdoTracer($pdo);
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
        Auth::provider('peskyorm', function($app, $config) {
            return new PeskyOrmUserProvider(array_get($config, 'model', CmfConfig::getPrimary()->user_object_class()));
        });

        \App::singleton('peskyorm.connection', function () {
            DbConnectionsManager::getConnection('default');
        });
    }

    public function provides() {
        return ['peskyorm.connection'];
    }
}

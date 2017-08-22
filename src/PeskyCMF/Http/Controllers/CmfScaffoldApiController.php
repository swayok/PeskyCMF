<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\TableInterface;

class CmfScaffoldApiController extends Controller {

    use AuthorizesRequests;

    protected $tableNameForRoutes;
    protected $table;
    protected $scaffoldConfig;

    /**
     * @return TableInterface
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function getTable() {
        if ($this->table === null) {
            $this->table = CmfConfig::getPrimary()->getTableByUnderscoredName($this->getTableNameForRoutes());
        }
        return $this->table;
    }

    /**
     * @return ScaffoldConfig
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     */
    public function getScaffoldConfig() {
        if ($this->scaffoldConfig === null) {
            $cmfConfig = CmfConfig::getPrimary();
            $customScaffoldConfig = $cmfConfig::getScaffoldConfig($this->getTable(), $this->getTableNameForRoutes());
            if ($customScaffoldConfig instanceof ScaffoldConfig) {
                $this->scaffoldConfig = $customScaffoldConfig;
            } else {
                throw new \UnexpectedValueException(
                    get_class($cmfConfig) . '::getScaffoldConfig() must instance of ScaffoldConfig class. '
                        . (is_object($customScaffoldConfig) ? get_class($customScaffoldConfig) : gettype($customScaffoldConfig))
                        . ' Received'
                );
            }
        }
        return $this->scaffoldConfig;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getTableNameForRoutes() {
        if ($this->tableNameForRoutes === null) {
            $tableName = CmfConfig::getPrimary()->getTableNameFromCurrentRoute();
            if (empty($tableName)) {
                abort(404, 'Table name not found in route');
            }
            $this->tableNameForRoutes = $tableName;
        }
        return $this->tableNameForRoutes;
    }

    public function __construct() {

    }

    public function getTemplates() {
        return $this->getScaffoldConfig()->renderTemplates();
    }

    public function getItemsList() {
        return $this->getScaffoldConfig()->getRecordsForDataGrid();
    }

    public function getItem($tableName, $id = null) {
        return $this->getScaffoldConfig()->getRecordValues($id);
    }

    public function getItemDefaults() {
        return $this->getScaffoldConfig()->getDefaultValuesForFormInputs();
    }

    public function getOptions() {
        return $this->getScaffoldConfig()->getHtmlOptionsForFormInputs();
    }

    public function addItem() {
        return $this->getScaffoldConfig()->addRecord();
    }

    public function updateItem() {
        return $this->getScaffoldConfig()->updateRecord();
    }

    public function updateBulk() {
        return $this->getScaffoldConfig()->updateBulkOfRecords();
    }

    public function deleteItem($tableName, $id) {
        return $this->getScaffoldConfig()->deleteRecord($id);
    }

    public function deleteBulk() {
        return $this->getScaffoldConfig()->deleteBulkOfRecords();
    }

    public function getCustomData($tableName, $dataId) {
        $this->authorize('resource.view', [$tableName]);
        return $this->getScaffoldConfig()->getCustomData($dataId);
    }

}

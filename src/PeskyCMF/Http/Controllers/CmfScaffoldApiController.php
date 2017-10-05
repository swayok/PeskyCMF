<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\TableInterface;

class CmfScaffoldApiController extends Controller {

    use AuthorizesRequests;

    protected $requestedResourceName;
    protected $table;
    protected $scaffoldConfig;

    /**
     * @return TableInterface
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \InvalidArgumentException
     */
    public function getTable() {
        return $this->getScaffoldConfig()->getTable();
    }

    /**
     * @return ScaffoldConfig
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function getScaffoldConfig() {
        if ($this->scaffoldConfig === null) {
            $cmfConfig = CmfConfig::getPrimary();
            $customScaffoldConfig = $cmfConfig::getScaffoldConfig($this->getRequestedResourceName());
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
    public function getRequestedResourceName() {
        if ($this->requestedResourceName === null) {
            $tableName = CmfConfig::getPrimary()->getResourceNameFromCurrentRoute();
            if (empty($tableName)) {
                abort(404, 'Table name not found in route');
            }
            $this->requestedResourceName = $tableName;
        }
        return $this->requestedResourceName;
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

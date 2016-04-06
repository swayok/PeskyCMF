<?php

namespace PeskyCMF\Db;

use PeskyCMF\Db\Traits\CacheForDbSelects;
use PeskyCMF\Db\Traits\CacheHelpersTrait;
use PeskyORM\DbObject;

abstract class CmfDbObject extends DbObject {

    use CacheHelpersTrait;

    protected $_autoAddPkValueToPublicArray = false;
    protected $_baseModelClass = CmfDbModel::class;

    protected function _loadModel() {
        $modelClass = get_called_class() . call_user_func([$this->_baseModelClass, 'getModelClassSuffix']);
        return call_user_func([$modelClass, 'getInstance']);
    }

    /**
     * Needed for IDE autocompletion
     * @return CmfDbModel|CacheForDbSelects
     */
    public function _getModel() {
        return parent::_getModel();
    }

    /**
     * @param array $values
     * @param bool|false $withLabels
     * @param string $translationsPath
     * @param bool|false $asValueLabelPair
     * @return array
     */
    static public function toOptions(array $values, $withLabels = false, $translationsPath = '', $asValueLabelPair = false) {
        if (!$withLabels) {
            return $values;
        }
        $options = array();
        foreach ($values as $value) {
            $label = trans($translationsPath . $value);
            if ($asValueLabelPair) {
                $options[$value] = $label;
            } else {
                $options[] = ['label' => $label, 'value' => $value];
            }
        }
        return $options;
    }

}
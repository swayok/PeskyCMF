<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbObject;

trait UserDataSavingHelper {

    public function save($verifyDbExistance = false, $createIfNotExists = false, $saveRelations = false) {
        $this->processUserDataBeforeSave();
        return parent::save($verifyDbExistance, $createIfNotExists, $saveRelations);
    }

    /**
     * 1. Test if password is empty and skips its saving for user update action
     * 2. normalizes user's login field
     * @throws \PeskyORM\Exception\DbObjectException
     */
    protected function processUserDataBeforeSave() {
        /** @var UserDataSavingHelper|CmfDbObject $this */
        $passwordField = $this->_getField('password');
        if ($this->exists() && !$passwordField->hasNotEmptyValue()) {
            // to avoid overwriting by empty or already hashed password
            $passwordField->resetValue();
        }
        $loginFieldName = $this->getUserLoginField();
        $loginField = $this->_getField($loginFieldName);
        if ($loginField->hasNotEmptyValue()) {
            $this->_setFieldValue($loginFieldName, mb_strtolower(trim($this->_getFieldValue($loginFieldName))));
        }
    }

    protected function getUserLoginField() {
        return CmfConfig::getInstance()->user_login_column();
    }

}
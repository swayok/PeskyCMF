<?php

namespace PeskyCMF\Db\Field;

use PeskyORM\DbObjectField\StringField;

class PasswordField extends StringField {

    protected function doBasicValueValidationAndConvertion($value) {
        if (!empty($value)) {
            $value = $this->hashPassword($value);
        }
        return parent::doBasicValueValidationAndConvertion($value);
    }

    public function canBeSkipped() {
        return $this->isValueReceivedFromDb() || parent::canBeSkipped();
    }

    public function setValueReceivedFromDb($fromDb = true) {
        if ($this->hasValue() && $fromDb) {
            $this->values['isDbValue'] = true;
            // reset hashing done by doBasicValueValidationAndConvertion
            $this->values['dbValue'] = $this->values['value'] = $this->values['rawValue'];
        } else {
            parent::setValueReceivedFromDb(false);
        }
        return $this;
    }

    public function valueWasSavedToDb() {
        if (!$this->isVirtual() && $this->hasValue()) {
            $this->values['isDbValue'] = true;
            $this->values['dbValue'] = $this->values['rawValue'] = $this->values['value'];
        }
        return $this;
    }

    public function setValue($value, $isDbValue = false) {
        // prevent cleaning password by update via form submit
        if (empty($value) && !$isDbValue && $this->isValueReceivedFromDb()) {
            return $this;
        }
        return parent::setValue($value, $isDbValue);
    }

    /**
     * @param $password
     * @return string
     */
    public function hashPassword($password) {
        if ($this->dbColumnConfig->hasHashFunction()) {
            return call_user_func($this->dbColumnConfig->getHashFunction(), $password);
        } else {
            return sha1($password);
        }
    }

}
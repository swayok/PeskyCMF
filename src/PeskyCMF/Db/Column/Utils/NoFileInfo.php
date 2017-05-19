<?php

namespace PeskyCMF\Db\Column\Utils;

use PeskyORM\ORM\RecordInterface;

class NoFileInfo extends FileInfo {

    static public function fromArray(array $fileInfo, FileConfig $fileConfig, RecordInterface $record) {
        throw new \BadMethodCallException('You should not call this method. NoFileInfo class is designed to be dummy.');
    }

    static public function fromSplFileInfo(\SplFileInfo $fileInfo, FileConfig $fileConfig, RecordInterface $record, $fileSuffix = null) {
        throw new \BadMethodCallException('You should not call this method. NoFileInfo class is designed to be dummy.');
    }

    static public function create() {
        return new static();
    }

    /** @noinspection PhpMissingParentConstructorInspection */
    /** @noinspection MagicMethodsValidityInspection */
    public function __construct() {

    }

    public function getAbsoluteFilePath() {
        return null;
    }

    public function getAbsoluteUrl() {
        return null;
    }

    public function getRelativeUrl() {
        return null;
    }

    public function getFileName() {
        return null;
    }

    public function getFileSuffix() {
        return null;
    }

    public function exists() {
        return false;
    }

    public function getCustomInfo() {
        return null;
    }

    public function getSplFileInfo() {
        return null;
    }

    public function collectImageInfoForDb() {
        throw new \BadMethodCallException('You should not call this method. NoFileInfo class is designed to be dummy.');
    }


}
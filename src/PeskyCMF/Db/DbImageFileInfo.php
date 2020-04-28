<?php


namespace PeskyCMF\Db;

use PeskyCMF\Db\DbColumnConfig\ImageColumnConfig;
use PeskyORM\ORM\RecordValue;
use Swayok\Utils\ImageUtils;

/**
 * @property ImageColumnConfig $column
 */
class DbImageFileInfo extends DbFileInfo {

    protected $filesNames = [];

    public function __construct(RecordValue $valueContainer) {
        $this->jsonMap['files_names'] = 'filesNames';
        parent::__construct($valueContainer);
    }

    /**
     * @return array
     */
    public function getFilesNames() {
        return $this->filesNames;
    }

    /**
     * @param array $filesNames
     * @return $this
     */
    public function setFilesNames($filesNames) {
        $this->filesNames = $filesNames;
        return $this;
    }

    /**
     * @param null|string $versionName
     * @return string|array
     */
    public function getFilePath($versionName = null) {
        return $this->column->getImageVersionPath($this->record, $versionName);
    }

    /**
     * @param null|string $versionName
     * @return string
     */
    public function getAbsoluteFileUrl($versionName = null) {
        return $this->column->getAbsoluteFileUrl($this->valueContainer, $versionName);
    }
    
    /**
     * @param string $versionName
     * @return bool|string
     */
    public function restoreImageVersion($versionName, $ext = null) {
        $configs = $this->column->getImageVersionsConfigs();
        if (empty($configs[$versionName])) {
            return false;
        }
        return ImageUtils::restoreVersionForConfig(
            $versionName,
            $configs[$versionName],
            $this->getFileNameWithoutExtension(),
            $this->column->getFileDirPath($this->record),
            $ext
        );
    }

}
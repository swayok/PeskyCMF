<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\ImagesColumn;
use PeskyORMLaravel\Db\Column\Utils\FileConfig;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;

/**
 * @method ImagesColumn|FilesColumn getTableColumn()
 */
class ImagesValueCell extends ValueCell {

    /** @var FileConfig[]|null */
    protected $fileConfigsToShow;
    /** @var string  */
    protected $templateForDefaultRenderer = 'cmf::item_details.images';

    /**
     * List of image names to display.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array $imageNames
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImagesToShow(...$imageNames) {
        if (empty($imageNames)) {
            throw new \InvalidArgumentException('$imageNames argument cannot be empty');
        }
        if (count($imageNames) === 1 && isset($imageNames[0]) && is_array($imageNames[0])) {
            $imageNames = $imageNames[0];
        }
        $this->fileConfigsToShow = $imageNames;
        return $this;
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (empty($value) || !is_array($value)) {
            return [];
        }
        $column = $this->getTableColumn();
        $object = $this
            ->getScaffoldSectionConfig()
            ->getTable()
            ->newRecord()
            ->enableTrustModeForDbData()
            ->fromData($record, true, false);
        $ret = [];
        foreach ($this->fileConfigsToShow as $configName) {
            $preparedInfo = [];
            /** @var FileInfo[] $filesForCofing */
            $filesForCofing = $object->getValue($column, $configName);
            foreach ($filesForCofing as $fileInfo) {
                if ($fileInfo->exists()) {
                    $preparedInfo[] = [
                        'name' => $fileInfo->getFileNameWithExtension(),
                        'original_name' => $fileInfo->getOriginalFileNameWithExtension(),
                        'url' => $fileInfo->getAbsoluteUrl()
                    ];
                }
            }
            if (!empty($preparedInfo)) {
                $ret[] = [
                    'config_name' => $configName,
                    'label' => $this->getScaffoldSectionConfig()->translate($this, $configName),
                    'files' => $preparedInfo
                ];
            }
        }
        return $ret;
    }

}
<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORMLaravel\Db\Column\FileColumn;
use PeskyORMLaravel\Db\Column\ImageColumn;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use PeskyORMLaravel\Db\Column\Utils\FilesGroupConfig;

/**
 * @method ImageColumn|FileColumn getTableColumn()
 */
class ImageValueCell extends ValueCell {

    /** @var FilesGroupConfig[]|null */
    protected $fileConfigsToShow;
    /** @var string  */
    protected $templateForDefaultRenderer = 'cmf::item_details.image';

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
        /** @var FileInfo[] $filesForCofing */
        $fileInfo = $object->getValue($column, 'file_info');
        if ($fileInfo->exists()) {
            return [
                'url' => $fileInfo->getAbsoluteUrl()
            ];
        }
        return null;
    }

}
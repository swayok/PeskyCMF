<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORMLaravel\Db\Column\FileColumn;
use PeskyORMLaravel\Db\Column\ImageColumn;
use PeskyORMLaravel\Db\Column\Utils\FilesGroupConfig;

/**
 * @method ImageColumn|FileColumn getTableColumn()
 */
class ImageValueCell extends ValueCell {

    /** @var FilesGroupConfig[]|null */
    protected $fileConfigsToShow;
    /** @var string  */
    protected ?string $templateForDefaultRenderer = 'cmf::item_details.image';

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
            ->enableReadOnlyMode()
            ->fromData($record, true, false);
        $url = $object->getValue($column, 'url');
        if ($url) {
            return [
                'url' => $url
            ];
        }
        return null;
    }

}
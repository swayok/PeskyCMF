<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORMColumns\Column\Files\MetadataImagesColumn;
use PeskyORMColumns\Column\Files\Utils\DbImageFileInfo;

/**
 * @method MetadataImagesColumn getTableColumn()
 */
class ImageValueCell extends ValueCell
{
    
    protected string $imageVersion = 'source';
    
    protected ?string $templateForDefaultRenderer = 'cmf::item_details.image';
    
    public function doDefaultValueConversionByType($value, string $type, array $record): ?array
    {
        $column = $this->getTableColumn();
        if (empty($value) || !is_array($value) || !$column->isItAnImage()) {
            return [];
        }
        if (!array_key_exists($column->getMetadataColumnName(), $record)) {
            throw new \UnexpectedValueException(
                'You need to select ' . $column->getMetadataColumnName() . ' column to access images list'
            );
        }
        $object = $this
            ->getScaffoldSectionConfig()
            ->getTable()
            ->newRecord()
            ->enableTrustModeForDbData()
            ->enableReadOnlyMode()
            ->fromData($record, true, false);
        
        /** @var DbImageFileInfo $fileInfo */
        $fileInfo = $object->getValue($column);
        if ($fileInfo instanceof DbImageFileInfo && $fileInfo->isFileExists()) {
            return [
                'url' => $fileInfo->getAbsoluteFileUrl(),
            ];
        }
        return [];
    }
    
    /**
     * @return static
     */
    public function setImageVerstion(string $imageVersion)
    {
        if (!$this->getTableColumn()->hasImageVersionConfig($imageVersion)) {
            throw new \InvalidArgumentException(
                "There is no image version config called [$imageVersion] in DB column ["
                . $this->getTableColumn()->getName() . ']'
            );
        }
        $this->imageVersion = $imageVersion;
        return $this;
    }
    
}
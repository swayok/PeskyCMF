<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\ItemDetails;

class JsonArrayValueCell extends ValueCell
{
    
    protected ?string $templateForDefaultRenderer = 'cmf::item_details.json_array_table';
    protected array $jsonKeys = [];
    
    protected array $valueContainerAttributes = [
        'class' => 'pn bg-white',
    ];
    
    /**
     * @param array $jsonKeys - ordering matters!
     */
    public function setJsonKeys(array $jsonKeys): static
    {
        $this->jsonKeys = $jsonKeys;
        return $this;
    }
    
    public function getJsonKeys(): array
    {
        return $this->jsonKeys;
    }
    
    public function getTableHeaders(): array
    {
        $headers = [];
        foreach ($this->jsonKeys as $jsonKey) {
            $headers[] = $this->getScaffoldSectionConfig()->translate($this, 'header.' . $jsonKey);
        }
        return $headers;
    }
    
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record)
    {
        return is_array($value) ? $value : json_decode($value);
    }
}
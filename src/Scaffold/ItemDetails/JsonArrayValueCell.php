<?php

namespace PeskyCMF\Scaffold\ItemDetails;

class JsonArrayValueCell extends ValueCell {
    
    /** @var string  */
    protected ?string $templateForDefaultRenderer = 'cmf::item_details.json_array_table';
    /** @var array */
    protected $jsonKeys = [];

    protected $valueContainerAttributes = [
        'class' => 'pn bg-white'
    ];
    
    /**
     * @param array $jsonKeys - ordering matters!
     * @return $this
     */
    public function setJsonKeys(array $jsonKeys) {
        $this->jsonKeys = $jsonKeys;
        return $this;
    }
    
    public function getJsonKeys() {
        return $this->jsonKeys;
    }
    
    public function getTableHeaders() {
        $headers = [];
        foreach ($this->jsonKeys as $jsonKey) {
            $headers[] = $this->getScaffoldSectionConfig()->translate($this, 'header.' . $jsonKey);
        }
        return $headers;
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        return is_array($value) ? $value : json_decode($value);
    }
}
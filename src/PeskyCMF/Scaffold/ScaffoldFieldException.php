<?php


namespace PeskyCMF\Scaffold;


class ScaffoldFieldException extends ScaffoldActionException {

    /** @var ScaffoldFieldConfig */
    private $scaffoldFieldConfig;

    function __construct(ScaffoldFieldConfig $fieldConfig, $message) {
        $this->scaffoldFieldConfig = $fieldConfig;
        parent::__construct($fieldConfig->getScaffoldActionConfig(), $message);
    }

    /**
     * @return ScaffoldFieldConfig
     */
    public function getScaffoldFieldConfig() {
        return $this->scaffoldFieldConfig;
    }

}
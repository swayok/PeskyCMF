<?php

namespace PeskyCMF\Scaffold;

class ScaffoldSectionException extends ScaffoldException {
    /** @var ScaffoldSectionConfig|null */
    private $scaffoldSectionConfig;

    /**
     * ScaffoldSectionException constructor.
     * @param ScaffoldSectionConfig|null $config
     * @param string $message
     */
    public function __construct($config, $message) {
        $this->scaffoldSectionConfig = $config;
        parent::__construct($message);
    }

    /**
     * @return ScaffoldSectionConfig
     */
    public function getScaffoldSectionConfig() {
        return $this->scaffoldSectionConfig;
    }

}
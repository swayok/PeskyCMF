<?php

namespace PeskyCMF\Scaffold;

class ScaffoldActionException extends ScaffoldException {
    /** @var ScaffoldActionConfig|null */
    private $scaffoldActionConfig;

    /**
     * ScaffoldActionException constructor.
     * @param ScaffoldActionConfig|null $config
     * @param string $message
     */
    public function __construct($config, $message) {
        $this->scaffoldActionConfig = $config;
        parent::__construct($message);
    }

    /**
     * @return ScaffoldActionConfig
     */
    public function getScaffoldActionConfig() {
        return $this->scaffoldActionConfig;
    }

}
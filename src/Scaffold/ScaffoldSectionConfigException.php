<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

class ScaffoldSectionConfigException extends ScaffoldException
{
    
    private ScaffoldSectionConfig $scaffoldSectionConfig;
    
    /**
     * @param ScaffoldSectionConfig|null $config
     * @param string $message
     */
    public function __construct($config, $message)
    {
        $this->scaffoldSectionConfig = $config;
        parent::__construct($message);
    }
    
    public function getScaffoldSectionConfig(): ScaffoldSectionConfig
    {
        return $this->scaffoldSectionConfig;
    }
    
}
<?php

declare(strict_types=1);

namespace PeskyCMF;

use Illuminate\Foundation\Application;
use PeskyCMF\Config\CmfConfig;
use Symfony\Component\HttpFoundation\ParameterBag;

class PeskyCmfManager
{
    
    protected $currentCmfSectionName;
    /** @var CmfConfig */
    protected $currentCmfConfig;
    /** @var Application */
    protected $app;
    /** @var \Closure[] */
    protected $callbacks = [];
    
    /**
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    protected function config($key)
    {
        return $this->appConfigs()->get('peskycmf.' . $key);
    }
    
    protected function appConfigs(): ParameterBag
    {
        return $this->app['config'];
    }
    
    public function getCurrentCmfConfig(): ?CmfConfig
    {
        return $this->currentCmfConfig;
    }
    
    public function getDefaultCmfConfig(): ?CmfConfig
    {
        return CmfConfig::getDefault();
    }
    
    public function getCurrentCmfSection(): ?string
    {
        return $this->currentCmfSectionName;
    }
    
    public function getCmfConfigForSection(?string $cmfSectionName = null): CmfConfig
    {
        if ($cmfSectionName === null) {
            $cmfSectionName = $this->config('default_cmf_config');
            if (!$cmfSectionName) {
                throw new \InvalidArgumentException(
                    '$cmfSectionName is required when config(\'peskycmf.default_cmf_config\') is empty'
                );
            }
        }
        $knownConfigs = (array)$this->config('cmf_configs');
        if (!isset($knownConfigs[$cmfSectionName])) {
            throw new \InvalidArgumentException("There is no key '$cmfSectionName' in config('peskycmf.cmf_configs') array");
        }
        $className = $knownConfigs[$cmfSectionName];
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class {$className} does not exists");
        }
        if (!is_subclass_of($className, CmfConfig::class)) {
            throw new \InvalidArgumentException("Class {$className} must extend " . CmfConfig::class . ' class');
        }
        /** @var CmfConfig $className */
        return $className::getInstance();
    }
    
    /**
     * Key from config('peskycmf.cmf_configs') array
     * @return static
     */
    public function setCurrentCmfSection(string $cmfSectionName)
    {
        if ($cmfSectionName !== $this->currentCmfSectionName) {
            $this->currentCmfConfig = $this->getCmfConfigForSection($cmfSectionName);
            $this->currentCmfSectionName = $cmfSectionName;
            
            $this->currentCmfConfig->initSection($this->app);
            
            foreach ($this->callbacks as $closure) {
                $closure($this->currentCmfConfig);
            }
        }
        return $this;
    }
    
    /**
     * Add Closure to be evaluated after setCurrentCmfSection() call.
     * CmfConfig instance will be passed to closure.
     * @return static
     */
    public function onSectionSet(\Closure $callback)
    {
        $this->callbacks[] = $callback;
        return $this;
    }
    
    
}
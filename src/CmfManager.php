<?php

declare(strict_types=1);

namespace PeskyCMF;

use Illuminate\Foundation\Application;
use PeskyCMF\Config\CmfConfig;
use Symfony\Component\HttpFoundation\ParameterBag;

class CmfManager
{
    
    protected Application $app;
    
    protected ?array $sectionsNames = null;
    protected ?string $defaultSectionName = null;
    protected ?string $currentCmfSectionName = null;
    
    /** @var CmfConfig[] */
    protected array $loadedCmfConfigs = [];
    protected ?CmfConfig $currentCmfConfig = null;
    
    /** @var \Closure[] */
    protected array $callbacks = [];
    
    public function __construct(Application $app)
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
    
    public function getCurrentCmfConfig(): CmfConfig
    {
        return $this->currentCmfConfig ?? $this->getCmfConfigForSection(null);
    }
    
    public function getCurrentCmfSectionName(): string
    {
        return $this->currentCmfSectionName ?? $this->getDefaultCmfSectionName();
    }
    
    public function getCmfConfigForSection(?string $cmfSectionName = null): CmfConfig
    {
        if ($cmfSectionName === null) {
            $cmfSectionName = $this->getDefaultCmfSectionName();
        }
        if (!isset($this->loadedCmfConfigs[$cmfSectionName])) {
            $knownConfigs = $this->getAllCmfSectionsNames();
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
            $this->loadedCmfConfigs[$cmfSectionName] = new $className();
        }
        return $this->loadedCmfConfigs[$cmfSectionName];
    }
    
    public function getDefaultCmfSectionName(): string
    {
        if (!$this->defaultSectionName) {
            $cmfSectionName = $this->config('default_cmf_config');
            if (!$cmfSectionName) {
                throw new \InvalidArgumentException(
                    '$cmfSectionName is required when config(\'peskycmf.default_cmf_config\') is empty'
                );
            }
            $this->defaultSectionName = $cmfSectionName;
        }
        return $this->defaultSectionName;
    }
    
    public function getAllCmfSectionsNames(): array
    {
        if ($this->sectionsNames === null) {
            $this->sectionsNames = (array)$this->config('cmf_configs');
        }
        return $this->sectionsNames;
    }
    
    /**
     * Key from config('peskycmf.cmf_configs') array
     */
    public function setCurrentCmfSection(string $cmfSectionName): void
    {
        if ($cmfSectionName !== $this->currentCmfSectionName) {
            $this->currentCmfConfig = $this->getCmfConfigForSection($cmfSectionName);
            $this->currentCmfSectionName = $cmfSectionName;
            
            $this->currentCmfConfig->initSection($this->app);
            
            foreach ($this->callbacks as $closure) {
                $closure($this->currentCmfConfig);
            }
        }
    }
    
    /**
     * Add Closure to be evaluated after setCurrentCmfSection() call.
     * CmfConfig instance will be passed to closure.
     */
    public function onSectionSet(\Closure $callback): void
    {
        $this->callbacks[] = $callback;
    }
    
    public function registerRoutesForAllCmfSections(): void
    {
        if (!$this->app->routesAreCached()) {
            foreach ($this->getAvailableCmfConfigs() as $sectionName => $cmfConfig) {
                $cmfConfig->declareRoutes($this->app, $sectionName);
            }
        }
    }
    
    public function extendLaravelAppConfigsForAllCmfSections(): void
    {
        if (!$this->app->configurationIsCached()) {
            foreach ($this->getAvailableCmfConfigs() as $cmfConfig) {
                $cmfConfig->extendLaravelAppConfigs($this->app);
            }
        }
    }
    
    /**
     * @return CmfConfig[]
     */
    public function getAvailableCmfConfigs(): array
    {
        $ret = [];
        foreach ($this->getAllCmfSectionsNames() as $sectionName) {
            $ret[$sectionName] = $this->getCmfConfigForSection($sectionName);
        }
        return $ret;
    }
}
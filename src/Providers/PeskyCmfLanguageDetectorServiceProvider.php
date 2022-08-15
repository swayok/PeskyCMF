<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\Config\CmfConfig;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

class PeskyCmfLanguageDetectorServiceProvider extends LanguageDetectorServiceProvider
{
    
    /** @var array */
    protected $configsOverride = [];
    /** @var null|string */
    protected $defaultLanguage = null;
    
    public function register()
    {
        parent::register();
        
        $this->app['events']->listen('locale.changed', function ($locale) {
            $this->applyNewLanguage($locale);
        });
    }
    
    public function importConfigsFromPeskyCmf(CmfConfig $cmfConfig): void
    {
        $this->defaultLanguage = $cmfConfig::default_locale();
        $this->configsOverride = $cmfConfig::language_detector_configs();
        $this->detectAndApplyLanguage();
    }
    
    public function boot()
    {
        if ($this->defaultLanguage) {
            /** @var Request $request */
            $request = $this->app['request'];
            $request->setDefaultLocale($this->defaultLanguage);
            if (method_exists($this->app['translator'], 'setFallback')) {
                $this->app['translator']->setFallback($this->defaultLanguage);
            }
        }
        parent::boot();
    }
    
    
    protected function config($key, $default = null)
    {
        return Arr::get($this->configsOverride, $key, function () use ($key, $default) {
            return parent::config($key, $default);
        });
    }
    
    /**
     * Detect and apply language for the application.
     * Failsafe
     */
    protected function detectAndApplyLanguage()
    {
        if ($this->config('autodetect', true)) {
            $detector = $this->getLanguageDetector();
            $language = $detector->getLanguageFromCookie();
            if (!$language || strlen($language) > 5 || !in_array($language, $this->getSupportedLanguages(), true)) {
                $driver = $detector->getDriver();
                if ($driver) {
                    $language = $driver->detect();
                    if (!$language || strlen($language) > 5) {
                        $language = $this->request->getDefaultLocale();
                    }
                }
            }
            $this->applyNewLanguage($language, true);
        }
    }
    
    protected function applyNewLanguage(string $language, bool $force = false): void
    {
        $langsMapping = $this->config('languages', []);
        // change $language according to mapping (key - proxied language, value - real language)
        $language = (string)Arr::get($langsMapping, $language, $language);
        // test if $language is supported by confings
        if (!in_array($language, $langsMapping, true)) {
            if (!$force) {
                return; //< leave current locale untouched
            }
            $language = $this->defaultLanguage ?: $this->request->getDefaultLocale();
        }
        $detector = $this->getLanguageDetector();
        $detector->addCookieToQueue($language);
        $detector->apply($language);
    }
    
    
}
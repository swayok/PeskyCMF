<?php

namespace PeskyCMF\Providers;

use PeskyCMF\Config\CmfConfig;
use Vluzrmos\LanguageDetector\LanguageDetector;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

class PeskyCmfLanguageDetectorServiceProvider extends LanguageDetectorServiceProvider {

    /** @var array */
    protected $configsOverride = [];
    /** @var null|string */
    protected $defaultLanguage = null;

    public function register() {
        parent::register();

        \Event::listen('locale.changed', function ($locale) {
            $this->applyNewLanguage($locale);
        });
    }

    public function importConfigsFromPeskyCmf(CmfConfig $cmfConfig) {
        $this->defaultLanguage = $cmfConfig::default_locale();
        $this->configsOverride = $cmfConfig::language_detector_configs();
        $this->detectAndApplyLanguage();
    }

    public function boot() {
        if ($this->defaultLanguage) {
            $this->app['request']->setDefaultLocale($this->defaultLanguage);
            if (method_exists($this->app['translator'], 'setFallback')) {
                $this->app['translator']->setFallback($this->defaultLanguage);
            }
        }
        parent::boot();
    }


    protected function config($key, $default = null) {
        return array_get($this->configsOverride, $key, function () use ($key, $default) {
            return parent::config($key, $default);
        });
    }

    /**
     * Detect and apply language for the application.
     * Failsafe
     */
    protected function detectAndApplyLanguage() {
        if ($this->config('autodetect', true)) {
            /** @var LanguageDetector $detector */
            $detector = $this->getLanguageDetector();
            $language = $detector->getLanguageFromCookie();
            if (!$language || strlen($language) > 5 || !in_array($language, $this->getSupportedLanguages(), true)) {
                $language = $detector->getDriver()->detect();
                if (!$language || strlen($language) > 5) {
                    $language = $this->request->getDefaultLocale();
                }
            }
            $this->applyNewLanguage($language, true);
        }
    }

    protected function applyNewLanguage($language, $force = false) {
        $langsMapping = $this->config('languages', []);
        // change $language according to mapping (key - proxied language, value - real language)
        $language = array_get($langsMapping, $language, $language);
        // test if $language is supported by confings
        if (!in_array($language, $langsMapping, true)) {
            if (!$force) {
                return; //< leave current locale untouched
            }
            $language = $this->defaultLanguage ?: $this->request->getDefaultLocale();
        }
        /** @var LanguageDetector $detector */
        $detector = $this->getLanguageDetector();
        $detector->addCookieToQueue($language);
        $detector->apply($language);
    }


}
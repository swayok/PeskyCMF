<?php

namespace PeskyCMF\Providers;

use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

class PeskyCmfLanguageDetectorServiceProvider extends LanguageDetectorServiceProvider {

    /**
     * Detect and apply language for the application.
     * Failsafe
     */
    protected function detectAndApplyLanguage() {
        if ($this->config('autodetect', true)) {
            $detector = $this->getLanguageDetector();
            $language = $detector->detect();
            if (strlen($language) > 5) {
                $detector->useCookies(false);
                $language = $detector->detect();
                if (strlen($language) > 5) {
                    $language = config('app.locale');
                }
            }
            $detector->useCookies($this->config('cookie_name', null));
            $detector->addCookieToQueue($language);
            $detector->apply($language);
        }

    }

    protected function registerAndPublishConfigurations() {
        $reflection = new \ReflectionClass(LanguageDetectorServiceProvider::class);
        $configFile = dirname($reflection->getFileName()) . '/../../config/lang-detector.php';

        // fix absent group name
        $this->publishes([$configFile => base_path('config/lang-detector.php')], 'config');

        $this->mergeConfigFrom($configFile, 'lang-detector');
    }
}
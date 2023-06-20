<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\Config\CmfConfig;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

// todo: get rid of extending LanguageDetectorServiceProvider and maybe from
//  PeskyCmfLanguageDetectorServiceProvider itself
class PeskyCmfLanguageDetectorServiceProvider extends LanguageDetectorServiceProvider
{
    /** @var array */
    protected array $configsOverride = [];
    /** @var null|string */
    protected ?string $defaultLanguage = null;

    public function importConfigsFromPeskyCmf(CmfConfig $cmfConfig): void
    {
        $this->defaultLanguage = $cmfConfig->defaultLocale();
        $this->configsOverride = $cmfConfig->languageDetectorConfigs();
        $this->detectAndApplyLanguage();
    }

    public function boot(): void
    {
        $this->app['events']->listen(LocaleUpdated::class, function (LocaleUpdated $event) {
            if ($event->locale !== $this->app->getLocale()) {
                $this->applyNewLanguage($event->locale);
            }
        });
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
    protected function detectAndApplyLanguage(): void
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

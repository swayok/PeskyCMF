<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\KeyValueSetFormInput;

/**
 * @method static string default_browser_title($default = null, $ignoreEmptyString = true)
 * @method static string browser_title_addition($default = null, $ignoreEmptyString = true)
 * @method static array languages($default = null, $ignoreEmptyString = true)
 * @method static string default_language($default = null, $ignoreEmptyString = true)
 * @method static array fallback_languages($default = null, $ignoreEmptyString = true)
 */
class CmsAppSettings {

    /** @var $this */
    static protected $instance;

    const DEFAULT_BROWSER_TITLE = 'default_browser_title';
    const BROWSER_TITLE_ADDITION = 'browser_title_addition';
    const LANGUAGES = 'languages';
    const DEFAULT_LANGUAGE = 'default_language';
    const FALLBACK_LANGUAGES = 'fallback_languages';

    /** @var null|array */
    static protected $loadedMergedSettings;
    /** @var null|array */
    static protected $loadedDbSettings;

    static protected $settingsForWysiwygDataIsnserts = [

    ];

    /**
     * @return $this
     */
    static public function getInstance() {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct() {
    }

    static public function getSettingsForWysiwygDataIsnserts() {
        return static::$settingsForWysiwygDataIsnserts;
    }

    /**
     * Get form inputs for app settings (used in CmsSettingsScaffoldConfig)
     * You can use setting name as form input - it will be simple text input;
     * In order to make non-text input - use instance of FormInput class or its descendants as value and
     * setting name as key;
     * @param FormConfig $scaffold
     * @return FormConfig
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     * @throws \UnexpectedValueException
     */
    static public function configureScaffoldFormConfig(FormConfig $scaffold) {
        return $scaffold
            ->addTab($scaffold->translate(null, 'tab.general'), [
                static::DEFAULT_BROWSER_TITLE,
                static::BROWSER_TITLE_ADDITION,
            ])
            ->addTab($scaffold->translate(null, 'tab.localization'), [
                static::LANGUAGES => KeyValueSetFormInput::create()
                    ->setMinValuesCount(1)
                    ->setAddRowButtonLabel($scaffold->translate(null, 'input.languages_add'))
                    ->setDeleteRowButtonLabel($scaffold->translate(null, 'input.languages_delete')),
                static::DEFAULT_LANGUAGE => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptions(function () {
                        return static::languages();
                    }),
                static::FALLBACK_LANGUAGES => KeyValueSetFormInput::create()
                    ->setAddRowButtonLabel($scaffold->translate(null, 'input.fallback_languages_add'))
                    ->setDeleteRowButtonLabel($scaffold->translate(null, 'input.fallback_languages_delete')),
            ]);
    }

    /**
     * @return array
     */
    static public function getValidatorsForScaffoldFormConfig() {
        return [
            static::DEFAULT_LANGUAGE => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%|in:' . implode(',', array_keys(static::languages())),
            static::LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
            static::LANGUAGES . '.*.value' => 'required|string|max:88',
            static::FALLBACK_LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
            static::FALLBACK_LANGUAGES . '.*.value' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%'
        ];
    }

    /**
     * Get default value for setting $name
     * @param string $name
     * @return mixed
     */
    static public function getDefaultValue($name) {
        static $defaults;
        if (!$defaults) {
            $defaults = static::getAllDefaultValues();
        }
        if (array_has($defaults, $name)) {
            if ($defaults[$name] instanceof \Closure) {
                $defaults[$name] = $defaults[$name]();
            }
            return $defaults[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    static public function getAllDefaultValues() {
        return [
            static::LANGUAGES => ['en' => 'English'],
            static::DEFAULT_LANGUAGE => 'en',
            static::FALLBACK_LANGUAGES => []
        ];
    }

    /**
     * @param bool $ignoreCache
     * @return array
     */
    static public function getAllValues($ignoreCache = false) {
        /** @var CmsSettingsTable $settingsTable */
        $settingsTable = app(CmsSettingsTable::class);
        $settings = $settingsTable::getValuesForForeignKey(null, $ignoreCache, true);
        foreach (static::getAllDefaultValues() as $name => $defaultValue) {
            if (!array_key_exists($name, $settings) || $settings[$name] === null) {
                $settings[$name] = value($defaultValue);
            }
        }
        return $settings;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __get($name) {
        static::$name();
    }

    static public function __callStatic($name, $arguments) {
        /** @var CmsSettingsTable $settingsTable */
        $settingsTable = app(CmsSettingsTable::class);
        $default = array_get($arguments, 0, static::getDefaultValue($name));
        $ignoreEmptyValue = (bool)array_get($arguments, 1, true);
        return $settingsTable::getValue($name, null, $default, $ignoreEmptyValue);
    }

    public function __call($name, $arguments) {
        /** @noinspection ImplicitMagicMethodCallInspection */
        return static::__callStatic($name, (array)$arguments);
    }

}
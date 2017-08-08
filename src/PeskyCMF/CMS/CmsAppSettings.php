<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\KeyValueSetFormInput;
use PeskyORM\Core\DbExpr;
use Psr\Log\InvalidArgumentException;
use Swayok\Utils\Set;

/**
 * @method static string default_browser_title($default = null, $ignoreEmptyValue = true)
 * @method static string browser_title_addition($default = null, $ignoreEmptyValue = true)
 * @method static array languages($default = null, $ignoreEmptyValue = true)
 * @method static string default_language($default = null, $ignoreEmptyValue = true)
 * @method static array fallback_languages($default = null, $ignoreEmptyValue = true)
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
     * Get all validators for specific key.
     * Override this if you need some specific validation for keys that are not present in scaffold config form and
     * can only be updated via static::update() method.
     * @param string $key
     * @return array
     */
    static protected function getValidatorsForKey($key) {
        $validators = static::getValidatorsForScaffoldFormConfig(true);
        $validatorsForKey = [];
        foreach ($validators as $setting => $rules) {
            if (preg_match("%^{$key}($|\.)%", $setting)) {
                $validatorsForKey[] = $rules;
            }
        }
        return $validatorsForKey;
    }

    /**
     * Passed to FormConfig->setIncomingDataModifier()
     * @param array $data
     * @return array
     */
    static public function modifyIncomingData(array $data) {
        return $data;
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
     * @return CmsSettingsTable
     */
    static protected function getTable() {
        return app(CmsSettingsTable::class);
    }

    /**
     * @param bool $ignoreCache
     * @return array
     */
    static public function getAllValues($ignoreCache = false) {
        $settings = static::getTable()->getValuesForForeignKey(null, $ignoreCache, true);
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
        $default = array_get($arguments, 0, static::getDefaultValue($name));
        $ignoreEmptyValue = (bool)array_get($arguments, 1, true);
        return static::getTable()->getValue($name, null, $default, $ignoreEmptyValue);
    }

    public function __call($name, $arguments) {
        /** @noinspection ImplicitMagicMethodCallInspection */
        return static::__callStatic($name, (array)$arguments);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $validate
     */
    static public function update($key, $value, $validate = true) {
        $data = [$key => $value];
        if ($validate && !($value instanceof DbExpr)) {
            $rules = static::getValidatorsForKey($key);
            if (!empty($rules)) {
                $validator = \Validator::make($data, $rules);
                if ($validator->fails()) {
                    throw new InvalidArgumentException(
                        "Invalid value received for setting '$key'. Errors: "
                        . var_export($validator->getMessageBag()->toArray(), true)
                    );
                }
            }
        }
        $table = static::getTable();
        $table::updateOrCreateRecord($table::makeDataForRecord($key, $value));
    }

    /**
     * @param string $key
     */
    static public function delete($key) {
        static::getTable()->delete([static::getTable()->getKeysColumnName() => $key]);
    }

}
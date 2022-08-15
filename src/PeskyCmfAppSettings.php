<?php

declare(strict_types=1);

namespace PeskyCMF;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use PeskyCMF\Db\Settings\CmfSettingsTable;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\KeyValueSetFormInput;
use PeskyORM\Core\DbExpr;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueTableInterface;

/**
 * @method static string default_browser_title($default = null, $ignoreEmptyValue = true)
 * @method static string browser_title_addition($default = null, $ignoreEmptyValue = true)
 * @method static array languages($default = null, $ignoreEmptyValue = true)
 * @method static string default_language($default = null, $ignoreEmptyValue = true)
 * @method static array fallback_languages($default = null, $ignoreEmptyValue = true)
 */
class PeskyCmfAppSettings
{
    
    /** @var $this */
    static protected $instance;
    
    public const DEFAULT_BROWSER_TITLE = 'default_browser_title';
    public const BROWSER_TITLE_ADDITION = 'browser_title_addition';
    public const LANGUAGES = 'languages';
    public const DEFAULT_LANGUAGE = 'default_language';
    public const FALLBACK_LANGUAGES = 'fallback_languages';
    
    static protected $settingsForWysiwygDataIsnserts = [
    
    ];
    
    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    protected function __construct()
    {
    }
    
    public static function getSettingsForWysiwygDataIsnserts(): array
    {
        return static::$settingsForWysiwygDataIsnserts;
    }
    
    /**
     * Get form inputs for app settings (used in CmfSettingsScaffoldConfig)
     * You can use setting name as form input - it will be simple text input;
     * In order to make non-text input - use instance of FormInput class or its descendants as value and
     * setting name as key;
     */
    public static function configureScaffoldFormConfig(FormConfig $scaffold): FormConfig
    {
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
    
    public static function getValidatorsForScaffoldFormConfig(): array
    {
        return [
            static::DEFAULT_LANGUAGE => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%|in:' . implode(',', array_keys(static::languages())),
            static::LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
            static::LANGUAGES . '.*.value' => 'required|string|max:88',
            static::FALLBACK_LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
            static::FALLBACK_LANGUAGES . '.*.value' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
        ];
    }
    
    /**
     * Get all validators for specific key.
     * Override this if you need some specific validation for keys that are not present in scaffold config form and
     * can only be updated via static::update() method.
     */
    protected static function getValidatorsForKey(string $key): array
    {
        $validators = static::getValidatorsForScaffoldFormConfig();
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
     */
    public static function modifyIncomingData(array $data): array
    {
        return $data;
    }
    
    private $defaultValues;
    
    /**
     * Get default value for setting $name
     * @return mixed
     */
    public static function getDefaultValue(string $name)
    {
        if (!static::getInstance()->defaultValues) {
            static::getInstance()->defaultValues = static::getAllDefaultValues();
        }
        if (Arr::has(static::getInstance()->defaultValues, $name)) {
            if (static::getInstance()->defaultValues[$name] instanceof \Closure) {
                static::getInstance()->defaultValues[$name] = static::getInstance()->defaultValues[$name]();
            }
            return static::getInstance()->defaultValues[$name];
        }
        return null;
    }
    
    public static function getAllDefaultValues(): array
    {
        return [
            static::LANGUAGES => ['en' => 'English'],
            static::DEFAULT_LANGUAGE => 'en',
            static::FALLBACK_LANGUAGES => [],
        ];
    }
    
    /**
     * @return CmfSettingsTable|LaravelKeyValueTableInterface
     */
    protected static function getTable(): LaravelKeyValueTableInterface
    {
        return app(CmfSettingsTable::class);
    }
    
    public static function getAllValues(bool $ignoreCache = false): array
    {
        $settings = static::getTable()->getValuesForForeignKey(null, $ignoreCache, true);
        foreach (static::getAllDefaultValues() as $name => $defaultValue) {
            if (!array_key_exists($name, $settings) || $settings[$name] === null) {
                $settings[$name] = value($defaultValue);
            }
        }
        return $settings;
    }
    
    /** @noinspection MagicMethodsValidityInspection */
    public function __get($name)
    {
        static::$name();
    }
    
    public static function __callStatic($name, $arguments)
    {
        $default = array_get($arguments, 0, static::getDefaultValue($name));
        $ignoreEmptyValue = (bool)array_get($arguments, 1, true);
        return static::getTable()->getValue($name, null, $default, $ignoreEmptyValue);
    }
    
    public function __call($name, $arguments)
    {
        return static::__callStatic($name, (array)$arguments);
    }
    
    /**
     * @param string $key
     * @param mixed $value
     * @param bool $validate
     * @throws \InvalidArgumentException
     */
    public static function update(string $key, $value, bool $validate = true): void
    {
        $data = [$key => $value];
        if ($validate && !($value instanceof DbExpr)) {
            $rules = static::getValidatorsForKey($key);
            if (!empty($rules)) {
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    throw new \InvalidArgumentException(
                        "Invalid value received for setting '$key'. Errors: "
                        . var_export($validator->getMessageBag()->toArray(), true)
                    );
                }
            }
        }
        $table = static::getTable();
        $table::updateOrCreateRecord($table::makeDataForRecord($key, $value));
    }
    
    public static function delete(string $key): void
    {
        static::getTable()->delete([static::getTable()->getKeysColumnName() => $key]);
    }
    
}
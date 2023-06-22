<?php

declare(strict_types=1);

namespace PeskyCMF;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use PeskyCMF\Db\Settings\CmfSetting;
use PeskyCMF\Db\Settings\CmfSettingsTable;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordsCollection\KeyValuePair;
use PeskyORM\ORM\Table\TableInterface;
use PeskyORM\ORM\TableStructure\TableColumn\TableColumnInterface;

abstract class PeskyCmfAppSettings
{
    protected array $settingsForWysiwygDataIsnserts = [];

    /**
     * @var TableColumnInterface[]
     */
    private array $valueConfigs = [];
    protected ?array $defaultValues = null;
    /** @var array[] */
    protected ?array $records = null;

    public static function getInstance(): static
    {
        return app(__CLASS__);
    }

    public function __construct(
        protected Application $app,
        protected CacheRepository $cacheRepository
    ) {
        $this->registerValueConfigs();
    }

    public function getTable(): TableInterface
    {
        return CmfSettingsTable::getInstance();
    }

    abstract protected function registerValueConfigs(): void;

    /**
     * Get form inputs for app settings (used in CmfSettingsScaffoldConfig)
     * You can use setting name as form input - it will be simple text input;
     * In order to make non-text input - use instance of FormInput class or its descendants as value and
     * setting name as key;
     */
    abstract public function configureScaffoldFormConfig(FormConfig $formConfig): FormConfig;

    abstract public function getValidatorsForScaffoldFormConfig(): array;

    protected function addValueConfig(TableColumnInterface $column): void
    {
        $this->valueConfigs[$column->getName()] = $column;
    }

    /**
     * @return TableColumnInterface[]
     */
    public function getValueConfigs(): array
    {
        return $this->valueConfigs;
    }

    public function getValueConfig(string $key): TableColumnInterface
    {
        if (!$this->hasValueConfig($key)) {
            throw new \InvalidArgumentException(
                static::class . " does not know about key named '{$key}'"
            );
        }
        return $this->valueConfigs[$key];
    }

    public function hasValueConfig(string $key): bool
    {
        return isset($this->valueConfigs[$key]);
    }

    protected function getCacheKey(): string
    {
        return 'app-settings';
    }

    protected function getCacheDuration(): ?int
    {
        return 28800;
    }

    public function getSettingsForWysiwygDataIsnserts(): array
    {
        return $this->settingsForWysiwygDataIsnserts;
    }

    /**
     * Get all validators for specific key.
     * Override this if you need some specific validation for keys that are not present in scaffold config form and
     * can only be updated via static::update() method.
     */
    protected function getValidatorsForKey(string $key): array
    {
        $validators = $this->getValidatorsForScaffoldFormConfig();
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
    public function modifyIncomingData(array $data): array
    {
        return $data;
    }

    /**
     * Get all default values for all settings
     */
    public function getDefaultValues(): array
    {
        if ($this->defaultValues === null) {
            $this->defaultValues = [];
            foreach ($this->valueConfigs as $column) {
                $this->defaultValues[$column->getName()] = $column->hasDefaultValue()
                    ? $column->getValidDefaultValue()
                    : null;
            }
        }
        return $this->defaultValues;
    }

    /**
     * Get default value for setting $name
     */
    public function getDefaultValue(string $name): mixed
    {
        return Arr::get($this->getDefaultValues(), $name, null);
    }

    public function getSettings(bool $ignoreCache = false): array
    {
        $records = $this->getRecords($ignoreCache);
        $settings = $this->getDefaultValues();
        foreach ($records as $record) {
            $settings[$record['key']] = $record['value'];
        }
        return $settings;
    }

    public function getSetting(
        string $name,
        bool $ignoreCache = false,
        mixed $default = null,
        bool $ignoreEmptyValue = false
    ): mixed {
        $this->getValueConfig($name); //< validate setting existence
        $allValues = $this->getSettings($ignoreCache);
        if (!isset($allValues[$name]) || ($ignoreEmptyValue && empty($allValues[$name]))) {
            return $default;
        }
        return $allValues[$name];
    }

    /**
     * Get DB records as arrays. Prefer cached data unless $ignoreCache is true.
     */
    protected function getRecords(bool $ignoreCache = false): array
    {
        $cacheKey = $this->getCacheKey();
        $records = null;
        if (!$ignoreCache) {
            if ($this->records !== null) {
                $records = $this->records;
            } else {
                $this->records = $records = $this->cacheRepository->get($cacheKey);
            }
        }
        if ($records === null) {
            $tempRecord = $this->getTable()->newRecord();
            $this->records = $records = $this->getTable()->select('*')
                ->toArrays(function (CmfSetting $setting) use ($tempRecord) {
                    $data = $setting->toArray();
                    $valueConfig = $this->getValueConfig($data['key']);
                    $valueContainer = $valueConfig->setValue(
                        $valueConfig->getNewRecordValueContainer($tempRecord),
                        $data['value'],
                        true,
                        true
                    );
                    $data['value'] = $valueConfig->getValue($valueContainer, null);
                    return new KeyValuePair($setting->key, $data);
                });
        }
        $this->cacheRepository->put($cacheKey, $records, $this->getCacheDuration());
        return $this->records;
    }

    public function __get(string $key)
    {
        return $this->$key();
    }

    public function __set(string $key, mixed $value): void
    {
        $this->update($key, $value, true);
    }

    public function __isset(string $key): bool
    {
        $this->getValueConfig($key); //< validate setting existence
        $records = $this->getRecords(false);
        return isset($records[$key]);
    }

    /**
     * @param string $key Setting name
     * @param array  $arguments [0 => mixed $default, 1 => bool $ignoreEmptyValue]
     * @return mixed
     */
    public function __call(string $key, array $arguments): mixed
    {
        $default = $arguments[0] ?? null;
        $ignoreEmptyValue = (bool)($arguments[1] ?? true);
        return $this->getSetting($key, false, $default, $ignoreEmptyValue);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function update(string $key, mixed $value, bool $validate = true): mixed
    {
        $valueConfig = $this->getValueConfig($key);
        try {
            $valueContainer = $valueConfig->setValue(
                $valueConfig->getNewRecordValueContainer($this->getTable()->newRecord()),
                $value,
                !$validate,
                true
            );
        } catch (InvalidDataException $exception) {
            throw new \InvalidArgumentException(
                "Invalid value received for setting '$key'. Errors: "
                . var_export($exception->getErrors(), true)
            );
        }
        $records = $this->getRecords(true);
        /** @var CmfSetting $record */
        $record = $this->getTable()->newRecord();
        if (isset($records[$key])) {
            $record->fromDbData($records[$key]);
        } else {
            $record->setKey($key);
        }
        $record->setValue($valueConfig->getValue($valueContainer, null), false);
        $record->save();
        $this->records[$key] = $record->toArray();
        return $this->getSetting($key);
    }

    /**
     * @param array $settings Key-value pairs of settings.
     * @param bool  $validate Run validation on values or not.
     * @return array Updated settings.
     * @throws \InvalidArgumentException
     */
    public function updateMany(array $settings, bool $validate = true): array
    {
        $table = $this->getTable();
        $table::beginTransaction();
        $ret = [];
        foreach ($settings as $key => $value) {
            $ret[$key] = $this->update($key, $value, $validate);
        }
        $table::commitTransaction();
        return $ret;
    }

    /**
     * Delete single setting
     */
    public function delete(string $key): void
    {
        $this->getTable()->delete(['key' => $key]);
    }
}

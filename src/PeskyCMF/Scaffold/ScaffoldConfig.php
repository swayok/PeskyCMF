<?php


namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;

abstract class ScaffoldConfig implements ScaffoldConfigInterface {

    use DataValidationHelper;

    /** @var TableInterface */
    protected $table;
    /** @var DataGridConfig */
    protected $dataGridConfig = null;
    /** @var FilterConfig */
    protected $dataGridFilterConfig = null;
    /** @var ItemDetailsConfig */
    protected $itemDetailsConfig = null;
    /** @var FormConfig */
    protected $formConfig = null;

    /** @var bool */
    protected $isDetailsViewerAllowed = true;
    /** @var bool */
    protected $isCreateAllowed = true;
    /** @var bool */
    protected $isEditAllowed = true;
    /** @var bool */
    protected $isDeleteAllowed = true;
    /**
     * Path to localization of views.
     * Usage: see $this->getLocalizationBasePath() method.
     * By default if $viewsBaseTranslationKey is empty - static::getResourceName() will be used
     * @return null|string
     */
    protected $viewsBaseTranslationKey = null;

    /**
     * @var null|ScaffoldLoggerInterface
     */
    protected $logger;

    /**
     * ScaffoldConfig constructor.
     */
    public function __construct() {
        if ($this->viewsBaseTranslationKey === null) {
            $this->viewsBaseTranslationKey = static::getResourceName();
        }
        $this->setLogger(CmfConfig::getPrimary()->getHttpRequestsLogger());
    }

    /**
     * @return string
     */
    static public function getResourceName() {
        return static::getTable()->getName();
    }

    /**
     * @return array|null
     */
    static public function getMainMenuItem() {
        $resoureName = static::getResourceName();
        $url = routeToCmfItemsTable(static::getResourceName());
        if ($url === null) {
            // access to this menu item was denied
            return null;
        }
        return [
            'label' => cmfTransCustom($resoureName . '.menu_title'),
            'icon' => static::getIconForMenuItem(),
            'url' => $url,
            'counter' => static::getMenuItemCounterName()
        ];
    }

    /**
     * @return null|string
     */
    static protected function getIconForMenuItem() {
        return null;
    }

    /**
     * @return string
     */
    static public function getMenuItemCounterName() {
        return static::getMenuItemCounterValue() ? static::getResourceName() . '_count' : null;
    }

    /**
     * Get value for menu item counter (some html code to display near menu item button: new items count, etc)
     * More info: CmfConfig::menu()
     * You may return an HTML string or \Closure that returns that string.
     * Note that self::getMenuItemCounterName() uses this method to decide if it should return null or counter name.
     * If you want to return HTML string consider overwriting of self::getMenuItemCounterName()
     * @return null|\Closure|string
     */
    static public function getMenuItemCounterValue() {
        return null;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return request();
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionConfigException
     */
    public function getConfigsForTemplatesRendering() {
        $configs = [
            'table' => static::getTable(),
            'scaffoldConfig' => $this
        ];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        if (!($configs['dataGridConfig'] instanceof DataGridConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createDataGridConfig() should return instance of DataGridConfig class');
        }
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        if (!($configs['dataGridFilterConfig'] instanceof FilterConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createDataGridFilterConfig() should return instance of FilterConfig class');
        }
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        if (!($configs['itemDetailsConfig'] instanceof ItemDetailsConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createItemDetailsConfig() should return instance of ItemDetailsConfig class');
        }
        $configs['formConfig'] = $this->getFormConfig();
        if (!($configs['formConfig'] instanceof FormConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createFormConfig() should return instance of FormConfig class');
        }
        $configs['dataGridConfig']->beforeRender();
        $configs['dataGridFilterConfig']->beforeRender();
        $configs['itemDetailsConfig']->beforeRender();
        $configs['formConfig']->beforeRender();
        return $configs;
    }

    /**
     * @return DataGridConfig
     */
    protected function createDataGridConfig() {
        return DataGridConfig::create(static::getTable(), $this);
    }

    /**
     * @return FilterConfig
     */
    protected function createDataGridFilterConfig() {
        return FilterConfig::create(static::getTable(), $this);
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create(static::getTable(), $this);
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create(static::getTable(), $this);
    }

    /**
     * @return DataGridConfig
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getDataGridConfig() {
        if (empty($this->dataGridConfig)) {
            $this->dataGridConfig = $this->createDataGridConfig();
            $this->dataGridConfig->finish();
        }
        return $this->dataGridConfig;
    }

    /**
     * @return FilterConfig
     */
    public function getDataGridFilterConfig() {
        if (empty($this->dataGridFilterConfig)) {
            $this->dataGridFilterConfig = $this->createDataGridFilterConfig();
            $this->dataGridFilterConfig->finish();
        }
        return $this->dataGridFilterConfig;
    }

    /**
     * @return ItemDetailsConfig
     * @throws \BadMethodCallException
     */
    public function getItemDetailsConfig() {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
            $this->itemDetailsConfig->finish();
        }
        return $this->itemDetailsConfig;
    }

    /**
     * @return FormConfig
     * @throws \BadMethodCallException
     */
    public function getFormConfig() {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
            $this->formConfig->finish();
        }
        return $this->formConfig;
    }

    /**
     * @return bool
     */
    public function isSectionAllowed() {
        return \Gate::allows('resource.view', [static::getResourceName()]);
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return (
            $this->isCreateAllowed
            && $this->isSectionAllowed()
            && \Gate::allows('resource.create', [static::getResourceName()])
        );
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->isEditAllowed && $this->isSectionAllowed();
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->isDetailsViewerAllowed && $this->isSectionAllowed();
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->isDeleteAllowed && $this->isSectionAllowed();
    }

    /**
     * Detects if $record deletable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDeleteAllowed(array $record) {
        return $this->isDeleteAllowed() && \Gate::allows('resource.delete', [static::getResourceName(), $record]);
    }

    /**
     * Detects if $record editable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordEditAllowed(array $record) {
        return $this->isEditAllowed() && \Gate::allows('resource.update', [static::getResourceName(), $record]);
    }

    /**
     * Detects if $record details can be displayed or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDetailsAllowed(array $record) {
        return (
            $this->isDetailsViewerAllowed()
            && \Gate::allows('resource.details', [static::getResourceName(), $record])
        );
    }

    /**
     * @param string $section - main sections are: 'datagrid.column', 'item_details.field', 'form.input'
     * @param AbstractValueViewer $viewer
     * @param string $suffix
     * @param array $parameters
     * @return string
     */
    public function translateForViewer($section, AbstractValueViewer $viewer, $suffix = '', array $parameters = []) {
        return $this->translate($section, rtrim("{$viewer->getNameForTranslation()}_{$suffix}", '_'), $parameters);
    }

    /**
     * @param string $section - main sections are: 'form.tooltip'
     * @param string $suffix
     * @param array $parameters
     * @return array|string
     */
    public function translate($section, $suffix = '', array $parameters = []) {
        return cmfTransCustom(rtrim(".{$this->viewsBaseTranslationKey}.{$section}.{$suffix}", '.'), $parameters);
    }

    /**
     * Translate general UI elements (button labels, tooltips, messages, etc..)
     * @param $path
     * @param array $parameters
     * @return mixed
     */
    public function translateGeneral($path, array $parameters = []) {
        $text = $this->translate($path, $parameters);
        if (preg_match('%\.' . preg_quote($path, '%') . '$%', $text)) {
            $text = cmfTransGeneral($path);
        }
        return $text;
    }

    public function renderTemplates() {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
        }
        return view(
            CmfConfig::getPrimary()->scaffold_templates_view_for_normal_table(),
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => static::getResourceName()]
            )
        )->render();
    }

    /**
     * @return array
     */
    public function renderTemplatesAndSplit() {
        $blocks = [
            'datagrid' => false,
            'itemForm' => false,
            'bulkEditForm' => false,
            'itemDetails' => false,
            'itemFormDefaults' => false
        ];
        if (!$this instanceof KeyValueTableScaffoldConfig && ($this->isCreateAllowed() || $this->isEditAllowed())) {
            /** @var JsonResponse $response */
            $response = $this->getDefaultValuesForFormInputs();
            $blocks['itemFormDefaults'] = $response->getData(true);
        }
        $html = $this->renderTemplates();
        foreach ($blocks as $block => &$template) {
            if (preg_match("%<!--\s*{$block}\s*start\s*-->(?:\s*\n*)*(.*?)<!--\s*{$block}\s*end\s*-->%is", $html, $matches)) {
                $template = trim(preg_replace(
                    ['%^\s*<(div|script)[^>]+id="(data-grid-tpl|item-form-tpl|item-details-tpl|bulk-edit-form-tpl)"[^>]*>\s*(.*)\s*</\1>\s*$%is', '%^\s+%im'],
                    ['$3', ' '],
                    $matches[1]
                ));
            }
        }
        return $blocks;
    }

    public function getHtmlOptionsForFormInputs() {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
        }
        if (!$this->isEditAllowed() && !$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse($this->getFormConfig()->translateGeneral('message.edit.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $columnsOptions = $formConfig->loadOptions($this->getRequest()->query('id'));
        foreach ($columnsOptions as $columnName => $options) {
            if (is_array($options)) {
                $columnsOptions[$columnName] = $this->renderOptionsForSelectInput(
                    $options,
                    $formConfig->getValueViewer($columnName)->getEmptyOptionLabel()
                );
            } else if (!is_string($options)) {
                unset($columnsOptions[$columnName]);
            }
        }
        return cmfJsonResponse()->setData($columnsOptions);
    }

    /**
     * @param array $options
     * @param bool|string $addEmptyOption - false: do not add default empty option | string: add default empty option
     * @return string
     */
    protected function renderOptionsForSelectInput(array $options, $addEmptyOption = false) {
        $ret = '';
        $hasEmptyOption = array_key_exists('', $options);
        if ($addEmptyOption !== false || $hasEmptyOption) {
            if ($hasEmptyOption) {
                $label = $options[''];
                unset($options['']);
            } else {
                $label = $addEmptyOption === true ? '' : $addEmptyOption;
            }
            $ret .= '<option value="">' . $label . '</option>';
        }
        foreach ($options as $value => $label) {
            if (!is_array($label)) {
                $ret .= '<option value="' . htmlentities($value) . '">' . $label . '</option>';
            } else {
                $ret .= '<optgroup label="' . htmlentities($value) . '">' . $this->renderOptionsForSelectInput($label) . '</optgroup>';
            }
        }
        return $ret;
    }

    /**
     * @param TableInterface $table
     * @param null|string $message
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    protected function makeRecordNotFoundResponse(TableInterface $table, $message = null) {
        if (empty($message)) {
            $message = $this->translateGeneral('message.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            routeToCmfItemsTable(static::getResourceName()),
            $message
        );
    }

    /**
     * @param string $message
     * @return CmfJsonResponse
     */
    protected function makeAccessDeniedReponse($message) {
        return cmfJsonResponse(HttpCode::FORBIDDEN)
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    /**
     * @param ScaffoldLoggerInterface $logger
     * @return $this
     */
    public function setLogger(ScaffoldLoggerInterface $logger = null) {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLogger() {
        return $this->logger !== null;
    }

    /**
     * @return null|ScaffoldLoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record, $tableName = null) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordBeforeChange($record, $tableName);
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordAfterChange($record);
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordLoad(RecordInterface $record, $tableName = null) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordUsage($record, $tableName);
        }
        return $this;
    }

    public function getCustomData($dataId) {
        return cmfJsonResponse(HttpCode::NOT_FOUND);
    }

}
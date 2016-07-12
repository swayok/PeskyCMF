<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $translationPrefix
 * @var string $idSuffix
 */
$formId = "scaffold-bulk-edit-form-{$idSuffix}";
$pkColName = $model->getPkColumnName();

$else = '{{??}}';
$endIf = '{{?}}';

$backUrl = route('cmf_items_table', ['table_name' => $tableNameForRoutes], false);
?>

<script type="text/html" id="bulk-edit-form-tpl">
    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="scaffold-bulk-edit-<?php echo $idSuffix; ?>-header">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                    aria-label="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.cancel'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="scaffold-bulk-edit-<?php echo $idSuffix; ?>-header">
                        <?php echo trans("$translationPrefix.form.header_bulk_edit"); ?>
                    </h4>
                </div>
                <?php
                    $formAttributes = [
                        'id' => $formId,
                        'data-id-field' => $pkColName,
                        'data-back-url' => $backUrl
                    ];
                    if ($formConfig->hasFiles()) {
                        $formAttributes['enctype'] = 'multipart/form-data';
                    }
                    if ($formConfig->hasOptionsLoader()) {
                        $formAttributes['data-load-options'] = '1';
                    }
                    if ($formConfig->hasJsInitiator()) {
                        $formAttributes['data-initiator'] = addslashes($formConfig->getJsInitiator());
                    }
                    $formAction = route('cmf_api_edit_bulk', ['table_name' => $tableNameForRoutes], false);
                ?>
                <form role="form" method="post" action="<?php echo $formAction; ?>" <?php echo \Swayok\Html\Tag::buildAttributes($formAttributes); ?>
                data-uuid="{{= it.formUUID }}">
                    <input type="hidden" name="_method" value="PUT">
                    {{~ it.ids :item_id }}
                        <input type="hidden" name="<?php echo $pkColName; ?>[]" value="{{= item_id }}">
                    {{~}}
                    <!-- disable chrome email & password autofill -->
                    <input type="text" class="hidden" formnovalidate><input type="password" class="hidden" formnovalidate>
                    <!-- end of autofill disabler -->
                    <div class="modal-body">
                    <?php
                        foreach ($formConfig->getBulkEditableFields() as $config) {
                            if (!$config->hasLabel()) {
                                $config->setLabel(trans("$translationPrefix.form.field.{$config->getName()}"));
                            }
                            if ($model->getTableColumn($config->getName())->isRequiredOnAnyAction()) {
                                $config->setLabel($config->getLabel() . '*');
                            }
                            try {
                                $renderedInput = $config->render(['translationPrefix' => $translationPrefix]);
                                // replace <script> tags to be able to render that template
                                echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates($renderedInput);
                            } catch (Exception $exc) {
                                echo '<div>' . $exc->getMessage() . '</div>';
                                echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
                            }
                        }
                    ?>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-xs-6">
                                <button type="button" class="close btn btn-default" data-dismiss="modal">
                                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.cancel'); ?>
                                </button>
                            </div>
                            <div class="col-xs-6 text-right">
                                <button type="submit" class="btn btn-success"><?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.submit'); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</script>

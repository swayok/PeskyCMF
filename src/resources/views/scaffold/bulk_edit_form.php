<?php
declare(strict_types=1);
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */
$formId = "scaffold-bulk-edit-form-{$idSuffix}";
$pkColName = $table::getPkColumnName();
$backUrl = routeToCmfItemsTable($tableNameForRoutes);
?>

<script type="text/html" id="bulk-edit-form-tpl">
    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="scaffold-bulk-edit-<?php echo $idSuffix; ?>-header">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                    aria-label="<?php echo $formConfig->translateGeneral('bulk_edit.toolbar.close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="scaffold-bulk-edit-<?php echo $idSuffix; ?>-header">
                        {{? it._ids && it._ids.length }}
                            <?php echo $formConfig->translate(null, 'header_bulk_edit_selected'); ?>
                        {{??}}
                            <?php echo $formConfig->translate(null, 'header_bulk_edit_filtered'); ?>
                        {{?}}
                    </h4>
                </div>
                <?php
                    $formAttributes = [
                        'id' => $formId,
                        'data-id-field' => $pkColName
                    ];
                    if ($formConfig->hasOptionsLoader()) {
                        $formAttributes['data-load-options'] = '1';
                    }
                    $formAction = cmfRoute('cmf_api_edit_bulk', ['resource' => $tableNameForRoutes], false);
                ?>
                <form role="form" method="post" action="<?php echo $formAction; ?>" <?php echo \Swayok\Html\Tag::buildAttributes($formAttributes); ?>
                data-uuid="{{= it.formUUID }}">
                    <input type="hidden" name="_method" value="PUT">
                    {{? it._ids && $.isArray(it._ids) }}
                        {{~ it._ids :value:index }}
                            <input type="hidden" name="_ids[]" value="{{= value }}">
                        {{~}}
                    {{??}}
                        {{? typeof it._conditions === 'string' }}
                            <input type="hidden" name="_conditions" value="{{! it._conditions }}">
                        {{?}}
                    {{?}}
                    <?php include __DIR__ . '/../input/login_inputs_autofill_disabler.php'; ?>
                    <div class="modal-body">
                    <?php
                        $enablerTooltip = $formConfig->translateGeneral('bulk_edit.enabler.tooltip');
                        $baseEnablerId = \Illuminate\Support\Str::random() . '-enabler-for-';
                        foreach ($formConfig->getBulkEditableColumns() as $inputConfig) {
                            try {
                                $renderedInput = $inputConfig->render();
                                $enablerSwitchId = $baseEnablerId . \Illuminate\Support\Str::slug($inputConfig->getName());
                                $enablerSwitch = <<<INPUT
                                    <div class="bulk-edit-form-input-enabler pull-left va-t mr15">
                                        <input class="bulk-edit-form-input-enabler-input" type="checkbox"
                                            id="{$enablerSwitchId}" data-toggle="tooltip" data-title="{$enablerTooltip}">
                                    </div>
INPUT;
                                $renderedInput = '<div class="bulk-edit-form-input va-t">' . $renderedInput . '</div>';
                                echo '<div class="bulk-edit-form-input-container">' . $enablerSwitch . $renderedInput . '</div>';
                            } catch (Exception $exc) {
                                echo '<div>' . $exc->getMessage() . '</div>';
                                echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
                            }
                        }
                    ?>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-xs-6 text-left">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <?php echo $formConfig->translateGeneral('bulk_edit.toolbar.cancel'); ?>
                                </button>
                            </div>
                            <div class="col-xs-6 text-right">
                                <button type="submit" class="btn btn-success" disabled>
                                    <?php echo $formConfig->translateGeneral('bulk_edit.toolbar.submit'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</script>

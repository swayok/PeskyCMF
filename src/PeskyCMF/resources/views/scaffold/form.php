<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $translationPrefix
 * @var string $idSuffix
 */

try {

$formId = "scaffold-form-{$idSuffix}";
$pkColName = $model->getPkColumnName();

$ifEdit = "{{? it.{$pkColName} > 0 }}";
$ifCreate = "{{? !it.{$pkColName} }}";
$else = '{{??}}';
$endIf = '{{?}}';
$printPk = "{{= it.{$pkColName} }}";

$backUrl = route('cmf_items_table', ['table_name' => $tableNameForRoutes], false);
?>

<script type="text/html" id="item-form-tpl">
    <?php echo view('cmf::ui.default_page_header', [
        'header' => $ifEdit . trans("$translationPrefix.form.header_edit")
                    . $else . trans("$translationPrefix.form.header_create")
                    . $endIf,
        'defaultBackUrl' => $backUrl,
    ])->render(); ?>
    <div class="content">
        <div class="row">
            <div class="<?php echo $formConfig->getCssClassesForContainer() ?>">
                <div class="box box-primary">
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
                        $editUrl = route('cmf_api_update_item', ['table_name' => $tableNameForRoutes, 'id' => ''], false) . '/' . $printPk;
                        $createUrl = route('cmf_api_create_item', ['table_name' => $tableNameForRoutes], false);
                        $formAction = $ifEdit . $editUrl . $else . $createUrl . $endIf;
                    ?>
                    <form role="form" method="post" action="<?php echo $formAction; ?>" <?php echo \Swayok\Html\Tag::buildAttributes($formAttributes); ?>
                    data-uuid="{{= it.formUUID }}">
                        <?php echo $ifEdit; ?>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="<?php echo $pkColName; ?>" value="<?php echo $printPk; ?>">
                        <?php echo $endIf ?>
                        <!-- disable chrome email & password autofill -->
                        <input type="text" class="hidden" formnovalidate><input type="password" class="hidden" formnovalidate>
                        <!-- end of autofill disabler -->
                        <div class="box-body">
                        <?php
                            foreach ($formConfig->getFields() as $inputConfig) {
                                if (!$inputConfig->hasLabel()) {
                                    $inputConfig->setLabel(trans("$translationPrefix.form.field.{$inputConfig->getName()}"));
                                }
                                try {
                                    $renderedInput = $inputConfig->render(['translationPrefix' => $translationPrefix]);
                                    // replace <script> tags to be able to render that template
                                    $renderedInput = modifyDotJsTemplateToAllowInnerScriptsAndTemplates($renderedInput);
                                    if ($inputConfig->isShownOnCreate() && $inputConfig->isShownOnEdit()) {
                                        echo $renderedInput;
                                    } else if ($inputConfig->isShownOnCreate()) {
                                        // display only when creating
                                        echo $ifCreate . $renderedInput . $endIf;
                                    } else {
                                        // display only when editing
                                        echo $ifEdit . $renderedInput . $endIf;
                                    }
                                } catch (Exception $exc) {
                                    echo '<div>' . $exc->getMessage() . '</div>';
                                    echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
                                }
                            }
                            echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates($formConfig->getAdditionalHtmlForForm());;
                        ?>
                        </div>
                        <div class="box-footer">
                            <div class="row">
                                <div class="col-xs-3">
                                    <a class="btn btn-default" href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                                        <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.cancel'); ?>
                                    </a>
                                </div>
                                <div class="col-xs-6 text-center">
                                <?php echo $ifEdit; ?>
                                    <?php if ($formConfig->isCreateAllowed()) : ?>
                                        <?php
                                            $createUrl = route('cmf_item_add_form', [$tableNameForRoutes]);
                                        ?>
                                        <a class="btn btn-primary" href="<?php echo $createUrl; ?>">
                                            <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.create'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($formConfig->isDeleteAllowed()) : ?>
                                        <?php
                                            $deleteUrl = str_ireplace(
                                                ':id:',
                                                "{{= it.{$model->getPkColumnName()} }}",
                                                route('cmf_api_delete_item', [$tableNameForRoutes, ':id:'])
                                            );
                                        ?>
                                        {{? !!it.___delete_allowed }}
                                        <a class="btn btn-danger" href="#"
                                        data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                                        data-confirm="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.action.delete.please_confirm'); ?>">
                                            <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.delete'); ?>
                                        </a>
                                        {{?}}
                                    <?php endif; ?>
                                <?php echo $endIf; ?>
                                </div>
                                <div class="col-xs-3 text-right">
                                    <button type="submit" class="btn btn-success">
                                        <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.submit'); ?>
                                    </button>
                                </div>
                            </div>
                            <?php $toolbarItems = $formConfig->getToolbarItems(); ?>
                            <?php if (count($toolbarItems) > 0) : ?>
                                <div class="mt10 text-center">
                                    <?php
                                        foreach ($toolbarItems as $toolbarItem) {
                                            echo ' ' . preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%is', '{{= it.$2 }}', $toolbarItem) . ' ';
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</script>

<?php } catch (Exception $exc) {
    echo $exc->getMessage();
    echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
}?>
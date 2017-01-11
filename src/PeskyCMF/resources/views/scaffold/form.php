<?php
/**
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $translationPrefix
 * @var string $idSuffix
 */

$formId = "scaffold-form-{$idSuffix}";
$pkColName = $model->getPkColumnName();

$ifEdit = "{{? it.{$pkColName} > 0 }}";
$ifCreate = "{{? !it.{$pkColName} }}";
$else = '{{??}}';
$endIf = '{{?}}';
$printPk = "{{= it.{$pkColName} }}";

$backUrl = routeToCmfItemsTable($tableNameForRoutes);
$tabs = $formConfig->getTabs();
$groups = $formConfig->getInputsGroups();
$hasTabs = count($tabs) > 1 || !empty($tabs[0]['label']);

$buildInputs = function ($tabInfo) use ($groups, $formConfig, $translationPrefix, $ifCreate, $ifEdit, $endIf) {
    $isFirstGroup = true;
    foreach ($tabInfo['groups'] as $groupIndex) {
        $groupInfo = $groups[$groupIndex];
        if (empty($groupInfo['label'])) {
            if (!$isFirstGroup) {
                echo '<div class="section-divider"></div>';
            }
        } else {
            echo '<div class="section-divider"><span>' . $groupInfo['label'] . '</span></div>';
        }
        $isFirstGroup = false;
        foreach ($groupInfo['inputs_names'] as $inputName) {
            $inputConfig = $formConfig->getFormInput($inputName);
            if (!$inputConfig->hasLabel()) {
                $inputConfig->setLabel(cmfTransCustom("$translationPrefix.form.input.{$inputConfig->getName()}"));
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
                echo '<div>' . htmlspecialchars($exc->getMessage()) . '</div>';
                echo '<pre>' . nl2br(htmlspecialchars($exc->getTraceAsString())) . '</pre>';
            }
        }
    }
    echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates($formConfig->getAdditionalHtmlForForm());;
}
?>

<?php View::startSection('scaffold-form-footer'); ?>
    <div class="box-footer">
        <div class="row">
            <div class="col-xs-3">
                <a class="btn btn-default" href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <?php echo cmfTransGeneral('.form.toolbar.cancel'); ?>
                </a>
            </div>
            <div class="col-xs-6 text-center">
            <?php echo $ifEdit; ?>
                <?php if ($formConfig->isCreateAllowed()) : ?>
                    <a class="btn btn-primary" href="<?php echo routeToCmfItemAddForm($tableNameForRoutes); ?>">
                        <?php echo cmfTransGeneral('.form.toolbar.create'); ?>
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
                    data-confirm="<?php echo cmfTransGeneral('.action.delete.please_confirm'); ?>">
                        <?php echo cmfTransGeneral('.form.toolbar.delete'); ?>
                    </a>
                    {{?}}
                <?php endif; ?>
            <?php echo $endIf; ?>
            </div>
            <div class="col-xs-3 text-right">
                <button type="submit" class="btn btn-success">
                    <?php echo cmfTransGeneral('.form.toolbar.submit'); ?>
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
<?php View::stopSection(); ?>

<script type="text/html" id="item-form-tpl">
    <?php echo view('cmf::ui.default_page_header', [
        'header' => $ifEdit . cmfTransCustom("$translationPrefix.form.header_edit")
                    . $else . cmfTransCustom("$translationPrefix.form.header_create")
                    . $endIf,
        'defaultBackUrl' => $backUrl,
    ])->render(); ?>
    <div class="content">
        <div class="row">
            <div class="<?php echo $formConfig->getCssClassesForContainer() ?>">
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
                    <div class="nav-tabs-custom">
                        <?php if ($hasTabs): ?>
                            <ul class="nav nav-tabs">
                                <?php foreach ($tabs as $idx => $tabInfo): ?>
                                    <li class="<?php echo $idx === 0 ? 'active' : '' ?>">
                                        <a href="#<?php echo $formId . '-' . (string)($idx + 1) ?>" data-toggle="tab" role="tab">
                                            <?php echo empty($tabInfo['label']) ? '*' : $tabInfo['label']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="<?php echo $hasTabs ? 'tab-content' : 'box box-primary' ?>">
                            <?php foreach ($tabs as $idx => $tabInfo): ?>
                                <?php $class = $hasTabs ? 'tab-pane' . ($idx === 0 ? ' active' : '') : 'box-body'; ?>
                                <div role="tabpanel" class=" <?php echo $class ?>" id="<?php echo $formId . '-' . (string)($idx + 1) ?>">
                                    <?php $buildInputs($tabInfo); ?>
                                </div>
                            <?php endforeach; ?>
                            <?php
                                if (!$hasTabs) {
                                    echo View::yieldContent('scaffold-form-footer');
                                }
                            ?>
                        </div>
                        <?php
                            if ($hasTabs) {
                                echo View::yieldContent('scaffold-form-footer');
                            }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</script>

<?php
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */

$formId = "scaffold-form-{$idSuffix}";
$pkColName = $table->getPkColumnName();

$ifEdit = '{{? !it._is_creation }}';
$ifCreate = '{{? it._is_creation }}';
$else = '{{??}}';
$endIf = '{{?}}';
$printPk = "{{= it.{$pkColName} }}";

$pageUrl = $ifEdit . routeTpl('cmf_item_edit_form', ['table_name' => $tableNameForRoutes], ['id' => 'it.' . $table->getPkColumnName()])
    . $else . routeTpl('cmf_item_add_form', ['table_name' => $tableNameForRoutes])
    . $endIf;
$backUrl = routeToCmfItemsTable($tableNameForRoutes);
$tabs = $formConfig->getTabs();
$groups = $formConfig->getInputsGroups();
$hasTabs = count($tabs) > 1 || !empty($tabs[0]['label']);

$buildInputs = function ($tabInfo) use ($groups, $formConfig) {
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
            echo $inputConfig->render() . $inputConfig->getAdditionalHtml();
        }
    }
    echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates($formConfig->getAdditionalHtmlForForm());
}
?>

<?php View::startSection('scaffold-form-footer'); ?>
    <div class="box-footer">
        <div class="row">
            <div class="col-xs-3">
                {{? it.__modal }}
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php echo cmfTransGeneral('.form.toolbar.close'); ?>
                    </button>
                {{??}}
                    <button type="button" class="btn btn-default" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                        <?php echo cmfTransGeneral('.form.toolbar.cancel'); ?>
                    </button>
                {{?}}
            </div>
            <div class="col-xs-6 text-center">
            <?php echo $ifEdit; ?>
                <?php /*if ($formConfig->isCreateAllowed()) : ?>
                    <a class="btn btn-primary" href="<?php echo routeToCmfItemAddForm($tableNameForRoutes); ?>">
                        <?php echo cmfTransGeneral('.form.toolbar.create'); ?>
                    </a>
                <?php endif;*/ ?>
                <?php if ($formConfig->isDeleteAllowed()) : ?>
                    {{? !!it.___delete_allowed }}
                    <a class="btn btn-danger" href="#"
                       data-action="request" data-method="delete"
                       data-url="<?php echo routeTpl('cmf_api_delete_item', ['table_name' => $tableNameForRoutes], ['id' => 'it.__' . $table->getPkColumnName()]); ?>"
                       data-confirm="<?php echo cmfTransGeneral('.action.delete.please_confirm'); ?>"
                       data-on-sucess="CmfRoutingHelpers.closeCurrentModalAndReloadDataGrid">
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

<?php View::startSection('scaffold-form-enablers-script'); ?>
    <?php
        $enablers = [];
        foreach ($formConfig->getFormInputs() as $inputConfig) {
            if ($inputConfig->hasDisablersConfigs()) {
                $enablers[] = $inputConfig->getDisablersConfigs();
            }
        }
    ?>
    <?php if (count($enablers) > 0) : ?>
        <script type="application/javascript">
            $('#<?php echo $formId; ?>').on('ready.cmfform', function () {
                FormHelper.inputsDisablers.init(this, <?php echo json_encode($enablers, JSON_UNESCAPED_UNICODE); ?>, true);
            });
        </script>
    <?php endif ?>
<?php View::stopSection(); ?>

<?php View::startSection('scaffold-form'); ?>
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
        <?php echo $endIf; ?>
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

            <div class="<?php echo $hasTabs ? 'tab-content' : 'box {{? it.__modal }} br-t-n {{??}} box-primary {{?}}' ?>">
                <?php foreach ($tabs as $idx => $tabInfo): ?>
                    <?php $class = $hasTabs ? 'tab-pane' . ($idx === 0 ? ' active' : '') : 'box-body'; ?>
                    <div role="tabpanel" class=" <?php echo $class ?>" id="<?php echo $formId . '-' . (string)($idx + 1) ?>">
                        <?php $buildInputs($tabInfo); ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$hasTabs) : ?>
                    {{# def.footer() }}
                <?php endif ?>
            </div>
            <?php if ($hasTabs) : ?>
                {{# def.footer() }}
            <?php endif ?>
        </div>
    </form>
    <?php echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates(View::yieldContent('scaffold-form-enablers-script')); ?>
<?php View::stopSection(); ?>

<script type="text/html" id="item-form-tpl">
    {{##def.footer = function () {
        return Base64.decode('<?php echo base64_encode(View::yieldContent('scaffold-form-footer')); ?>');
    }
    #}}
    {{##def.form = function () {
        return Base64.decode('<?php echo base64_encode(View::yieldContent('scaffold-form')); ?>');
    }
    #}}
    {{##def.title:
        <?php
            echo $ifEdit . $formConfig->translate(null, 'header_edit')
                . $else . $formConfig->translate(null, 'header_create')
                . $endIf
        ?>
    #}}
    {{? it.__modal }}
        <div class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog <?php echo $formConfig->getWidth() >= 60 ? 'modal-lg' : 'modal-md' ?>">
                <div class="modal-content item-form-modal-content">
                    <div class="modal-header pv10">
                        <div class="box-tools pull-right">
                            <a class="btn btn-box-tool fs13 va-t ptn mt5"
                               href="<?php echo $pageUrl ?>"
                               data-toggle="tooltip" title="<?php echo cmfTransGeneral('.modal.reload'); ?>">
                                <i class="glyphicon glyphicon-refresh"></i>
                            </a>
<!--                            <button type="button" data-action="reload" class="btn btn-box-tool fs13 va-t ptn mt5"-->
<!--                                    data-toggle="tooltip" title="--><?php //echo cmfTransGeneral('.modal.reload'); ?><!--">-->
<!--                                <i class="glyphicon glyphicon-refresh"></i>-->
<!--                            </button>-->
                            <a class="btn btn-box-tool fs13 va-t ptn mt5" target="_blank"
                               href="<?php echo $pageUrl; ?>"
                               data-toggle="tooltip" title="<?php echo cmfTransGeneral('.modal.open_in_new_tab'); ?>">
                                <i class="glyphicon glyphicon-share"></i>
                            </a>
                            <button type="button" data-dismiss="modal" class="btn btn-box-tool va-t pbn ptn mt5"
                                    data-toggle="tooltip" title="<?php echo cmfTransGeneral('.modal.close'); ?>">
                                <span class="fs24 lh15">&times;</span>
                            </button>
                        </div>
                        <h4 class="modal-title lh30">{{# def.title }}</h4>
                    </div>
                    <div class="modal-body pn">
                        {{# def.form() }}
                    </div>
                </div>
            </div>
        </div>
    {{??}}
        <?php echo view('cmf::ui.default_page_header', [
            'header' => '{{# def.title }}',
            'defaultBackUrl' => $backUrl,
        ])->render(); ?>
        <div class="content">
            <div class="row">
                <div class="<?php echo $formConfig->getCssClassesForContainer() ?>">
                    {{# def.form() }}
                </div>
            </div>
        </div>
    {{?}}
</script>

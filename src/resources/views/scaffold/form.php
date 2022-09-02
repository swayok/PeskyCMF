<?php
declare(strict_types=1);

use PeskyCMF\CmfUrl;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

/**
 * @var TableInterface $table
 * @var FormConfig $formConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */

$formId = "scaffold-form-{$idSuffix}";
$pkColName = $table::getPkColumnName();

$ifEdit = '{{? !it._is_creation }}';
$ifCreate = '{{? it._is_creation }}';
$else = '{{??}}';
$endIf = '{{?}}';
$ifClone = '{{? !!it._is_cloning }}';

$pageUrl = $ifEdit
        . CmfUrl::toItemEditForm($tableNameForRoutes, '{{= it.___pk_value}}', false, $formConfig->getCmfConfig())
    . $else
        . $ifClone
            . CmfUrl::toItemCloneForm($tableNameForRoutes, '{{= it.___pk_value}}', false, $formConfig->getCmfConfig())
        . $else
            . CmfUrl::toItemAddForm($tableNameForRoutes, [], false, $formConfig->getCmfConfig())
        .$endIf
    . $endIf;
$backUrl = CmfUrl::toItemsTable($tableNameForRoutes, [], false, $formConfig->getCmfConfig());
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
    echo $formConfig->getCmfConfig()->getUiModule()::modifyDotJsTemplateToAllowInnerScriptsAndTemplates($formConfig->getAdditionalHtmlForForm());
};

$viewFactory = $formConfig->getCmfConfig()->getViewsFactory();
?>

<?php $viewFactory->startSection('scaffold-form-footer'); ?>
    <div class="box-footer">
        <div class="row toolbar">
            <div class="<?php echo $ifCreate; ?>col-xs-3<?php echo $else; ?>col-xs-5<?php echo $endIf; ?> toolbar-left">
                {{? it.__modal }}
                    <button type="button" class="btn btn-default mr5" data-dismiss="modal">
                        <?php echo $formConfig->translateGeneral('toolbar.close'); ?>
                    </button>
                {{??}}
                    <button type="button" class="btn btn-default mr5" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                        <?php echo $formConfig->translateGeneral('toolbar.cancel'); ?>
                    </button>
                {{?}}
                <?php echo $ifEdit . $formConfig->getItemDeleteMenuItem()->renderAsButton(false) . $endIf; ?>
            </div>
            <div class="<?php echo $ifCreate; ?>col-xs-9<?php echo $else; ?>col-xs-7<?php echo $endIf; ?> text-right toolbar-right">
                <div class="btn-group ib" role="group">
                    <button type="submit" class="btn <?php echo $ifCreate; ?>btn-primary<?php echo $else; ?>btn-success<?php echo $endIf; ?>">
                        <?php echo $formConfig->translateGeneral('toolbar.submit'); ?>
                    </button>
                    <?php if (!$formConfig->getScaffoldConfig() instanceof KeyValueTableScaffoldConfig): ?>
                        <?php echo $ifCreate; ?>
                            <input type="submit" class="btn btn-primary" name="create_another" value="+1" data-toggle="tooltip"
                                   title="<?php echo $formConfig->translateGeneral('toolbar.submit_and_add_another'); ?>">
                        <?php echo $else; ?>
                            <?php echo $formConfig->getItemCloneMenuItem()->renderAsIcon('btn btn-success', false) ?>
                        <?php echo $endIf; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php $toolbarItems = $formConfig->getToolbarItems(); ?>
        <?php if (count($toolbarItems) > 0) : ?>
            <div class="mt10 text-center">
                <?php
                    foreach ($toolbarItems as $item) {
                        if ($item instanceof Tag) {
                            echo $item->build();
                        } else if ($item instanceof PeskyCMF\Scaffold\MenuItem\CmfMenuItem) {
                            echo $item->renderAsButton();
                        } else {
                            echo $item;
                        }
                    }
                ?>
            </div>
        <?php endif; ?>
    </div>
<?php $viewFactory->stopSection(); ?>

<?php $viewFactory->startSection('scaffold-form-enablers-script'); ?>
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
<?php $viewFactory->stopSection(); ?>

<?php $viewFactory->startSection('scaffold-form'); ?>
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
        $editUrl = CmfUrl::routeTpl('cmf_api_update_item', ['resource' => $tableNameForRoutes], ['id' => 'it.___pk_value'], false, $formConfig->getCmfConfig());
        $createUrl = CmfUrl::route('cmf_api_create_item', ['resource' => $tableNameForRoutes], false, $formConfig->getCmfConfig());
        $formAction = $ifEdit . $editUrl . $else . $createUrl . $endIf;
    ?>
    <form role="form" method="post" action="<?php echo $formAction; ?>" <?php echo Tag::buildAttributes($formAttributes); ?>
    data-uuid="{{= it.formUUID }}">
        <?php echo $ifEdit; ?>
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="<?php echo $pkColName; ?>" value="{{= it.___pk_value }}">
        <?php echo $else; ?>
            <?php echo $ifClone; ?>
                <input type="hidden" name="_clone" value="{{= it.___pk_value }}">
            <?php echo $endIf; ?>
        <?php echo $endIf; ?>
        <?php include __DIR__ . '/../input/login_inputs_autofill_disabler.php'; ?>
        <div class="nav-tabs-custom mbn">
            <?php if ($hasTabs): ?>
                <ul class="nav nav-tabs">
                    <?php foreach ($tabs as $idx => $tabInfo): ?>
                        <li class="<?php echo $idx === 0 ? 'active' : '' ?>">
                            <a href="#<?php echo $formId . '-' . ($idx + 1) ?>" data-toggle="tab" role="tab">
                                <?php echo empty($tabInfo['label']) ? '*' : $tabInfo['label']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="<?php echo $hasTabs ? 'tab-content' : 'box {{? it.__modal }} mbn br-t-n {{??}} ' . $ifCreate . 'box-primary' . $else . 'box-success' . $endIf . ' {{?}}' ?>">
                <?php foreach ($tabs as $idx => $tabInfo): ?>
                    <?php $class = $hasTabs ? 'tab-pane' . ($idx === 0 ? ' active' : '') : 'box-body'; ?>
                    <div role="tabpanel" class=" <?php echo $class ?>" id="<?php echo $formId . '-' . ($idx + 1) ?>">
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
    <?php echo $formConfig->getCmfConfig()->getUiModule()::modifyDotJsTemplateToAllowInnerScriptsAndTemplates($viewFactory->yieldContent('scaffold-form-enablers-script')); ?>
<?php $viewFactory->stopSection(); ?>

<script type="text/html" id="item-form-tpl">
    {{##def.footer = function () {
        return Base64.decode('<?php echo base64_encode($viewFactory->yieldContent('scaffold-form-footer')); ?>');
    }
    #}}
    {{##def.form = function () {
        return Base64.decode('<?php echo base64_encode($viewFactory->yieldContent('scaffold-form')); ?>');
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
            <div class="modal-dialog modal-<?php echo $formConfig->getModalSize() ?>">
                <div class="modal-content item-form-modal-content <?php echo $ifCreate; ?>item-creation<?php echo $else; ?>item-editing<?php echo $endIf; ?>">
                    <div class="modal-header pv10">
                        <div class="box-tools pull-right">
                            <a class="btn btn-box-tool fs13 va-t ptn mt5"
                               href="<?php echo $pageUrl ?>"
                               data-toggle="tooltip" title="<?php echo $formConfig->translateGeneral('modal.reload'); ?>">
                                <i class="glyphicon glyphicon-refresh"></i>
                            </a>
<!--                            <button type="button" data-action="reload" class="btn btn-box-tool fs13 va-t ptn mt5"-->
<!--                                    data-toggle="tooltip" title="--><?php //echo $formConfig->translateGeneral('modal.reload'); ?><!--">-->
<!--                                <i class="glyphicon glyphicon-refresh"></i>-->
<!--                            </button>-->
                            <a class="btn btn-box-tool fs13 va-t ptn mt5" target="_blank"
                               href="<?php echo $pageUrl; ?>"
                               data-toggle="tooltip" title="<?php echo $formConfig->translateGeneral('modal.open_in_new_tab'); ?>">
                                <i class="glyphicon glyphicon-share"></i>
                            </a>
                            <button type="button" data-dismiss="modal" class="btn btn-box-tool va-t pbn ptn mt5"
                                    data-toggle="tooltip" title="<?php echo $formConfig->translateGeneral('modal.close'); ?>">
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
            'cmfConfig' => $formConfig->getCmfConfig()
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

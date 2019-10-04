<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var string $tableNameForRoutes
 * @var string $translationPrefix
 * @var string $idSuffix
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
$fieldConfigs = $itemDetailsConfig->getFields();
$backUrl = route('cmf_items_table', ['table_name' => $tableNameForRoutes], false);

$jsInitiator = '';
if ($itemDetailsConfig->hasJsInitiator()) {
    $jsInitiator = 'data-initiator="' . addslashes($itemDetailsConfig->getJsInitiator()) . '"';
}
?>

<?php View::startSection('item-detials-table') ;?>
    <table class="table table-striped table-bordered mn item-details-table" <?php echo $jsInitiator; ?>>
        <?php foreach ($fieldConfigs as $config) : ?>
        <tr id="item-details-<?php echo $config->getName(); ?>">
            <th class="text-nowrap">
                <?php
                    if ($config->hasLabel()) {
                        echo $config->getLabel();
                    } else {
                        echo trans("$translationPrefix.item_details.field.{$config->getName()}");
                    }
                ?>
            </th>
            <td width="80%">
                <?php
                    try {
                        echo modifyDotJsTemplateToAllowInnerScriptsAndTemplates(
                            $config->render(['translationPrefix' => $translationPrefix])
                        );
                    } catch (Exception $exc) {
                        echo '<div>' . $exc->getMessage() . '</div>';
                        echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
                    }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php View::stopSection(); ?>

<?php View::startSection('item-detials-footer') ;?>
    <div class="row">
        <div class="col-xs-3 text-left">
            {{? it._modal }}
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.close'); ?>
                </button>
            {{??}}
                <button type="button" class="btn btn-default" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.cancel'); ?>
                </button>
            {{?}}
            <?php if ($itemDetailsConfig->isCreateAllowed()) : ?>
                <?php
                    $createUrl = route('cmf_item_add_form', ['table_name' => $tableNameForRoutes]);
                ?>
                <a class="btn btn-primary" href="<?php echo $createUrl; ?>">
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.create'); ?>
                </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-9 text-right">
            <?php
                foreach ($itemDetailsConfig->getToolbarItems() as $toolbarItem) {
                    echo ' ' . preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%is', '{{= it.$2 }}', $toolbarItem) . ' ';
                }
            ?>
            <?php if ($itemDetailsConfig->isDeleteAllowed()) : ?>
                <?php
                    $deleteUrl = str_ireplace(
                        ':id:',
                        "{{= it.{$model->getPkColumnName()} }}",
                        route('cmf_api_delete_item', ['table_name' => $tableNameForRoutes, 'id' => ':id:'])
                    );
                ?>
                {{? !!it.___delete_allowed }}
                <a class="btn btn-danger" href="#"
                data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                data-confirm="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.action.delete.please_confirm'); ?>">
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.delete'); ?>
                </a>
                {{?}}
            <?php endif; ?>
            <?php if ($itemDetailsConfig->isEditAllowed()) : ?>
                <?php
                    $editUrl = str_ireplace(
                        ':id:',
                        "{{= it.{$model->getPkColumnName()} }}",
                        route('cmf_item_edit_form', ['table_name' => $tableNameForRoutes, 'id' => ':id:'])
                    );
                ?>
                {{? !!it.___edit_allowed }}
                <a class="btn btn-success" href="<?php echo $editUrl; ?>">
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.edit'); ?>
                </a>
                {{?}}
            <?php endif; ?>
        </div>
    </div>
<?php View::stopSection(); ?>

<script type="text/html" id="item-details-tpl">
    {{? it._modal }}
        <div class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                        aria-label="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><?php echo trans("$translationPrefix.item_details.header"); ?></h4>
                    </div>
                    <div class="modal-body pn">
                        <?php echo View::yieldContent('item-detials-table'); ?>
                    </div>
                    <div class="modal-footer">
                        <?php echo View::yieldContent('item-detials-footer'); ?>
                    </div>
                </div>
            </div>
        </div>
    {{??}}
        <?php echo view('cmf::ui.default_page_header', [
            'header' => trans("$translationPrefix.item_details.header"),
            'defaultBackUrl' => $backUrl,
        ])->render(); ?>
        <div class="content">
            <div class="row"><div class="<?php echo $itemDetailsConfig->getCssClassesForContainer() ?>">
                <div class="box">
                    <div class="box-body pn">
                        <?php echo View::yieldContent('item-detials-table'); ?>
                    </div>
                    <div class="box-footer">
                        <?php echo View::yieldContent('item-detials-footer'); ?>
                    </div>
                </div>
            </div></div>
        </div>
    {{?}}
</script>
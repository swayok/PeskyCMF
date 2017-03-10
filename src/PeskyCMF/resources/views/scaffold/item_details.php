<?php
/**
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
$valueViewers = $itemDetailsConfig->getValueCells();
$backUrl = routeToCmfItemsTable($tableNameForRoutes);

$jsInitiator = '';
if ($itemDetailsConfig->hasJsInitiator()) {
    $jsInitiator = 'data-initiator="' . addslashes($itemDetailsConfig->getJsInitiator()) . '"';
}
?>

<?php View::startSection('item-detials-table') ;?>
    <table class="table table-striped table-bordered mn item-details-table" <?php echo $jsInitiator; ?>>
        <?php foreach ($valueViewers as $viewer) : ?>
        <tr id="item-details-<?php echo $viewer->getName(); ?>">
            <th class="text-nowrap">
                <?php echo $viewer->getLabel(); ?>
            </th>
            <td width="80%">
                <?php
                    try {
                        echo $viewer->render();
                    } catch (Exception $exc) {
                        echo '<div>' . htmlspecialchars($exc->getMessage()) . '</div>';
                        echo '<pre>' . nl2br(htmlspecialchars($exc->getTraceAsString())) . '</pre>';
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
                    <?php echo cmfTransGeneral('.form.toolbar.close'); ?>
                </button>
            {{??}}
                <button type="button" class="btn btn-default" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.cancel'); ?>
                </button>
            {{?}}
            <?php if ($itemDetailsConfig->isCreateAllowed()) : ?>
                <a class="btn btn-primary" href="<?php echo routeToCmfItemAddForm($tableNameForRoutes); ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.create'); ?>
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
                        route('cmf_api_delete_item', [$tableNameForRoutes, ':id:'])
                    );
                ?>
                {{? !!it.___delete_allowed }}
                <a class="btn btn-danger" href="#"
                data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                data-confirm="<?php echo cmfTransGeneral('.action.delete.please_confirm'); ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.delete'); ?>
                </a>
                {{?}}
            <?php endif; ?>
            <?php if ($itemDetailsConfig->isEditAllowed()) : ?>
                <?php
                    $editUrl = str_ireplace(
                        ':id:',
                        "{{= it.{$model->getPkColumnName()} }}",
                        routeToCmfItemEditForm($tableNameForRoutes, ':id:')
                    );
                ?>
                {{? !!it.___edit_allowed }}
                <a class="btn btn-success" href="<?php echo $editUrl; ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.edit'); ?>
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
                        aria-label="<?php echo cmfTransGeneral('.form.toolbar.close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><?php echo $itemDetailsConfig->translate(null, 'header'); ?></h4>
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
            'header' => $itemDetailsConfig->translate(null, 'header'),
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
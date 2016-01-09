<?php
/**
 * @var \App\Db\BaseDbModel $model
 * @var \App\Admin\Scaffold\Form\FormConfig $formConfig
 * @var string $translationPrefix
 * @var string $idSuffix
 */
$formId = "scaffold-form-{$idSuffix}";
$pkColName = $model->getPkColumnName();

$ifEdit = "{{? it.{$pkColName} > 0 }}";
$else = '{{??}}';
$endIf = '{{?}}';
$printPk = "{{= it.{$pkColName} }}";

$backUrl = route('cmf_items_table', ['table_name' => $model->getTableName()], false);
?>

<script type="text/html" id="item-form-tpl">
    <div class="content-header">
        <h1>
            <?php
                echo $ifEdit . trans("$translationPrefix.form.header_edit")
                    . $else . trans("$translationPrefix.form.header_create")
                    . $endIf;
            ?>
        </h1>
        <ol class="breadcrumb">
            <li>
                <a href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <i class="glyphicon fa fa-reply"></i>
                    <?php echo trans('cmf::cmf.action.back'); ?>
                </a>
            </li>
            <li>
                <a href="#" data-nav="reload">
                    <i class="glyphicon glyphicon-refresh"></i>
                    <?php echo trans('cmf::cmf.action.reload_page'); ?>
                </a>
            </li>
        </ol>
    </div>
    <div class="content">
        <?php
            $cols = $formConfig->getWidth() >= 100 ? 12 : ceil(12 * ($formConfig->getWidth() / 100));
            $colsLeft = floor((12 - $cols) / 2);
        ?>
        <div class="row">
            <div class="col-xs-<?php echo $cols; ?> col-xs-offset-<?php echo $colsLeft; ?>">
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
                        $editUrl = route('cmf_api_update_item', ['table_name' => $model->getTableName(), 'id' => ''], false) . '/' . $printPk;
                        $createUrl = route('cmf_api_create_item', ['table_name' => $model->getTableName()], false);
                        $formAction = $ifEdit . $editUrl . $else . $createUrl . $endIf;
                    ?>
                    <form role="form" method="post" action="<?php echo $formAction; ?>" <?php echo \Swayok\Html\Tag::buildAttributes($formAttributes); ?>>
                        <?php echo $ifEdit; ?>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="<?php echo $pkColName; ?>" value="<?php echo $printPk; ?>">
                        <?php echo $endIf ?>
                        <!-- disable chrome email & password autofill -->
                        <input type="email" class="hidden"><input type="password" class="hidden">
                        <!-- end of autofill disabler -->
                        <div class="box-body">
                        <?php
                            foreach ($formConfig->getFields() as $config) {
                                if (!$config->hasLabel()) {
                                    $config->setLabel(trans("$translationPrefix.form.field.{$config->getName()}"));
                                }
                                if ($model->getTableColumn($config->getName())->isRequiredOnAnyAction()) {
                                    $config->setLabel($config->getLabel() . '*');
                                }
                                try {
                                    $renderedInput = $config->render();
                                    // replace <script> tags to be able to render that template
                                    echo preg_replace_callback('%<script([^>]*)>(.*?)</script>%is', function ($matches) {
                                        return "{{= '<' + 'script{$matches[1]}>' }}$matches[2]{{= '</' + 'script>'}}";
                                    }, $renderedInput);
                                } catch (Exception $exc) {
                                    echo '<div>' . $exc->getMessage() . '</div>';
                                    echo '<pre>' . $exc->getTraceAsString() . '</pre>';
                                }
                            }
                        ?>
                        </div>
                        <div class="box-footer">
                            <div class="row">
                                <div class="col-xs-3">
                                    <a class="btn btn-default" href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                                        <?php echo trans('cmf::cmf.form.toolbar.cancel'); ?>
                                    </a>
                                </div>
                                <div class="col-xs-6 text-center">
                                <?php echo $ifEdit; ?>
                                    <?php if ($formConfig->isCreateAllowed()) : ?>
                                        <?php
                                            $createUrl = route('cmf_item_add_form', [$model->getTableName()]);
                                        ?>
                                        <a class="btn btn-primary" href="<?php echo $createUrl; ?>">
                                            <?php echo trans('cmf::cmf.form.toolbar.create'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($formConfig->isDeleteAllowed()) : ?>
                                        <?php
                                            $deleteUrl = str_ireplace(
                                                ':id:',
                                                "{{= it.{$model->getPkColumnName()} }}",
                                                route('cmf_api_delete_item', [$model->getTableName(), ':id:'])
                                            );
                                        ?>
                                        <a class="btn btn-danger" href="#"
                                        data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                                        data-confirm="<?php echo trans('cmf::cmf.action.delete.please_confirm'); ?>">
                                            <?php echo trans('cmf::cmf.form.toolbar.delete'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php echo $endIf; ?>
                                </div>
                                <div class="col-xs-3">
                                    <button type="submit" class="btn btn-success pull-right"><?php echo trans('cmf::cmf.form.toolbar.submit'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</script>

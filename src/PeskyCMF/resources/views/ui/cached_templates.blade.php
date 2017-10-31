<?php
/**
 * @var array $pages
 * @var array $resources
 */
?>

var CmfTemplates = {
    pages: <?php echo json_encode($pages, JSON_UNESCAPED_UNICODE) ?>,
    resources: <?php echo json_encode($resources, JSON_UNESCAPED_UNICODE) ?>
};
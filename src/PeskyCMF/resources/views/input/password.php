<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$rendererConfig
    ->addAttribute('autocomplete', 'new-password', false)
    ->addAttribute('type', 'password', true);
?>

<?php include __DIR__ . '/password_inputs_autofill_disabler.php'; ?>

<?php include __DIR__ . '/text.php'; ?>

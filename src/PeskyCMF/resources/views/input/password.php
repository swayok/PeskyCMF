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

<!-- disable chrome email & password autofill -->
<input type="text" name="login" class="hidden" formnovalidate disabled>
<input type="password" class="hidden" formnovalidate disabled>
<input type="text" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
<input type="password" class="hidden" formnovalidate disabled>
<input type="email" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
<input type="password" class="hidden" formnovalidate disabled>
<input type="email" formnovalidate style="display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;" value="test@test.com">
<input type="password" formnovalidate style="display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;">
<!-- end of autofill disabler -->

<?php include __DIR__ . '/text.php'; ?>

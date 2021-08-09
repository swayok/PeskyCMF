<?php $hidingStyles = 'display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;" value="test@test.com'; ?>
<!-- disable chrome email & password autofill -->
<input type="text" name="email" formnovalidate value="test@test.com" disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="name" formnovalidate value="test" disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="login" formnovalidate value="test" disabled style="<?php echo $hidingStyles ?>" >
<input type="email" name="email" formnovalidate value="test@test.com" disabled style="<?php echo $hidingStyles ?>" >
<input type="email" formnovalidate value="test@test.com" style="<?php echo $hidingStyles ?>" >
<input name="login" type="text" value="test" formnovalidate style="<?php echo $hidingStyles ?>" >
<input name="name" type="text" value="test" formnovalidate style="<?php echo $hidingStyles ?>" >
<!-- end of autofill disabler -->
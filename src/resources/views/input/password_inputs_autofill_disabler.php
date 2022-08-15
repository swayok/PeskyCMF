<?php $hidingStyles = 'display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;" value="test@test.com'; ?>
<!-- disable chrome email & password autofill -->
<input type="text" name="__login" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__name" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="password" name="__password" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__email" formnovalidate value="test@test.com" disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__name" formnovalidate value="test" disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__login" formnovalidate value="test" disabled style="<?php echo $hidingStyles ?>" >
<input type="password" name="__password" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="email" name="__email" formnovalidate value="test@test.com" disabled style="<?php echo $hidingStyles ?>" >
<input type="password" name="__password" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__login" value="test" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="text" name="__name" value="test" formnovalidate disabled style="<?php echo $hidingStyles ?>" >
<input type="email" formnovalidate value="test@test.com" style="<?php echo $hidingStyles ?>" >
<input type="password" formnovalidate style="<?php echo $hidingStyles ?>">
<!-- end of autofill disabler -->
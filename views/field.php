<?php
$limits=['title'=>100,'progress'=>2000,'next_step'=>2000,'memo'=>4000];
$hints=['title'=>'title_hint','progress'=>'progress_hint','next_step'=>'next_hint','memo'=>'memo_hint'];
$required=in_array($field,['title','progress'],true);$invalid=isset($errors[$field]);
?>
<div class="field"><label for="<?= $field ?>"><?= e(t($field)) ?><span class="<?= $required?'required':'optional' ?>"><?= e(t($required?'required':'optional')) ?></span></label>
<?php if($field==='title'): ?>
<input type="text" id="title" name="title" value="<?= e($values[$field]) ?>" maxlength="100" required placeholder="<?= e(t($hints[$field])) ?>" aria-describedby="title-hint<?= $invalid?' title-error':'' ?>" <?= $invalid?'aria-invalid="true"':'' ?>>
<?php else: ?><textarea id="<?= $field ?>" name="<?= $field ?>" rows="<?= $field==='next_step'?3:4 ?>" maxlength="<?= $limits[$field] ?>" <?= $required?'required':'' ?> placeholder="<?= e(t($hints[$field])) ?>" aria-describedby="<?= $field ?>-hint<?= $invalid?' '.$field.'-error':'' ?>" <?= $invalid?'aria-invalid="true"':'' ?>><?= e($values[$field]) ?></textarea><?php endif; ?>
<p class="field-hint" id="<?= $field ?>-hint"><?= e(t('limit',['n'=>$limits[$field]])) ?></p>
<?php if($invalid): ?><p class="field-error" id="<?= $field ?>-error"><?= e(t($errors[$field])) ?></p><?php endif; ?></div>

<a class="back-link" href="<?= e(url()) ?>">← <?= e(t('back')) ?></a>
<div class="page-heading"><p class="eyebrow">A NOTE TO FUTURE YOU</p><h1><?= e(t($view==='new'?'new_title':'edit_title')) ?><span class="heading-spark">✦</span></h1><p><?= e(t('form_intro')) ?></p></div>
<div class="form-layout"><form class="save-form panel" method="post" action="<?= e(url()) ?>" aria-label="<?= e($title) ?>">
<?= form_fields($view==='new'?'create':'update',$view==='new'?null:$id) ?>
<?php if($view==='edit'): ?><input type="hidden" name="version" value="<?= $version ?>"><?php endif; ?>
<div class="form-section-label"><span>01</span><?= e(t('about')) ?></div>
<?php $field='title';require __DIR__.'/field.php'; ?>
<div class="form-two-columns">
<div class="field"><label for="category"><?= e(t('category')) ?></label><select id="category" name="category" <?= isset($errors['category'])?'aria-invalid="true" aria-describedby="category-error"':'' ?>>
<?php foreach(SAVE_CATEGORIES as $option): ?><option value="<?= $option ?>" <?= $values['category']===$option?'selected':'' ?>><?= e(t($option)) ?></option><?php endforeach; ?></select>
<?php if(isset($errors['category'])): ?><p class="field-error" id="category-error"><?= e(t($errors['category'])) ?></p><?php endif; ?></div>
<fieldset class="field status-field" id="status"><legend><?= e(t('status')) ?></legend><div class="status-picker">
<?php foreach(SAVE_STATUSES as $option): ?><label><input type="radio" name="status" value="<?= $option ?>" <?= $values['status']===$option?'checked':'' ?> required><span><?= e(t($option)) ?></span></label><?php endforeach; ?></div>
<?php if(isset($errors['status'])): ?><p class="field-error"><?= e(t($errors['status'])) ?></p><?php endif; ?></fieldset></div>
<div class="form-divider"></div><div class="form-section-label"><span>02</span><?= e(t('notes')) ?></div>
<?php foreach(['progress','next_step','memo'] as $field) require __DIR__.'/field.php'; ?>
<div class="form-actions"><a class="button secondary" href="<?= e($id?url(['view'=>'detail','id'=>$id]):url()) ?>"><?= e(t('cancel')) ?></a><button class="button primary" type="submit"><?= icon('save') ?><?= e(t('save')) ?></button></div>
<p class="form-prototype-note"><?= e(t('form_note')) ?></p>
</form><aside class="form-side"><div class="tip-panel"><div class="tip-icon"><?= icon('other') ?></div><p class="eyebrow">A NOTE TO FUTURE YOU</p><h2><?= e(t('step_two')) ?></h2><p><?= e(t('form_intro')) ?></p><div class="tip-example"><p><?= e(t('progress_hint')) ?></p></div><span class="tip-line">PAUSE. SAVE. CONTINUE.</span></div></aside></div>

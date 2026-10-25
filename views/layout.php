<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#10141e"><title><?= e($title) ?> | ReStart</title>
<link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="php-app">
<a class="skip-link" href="#main"><?= e(t('skip')) ?></a>
<aside class="sidebar">
<a class="brand" href="<?= e(url()) ?>"><span class="brand-mark"><?= icon('save') ?></span><span>Re<span class="brand-accent">Start</span><small><?= e(t('brand_note')) ?></small></span></a>
<p class="sidebar-section">YOUR ADVENTURE</p>
<nav class="main-nav" aria-label="<?= e(t('nav')) ?>">
<?php foreach(['all','active','paused','completed'] as $navStatus): $selected=$view==='list'&&$status===$navStatus; ?>
<a class="nav-item <?= $selected?'selected':'' ?>" href="<?= e(url(['status'=>$navStatus])) ?>" <?= $selected?'aria-current="page"':'' ?>><?= icon($navStatus) ?><span><?= e(t($navStatus)) ?></span><span class="nav-count"><?= $counts[$navStatus] ?></span></a>
<?php endforeach; ?></nav>
<div class="sidebar-note"><span class="tiny-stars">✦　✧　✦</span><p><?= e(t('tagline')) ?></p></div>
<div class="sidebar-bottom"><a class="nav-item <?= $view==='help'?'selected':'' ?>" href="<?= e(url(['view'=>'help'])) ?>" <?= $view==='help'?'aria-current="page"':'' ?>><?= icon('help') ?><span><?= e(t('help')) ?></span></a><div class="local-status"><span class="status-dot"></span><?= e(t('local')) ?></div></div>
</aside>
<div class="workspace">
<header class="topbar"><span class="topbar-label">LITTLE MOMENTS. BIG ADVENTURES.</span><div class="topbar-right">
<nav class="language-switch" aria-label="<?= e(t('language')) ?>">
<?php foreach(['zh-Hant'=>'繁體中文','ja'=>'日本語','en'=>'English'] as $code=>$label):
$params=['view'=>$view==='error'?'list':$view,'lang'=>$code];if($id!==null)$params['id']=$id;
if($view==='list')$params+=['status'=>$status,'category'=>$category,'page'=>$page]; ?>
<a href="<?= e(url($params)) ?>" lang="<?= e($code) ?>" hreflang="<?= e($code) ?>" <?= $lang===$code?'aria-current="page"':'' ?>><?= e($label) ?></a>
<?php endforeach; ?></nav><span class="prototype-tag"><?= e(t('local')) ?></span></div></header>
<main id="main" class="main-content" tabindex="-1">
<?php if($message): $isError=http_response_code()>=400; ?>
<div class="notice <?= $isError?'notice-error':'notice-success' ?>" role="<?= $isError?'alert':'status' ?>">
<p><?= e(t($message)) ?></p>
<?php if($errors): ?><ul><?php foreach($errors as $field=>$key): ?><li><a href="#<?= e($field) ?>"><?= e(t($field)) ?>：<?= e(t($key)) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
<?php if($message==='conflict'&&$id!==null): ?><a class="preview-link" href="<?= e(url(['view'=>'detail','id'=>$id])) ?>"><?= e(t('latest')) ?></a><?php endif; ?>
</div><?php endif; ?>
<?php require __DIR__.'/'.$view.'.php'; ?>
</main><footer class="page-footer"><span>ReStart / <?= e(t('brand_note')) ?></span><span>PAUSE. SAVE. CONTINUE. ✦</span></footer>
</div></body></html>

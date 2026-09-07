<?php
$pageTitle=$pageTitle??'РемонтКлюч';
$root=$root??'';
$user=current_user();?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($pageTitle)?>
 – РемонтКлюч</title><link rel="stylesheet" href="<?=e($root)?>
css/style.css"><link rel="stylesheet" href="<?=e($root)?>
css/backend.css"></head><body><header class="header"><div class="layout__container header__inner"><a class="logo" href="<?=e($root)?>
index.html"><img class="logo__image" src="<?=e($root)?>
images/logo_main.svg" alt="РемонтКлюч"></a><nav class="header__nav"><a class="header__link" href="<?=e($root)?>
index.html">Главная</a><a class="header__link" href="<?=e($root)?>
about.html">О компании</a><a class="header__link" href="<?=e($root)?>
services.html">Услуги</a><a class="header__link" href="<?=e($root)?>
portfolio.html">Портфолио</a><a class="header__link" href="<?=e($root)?>
calculator.html">Калькулятор</a><a class="header__link" href="<?=e($root)?>
contacts.html">Контакты</a></nav><?php
if($user):?>
<a class="button button--small" href="<?=e($root)?>
<?=is_admin()?'admin/index.php':'account.php'?>
"><?=is_admin()?'Админ-панель':'Кабинет'?>
</a><?php
else:?>
<a class="button button--small" href="<?=e($root)?>
login.php">Войти</a><?php
endif;?>
<button class="header__burger" type="button" aria-label="Открыть меню" aria-expanded="false"><span></span><span></span><span></span></button></div></header><?php
if($f=take_flash()):?>
<div class="layout__container"><div class="alert alert--<?=e($f['type'])?>
"><?=e($f['message'])?>
</div></div><?php
endif;?>

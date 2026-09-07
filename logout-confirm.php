<?php
require __DIR__.'/config/bootstrap.php';
require_auth();
$u=current_user();
$pageTitle='Выход';
include __DIR__.'/includes/header.php';?>
<main><section class="account-section"><div class="layout__container account"><aside class="account__nav"><h2><?=e($u['full_name'])?>
</h2><p><?=e($u['email'])?>
</p><a class="account__link" href="<?=is_admin()?'admin/index.php':'account.php'?>
"><?=is_admin()?'Админ-панель':'Мои заявки'?>
</a><?php
if(!is_admin()):?>
<a class="account__link" href="profile.php">Профиль</a><?php
endif;?>
<a class="account__link account__link--active" href="logout-confirm.php">Выйти</a></aside><div class="account__content logout-content"><h1>Выход из профиля</h1><div class="account__panel logout-panel"><h2>Вы уверены, что хотите выйти?</h2><p>После выхода потребуется повторно ввести электронную почту и пароль.</p><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><div class="hero__actions"><button class="button" type="submit">Выйти</button><a class="button button--outline" href="<?=is_admin()?'admin/index.php':'account.php'?>
">Отмена</a></div></form></div></div></div></section></main><?php
include __DIR__.'/includes/footer.php';?>

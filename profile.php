<?php
require __DIR__.'/config/bootstrap.php';
require_auth();
if(is_admin())redirect('admin/index.php');
$u=current_user();
$q=db()->prepare('SELECT * FROM clients WHERE id_user=?');
$q->execute([$u['id_user']]);
$c=$q->fetch();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
verify_csrf();
$name=trim($_POST['full_name']??'');
$phone=normalise_phone($_POST['phone']??'');
$address=trim($_POST['address']??'');
$district=trim($_POST['district']??'');
if($name==='')$errors['full_name']='Укажите Ф. И. О.';
if(!phone_is_valid($phone))$errors['phone']='Введите телефон полностью.';
if($district==='')$errors['district']='Укажите район.';
if(!$errors){
$pdo=db();
$pdo->beginTransaction();
$q=$pdo->prepare('UPDATE users SET full_name=?,phone=? WHERE id_user=?');
$q->execute([$name,$phone,$u['id_user']]);
$q=$pdo->prepare('UPDATE clients SET full_name=?,phone=?,address=?,district=? WHERE id_user=?');
$q->execute([$name,$phone,$address?:null,$district,$u['id_user']]);
$pdo->commit();
flash('success','Данные профиля обновлены.');
redirect('profile.php');
}
}
$pageTitle='Профиль';
include __DIR__.'/includes/header.php';?>
<main><section class="account-section"><div class="layout__container account"><aside class="account__nav"><h2><?=e($u['full_name'])?>
</h2><p><?=e($u['email'])?>
</p><a class="account__link" href="account.php">Мои заявки</a><a class="account__link account__link--active" href="profile.php">Профиль</a><a class="account__link" href="logout-confirm.php">Выйти</a></aside><div class="account__content"><h1>Профиль</h1><form class="account__panel profile-form" method="post" data-validate novalidate><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><h2>Личные данные</h2><?php
foreach(['full_name'=>'Ф. И. О.','phone'=>'Телефон','address'=>'Адрес','district'=>'Район'] as$n=>$l):$v=$_POST[$n]??($c[$n]??$u[$n]??'');?>
<div class="form-field"><label for="<?=e($n)?>
"><?=e($l)?>
</label><input id="<?=e($n)?>
" name="<?=e($n)?>
" <?=$n!=='address'?'required':''?>
 type="<?=$n==='phone'?'tel':'text'?>
" value="<?=e($v)?>
"><?php
if(isset($errors[$n])):?>
<small class="field-error"><?=e($errors[$n])?>
</small><?php
endif;?>
</div><?php
endforeach;?>
<div class="form-field"><label>Электронная почта</label><input value="<?=e($u['email'])?>
" disabled></div><button class="button" type="submit">Сохранить изменения</button></form></div></div></section></main><?php
include __DIR__.'/includes/footer.php';?>

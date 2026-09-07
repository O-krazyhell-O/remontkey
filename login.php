<?php
require __DIR__.'/config/bootstrap.php';
if(current_user())redirect(is_admin()?'admin/index.php':'account.php');
$errors=[];
$email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
verify_csrf();
$email=trim($_POST['email']??'');
$password=$_POST['password']??'';
if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors['email']='Введите корректный email.';
if($password==='')$errors['password']='Введите пароль.';
if(!$errors){
$q=db()->prepare('SELECT * FROM users WHERE email=?');
$q->execute([$email]);
$u=$q->fetch();
if(!$u||!password_verify($password,$u['password_hash']))$errors['common']='Неверная электронная почта или пароль.';
else{
session_regenerate_id(true);
$_SESSION['user_id']=$u['id_user'];
redirect((int)$u['id_role']===1?'admin/index.php':'account.php');
}
}
}
$pageTitle='Вход';
include __DIR__.'/includes/header.php';?>
<main class="auth"><div class="auth__layout"><img class="auth__image" src="images/bathroom.jpg" alt="Интерьер ванной"><form class="auth__card" method="post" data-validate novalidate><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><nav class="auth__tabs"><a class="auth__tab auth__tab--active" href="login.php">Вход</a><a class="auth__tab" href="register.php">Регистрация</a></nav><h1>Войти в аккаунт</h1><?php
if(isset($errors['common'])):?>
<div class="alert alert--error"><?=e($errors['common'])?>
</div><?php
endif;?>
<div class="form-field"><label for="email">Электронная почта</label><input id="email" name="email" required type="email" value="<?=e($email)?>
" placeholder="name@example.ru"><?php
if(isset($errors['email'])):?>
<small class="field-error"><?=e($errors['email'])?>
</small><?php
endif;?>
</div><div class="form-field"><label for="password">Пароль</label><input id="password" name="password" required type="password" placeholder="••••••••"><?php
if(isset($errors['password'])):?>
<small class="field-error"><?=e($errors['password'])?>
</small><?php
endif;?>
</div><button class="button" type="submit">Войти</button></form></div></main><?php
include __DIR__.'/includes/footer.php';?>

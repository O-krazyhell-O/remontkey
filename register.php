<?php
require __DIR__.'/config/bootstrap.php';
if(current_user())redirect(is_admin()?'admin/index.php':'account.php');
$errors=[];
$data=['full_name'=>'','email'=>'','phone'=>'','address'=>'','district'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
verify_csrf();
foreach($data as$k=>$v)$data[$k]=trim($_POST[$k]??'');
$data['phone']=normalise_phone($data['phone']);
$password=$_POST['password']??'';
if($data['full_name']==='')$errors['full_name']='Укажите Ф. И. О.';
if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL))$errors['email']='Введите корректный email.';
if(!phone_is_valid($data['phone']))$errors['phone']='Введите телефон полностью.';
if($data['district']==='')$errors['district']='Укажите район.';
if(strlen($password)<6)$errors['password']='Пароль должен содержать минимум 6 символов.';
$q=db()->prepare('SELECT 1 FROM users WHERE email=?');
$q->execute([$data['email']]);
if($q->fetch())$errors['email']='Пользователь с таким email уже зарегистрирован.';
if(!$errors){
$pdo=db();
try{
$pdo->beginTransaction();
$q=$pdo->prepare('INSERT INTO users(id_role,full_name,email,phone,password_hash,created_at) VALUES(2,?,?,?,?,NOW())');
$q->execute([$data['full_name'],$data['email'],$data['phone'],password_hash($password,PASSWORD_DEFAULT)]);
$id=(int)$pdo->lastInsertId();
$q=$pdo->prepare('INSERT INTO clients(id_user,full_name,phone,email,address,district) VALUES(?,?,?,?,?,?)');
$q->execute([$id,$data['full_name'],$data['phone'],$data['email'],$data['address']?:null,$data['district']]);
$pdo->commit();
session_regenerate_id(true);
$_SESSION['user_id']=$id;
flash('success','Регистрация завершена. Добро пожаловать!');
redirect('account.php');
}
catch(Throwable$e){
if($pdo->inTransaction())$pdo->rollBack();
$errors['common']='Не удалось завершить регистрацию.';
}
}
}
$pageTitle='Регистрация';
include __DIR__.'/includes/header.php';?>
<main class="auth"><div class="auth__layout"><img class="auth__image" src="images/bathroom.jpg" alt="Интерьер ванной"><form class="auth__card" method="post" data-validate novalidate><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><nav class="auth__tabs"><a class="auth__tab" href="login.php">Вход</a><a class="auth__tab auth__tab--active" href="register.php">Регистрация</a></nav><h1>Создать аккаунт</h1><?php
if(isset($errors['common'])):?>
<div class="alert alert--error"><?=e($errors['common'])?>
</div><?php
endif;?><?php
foreach(['full_name'=>'Ф. И. О.','email'=>'Электронная почта','phone'=>'Телефон','address'=>'Адрес','district'=>'Район','password'=>'Пароль'] as$n=>$label):?>
<div class="form-field"><label for="<?=e($n)?>
"><?=e($label)?>
</label><input id="<?=e($n)?>
" name="<?=e($n)?>
" <?=in_array($n,['full_name','email','phone','district','password'],true)?'required':''?>
 type="<?=$n==='email'?'email':($n==='phone'?'tel':($n==='password'?'password':'text'))?>
" value="<?=$n==='password'?'':e($data[$n]??'')?>
" placeholder="<?=$n==='phone'?'+7 (___) ___-__-__':''?>
"><?php
if(isset($errors[$n])):?>
<small class="field-error"><?=e($errors[$n])?>
</small><?php
endif;?>
</div><?php
endforeach;?>
<button class="button" type="submit">Зарегистрироваться</button></form></div></main><?php
include __DIR__.'/includes/footer.php';?>

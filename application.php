<?php
require __DIR__.'/config/bootstrap.php';
$u=current_user();
$types=db()->query('SELECT * FROM repair_types ORDER BY id_repair_type')->fetchAll();
$errors=[];
$data=['client_name'=>$u['full_name']??'','phone'=>$u['phone']??'','email'=>$u['email']??'','id_repair_type'=>'','area'=>'','rooms'=>'','message'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
verify_csrf();
foreach($data as$k=>$v)$data[$k]=trim((string)($_POST[$k]??''));
$data['phone']=normalise_phone($data['phone']);
if($data['client_name']==='')$errors['client_name']='Укажите имя.';
if(!phone_is_valid($data['phone']))$errors['phone']='Введите телефон полностью.';
if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL))$errors['email']='Введите корректный email.';
if(!ctype_digit($data['id_repair_type']))$errors['id_repair_type']='Выберите тип ремонта.';
if(!is_numeric($data['area'])||(float)$data['area']<=0)$errors['area']='Укажите площадь.';
if(!ctype_digit($data['rooms'])||(int)$data['rooms']<1)$errors['rooms']='Укажите количество комнат.';
if(!$errors){
$clientId=null;
if($u){
$q=db()->prepare('SELECT id_client FROM clients WHERE id_user=?');
$q->execute([$u['id_user']]);
$clientId=$q->fetchColumn()?:null;
}
$q=db()->prepare("INSERT INTO applications(id_client,id_repair_type,client_name,phone,email,area,rooms,message,status,created_at) VALUES(?,?,?,?,?,?,?,?, 'Новая',NOW())");
$q->execute([$clientId,$data['id_repair_type'],$data['client_name'],$data['phone'],$data['email'],$data['area'],$data['rooms'],$data['message']?:null]);
flash('success','Заявка отправлена. Специалист свяжется с вами.');
redirect($u&&!is_admin()?'account.php':'application.php');
}
}
$pageTitle='Заявка';
include __DIR__.'/includes/header.php';?>
<main><section class="application-section"><div class="layout__container application-layout"><div class="application-intro"><p class="section__eyebrow">ЗАЯВКА</p><h1>Обсудим ваш будущий ремонт</h1><p>Оставьте контакты и параметры квартиры.</p><img src="images/worker_plaster.jpg" alt="Мастер выполняет ремонт"></div><form class="form-card application-form" method="post" data-validate novalidate><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><h2>Оставить заявку</h2><div class="form-card__grid"><?php
foreach(['client_name'=>'Имя','phone'=>'Телефон','email'=>'Электронная почта','area'=>'Площадь, м²','rooms'=>'Количество комнат'] as$n=>$l):?>
<div class="form-field"><label for="<?=e($n)?>
"><?=e($l)?>
</label><input id="<?=e($n)?>
" name="<?=e($n)?>
" required type="<?=$n==='phone'?'tel':($n==='email'?'email':(in_array($n,['area','rooms'])?'number':'text'))?>
" value="<?=e($data[$n])?>
"><?php
if(isset($errors[$n])):?>
<small class="field-error"><?=e($errors[$n])?>
</small><?php
endif;?>
</div><?php
endforeach;?>
<div class="form-field"><label for="id_repair_type">Тип ремонта</label><select id="id_repair_type" name="id_repair_type" required><option value="">Выберите тип ремонта</option><?php
foreach($types as$t):?>
<option value="<?=e($t['id_repair_type'])?>
" <?=$data['id_repair_type']==$t['id_repair_type']?'selected':''?>
><?=e($t['name'])?>
</option><?php
endforeach;?>
</select><?php
if(isset($errors['id_repair_type'])):?>
<small class="field-error"><?=e($errors['id_repair_type'])?>
</small><?php
endif;?>
</div><div class="form-field form-field--full"><label for="message">Комментарий</label><textarea id="message" name="message"><?=e($data['message'])?>
</textarea></div></div><button class="button" type="submit">Отправить заявку</button></form></div></section></main><?php
include __DIR__.'/includes/footer.php';?>

<?php
require __DIR__.'/config/bootstrap.php';
require_auth();
if(is_admin())redirect('admin/index.php');
$u=current_user();
$q=db()->prepare('SELECT * FROM clients WHERE id_user=?');
$q->execute([$u['id_user']]);
$client=$q->fetch();
$apps=[];
$projects=[];
if($client){
$q=db()->prepare('SELECT a.*,rt.name repair_type FROM applications a JOIN repair_types rt ON rt.id_repair_type=a.id_repair_type WHERE a.id_client=? ORDER BY a.created_at DESC');
$q->execute([$client['id_client']]);
$apps=$q->fetchAll();
$q=db()->prepare('SELECT p.*,rt.name repair_type FROM repair_projects p JOIN repair_types rt ON rt.id_repair_type=p.id_repair_type WHERE p.id_client=? ORDER BY p.start_date DESC');
$q->execute([$client['id_client']]);
$projects=$q->fetchAll();
}
$pageTitle='Личный кабинет';
include __DIR__.'/includes/header.php';?>
<main><section class="account-section"><div class="layout__container account"><aside class="account__nav"><h2><?=e($u['full_name'])?>
</h2><p><?=e($u['email'])?>
</p><a class="account__link account__link--active" href="account.php">Мои заявки</a><a class="account__link" href="profile.php">Профиль</a><a class="account__link" href="logout-confirm.php">Выйти</a></aside><div class="account__content"><h1>Личный кабинет</h1><div class="account-summary"><article><strong><?=count(array_filter($apps,fn($a)=>$a['status']!=='Завершена'))?>
</strong><span>активные заявки</span></article><article><strong><?=count(array_filter($projects,fn($p)=>$p['status']==='Завершён'))?>
</strong><span>завершённые проекты</span></article><article><strong><?=count($projects)?>
</strong><span>всего проектов</span></article></div><h2>Мои заявки</h2><?php
if(!$apps):?>
<div class="account__panel account__panel--empty">Заявок пока нет. <a href="application.php">Оставить первую заявку</a>.</div><?php
endif;?><?php
foreach($apps as$a):?>
<div class="account__panel applications__item"><div><h3>Заявка №<?=e($a['id_application'])?>
</h3><p><?=e($a['repair_type'])?>
, <?=e($a['area'])?>
 м²</p><div class="account__meta"><span><?=e($a['created_at'])?>
</span><span><?=e($a['rooms'])?>
 комн.</span></div></div><span class="applications__status"><?=e($a['status'])?>
</span></div><?php
endforeach;?>
<h2>Ремонтные проекты</h2><?php
if(!$projects):?>
<div class="account__panel account__panel--empty">Активных проектов пока нет.</div><?php
endif;?><?php
foreach($projects as$p):?>
<div class="account__panel applications__item"><div><h3><?=e($p['title'])?>
</h3><p><?=e($p['repair_type'])?>
 – <?=e($p['address'])?>
</p><div class="account__meta"><span>Срок: <?=e($p['deadline'])?>
</span><span><?=e($p['area'])?>
 м²</span></div></div><span class="applications__status"><?=e($p['status'])?>
</span></div><?php
endforeach;?>
</div></div></section></main><?php
include __DIR__.'/includes/footer.php';?>

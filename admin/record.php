<?php
require __DIR__.'/../config/bootstrap.php';
require_admin();
$root='../';
$table=safe_table($_GET['table']??'');
$columns=table_columns($table);
$keys=$_GET['key']??[];
$editing=is_array($keys)&&$keys!==[];
$row=[];
if($editing){
$where=[];
$values=[];
foreach(primary_columns($table)as$pk){
if(!array_key_exists($pk,$keys)){
http_response_code(400);
exit('Не передан первичный ключ.');
}
$where[]=qi($pk).'=?';
$values[]=$keys[$pk];
}
$q=db()->prepare('SELECT * FROM '.qi($table).' WHERE '.implode(' AND ',$where).' LIMIT 1');
$q->execute($values);
$row=$q->fetch()?:[];
if(!$row){
http_response_code(404);
exit('Запись не найдена.');
}
}
$pageTitle=($editing?'Изменение':'Добавление').' записи';
include __DIR__.'/../includes/header.php';?>
<main class="admin"><div class="layout__container"><div class="admin__heading"><h1><?=$editing?'Изменение':'Добавление'?>
 записи</h1><a class="button button--outline" href="index.php?table=<?=urlencode($table)?>
">Назад</a></div><p class="admin-note">Таблица: <b><?=e($table)?>
</b>. Поля с NULL можно оставить пустыми.</p><form class="admin-form" method="post" action="save.php" data-validate><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><input type="hidden" name="action" value="<?=$editing?'update':'insert'?>
"><input type="hidden" name="table" value="<?=e($table)?>
"><?php
foreach($keys as$k=>$v):?>
<input type="hidden" name="key[<?=e($k)?>
]" value="<?=e($v)?>
"><?php
endforeach;?><?php
foreach($columns as$c):$n=$c['COLUMN_NAME'];
$auto=str_contains($c['EXTRA'],'auto_increment');
if($auto&&!$editing)continue;
$value=$row[$n]??'';
$required=$c['IS_NULLABLE']==='NO'&&!$auto;
$type=in_array($c['DATA_TYPE'],['int','decimal','tinyint'],true)?'number':(in_array($c['DATA_TYPE'],['date'],true)?'date':(in_array($c['DATA_TYPE'],['datetime','timestamp'],true)?'datetime-local':'text'));
if($type==='datetime-local'&&$value)$value=str_replace(' ','T',substr((string)$value,0,16));?>
<div class="form-field"><label for="<?=e($n)?>
"><?=e($n)?>
 <small><?=e($c['COLUMN_TYPE'])?>
</small></label><?php
if(in_array($c['DATA_TYPE'],['text','longtext'],true)):?>
<textarea id="<?=e($n)?>
" name="data[<?=e($n)?>
]" <?=$required?'required':''?>
><?=e($value)?>
</textarea><?php
else:?>
<input id="<?=e($n)?>
" name="data[<?=e($n)?>
]" type="<?=e($type)?>
" value="<?=e($value)?>
" <?=$required?'required':''?>
 <?=$n==='password_hash'?'autocomplete="new-password"':''?>
 step="<?=$c['DATA_TYPE']==='decimal'?'0.01':'1'?>
"><?php
endif;?>
</div><?php
endforeach;?>
<div class="admin-form__actions"><button class="button" type="submit">Сохранить</button><a class="button button--outline" href="index.php?table=<?=urlencode($table)?>
">Отмена</a></div></form></div></main><?php
include __DIR__.'/../includes/footer.php';?>

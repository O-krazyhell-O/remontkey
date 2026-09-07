<?php
require __DIR__.'/../config/bootstrap.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('index.php');
verify_csrf();
$table=safe_table((string)($_POST['table']??''));
$action=$_POST['action']??'';
$keys=is_array($_POST['key']??null)?$_POST['key']:[];
$data=is_array($_POST['data']??null)?$_POST['data']:[];
$columns=table_columns($table);
$allowed=array_column($columns,'COLUMN_NAME');
try{
if($action==='delete'){
$where=[];
$values=[];
foreach(primary_columns($table)as$pk){
if(!array_key_exists($pk,$keys))throw new RuntimeException('Не передан первичный ключ.');
$where[]=qi($pk).'=?';
$values[]=$keys[$pk];
}
$q=db()->prepare('DELETE FROM '.qi($table).' WHERE '.implode(' AND ',$where));
$q->execute($values);
flash('success','Запись удалена.');
}
elseif($action==='insert'||$action==='update'){
$clean=[];
foreach($columns as$c){
$n=$c['COLUMN_NAME'];
if(!array_key_exists($n,$data))continue;
$v=trim((string)$data[$n]);
if($table==='users'&&$n==='password_hash'&&$v!==''&&!str_starts_with($v,'$2y$'))$v=password_hash($v,PASSWORD_DEFAULT);
if($v===''&&$c['IS_NULLABLE']==='YES')$v=null;
if($v===''&&in_array($n,['created_at'],true))$v=date('Y-m-d H:i:s');
$clean[$n]=$v;
}
if($action==='insert'){
if(!$clean)throw new RuntimeException('Нет данных для добавления.');
$names=array_keys($clean);
$sql='INSERT INTO '.qi($table).' ('.implode(',',array_map('qi',$names)).') VALUES ('.implode(',',array_fill(0,count($names),'?')).')';
db()->prepare($sql)->execute(array_values($clean));
flash('success','Запись добавлена.');
}
else{
$sets=[];
$values=[];
foreach($clean as$n=>$v){
$sets[]=qi($n).'=?';
$values[]=$v;
}
$where=[];
foreach(primary_columns($table)as$pk){
if(!array_key_exists($pk,$keys))throw new RuntimeException('Не передан первичный ключ.');
$where[]=qi($pk).'=?';
$values[]=$keys[$pk];
}
db()->prepare('UPDATE '.qi($table).' SET '.implode(',',$sets).' WHERE '.implode(' AND ',$where))->execute($values);
flash('success','Запись изменена.');
}
}
else throw new RuntimeException('Неизвестное действие.');
}
catch(Throwable$e){
flash('error','Операция не выполнена: '.$e->getMessage());
}
redirect('index.php?table='.urlencode($table));


<?php
declare(strict_types=1);

if(session_status()!==PHP_SESSION_ACTIVE)session_start();

const DB_HOST='127.0.0.1',DB_NAME='remont_kvartir_pod_klyuch',DB_USER='root',DB_PASS='';

function db():PDO{
static $p;
if(!$p)$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
return$p;
}

function e(mixed $v):string{
return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
}

function redirect(string $u):never{
header('Location: '.$u);
exit;
}

function csrf_token():string{
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
return$_SESSION['csrf_token'];
}

function verify_csrf():void{
$t=$_POST['csrf_token']??'';
if(!is_string($t)||!hash_equals($_SESSION['csrf_token']??'',$t)){
http_response_code(419);
exit('Срок действия формы истёк.');
}
}

function flash(string $type,string $message):void{
$_SESSION['flash']=compact('type','message');
}

function take_flash():?array{
$f=$_SESSION['flash']??null;
unset($_SESSION['flash']);
return$f;
}

function current_user():?array{
if(empty($_SESSION['user_id']))return null;
static$u=null;
if($u===null){
$q=db()->prepare('SELECT u.*,r.name_role FROM users u JOIN roles r ON r.id_role=u.id_role WHERE u.id_user=?');
$q->execute([$_SESSION['user_id']]);
$u=$q->fetch()?:false;
}
return$u?:null;
}

function is_admin():bool{
return(current_user()['name_role']??'')==='Администратор';
}

function require_auth():void{
if(!current_user()){
flash('error','Сначала войдите в аккаунт.');
redirect('login.php');
}
}

function require_admin():void{
if(!current_user()){
flash('error','Сначала войдите как администратор.');
redirect('../login.php');
}
if(!is_admin()){
http_response_code(403);
exit('Доступ запрещён.');
}
}

function normalise_phone(string$p):string{
$d=preg_replace('/\D+/','',$p)??'';
if(strlen($d)===11&&($d[0]==='7'||$d[0]==='8'))$d=substr($d,1);
if(strlen($d)!==10)return trim($p);
return sprintf('+7 (%s) %s-%s-%s',substr($d,0,3),substr($d,3,3),substr($d,6,2),substr($d,8,2));
}

function phone_is_valid(string$p):bool{
return(bool)preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/u',$p);
}

function base_tables():array{
$q=db()->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME");
$q->execute([DB_NAME]);
return array_column($q->fetchAll(),'TABLE_NAME');
}

function database_views():array{
$q=db()->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_TYPE='VIEW' ORDER BY TABLE_NAME");
$q->execute([DB_NAME]);
return array_column($q->fetchAll(),'TABLE_NAME');
}

function table_columns(string$t):array{
$q=db()->prepare('SELECT COLUMN_NAME,COLUMN_TYPE,DATA_TYPE,IS_NULLABLE,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');
$q->execute([DB_NAME,$t]);
return$q->fetchAll();
}

function primary_columns(string$t):array{
return array_column(array_filter(table_columns($t),fn($c)=>$c['COLUMN_KEY']==='PRI'),'COLUMN_NAME');
}

function safe_table(string$t,bool$v=false):string{
$a=$v?array_merge(base_tables(),database_views()):base_tables();
if(!in_array($t,$a,true)){
http_response_code(404);
exit('Таблица не найдена.');
}
return$t;
}

function qi(string$i):string{
return '`'.str_replace('`','``',$i).'`';
}


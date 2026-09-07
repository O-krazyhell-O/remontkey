<?php
require __DIR__.'/../config/bootstrap.php';
require_admin();
$root='../';
$tables=base_tables();
$views=database_views();
$name=$_GET['table']??$tables[0];
$isView=in_array($name,$views,true);
$name=safe_table($name,true);
$columns=table_columns($name);
$rows=db()->query('SELECT * FROM '.qi($name).' LIMIT 100')->fetchAll();
$pageTitle='Административная панель';
include __DIR__.'/../includes/header.php';?>
<main class="admin"><div class="layout__container"><div class="admin__top"><div><p class="section__eyebrow">УПРАВЛЕНИЕ БАЗОЙ ДАННЫХ</p><h1>Административная панель</h1></div><a class="button button--outline" href="../logout-confirm.php">Выйти</a></div><div class="admin__layout"><aside class="admin__sidebar"><h3>Таблицы</h3><?php
foreach($tables as$t):?>
<a class="<?=$name===$t?'is-active':''?>
" href="?table=<?=urlencode($t)?>
"><?=e($t)?>
</a><?php
endforeach;?>
<div class="admin__sidebar-separator"></div><h3>Представления</h3><?php
foreach($views as$v):?>
<a class="<?=$name===$v?'is-active':''?>
" href="?table=<?=urlencode($v)?>
"><?=e($v)?>
</a><?php
endforeach;?>
</aside><section class="admin__content"><div class="admin__heading"><h2><?=e($name)?>
</h2><?php
if(!$isView):?>
<a class="button button--small" href="record.php?table=<?=urlencode($name)?>
">Добавить запись</a><?php
endif;?>
</div><?php
if($isView):?>
<div class="admin-note">Представление доступно только для просмотра. Его данные формируются запросом из связанных таблиц.</div><?php
endif;?>
<div class="table-wrap"><table class="data-table"><thead><tr><?php
foreach($columns as$c):?>
<th><?=e($c['COLUMN_NAME'])?>
</th><?php
endforeach;?><?php
if(!$isView):?>
<th>Действия</th><?php
endif;?>
</tr></thead><tbody><?php
if(!$rows):?>
<tr><td colspan="<?=count($columns)+1?>
">Нет данных</td></tr><?php
endif;?><?php
foreach($rows as$row):?>
<tr><?php
foreach($columns as$c):?>
<td><?=e($row[$c['COLUMN_NAME']]??'NULL')?>
</td><?php
endforeach;?><?php
if(!$isView):$keys=[];
foreach(primary_columns($name)as$pk)$keys[$pk]=$row[$pk];
$query=http_build_query(['table'=>$name,'key'=>$keys]);?>
<td><div class="table-actions"><a class="button button--ghost button--tiny" href="record.php?<?=e($query)?>
">Изменить</a><form method="post" action="save.php" onsubmit="return confirm('Удалить запись? Связанные данные могут помешать удалению.')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>
"><input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="<?=e($name)?>
"><?php
foreach($keys as$k=>$v):?>
<input type="hidden" name="key[<?=e($k)?>
]" value="<?=e($v)?>
"><?php
endforeach;?>
<button class="button button--danger button--tiny" type="submit">Удалить</button></form></div></td><?php
endif;?>
</tr><?php
endforeach;?>
</tbody></table></div></section></div></div></main><?php
include __DIR__.'/../includes/footer.php';?>

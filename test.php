<?php
require_once(__DIR__ . '/src/class-core.php');

$core = new \Ohagi\Core('./test');
$tbl = $core->getTable('post');

$r = $tbl->createRecord();
$r->text = 'hoge hoge text';
$r->save();

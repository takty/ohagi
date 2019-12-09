<?php
require_once(__DIR__ . '/src/class-core.php');

$core = new \Ohagi\Core('./test');
$tbl = $core->getTable('post');
$tbl->truncate();

$r = $tbl->createRecord();
$r->text = 'hoge hoge text';
$r->setLarge('text_large', 'long long long text', 'txt');
$r->save();

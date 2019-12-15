<?php
require_once(__DIR__ . '/src/class-core.php');

$core = new \Ohagi\Core('./test');
$tbl = $core->getTable('post');
$tbl->truncate();

for ($i = 0; $i < 4; $i += 1) {
	$r = $tbl->createRecord();
	$r->text = 'hoge hoge text';
	$r->set('key.sub.subsub', 'hoge hoge text');
	$r->setLarge('text_large', 'long long long text', 'txt');
	$r->setLarge('key.sub.text_large', 'long long long text', 'txt');
	$r->save();
}
var_dump($r->text);
var_dump($r->get('key.sub.subsub'));
var_dump($r->getLarge('text_large'));
var_dump($r->getLarge('key.sub.text_large'));

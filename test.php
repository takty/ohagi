<?php
require_once __DIR__ . '/src/class-core.php';

$core = new \ohagi\Core( './test' );
$tbl  = $core->get_table( 'post' );
$tbl->truncate();

for ( $i = 0; $i < 4; $i += 1 ) {
	$r       = $tbl->create_record();
	$r->text = 'hoge hoge text';
	$r->set( 'key.sub.subsub', 'hoge hoge text' );
	$r->set_large( 'text_large', 'long long long text', 'txt' );
	$r->set_large( 'key.sub.text_large', 'long long long text', 'txt' );
	$r->save();
}
var_dump( $r->text );
var_dump( $r->get( 'key.sub.subsub' ) );
var_dump( $r->get_large( 'text_large' ) );
var_dump( $r->get_large( 'key.sub.text_large' ) );

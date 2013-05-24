<?php
include_once('../includeAll.php');
includeModule('sgo');
$res = array();
$data = array();

//conexao no BD
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$res = $bd->query("SELECT id FROM sg.doc WHERE labelID = 4");

foreach ($res as $r) {
	$bd->query("DELETE FROM sg.data_historico WHERE docID = {$r['id']}");
	$bd->query("DELETE FROM sg.doc WHERE id = {$r['id']}");
	print $r['id'].'<BR>';
}

?>
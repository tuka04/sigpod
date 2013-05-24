<?php
include '../includeAll.php';

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

/*1- convertendo despacho Externo
$res = $bd->query("SELECT * FROM data_historico WHERE tipo = 'despIntern'");
	foreach ($res as $r) {
		$bd->query("UPDATE data_historico SET acao = '', unidade = '".substr($r['acao'],15)."' WHERE id = ".$r['id']);
	}
	///*/

/*2- observacao*
$res = $bd->query("SELECT * FROM  `data_historico` WHERE  `tipo` =  'despIntern' AND  `unidade` LIKE  '%va&ccedil;&atilde;0%'");
*/

/*3- criacao*/
//$bd->query("UPDATE data_historico SET acao='' WHERE tipo='criacao'");

//4-saida
/*$res = $bd->query("SELECT * FROM data_historico WHERE tipo='saida' AND acao LIKE '%Despachou%'");
foreach ($res as $r) {
	$bd->query("UPDATE data_historico SET acao='',unidade='".substr($r['acao'],15)."' WHERE id = ".$r['id']);
}*/

//5-saida 2
/*$res = $bd->query("Select * FROM  `data_historico` WHERE  `tipo` =  'saida' AND unidade LIKE  'umento%'");
foreach ($res as $r) {
	$bd->query("UPDATE data_historico SET acao='',unidade='".substr($r['acao'],11)."' WHERE id = ".$r['id']);
}//*/

/*$res = $bd->query("Select * FROM  `data_historico` WHERE  `tipo` =  'entrada' AND unidade LIKE '% via %'");
foreach ($res as $r) {
	$bd->query("UPDATE data_historico SET acao='',unidade='".substr($r['unidade'],strpos($r['unidade'], ' via '))."' WHERE id = ".$r['id']);
}*/

$res = $bd->query("SELECT id FROM doc ORDER BY id ASC");

foreach ($res as $r) {
	$doc = new Documento($r['id']);
	$doc->loadCampos();
}

?>
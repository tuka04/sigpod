<?php
include '../../includeAll.php';

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], "sg");

//$res = $bd->query("SELECT id FROM doc WHERE empreendID=344 ORDER BY id ASC");

$res = $bd->query("
SELECT processoID as id 
FROM  `obra_etapa` 
WHERE  `ObraID` =344");

foreach ($res as $r) {
	print 'Testando documento '.$r['id'].'<br>';
	$doc = new Documento($r['id']);
	$doc->loadCampos();
	
}
?>
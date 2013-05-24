<?php
	include_once('includeAll.php');
	include_once('sgd_modules.php');

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

	//$adm = 0;
	//$obras = 0;
	$areas = array();
	$users = array();
	$bizonhos = array();
	
	$sql = "SELECT * FROM usuarios ORDER BY id";
	$temp = $bd->query($sql);
	foreach ($temp as $t) {
		$users[$t['username']] = 0;
		$areas[$t['area']] = 0;
	}
	
	set_time_limit(180);
	
	$sql = "SELECT id FROM doc ORDER BY id";
	$res = $bd->query($sql);
	
	foreach ($res as $d) {
		// pega o 1o despacho interno deste doc
		$sql = "SELECT * FROM data_historico WHERE docID = ".$d['id']." AND tipo = 'despIntern' ORDER BY data LIMIT 1";
		$desp = $bd->query($sql);
		
		if (count($desp) <= 0) continue;
		
		$desp = $desp[0];
		
		$sql = "SELECT * FROM usuarios WHERE nomeCompl = '".rtrim($desp['unidade'], ".")."'";
		$usuario = $bd->query($sql);
		if (count($usuario) > 0) {
			$usuario = $usuario[0];
			$users[$usuario['username']]++;
			$areas[$usuario['area']]++;
		}
		else {
			if (isset($areas[rtrim($desp['unidade'], ".")]))
				$areas[rtrim($desp['unidade'], ".")]++;
			else {
				if (isset($bizonhos[$desp['unidade']]))
					$bizonhos[$desp['unidade']]++;
				else
					$bizonhos[$desp['unidade']] = 1;
			}
		}
	}
	
	print("Relat&oacute;rio de despachos internos: Primeiros despachos. <br /><br />");
	print("Despachos por &Aacute;rea:<br />");
	foreach($areas as $a => $v) {
		print("<b>$a</b>: $v<br />");
	}
	
	print("<br />Despacho por Usu&aacute;rios:<br />");
	foreach($users as $u => $v) {
		print("<b>$u</b>: $v<br />");
	}
	
	print("<br />&Aacute;rea/Usu&aacute;rio inv&aacute;lidos ou n&atilde;o mais existentes no sistema<br />");
	foreach($bizonhos as $b => $v) {
		print("<b>$b</b>: $v<br />");
	}
	
	print("<br /><br />Fim do relat&oacute;rio.");
	set_time_limit(0);
	
?>
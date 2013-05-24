<?php
	include 'includeAll.php';
	include_once 'classes/BD.php';
	include_once 'conf.inc.php';
	include_once 'includeAll.php';

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	if($_GET['show'] == "un" && isset($_GET['q'])){
		$ca = array("á","ã","à","â","é","ê","è","í","î","ï","ó","õ","ò","ô","ú","û","ü","ç","Á","Ã","À","Â","É","Ê","È","Í","Î","Ï","Ó","Õ","Ò","Ô","Ú","Û","Ü","Ç");
		$sa = array("a","a","a","a","e","e","e","i","i","i","o","o","o","o","u","u","u","c","A","A","A","A","E","E","E","I","I","I","O","O","O","O","U","U","U","C");

		$q = strtoupper(str_replace($ca, $sa, $_GET["q"]));
		$items = $bd->query("SELECT id, sub, CONCAT(nome,' (',sigla,')') AS nome FROM unidades WHERE (id LIKE '".$q."%' OR REPLACE(id, '.', '') LIKE '".$q."%' OR nome LIKE '%".$q."%' OR sigla LIKE  '%".$q."%') AND ativo = 1 ORDER BY id ASC");
		
		$ret = array();
		
		foreach ($items as $i) {
			$ret[] = $i['id'].' - '.$i['nome'];
			//echo $ret;
		} 
		
		print json_encode($ret);
	} elseif ($_GET['show'] == "pessoas") { //var_dump($_GET['area']);
		//var_dump(SGEncode($_GET['area'], ENT_QUOTES, null, false));
		$res = $bd->query("SELECT CONCAT( nome,  ' ', sobrenome ) AS nome,id FROM usuarios WHERE area='".SGEncode($_GET['area'], ENT_QUOTES, null, false)."' AND ativo = 1 ORDER BY nome ASC");
		//$res = $bd->query("SELECT CONCAT( nome,  ' ', sobrenome ) AS nome,id FROM usuarios WHERE area='".$_GET['area']."' AND ativo = 1 ORDER BY nome ASC");
		for ($i = 0; $i < count($res); $i++) {
			$res[$i]['nome'] = utf8_encode(urldecode($res[$i]['nome']));
		}
		print(json_encode($res));
	//} elseif ($_GET['show'] == "subun") {
	//	$res = $bd->query("SELECT id, CONCAT( nome,' (', sigla,')') AS nome FROM  unidades WHERE id LIKE '".$_GET['id'].".__' ORDER BY id ASC");
	//	print(json_encode($res));
	} elseif ($_GET['show'] == "verifCampo" && isset($_GET['nome'])){
		$res = $bd->query("SELECT COUNT(nome) AS qtdeNome FROM label_campo WHERE nome = '".$_GET['nome']."'");
		print(json_encode($res));
	} elseif ($_GET['show'] == "buscaCampo" && isset($_GET['label'])) {
		$res = $bd->query("SELECT * FROM label_campo WHERE label LIKE '%".SGEncode($_GET['label'], ENT_QUOTES, null, false)."%'");
		print(json_encode($res));
	} elseif ($_GET['show'] == "cadCampo" && isset($_GET['nome']) && isset($_GET['label']) && isset($_GET['tipo']) && isset($_GET['attr']) && isset($_GET['extra'])) {
		print $bd->query("INSERT INTO label_campo (nome,label,tipo,attr,extra) VALUES ('".$_GET['nome']."','".$_GET['label']."','".$_GET['tipo']."','".$_GET['attr']."','".$_GET['extra']."')");
	} elseif($_GET['show'] == "un2" && isset($_GET['q'])){
		$ca = array("á","ã","à","â","é","ê","è","í","î","ï","ó","õ","ò","ô","ú","û","ü","ç","Á","Ã","À","Â","É","Ê","È","Í","Î","Ï","Ó","Õ","Ò","Ô","Ú","Û","Ü","Ç");
		$sa = array("a","a","a","a","e","e","e","i","i","i","o","o","o","o","u","u","u","c","A","A","A","A","E","E","E","I","I","I","O","O","O","O","U","U","U","C");

		$q = strtoupper(str_replace($ca, $sa, $_GET["q"]));
		$items = $bd->query("SELECT id, sub, CONCAT(nome,' (',sigla,')') AS nome FROM unidades WHERE (id LIKE '".$q."%' OR REPLACE(id, '.', '') LIKE '".$q."%' OR nome LIKE '%".$q."%' OR sigla LIKE  '%".$q."%') AND ativo = 1 ORDER BY id ASC");
		
		//$ret = array();
		
		foreach ($items as $i) {
			//$ret[] = $i['id'].' - '.$i['nome'];
			$ret = $i['id'].' - '.$i['nome']."|".str_replace(".","",$i['id'])."\n";
			echo $ret;
		} 
		
		//print json_encode($ret);
	}
?>
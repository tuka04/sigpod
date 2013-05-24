<?php
include '../includeAll.php';
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

require_once '../classes/nusoap/nusoap.php';

$server = new soap_server;

$server->register('getBuscaParams');
$server->register('doBusca');
$server->register('getObraImage');


/**
 * Busca os parametros para montar o formulario de busca
 * @return Array
 * 
 * array['caracteristicas'] contem as caracteristicas cadastradas
 * array['tipo'] contem os tipos cadastrados
 * array['area'] contem as areas maximas e minimas cadastradas;
 */
function getBuscaParams() {
	global $bd;
	
	$params['caracteristicas'] = $bd->query("SELECT * FROM label_obra_caract");
	$params['tipos'] = $bd->query("SELECT * FROM label_obra_tipo");
	$area = $bd->query("SELECT dimensao FROM obra_obra WHERE dimensao IS NOT NULL GROUP BY dimensao ORDER BY dimensao");	
	if(count($area) < 2){
		$params['area'] = array(1, 1);		
	} else {
		$params['area'] = array($area[0]['dimensao'],$area[count($area)-1]['dimensao'],);
	}
	
	return $params;
}


function doBusca($campus, $string, $tipo, $caract, $area) {
	global $bd;
	
	if(is_array($campus) && count($campus) > 0 && count($campus) < 7){
		$restr['campus'] = '';
		foreach ($campus as $c) {
			$restr['campus'] .= "o.campus = '$c' OR ";
		}
		$restr['campus'] = rtrim($restr['campus'],' OR ');
	}
	
	
	if(strlen($string) > 2){
		$string = explode(' ', stringBusca($string));
		$restr['string'] = '';
		
		foreach ($string as $s) {
			$restr['string'] .= "o.nomeBusca LIKE '%$s%' OR e.nomeBusca LIKE '%$s%' OR u.nome LIKE '%$s%' OR ";
		}
		$restr['string'] = rtrim($restr['string'], ' OR ');
	}
	
	$no_caract = $bd->query('SELECT count(nome) AS caract_qty FROM label_obra_caract');
	if(is_array($caract) && count($caract) > 0 && count($caract) <= $no_caract[0]['caract_qty']){
		$restr['caract'] = '';
		foreach ($caract as $c) {
			$restr['caract'] .= "o.caract = '$c' OR ";
		}
		$restr['caract'] = rtrim($restr['caract'], ' OR ');
		
	}
	
	$no_tipo = $bd->query('SELECT count(nome) AS tipo_qty FROM label_obra_tipo');
	if(is_array($tipo) && count($tipo) > 0 && count($tipo) <= $no_tipo[0]['tipo_qty']){
		$restr['tipo'] = '';
		foreach ($tipo as $c) {
			$restr['tipo'] .= "o.tipo = '$c' OR ";
		}
		$restr['tipo'] = rtrim($restr['tipo'], ' OR ');
	}
	
	if(isset($area['min']) && $area['min'] > 1 && isset($area['max'])) {
		if($area['sem'] === "true")
			$restr['area'] = "(o.dimensao >= {$area['min']} AND o.dimensao <= {$area['max']} ) OR o.dimensao IS NULL OR o.dimensao = ''";
		else
			$restr['area'] = "o.dimensao >= {$area['min']} AND o.dimensao <= {$area['max']}";
	}
	
	//return $restr;
	$sql = 'SELECT e.id AS empreendID, e.nome AS empreendNome, o.id AS obraID, o.nome AS obraNome, o.descricao, caract.abrv as caractID, o.cod, o.desc_img, caract.nome AS caractNome, tipo.abrv AS tipoID, tipo.nome as tipoNome, o.lat, o.lng, o.campus, o.dimensao, o.dimensaoUn, u.id AS unidadeID, u.nome AS unidadeNome, u.sigla AS unidadeSigla FROM obra_empreendimento AS e INNER JOIN obra_obra as o ON o.empreendID = e.id INNER JOIN unidades AS u ON e.unOrg = u.id INNER JOIN label_obra_caract AS caract ON caract.abrv = o.caract INNER JOIN label_obra_tipo AS tipo ON tipo.abrv = o.tipo WHERE o.visivel = 1 ';
	
	foreach ($restr as $r) {
		$sql .= ' AND ('.$r.') ';
	}
	
	$sql = $sql." ORDER BY empreendNome ASC";
	
	$obras = $bd->query($sql);
		
	return $obras;
}

function getObraImage($obraID){
	global $bd; 
	
	$sql = 'SELECT o.cod, o.desc_img FROM obra_obra AS o WHERE o.id='.$obraID;
	$res = $bd->query($sql);
	
	if(count($res) == 0 || $res[0]['desc_img'] == null || !file_exists('../img/obras/'.$res[0]['cod'].'/'.$res[0]['desc_img'])){
		if(count($res) != 0 && $res[0]['desc_img'] != null && !file_exists('../img/obras/'.$res[0]['cod'].'/'.$res[0]['desc_img']))
			$bd->query('UPDATE obra_obra SET desc_img = NULL WHERE id='.$obraID);
		return false;
	}
	else
		return base64_encode(file_get_contents('../img/obras/'.$res[0]['cod'].'/'.$res[0]['desc_img']));
	
}


// Usar o request para invocar o servico
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>

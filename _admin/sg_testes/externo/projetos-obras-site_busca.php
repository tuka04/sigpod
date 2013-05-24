<?php
include_once('../includeAll.php');
includeModule('sgo');
$res = array();
$data = array();

//conexao no BD
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

if (isset($_GET['campus']) && isset($_GET['nome']) && isset($_GET['unOrg']) && isset($_GET['tipo']) && isset($_GET['area'])) {
	$restr = null;

	if($_GET['campus']) {
		$c_restr = '(';
		$campus = explode("|", $_GET['campus']);
		foreach ($campus as $c) {
			$c_restr .= "o.campus = '$c' OR ";
		}
		$restr[] = rtrim($c_restr, ' OR ') . ')';
	}
	
	if($_GET['nome']) {
		$nome = str_replace(" ", "%" ,htmlentities(urldecode($_GET['nome'])));
		$nome = stringBusca($nome);
		$restr[] = "(o.nomeBusca LIKE '%{$nome}%' OR e.nomeBusca LIKE '%$nome%')";
	}
	
	if($_GET['unOrg']) {
		$regex1 = preg_match("|^[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}|", $_GET['unOrg'], $matches);
		if ($regex1)
			$restr[] = "e.unOrg ='".substr($_GET['unOrg'], 0, 17)."'";
		else	
			$restr[] = "e.unOrg LIKE '%".htmlentities($_GET['unOrg'])."%'";
	}
	
	if($_GET['caract']) {
		$c_restr = '(';
		$tipos = explode("|", $_GET['caract']);
		foreach ($tipos as $c) {
			if($c == 'ref') $c_restr .= "o.caract = 'ref' OR ";
		if($c == 'nova') $c_restr .= "o.caract = 'nova' OR ";
			if($c == 'ampl') $c_restr .= "o.caract = 'ampl' OR ";
			if($c == 'ampl_ref') $c_restr .= "o.caract = 'ampl_ref' OR ";
			if($c == 'continuidade') $c_restr .= "o.caract = 'continuidade' OR ";
		}
		$restr[] = rtrim($c_restr, ' OR ') . ')';
	}
	
	if($_GET['tipo']) {
		$c_restr = '(';
		$tipos = explode("|", $_GET['tipo']);
		foreach ($tipos as $c) {
			if($c == 'pred') $c_restr .= "o.tipo = 'pred' OR ";
			if($c == 'infra') $c_restr .= "o.tipo = 'infra' OR ";
			if($c == 'infraUrb') $c_restr .= "o.tipo = 'infraUrb' OR ";
		}
		$restr[] = rtrim($c_restr, ' OR ') . ')';
	}
	
	//filtro de area
	if($_GET['area']) {
		$c_restr = '(';
		$notdef = false;
		//adicionando restricao de nulidade quando alguma dimensao for selecionada
		if (substr($_GET['area'], 0 , 2) == 'N|') {
			$c_restr .= "o.dimensao IS NULL ";
			$_GET['area'] = substr($_GET['area'], 2);
			$notdef = true;
		}
		//separa os campos min e max passados
		$tipo = explode('-', $_GET['area']);
		if (count($tipo) == 2) {
			if ($tipo[0] || $tipo[1]){
				if ($notdef) $c_restr .= "OR (";
				else $c_restr .="(";
				
				if($tipo[0]) $c_restr .= " o.dimensao >= {$tipo[0]} AND ";
				else $c_restr .= '';
			
				if($tipo[1]) $c_restr .= " o.dimensao <= {$tipo[1]}";
				else $c_restr .= '';
				
				$c_restr = rtrim($c_restr, " AND ") . ')';
				
			}
		}
		//se nao tiver nenhuma restricao, nao coloca no vetor de restricao
		if($c_restr != '(')
			$restr[] = $c_restr . ')';
	}
	
	$restr[] = "o.visivel = 1";
	
	//monta consulta SQL
	$sql = "SELECT o.id AS obraID, e.id AS empreendID FROM obra_obra AS o RIGHT JOIN obra_empreendimento AS e ON o.empreendID = e.id ";
	
	if(count($restr)) {
		$sql .= " WHERE ";
		foreach ($restr as $r) {
			$sql .= $r . ' AND ';
		}
		$sql = rtrim($sql, ' AND ');
	}
	
	$sql .= ' ORDER BY e.unOrg, e.id, o.nome';
	//DEBUG
	//print ($sql); exit();
	
	//consulta o BD
	$res = $bd->query($sql);
	//print_r($res); exit();
}

$lastEmpreendID = 0;
$empreend = null;

foreach ($res as $r) {
	
	if($lastEmpreendID != $r['empreendID']){//agrupamento por empreendimento
		//se o empreendimento for diferente do ultimo lido
		//cria um novo vetor para agrupar as obras
		if($lastEmpreendID) $results[] = $empreend; 	
		
		$empreend = null;
		//lê os dados do empreendimento
		$e = new Empreendimento($bd);
		$e->load($r['empreendID']);
		
		//cria novo vetor de obras pra o empreendimento
		$empreend = array(
			'id' => $e->get('id') ,
			'nome' => $e->get("nome"),
			'unOrg' => $e->get("unOrg"),
			'descricao' => $e->get("descricao")
		);
		$empreend["obras"][] = carregaObra($r['obraID']);
		
	} else {
		$empreend["obras"][] = carregaObra($r['obraID']);
	}
	$lastEmpreendID = $e->get('id');
}
if($empreend != null)
	$results[] = $empreend;
else
	$results = array();
//print_r($results); exit();

print json_encode($results);

function carregaObra ($obraID) {
	global $bd;
	
	//if(!$obraID) return;
	
	$obra = new Obra($bd);
	$obra->load($obraID,true);
	
	return array(
		'id'       => $obra->get('id'),
		'nome'     => $obra->nome,
		'area'     => $obra->area,
		'caract'   => $obra->caract,
		'tipo'     => $obra->tipo,
		'img_desc' => $obra->desc_img,
		'lat'      => $obra->local['lat'],
		'lng'      => $obra->local['lng'],
		'estado'   =>$obra->estado
		);
}
?>
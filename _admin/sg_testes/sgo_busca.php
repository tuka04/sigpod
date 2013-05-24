<?php
include_once('includeAll.php');
includeModule('sgo');
$res = array();
$data = array();

//conexao no BD
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

if(!isset($_GET['resultType'])) $_GET['resultType'] = 'full'; 

if (isset($_GET['tipoBusca'])) {
	if($_GET['tipoBusca'] == 'empreendMiniBusca' && isset($_GET['string'])) {
		//$string = SGEncode(urldecode($_GET['string']),ENT_QUOTES);
		$string = stringBusca(urldecode($_GET['string']));
		//var_dump($string);
		
		$sql = "SELECT o.id AS obraID, e.id AS empreendID
				FROM obra_empreendimento AS e
				LEFT JOIN unidades AS u ON e.unOrg = u.id
				LEFT JOIN obra_obra AS o ON o.empreendID = e.id
				WHERE e.nomeBusca LIKE '%$string%' OR u.nome LIKE '%{$string}%' OR u.sigla LIKE '%{$string}%' OR o.nomeBusca LIKE '%$string%' 
				ORDER BY e.id, o.nome";
		
		$res = $bd->query($sql);
		
	} elseif($_GET['tipoBusca'] == 'obraMiniBusca' && isset($_GET['string'])) {
		//$string = SGEncode(urldecode($_GET['string']),ENT_QUOTES);
		$string = stringBusca(urldecode($_GET['string']));
		//var_dump($string);
		
		$sql = "SELECT o.id AS obraID, e.id AS empreendID, o.nome AS obraNome, e.nome AS empreendNome, CONCAT(u.nome,' (',u.sigla,')') AS unOrg
				FROM obra_empreendimento AS e
				LEFT JOIN unidades AS u ON e.unOrg = u.id
				LEFT JOIN obra_obra AS o ON o.empreendID = e.id
				WHERE e.nomeBusca LIKE '%$string%' OR u.nome LIKE '%{$string}%' OR u.sigla LIKE '%{$string}%' OR o.nomeBusca LIKE '%$string%' 
				ORDER BY e.id, o.nome";
		
		$res = $bd->query($sql);
		//var_dump($res);
		print(json_encode($res));
		exit();
		
		
	} elseif ($_GET['tipoBusca'] == 'processoMiniBusca' && isset($_GET['string'])) {
		$string = stringBusca(urldecode($_GET['string']));
		
		// seleciona os processos que tem o número parecido com o digitado
		$sql = "SELECT id, numero_pr, guardachuva, assunto FROM doc_processo WHERE numero_pr LIKE '%".$string."%'";
		$procs = $bd->query($sql);
		
		// monta array de retorno
		$res = array();
		
		// se achou algum processo
		if (count($procs) > 0) {
			// percorre os processos achados
			foreach ($procs as $p) {
				// se ele não for guardachuva, vê o id do empreendimento no campo empreendID da tabela doc
				if (isset($p['guardachuva']) && $p['guardachuva'] == 0) {
					$sql = "SELECT id AS docID, empreendID, numeroComp FROM doc WHERE labelID = 1 AND tipoID = " . $p['id'];
					$empr = $bd->query($sql);
				}
				else {					
					// processo guardachuva
					$sql = "SELECT g.docID, g.empreendID FROM guardachuva_empreend AS g INNER JOIN doc AS d ON d.id = g.docID 
							WHERE d.labelID = 1 AND d.tipoID = " . $p['id'];
					
					$empr = $bd->query($sql);
				}
					
				foreach($empr as $e) {
					if (!isset($e['empreendID']) || $e['empreendID'] == 0)
						continue;
						
					$res[] = array("empreendID" => $e['empreendID'], "docID" => $e['docID'], "numero_pr" => $p['numero_pr'], "assunto" => $p['assunto']);
				} 
			} /* foreach */
			
			
		} /* if */
		

		
	} elseif ($_GET['tipoBusca'] == 'sugestao' && isset($_GET['nome']) && isset($_GET['unOrg'])) {
		$_GET['resultType'] = 'basic';
		if (strlen($_GET['unOrg'])) {
			//tratamento de un/org
			$regex1 = preg_match("|^[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}|", $_GET['unOrg'], $matches);
			if ($regex1)
				$unOrg = substr($_GET['unOrg'], 0, 17);
			else 
				$unOrg = $_GET['unOrg'];
		} else {
			$unOrg = "%";
		}
		
		//$nome = '%'.str_ireplace('%', ' ', SGEncode(urldecode($_GET['nome']),ENT_QUOTES)).'%';
		$nome = stringBusca(urldecode($_GET['nome'], ENT_QUOTES));
		
		$res = $bd->query("SELECT o.id AS obraID, e.id AS empreendID FROM obra_obra AS o INNER JOIN obra_empreendimento AS e ON e.id = o.empreendID WHERE o.nomeBusca LIKE '$nome' AND e.unOrg LIKE '$unOrg'");
		
	} elseif ($_GET['tipoBusca'] == 'filtro' && isset($_GET['campus']) && isset($_GET['nome']) && isset($_GET['unOrg']) && isset($_GET['tipo']) && isset($_GET['area']) && isset($_GET['pav']) && isset($_GET['elev']) && isset($_GET['rec']) && isset($_GET['rec_total'])) {
		$restr = null;
		
		if($_GET['campus']) {
			$c_restr = '(';
			$campus = explode("|", $_GET['campus']);
			foreach ($campus as $c) {
				$c_restr .= "o.campus = '$c' OR ";
			}
			$restr[] = rtrim($c_restr, ' OR ') . ')';
		}
		
		if($_GET['nome'] && $_GET['nome'] != "") {
			//$nome = str_replace(" ", "%" ,SGEncode(urldecode($_GET['nome'])));
			$nome = stringBusca($_GET['nome']);
			$partesNome = explode(" ", $nome);
			foreach($partesNome as $pedaco) {
				//$restr[] = "(".removeAcentosSQL('o.nome')." LIKE '%{$pedaco}%' OR ".removeAcentosSQL('e.nome')." LIKE '%$pedaco%')";
				$restr[] = "(o.nomeBusca LIKE '%{$pedaco}%' OR e.nomeBusca LIKE '%$pedaco%')";
			}
		}
		
		if($_GET['unOrg'] && $_GET['unOrg'] != "") {
			$regex1 = preg_match("|^[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}|", $_GET['unOrg'], $matches);
			if ($regex1)
				$restr[] = "e.unOrg ='".substr($_GET['unOrg'], 0, 17)."'";
			else	
				$restr[] = "(u.id LIKE '".SGEncode($_GET['unOrg'], ENT_QUOTES, null, false)."%' 
							 OR u.sigla LIKE '".SGEncode($_GET['unOrg'], ENT_QUOTES, null, false)."%' 
							 OR u.nome LIKE '".SGEncode($_GET['unOrg'], ENT_QUOTES, null, false)."%')";
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
		
		if($_GET['pav']) {
			$pav = explode('|', $_GET['pav']);
			$c_restr = '(';
			foreach ($pav as $p) {
				if($p == 0) $c_restr .= "o.pavimentos IS NULL OR ";
				if($p == 1) $c_restr .= "o.pavimentos = 1 OR ";
				if($p == 2) $c_restr .= "o.pavimentos = 2 OR ";
				if($p == 3) $c_restr .= "o.pavimentos > 3 OR ";
			}
			$restr[] = rtrim($c_restr, " OR ") . ")";
		}
		
		if($_GET['elev']) {
			$c_restr = '(';
			$elev = explode('-', $_GET['elev']);
			foreach ($elev as $e) {
				if($e == 0) $c_restr .= "o.elevador IS NULL OR elevador = 0 OR ";
				if($e == 1) $c_restr .= "o.elevador = 1";
			}
			$restr[] = rtrim($c_restr, " OR ") . ")";
		}
		
		if($_GET['rec']) {
			$c_restr = '(';
			if (substr($_GET['rec'], 0 , 2) == 'N|') {
				$c_restr .= "o.custo IS NULL ";
				$_GET['rec'] = substr($_GET['rec'], 2);
			}
			$tipo = explode('-', $_GET['rec']);
			if (count($tipo) == 2) {
				if ($tipo[0] || $tipo[1]){
					$c_restr .= "OR (";
					if($tipo[0]) $c_restr .= " custo >= {$tipo[0]} AND ";
					else $c_restr .= '';
					if($tipo[1]) $c_restr .= " custo <= {$tipo[1]}";
					else $c_restr .= '';
					$c_restr = rtrim($c_restr, " AND ") . ')';
					
				}
			}
			if($c_restr != '(')
				$restr[] = $c_restr . ')';
		}
		
		if($_GET['rec_total']) {
			//TODO
		}
		//monta consulta SQL
		$sql = "SELECT o.id AS obraID, e.id AS empreendID FROM obra_obra AS o RIGHT JOIN obra_empreendimento AS e ON o.empreendID = e.id ";
		$sql .= "INNER JOIN unidades AS u ON e.unOrg = u.id ";
		//var_dump("oe");
		/*$sql = "SELECT o.id AS obraID, e.id AS empreendID FROM view_empreend AS e ";
		$sql .= "INNER JOIN unidades AS u ON e.unOrg = u.id LEFT JOIN view_obra AS o ON e.id = o.empreendID ";*/
	
		if(count($restr)) {
			$sql .= " WHERE ";
			foreach ($restr as $r) {
				$sql .= $r . ' AND ';
			}
			$sql = rtrim($sql, ' AND ');
		}
		
		$sql .= ' ORDER BY e.unOrg, e.id, o.nome';
		//DEBUG
		//print ($sql);exit();

		//print $sql;
		
		//consulta o BD
		$res = $bd->query($sql);
		//print_r($res); exit();
	}
	
} else {
	print("Nenhum tipoBusca selecionado");
	exit();
}

//var_dump($_GET);
//var_dump($res);
//var_dump(isset($_GET['nome']));
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
		
		$docID = "";
		$numero_pr = "";
		$assunto = "";
		
		if (isset($r['docID'])) {
			$docID = $r['docID'];
			$numero_pr = $r['numero_pr'];
			$assunto = $r['assunto'];
		}
		
		//cria novo vetor de empreend pra o empreendimento
		$empreend = array(
			'id' => $e->get('id') ,
			'nome' => $e->get("nome"),
			'unOrg' => $e->get("unOrg"),
			'descricao' => $e->get("descricao"),
			'docID' => $docID,
			'numero_pr' => $numero_pr,
			'assunto' => $assunto
		);
		
		if (isset($r['obraID'])) $empreend["obras"][]  = carregaObra($r['obraID']);
		else $empreend["obras"] = array();
		
	} else {
		if (isset($r['obraID'])) $empreend["obras"][] = carregaObra($r['obraID']);
	}
	$lastEmpreendID = $e->get('id');
}
$results[] = $empreend;

if($results[0] == null)
	$results = array();
//print_r($results); exit();

print json_encode($results);

function carregaObra ($obraID) {
	global $bd;
	
	//if(!$obraID) return;
	
	$obra = new Obra($bd);
	$obra->load($obraID,true);
	
	return array(
		'id' => $obra->get('id'),
		'nome' => $obra->nome,
		'area' => $obra->area,
		'caract' => $obra->caract,
		'tipo' => $obra->tipo,
		'img_desc'=> $obra->desc_img,
		'lat' => $obra->local['lat'],
		'lng' => $obra->local['lng'],
		'estado' =>$obra->estado
		);
}
?>
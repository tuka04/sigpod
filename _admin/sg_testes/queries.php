<?php

	//
	//  Consultas as tabelas de dados relativos a documentos
	//
	/**
	 * Retorna todos os documentos de posse do usuario atual
	 * @uses $_SESSION
	 * @uses $bd
	 * @return array
	 */
	function getPendentDocs($id_usuario,$area_usuario = null){
		global $bd;
		if ($area_usuario != null) {
			if (stripos($area_usuario, "protocolo") !== false) {
				return $bd->query("SELECT id FROM doc WHERE ownerID = $id_usuario OR (ownerID = -1 AND ".removeAcentosSQL('OwnerArea')." = '".stringBusca($area_usuario)."') OR (solicitante <> '0' AND solicitado = 0) OR (solicDesarquivamento <> '0')");
				//return $bd->query("SELECT * FROM view_docs_pend WHERE ownerID = $id_usuario OR (ownerID = -1 AND ".removeAcentosSQL('OwnerArea')." = '".stringBusca($area_usuario)."') OR (solicitante <> '0' AND solicitado = 0) OR (solicDesarquivamento <> '0')");
			}
			else {
				return $bd->query("SELECT id FROM doc WHERE ownerID = $id_usuario OR (ownerID = -1 AND ".removeAcentosSQL('OwnerArea')." = '".stringBusca($area_usuario)."')");
			}
		}
		else {
			return $bd->query("SELECT id FROM doc WHERE ownerID = $id_usuario");
		}
	}
	
	function getTodasAcoes() {
		global $bd;
		
		$res = $bd->query("SELECT * FROM label_acao");
		
		$acoes = array();
		
		foreach($res as $r) {
			$acoes[$r['id']] = array('nome' => $r['nome'], 'abrv' => $r['abrv']);
		}
		
		return $acoes;
	}
	
	/**
	 * Retorna o nome e abrev. de uma acao dado ID
	 * @param int $id_acao
	 * @uses $bd
	 * @return array
	 */
	function getAcao($id_acao){
		global $bd;
		
		return $bd->query("SELECT * FROM label_acao WHERE id = $id_acao");
	}
	
	/**
	 * Consulta os dados dos campos
	 * @uses $bd 
	 * @param string $campo_nome
	 */
	function getCampo($campo_nome){
		global $bd;
		
		return $bd->query("SELECT tipo,attr,extra,nome,label,verAcao,editarAcao FROM label_campo WHERE nome = '$campo_nome'");
	}
	
	/**
	 * consulta atributos do tipo de documento
	 * @param string $tipo_doc
	 */
	function getDocTipo($tipo_doc){
		global $bd;
		
		return $bd->query("SELECT * FROM label_doc WHERE nomeAbrv = '$tipo_doc'");
	}
	
	/**
	 * Consulta todos os tipos de documento
	 */
	function getAllDocTypes(){
		global $bd;
		
		return  $bd->query("SELECT * FROM label_doc");
	}
	
	/**
	 * Retorna ultimo despacho
	 */
	function getLastDesp(Documento $doc) {
		global $bd;
		$sql = "SELECT * FROM data_historico WHERE docID = " .$doc->id. " ORDER BY data DESC, id DESC LIMIT 1";
		$res = $bd->query($sql);
		if (count($res) > 0) return $res[0];
		else return array();
	}
	
	/**
	 * Seleciona algum atributo de uma tabela passada por parametro seguindo uma condicao predefinida
	 * e ordena por um atributo passado (ou nao ordena) com um certo limite de resultados (opcional)
	 * @param string $attr
	 * @param string $table
	 * @param string $condition
	 * @param string $orderBy
	 * @param string $order
	 * @param string $limit
	 */
	function attrFromGenericTable($attr, $table, $condition = '1', $orderBy = '', $order = "ASC", $limit = ''){
		global $bd;
		
		if($orderBy) $orderBy = "ORDER BY $orderBy $order";
		if($limit) $limit = "LIMIT $limit ";
		
		return $bd->query("SELECT $attr FROM $table WHERE $condition $orderBy $limit");
	}	
	
	//
	// Consulta as tabelas do sistema
	//
	/**
	 * loga a acao no BD
	 * @param string $user
	 * @param string $action
	 * @param connection $bd
	 */
	function doLog($user,$action) {
		global $bd;
		
		return $bd->query("INSERT INTO data_log (data,username,acao) VALUES (".time().",'$user','".SGEncode($action,ENT_QUOTES, null, false)."')");
	}
	
	//
	//  Consulta as tabelas de usuarios
	//
	function getAllUsersName($activeOnly = true){
		global $bd;
		
		if ($activeOnly) $where = "WHERE ativo = 1";
		else $where = '';
		
		return $bd->query("SELECT id, nomeCompl FROM usuarios $where ORDER BY nomeCompl");
	}
	
	function getUsers($user_id){
		global $bd;
		
		return $bd->query("SELECT * FROM usuarios WHERE id = $user_id");
	}
	
	function getUserFromUsername($username) {
		global $bd;
		
		return $bd->query("SELECT * FROM usuarios WHERE username = '".$username."'");
	}
	
	/**
	 * Consulta os nomes de todos os usuarios
	 * @param string $user_id
	 */
	function getNamesFromUsers($user_id){
		global $bd;
		
		return $bd->query("SELECT nome, sobrenome, nomeCompl FROM usuarios WHERE id = $user_id");
	}
	
	/**
	 * Consultas todas as areas cadastradas
	 */
	function getAreasFromUsers(){
		global $bd;
		
		return $bd->query("SELECT area FROM usuarios WHERE ativo > 0 GROUP BY area");
	} 
	
	function getAreaFromUser($id){
		global $bd;
		
		return $bd->query("SELECT area FROM usuarios WHERE id=$id LIMIT 1");
	}
	
	//
	//CONSULTAS DE BUSCA DE DOCUMENTOS
	//
	
	function searchDesp($variables) {
		if(!count($variables))
			return null;
		$recebIDs = "h.tipo = 'entrada' AND ";
		$despIDs = "h.tipo = 'saida' AND ";
		$dataDespacho = montaData($variables['dataDespacho1'], $variables['dataDespacho2']);
		$dataReceb = montaData($variables['dataReceb1'], $variables['dataReceb2']);
		
		if(count($dataDespacho) || $variables['un']) { // procura por despacho
			if($dataDespacho[0])
				$despIDs .= 'h.data > '.$dataDespacho[0].' AND ';
			if($dataDespacho[1])
				$despIDs .= 'h.data < '.$dataDespacho[1].' AND ';
			if($variables['unReceb'])
				$despIDs .= "h.unidade LIKE '%".$variables['unDespacho']."%' AND ";
		
		}
		if(count($dataReceb) || $variables['unReceb']) { //procura por Recebimento
			if($dataReceb[0])
				$recebIDs .= 'h.data > '.$dataReceb[0].' AND ';
			if($dataReceb[1])
				$recebIDs .= 'h.data < '.$dataReceb[1].' AND ';
			if($variables['unDespacho'])
				$recebIDs .= "h.unidade LIKE '%".$variables['unReceb']."%' AND ";
		} 
		
		$despIDs = rtrim($despIDs," AND ");
		$recebIDs = rtrim($recebIDs," AND ");
		
		$sql = "SELECT docID FROM data_historico AS h WHERE ";
		
		if($dataDespacho[0] || $dataDespacho[1] || $variables['unDespacho']) {
			$sql .= " ($despIDs) AND ";
		} elseif($dataReceb[0] || $dataReceb[1] || $variables['unDespacho']) {
			$sql .= " ($recebIDs) AND ";
		}
		
		if($variables['contDesp']) {//procura em todo historico
			$sql .= " (h.despacho LIKE '%".$variables['contDesp']."%' OR h.acao LIKE '%".$variables['contDesp']."%') GROUP BY h.docID";
		} else {
			$sql = rtrim($sql, ' WHERE ');
			$sql = rtrim($sql, ' AND ');
			$sql .= " GROUP BY h.docID";
		}
		//print $sql;exit();
		return $sql;
	}
	
	function searchDoc($id, $docNum, $criacao, $tipos ,$restrTipos, $histBuscaSQL, $contGen, $buscaArquivo = 0, $buscaOwner = '0', $buscaIni = 0, $numResultados = 100) {
		global $bd;
		//verifica se ha algum criterio de busca para efetua-la
		if(!$id && !$docNum && !$criacao && !count($restrTipos) && !count($histBuscaSQL))
			return null;
		//verifica se ha busca por ID
		if($id)
			$restr[] = "d.id = $id";
		//verifica se ha busca por numCPO
		if($docNum)
			$restr[] = "d.numeroComp LIKE '%$docNum%'";
		//verifica se ha busca por data de criacao
		if(isset($criacao[0]))
			$restr[] = "d.data > {$criacao[0]}";
		if(isset($criacao[1]))
			$restr[] = "d.data < {$criacao[1]}";
		//verifica quais os tipos de documentos serao procurados e gera a parte da consulta SQL referente a isso
		$restr['tipo'] = '(';
		foreach ($tipos as $t) {
			if (count($tipos) == 1 && $t == 5) {
				$restr['tipo'] .= "d.labelID = 1 OR d.labelID = 2 OR d.labelID = 3 OR d.labelID = 4 OR d.labelID = 5 OR d.labelID = 6 OR d.labelID = 7";
			}
			else {
				$restr['tipo'] .= "d.labelID = ".$t['id']." OR ";
			}
		}
		$restr['tipo'] = rtrim($restr['tipo'], " OR");
		$restr['tipo'] .= ')';
		
		// verifica se o usuario tem permissao para buscar o arquivo
		if (checkPermission(68)) {
			// caso afirmativo,
			if ($buscaArquivo == 1) { 
				// se ele deseja buscar o arquivo, adiciona restricao
				$restr[] = "d.arquivado = 1";
			}
			elseif ($buscaArquivo == 0) {
				// caso nao deseje, adiciona restricao para buscar docs que nao estao com o arquivo
				$restr[] = "d.arquivado = 0";
			}
			// se ele nao especificar, nao busca qq coisa (no caso, nao precisa de restricao)
		}
		else { 
			// não tem permissao para buscar o Arquivo, adiciona restricao para buscar docs que não estao com o arquivo
			$restr[] = "d.arquivado = 0";
		}
		
		if ($buscaOwner != '0') {
			$restr[] = "(d.ownerID = '" .$_SESSION['id']. "' OR (d.ownerID = '-1' AND ".removeAcentosSQL('d.OwnerArea')." = '".$_SESSION['area']."'))";
		}
		
		if(count($tipos) > 1) {
			if ($histBuscaSQL || (count($tipos) == 1 && $tipos[0]['id'] == 5 && $docNum))
				$sql = "SELECT d.id FROM doc AS d RIGHT JOIN data_historico AS h ON d.id = h.docID WHERE ";
			else
				$sql = "SELECT d.id FROM doc AS d WHERE ";
		} else {
			$tab = $tipos[0]['tab'];
			
			if ($histBuscaSQL || (count($tipos) == 1 && $tipos[0]['id'] == 5 && $docNum))
				$sql = "SELECT d.id FROM doc AS d INNER JOIN $tab AS t ON t.id = d.tipoID RIGHT JOIN data_historico AS h ON d.id = h.docID WHERE ";
			else
				$sql = "SELECT d.id FROM doc AS d INNER JOIN $tab AS t ON t.id = d.tipoID WHERE ";
				
				
			foreach ($restrTipos as $cName => $cValue) {
				
				if (stripos($cName, "_operador") !== false) {
					continue;
				}
				
				$valor = montaCampo($cName,'bus',$restrTipos);
				//tratamento de campos compostos
				if($valor['tipo'] == 'composto'){
					$composto = $valor['valor'];
					foreach (explode(",",$valor['nome']) as $n) {
						if ($restrTipos[$n] == "") {
							$composto = ""; 
							break;
						}
						//$v = montaCampo($n,'bus',$restrTipos);
						//if($v['valor'] != "" && $v['tipo'] != 'composto')
							//$restr[] = " t.".$cName." LIKE '%".$v['valor']."%' ";
							//$composto .= $v['valor'];
							
					}
					if ($composto != "") $restr[] = " t.".$cName." LIKE '%".$composto."%' ";
				} elseif($valor['parte']) { //tratamento de campos partes
					continue;
				} elseif($valor['tipo'] == 'userID') {//tratamento especial para campos de nome de usuario - deve procurar pelo nome e nao pelo ID
					if($cValue){
						//procura os usuarios que tem a string buscada no nome
						$sql1 = "SELECT id FROM usuarios WHERE nome LIKE '%".($cValue)."%' OR sobrenome LIKE '%".($cValue)."%' OR username LIKE '%".($cValue)."%'";
						$res = $bd->query($sql1);
						if(count($res) == 0) {
							print json_encode(array());
							exit();
						}
						//para cada usuario encontrado, faz uma restricao pelo Userid dele
						$restrUserID = '';
						foreach ($res as $user) {
							$restrUserID .= ' t.'.$cName." = ".$user['id'].' OR';
						}
						//trata e adiciona a restricao
						$restr[] = '('.rtrim($restrUserID,' OR').')';
					}
				} elseif ($valor['tipo'] == 'select') {
					if ($cValue != 'nenhum') $restr[] = " t.".$cName." = '".$cValue."' ";
				} elseif ($valor['tipo'] == 'empresa') {
					if ($cValue != null) {
						$sql_empresa = "SELECT id FROM empresa WHERE ".removeAcentosSQL('nome')." LIKE '%$cValue%'";
						$empresas = $bd->query($sql_empresa);
						
						$temp_restr = ' ';
						foreach($empresas as $e) {
							$temp_restr .= "t.$cName = " .$e['id']. " OR ";
						}
						
						$temp_restr = rtrim($temp_restr, "OR ");
						$restr[] = $temp_restr;
						
					}
					
				} elseif($valor['tipo'] == 'input' && strpos($valor['extra'],"unOrg_autocompletar") !== false && (strpos($cValue, "CPO") !== false || strpos($cValue, "cpo") !== false) || strpos($cValue, "01.07.63.00") !== false) {
					$restr[] = " (t.".$cName." LIKE '%01.14.16.00%' OR t.".$cName." LIKE '%".$cValue."%') ";
				} elseif(stripos($valor['extra'], "moeda") !== false && isset($valor['operador']) && $valor['valor'] != "") {
					$restr[] = " t.".$cName." ".$valor['operador']." ".$valor['valor'];
                } elseif($valor['valor']) { //montagem da condicao
					$restr[] = " t.".$cName." LIKE '%".$cValue."%' ";
				}
				
				/*if (stripos($valor['extra'], "moeda") !== false) {
					var_dump($valor);
				}*/
			}
			/*foreach($restrTipos as $cName => $cValue) {
				if ($cName == 'guardachuva' && $cValue != "") {
					$restr[] = " t.".$cName." LIKE '%".$cValue."%' ";
				}
			}*/
		}
		//adicionando condicoes
		foreach ($restr as $r) {
			$sql .= $r." AND ";
		}
		
		$sql = rtrim($sql," AND ");
		
		// condicao especial para RR: busca por rr de entrada
		// se for busca por RR e docNum tiver sido preenchido, busca no historico pelas RR de entrada
		if (count($tipos) == 1 && $tipos[0]['id'] == 5 && $docNum) {
			$sql .= " OR (h.label LIKE '%Recebido%' AND h.despacho LIKE '%via Rel. Remessa n&deg;$docNum%')"; 
		}
		
		//adicionando condicoes de historico
		if ($histBuscaSQL){
			$sql_desp = '';
			$sql_receb = '';
			$sql_cont = '';
			if((isset($histBuscaSQL['dataDespacho']) && count($histBuscaSQL['dataDespacho']) == 2) || (isset($histBuscaSQL['unDespacho']) && $histBuscaSQL['unDespacho'])) {
				$sql_desp = "(h.tipo = 'saida' AND ";
				if(isset($histBuscaSQL['dataDespacho']) && count($histBuscaSQL['dataDespacho']) == 2)
					$sql_desp .= "h.data < ".$histBuscaSQL['dataDespacho'][1]." AND h.data > ".$histBuscaSQL['dataDespacho'][0]." AND ";
				if(isset($histBuscaSQL['unDespacho']) && $histBuscaSQL['unDespacho'])
					$sql_desp .= "h.unidade LIKE '%".str_replace(' ', '%', $histBuscaSQL['unDespacho'])."%'";
				$sql_desp = rtrim($sql_desp, " AND "). ")";
			}
			if((isset($histBuscaSQL['dataReceb']) && count($histBuscaSQL['dataReceb']) == 2) || (isset($histBuscaSQL['unReceb']) && $histBuscaSQL['unReceb'])) {
				$sql_receb = "(h.tipo = 'entrada' AND ";
				if(isset($histBuscaSQL['dataReceb']) && count($histBuscaSQL['dataReceb']) == 2)
					$sql_receb .= "h.data < ".$histBuscaSQL['dataReceb'][1]." AND h.data > ".$histBuscaSQL['dataReceb'][0]." AND ";
				if(isset($histBuscaSQL['unReceb']) && $histBuscaSQL['unReceb'])
					$sql_receb .= "h.unidade LIKE '%".str_replace(' ', '%', $histBuscaSQL['unReceb'])."%'";
				$sql_receb = rtrim($sql_receb, " AND "). ")";
			}
			if(isset($histBuscaSQL['contDesp']) && $histBuscaSQL['contDesp']) {
				$sql_cont = "( h.despacho LIKE '%".str_replace(' ', '%', $histBuscaSQL['contDesp'])."%') ";
			}
			if($sql_cont || $sql_desp || $sql_receb){
				$sql .= " AND (";
				if($sql_cont)
					$sql .= $sql_cont." OR ";
				if($sql_desp)
					$sql .= $sql_desp." OR ";
				if($sql_receb)
					$sql .= $sql_receb;
				$sql = rtrim($sql," OR ").")";
			}
			
		}
		$genID = buscaGen($contGen,$restr['tipo'],$tipos);
		if($genID)
			$sql .= " AND d.id IN ($genID)";
		//nao repetir documentos e ordenar em ordem decrescente
		//$sql .= ' GROUP BY d.id ORDER BY d.data DESC LIMIT 100';
		$sql .= ' GROUP BY d.id ORDER BY d.data DESC, d.id DESC';
		
		/*$arqTemp = fopen("testeSql.txt", "w");
		fwrite($arqTemp, $sql);
		//fwrite($arqTemp, "\n".$valor['extra']." - ".$valor['operador']);
		fwrite($arqTemp, "\n".$sql_empresa);
		fclose($arqTemp);*/
		
		// executa a query
		$res = $bd->query($sql);
		$qtde = count($res); // conta resultados
		
		// faz a paginacao cortando a array, comecando em $buscaIni e exibindo $numResultados resultados
		if ($qtde > 0) {
			$ret = array_slice($res, $buscaIni, $numResultados);
			$ret[0]['total'] = $qtde;
		}
		else {
			$ret = array();
		}
		//print($sql);
		//exit();
		
		return $ret; 
	}
	
	function getUnidadeName($id){
		global $bd;
		
		$res = $bd->query("SELECT * FROM unidades WHERE id = '{$id}'");
		
		if (count($res)){
			return $id . ' - ' . $res[0]['nome'] . ' (' . $res[0]['sigla'] . ')';
		}
	}
	
	function getGrupoName($gid) {
		global $bd;
		
		return $bd->query("SELECT * FROM label_grupos WHERE id = '{$gid}'");
	}
	
	function buscaGen($contGen,$restrTipo,$tipos) {
		if($contGen) {
			$rPalavra = '';
			foreach (explode(' ', $contGen) as $palavra) {
				$rTipo = '';
				foreach ($tipos as $rt) {
					$doc = new Documento(0);
					$doc->dadosTipo['nomeAbrv'] = $rt['nomeAbrv'];
					$doc->loadTipoData();
					$rCampos = '';
					foreach (explode(',',$doc->dadosTipo['campos']) as $campo) {
						$campo = montaCampo($campo,'bus');
						if($campo['tipo'] == 'input' || $campo['tipo'] == 'textarea') {
							//$rCampos .= " ".$rt['nomeAbrv'].".".$campo['nome']." LIKE '%$palavra%' OR ";
							//$rCampos .= " ".$rt['nomeAbrv'].".".$campo['nome']." REGEXP \"".stringBusca($palavra)."\" OR ";
							$palavra = SGDecode($palavra);
							$rCampos .= " " . removeAcentosSQL($rt['nomeAbrv'].".".$campo['nome']) . " LIKE '%" . stringBusca($palavra) . "%' OR ";
						}
					}
					$rCampos = rtrim($rCampos,' OR');
					//$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tab'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR d.numeroComp LIKE '%$palavra%') AND d.labelID = ".$rt['id'].") OR ";
					//$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tab'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR d.numeroComp REGEXP \"".stringBusca($palavra)."\") AND d.labelID = ".$rt['id'].") OR ";
					$palavra = SGDecode($palavra);
					$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tab'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR " . removeAcentosSQL('d.numeroComp') . " LIKE '%" . stringBusca($palavra) . "%') AND d.labelID = ".$rt['id'].") OR ";
	
				}
				$rTipo = rtrim($rTipo,' OR');
				
				$rPalavra .= '(' . $rTipo . ') AND ';			
			}
			$rPalavra = rtrim($rPalavra, ' AND ');
			$sql = "SELECT d.id FROM doc AS d WHERE $rPalavra ";
			//print($sql); exit();
			
			return $sql;
		} else {
			return null;
		}
	}
	
	/** Gera código SQL para remoção de código HTML de acentuação e caracteres especiais de um determinado campo
	 * 	Função para ser usada dentro de SELECT's
	 * @param $campo string Nome do campo a ter seu conteúdo trocado
	 * @return string código sql  
	 */
	function removeAcentosSQL($campo) {
		// observação: REPLACE do MySQL é case sentitve...
		$sql = 'REPLACE('.$campo.', "&amp;", "&")';
		$sql = 'REPLACE('.$sql.', "&aacute;", "a")';
		$sql = 'REPLACE('.$sql.', "&atilde;", "a")';
		$sql = 'REPLACE('.$sql.', "&agrave;", "a")';
		$sql = 'REPLACE('.$sql.', "&acirc;", "a")';
	
		$sql = 'REPLACE('.$sql.', "&eacute;","e")';
		$sql = 'REPLACE('.$sql.', "&egrave;","e")';
		$sql = 'REPLACE('.$sql.', "&ecirc;","e")';
		
		$sql = 'REPLACE('.$sql.', "&iacute;","i")';
		$sql = 'REPLACE('.$sql.', "&igrave;","i")';
		
		$sql = 'REPLACE('.$sql.', "&oacute;","o")';
		$sql = 'REPLACE('.$sql.', "&ograve;","o")';
		$sql = 'REPLACE('.$sql.', "&ocirc;","o")';
		$sql = 'REPLACE('.$sql.', "&otilde;","o")';
		
		$sql = 'REPLACE('.$sql.', "&uacute;","u")';
		$sql = 'REPLACE('.$sql.', "&uuml;","u")';
		
		$sql = 'REPLACE('.$sql.', "&ccedil;","c")';
		
		$sql = 'REPLACE('.$sql.', "&Aacute;","a")';
		$sql = 'REPLACE('.$sql.', "&Atilde;","a")';
		$sql = 'REPLACE('.$sql.', "&Agrave;","a")';
		$sql = 'REPLACE('.$sql.', "&Acirc;","a")';
		
		$sql = 'REPLACE('.$sql.', "&Eacute;","e")';
		$sql = 'REPLACE('.$sql.', "&Egrave;","e")';
		$sql = 'REPLACE('.$sql.', "&Ecirc;","e")';
		
		$sql = 'REPLACE('.$sql.', "&Iacute;","i")';
		$sql = 'REPLACE('.$sql.', "&Igrave;","i")';
		
		$sql = 'REPLACE('.$sql.', "&Oacute;","o")';
		$sql = 'REPLACE('.$sql.', "&Ograve;","o")';
		$sql = 'REPLACE('.$sql.', "&Ocirc;","o")';
		$sql = 'REPLACE('.$sql.', "&Otilde;","o")';
		
		$sql = 'REPLACE('.$sql.', "&Uacute;","u")';
		$sql = 'REPLACE('.$sql.', "&Uuml;","u")';
		
		$sql = 'REPLACE('.$sql.', "&Ccedil;","c")';

		return $sql;
	}
	
	/**
	 * Retorna lista de usuarios que o usuario determinado por $userID gerencia
	 * @param int $userID
	 * @param BD $bd
	 * @return array de usuarios
	 */
	function getGerenciados($userID, BD $bd) {
		$usuario = getUsers($userID);
		if (count($usuario) <= 0)
			return array();
			
		$sql = "SELECT * FROM usuarios WHERE gerente = '".$usuario[0]['username']."' ORDER BY nome";
		return $bd->query($sql);
		
	}
	
	function getDocOwner($doc, BD $bd) {
		if ($doc->owner == 0 && !$doc->anexado) {
			// doc está fora da CPO. busca para quem ele foi
			$desp = getLastDesp($doc);
			if (isset($desp['tipo']) && $desp['tipo'] == 'saida') {
				return $desp['unidade'];
			}
			else {
				return null;
			}
		}
		elseif ($doc->owner > 0 && !$doc->anexado) {
			// doc está com uma pessoa apenas
			$owner = getUsers($doc->owner);
			return $owner[0]['username'];
		}
		elseif ($doc->anexado) {
			// doc está anexado a algum outro... busca qual
			$pai = new Documento($doc->docPaiID);
			$pai->loadDados();
			while ($pai->anexado) {
				$pai = new Documento($pai->docPaiID);
				$pai->loadDados();
			}
			// pega com quem ele está
			return getDocOwner($pai, $bd);
		}
		else {
			// doc não satisfaz nenhuma destas condições... provavelmente está com alguma área
			return $doc->areaOwner;
		}
		
		return null;
	}
?>
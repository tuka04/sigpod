<?php
ob_implicit_flush(true);

include_once '../includeAll.php';
ini_set('mbstring.internal_encoding', 'UTF-8');
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$contratos = array('13609');


//var_dump($contratos); 
foreach ($contratos as $c) {
	$tipoID = $bd->query("SELECT tipoID FROM doc WHERE id=".$c);
	
	$deleta_doc = $bd->query("DELETE FROM doc WHERE id=".$c);
	
	$deleta_doc_contr = $bd->query("DELETE FROM doc_contrato WHERE id=".$tipoID[0]['tipoID']); 
	
	$deleta_hist = $bd->query("DELETE FROM data_historico WHERE docID = ".$c." OR doc_targetID = ".$c);
	
	$deleta_responsavel = $bd->query("DELETE FROM contrato_cpo_resp WHERE docID=".$c);
	$deleta_obra_contr = $bd->query("DELETE FROM obra_contrato WHERE contratoID=".$c);
	print "contrato ".$c.' deletado<br>';
}











exit();
$file = file_get_contents("contratos.csv");
$file = explode("\n", $file);

$i =0;
foreach ($file as $line) {
	$campos = explode(";", $line);// var_dump($campos);
	if($campos[0] != $i && $i != 0 && $campos[0] != ''){
		
		$aditivo = array();
	}
	
	if($campos[0] != ''){
		$i++;
		$ee = $campos[14];
		$numero = explode('/', $campos[3]);
		$unidade = descobre_unidade($campos[1]);
		$processoID = descobre_processo($campos[4]); //print 'proc = '.$campos[4].' / procID = '.$processoID.'';
		$obraID = descobre_obra($campos[2], $unidade['id'], $processoID);
		$empresaID = descobre_empresa($campos[5]);
		$data1 = descobre_data($campos[6]);
		$data2 = descobre_data($campos[7]);
		$data3 = descobre_data($campos[8]);
		if($data3== 0 )$escopo=true; else $escopo=false;
		$valorMat = str_ireplace(',', '.', $campos[12]);
		$valorMo = str_ireplace(',', '.', $campos[13]);
		$respID = trataResponsavel($campos[15], $bd);
		
		
		
		
		
		
		
		$tipoID = $bd->query("SELECT id FROM doc_contrato WHERE numeroContr = ".$numero[0]." AND anoE = ".$numero[1]." AND unOrg = '".$unidade['id']."'");var_dump($tipoID);
		if(count($tipoID) == 0)continue;
		$muda_num = $bd->query("UPDATE doc_contrato SET unOrg = '".$unidade['id']." - ".$unidade['nome']." (".$unidade['sigla'].")' WHERE id= ".$tipoID[0]['id']);
		//$sigla = $bd->query("SELECT abrev FROM unidades WHERE id=".$)
		$docID = $bd->query("update doc SET numeroComp = '".$unidade['sigla']." ".$numero[0]."/".$numero[1]."', emitente='".$unidade['id']." - ".$unidade['nome']." (".$unidade['sigla'].")' WHERE labelID=10 AND tipoID=".$tipoID[0]['id']); 
	//	$insert_obra = $bd->query("INSERT INTO obra_contrato (contratoID,obraID) VALUES (".$docID[0]['id'].",".$obraID.")");
		
		
		
		/*$sql = "INSERT INTO `doc_contrato`
		(`numeroContr`, `anoE`, `numProcContr`, `unOrg`, `valorProj`, `valorMaoObra`, `valorMaterial`, `valorTotal`, `dataAssinatura`, `dataReuniao`, `prazoContr`, `vigenciaContr`, `inicioProjObra`, `prazoProjObra`, `dataTermino`, `recursosOrc`, `elemEconomico`, `empresaID`)
		VALUES
		(".$numero[0].",".$numero[1].",".$processoID.",'".$unidade."',0,".$valorMo.",".$valorMat.",".($valorMat+$valorMo).",".$data1.",0,".(($data2-$data1)/(3600*24)).",".$data3.",0,0,0,'','".$ee."',".$empresaID.")";
		$tipoID = $bd->query($sql,null,true);
		
		$empreendID = $bd->query("SELECT empreendID FROM obra_obra WHERE id = ".$obraID);
		
		$sql_doc = "INSERT INTO `doc`(`anexado`, `docPaiID`, `data`, `criadorID`, `ownerID`, `OwnerArea`, `anexos`, `labelID`, `tipoID`, `emitente`, `numeroComp`,                                 `empreendID`, `obraID`  , `arquivado`, `solicDesarquivamento`, `solicitante`, `solicitado`, `ultimoHist`) 
		VALUES (                          1,".$processoID.",".time().",40,       40        ,''          ,''       ,        10,".$tipoID.",''       ,'".$numero[0]."/".$numero[1]."',".$empreendID[0]['empreendID'].",".$obraID.",0           ,0                      ,             0,0            ,0)";
		$docID = $bd->query($sql_doc,null,TRUE);
		
		$hist1 = "INSERT INTO `data_historico`(`data`, `tipo`, `docID`, `usuarioID`, `acao`, `unidade`, `label`, `despacho`, `volumes`, `doc_targetID`)
		VALUES (                          ".time().",'criacao',".$docID.",40       ,''     ,''        ,''      ,''         ,''        ,0)";
		$bd->query($hist1);
		
		$hist2 = "INSERT INTO `data_historico`(`data`, `tipo`, `docID`, `usuarioID`, `acao`, `unidade`, `label`, `despacho`, `volumes`, `doc_targetID`)
		VALUES (".time().",'obs',".$docID.",40,'Adicionou observa&ccedil;&atilde;o a esse documento','','Observa&ccedil;&atilde;o','Contrato adicionado automaticamente via controle de contratos','',0)";
		$ultHist = $bd->query($hist2,NULL,true);
		
		$last_hist = $bd->query("UPDATE doc SET ultimoHist = ".$ultHist." WHERE id = ".$docID);
		
		//print($sql.'<br>');*/
		
	} else {
		$aditivo[] = trataAditivo($campos[9], $escopo);
		$aditivo[] = trataAditivo($campos[10], $escopo, false);
	}
	
	

}

function descobre_data($string){
	if(stripos(mb_strtolower($string,'UTF-8'), 'escopo'))
		return 0;
	$data = explode('/', $string); //print_r($data);
	if(count($data) == 3){
		if(strlen($data[0]) == 2 && strlen($data[1]) == 2 &&strlen($data[2]) == 4){
			return mktime(0,0,0,$data[1],$data[0],$data[2]);
		}
	} else {
		return 0;
	}
}

function descobre_empresa($string) {
	global $bd;
	
	if(stripos(mb_strtolower($string,'UTF-8'),'ohana'))
		return 0;
	
	$palavra = explode(' ', htmlentities(mb_strtolower($string,'UTF-8'),ENT_QUOTES,'UTF-8',false));//print_r($palavra);
	foreach ($palavra as $pkey => $p) {
		if(strlen($p) > 1){
			$likes .= ' nome LIKE \'%'.$p.'%\' OR';
		}
	}
	$likes = rtrim($likes, 'OR');
	$sql = "SELECT id,nome FROM empresa WHERE $likes";
	$empr = $bd->query($sql);
	
	foreach ($empr as $ek => $e) {
		if(strlen($p)>0 && strlen($e['nome'])>0){
			$empr[$ek]['p'] = 0;
			//print_r($e['nome']); exit();
			$i=10;
			foreach ($palavra as $pk => $p) {
				//print ($empr['nome']);
				if(stripos(mb_strtolower($e['nome'],'utf-8'), ($p))!== false){
					$empr[$ek]['p'] += strlen($p)*$i;
				}
				$i /= 2;
				set_time_limit(0);
			}
			
			$empr[$ek]['p'] = $empr[$ek]['p']/strlen($p) + $empr[$ek]['p']/strlen($e['nome']) ;
			
			set_time_limit(0);
		}
	}
	
	$resposta = null;
	foreach ($empr as $r) {
		if($resposta == null || $resposta['p'] < $r['p'])
			$resposta = $r;
		set_time_limit(0);
	}
	
	//print $string .'='; var_dump($resposta);print("<BR>");//exit();
	flush();
	return $resposta['id'];
}

function descobre_unidade($string) {
	global $bd;
	
	$sql = "SELECT id,nome,sigla FROM unidades WHERE sigla = '$string' AND ativo = 1";
	$res = $bd->query($sql);
	
	if(count($res) != 1){
		if($string == 'PRP') return array(array('id' => '01.06.00.00.00.00', 'nome'=> 'PRO-REITORIA DE PESQUISA', 'sigla' => 'PRP'));
		else if($string == 'gastro') return array(array('id' => '01.28.00.00.00.00', 'nome'=> 'CENTRO DIAGNOSTICO DE DOENCAS APARELHO DIGESTIVO', 'sigla' => 'GASTRO'));
		else {print $string. '='; var_dump($res); print '<br>';}
	}
	
	return $res[0];
}

function descobre_processo($string){
	global $bd; 
	
	if(stripos($string, 'P') === false){
		$res = $bd->query("SELECT d.id,p.numero_pr FROM doc_processo AS p INNER JOIN doc AS d ON p.id = d.tipoID WHERE d.labelID=1 AND p.numero_pr LIKE '%".str_ireplace('/', '-', $string)."'");
		if(count($res) == 1)
			return $res[0]['id'];
		else
			return null;
	} else {
		$string = str_ireplace('-', '', $string);
		$un_pr = explode('P', $string);
		$num_pr = explode('/', $un_pr[1]);
		if(strlen($num_pr[1]) == 2){
			$ano = '20'.$num_pr[1];
		} else {
			$ano = $num_pr[1];
		}
		$num = '';
		if(strlen($num_pr[0]) == 1)
			$num .= '0000';
		elseif(strlen($num_pr[0]) == 2)
			$num .= '000';
		elseif(strlen($num_pr[0]) == 3)
			$num .= '00';
		elseif(strlen($num_pr[0]) == 4)
			$num .= '0';
		
		$num .= $num_pr[0];
		
		$num_pr =  $un_pr[0].' P-'.$num.'-'.$ano;
		
		global $bd;
		$sql = "SELECT d.id FROM doc as d INNER JOIN doc_processo AS p ON p.id = d.tipoID WHERE d.labelID = 1 AND p.numero_pr='".$num_pr."'";
		$proc = $bd->query($sql);
		
		if(count($proc) == 1)
			return $proc[0]['id'];
	}
}

function descobre_obra($string, $unidade, $procID){
	if($procID == null) return null;
	
	global $bd;
	
	$empreend_proc = $bd->query("select d.empreendID,p.guardachuva FROM doc as d INNER JOIN doc_processo as p ON p.id = d.tipoID WHERE d.id=$procID");
	
	if($empreend_proc[0]['guardachuva'] == 0){
		$empreendID = array($empreend_proc[0]['empreendID']);
	} else {
		$empreendID = $bd->query('SELECT empreendID FROM guardachuva_empreend WHERE docID = '.$procID);
	}
	
	//print 'procID='.$procID.' empreendID=';
	//var_dump($empreendID[0]);
	//print '<br>';
	
	$palavra = explode(' ', $string);
	foreach ($palavra as $pkey => $p) {
		if(strlen($p) > 1){
			//print $p;
			$palavra[$pkey] = stringBusca($p);
			$likes .= ' o.nomeBusca LIKE \'%'.$palavra[$pkey].'%\' OR';
		}
	}
	$likes = '('.rtrim($likes,'OR').')';
	if($unidade == '01.07.63.00.00.00'){
		$unOrg = "(e.unOrg = '01.07.63.00.00.00' OR e.unOrg = '01.14.16.00.00.00')";
	} else {
		$unOrg = "e.unOrg = '$unidade'";
	}
	
	$sql = "SELECT o.id,o.nomeBusca FROM obra_obra AS o INNER JOIN obra_empreendimento AS e ON o.empreendID = e.id WHERE empreendID=".$empreendID[0]." AND $likes AND $unOrg";
	$res = $bd->query($sql);
	
	if(count($res) > 1){
		foreach ($res as $rkey => $r) {
			if(!isset($res[$rkey]['parecido']))
				$res[$rkey]['parecido'] = 0;
			//print "<BR/> ".$r['nomeBusca'].'=';
			foreach ($palavra as $p) {
				if (strlen($p) > 0){
					if (strpos($r['nomeBusca'], $p) !== false){
						//print $p;
						$res[$rkey]['parecido'] += strlen($p);
					}
				}
			}
			//print $res[$rkey]['parecido'].'/'. count($r['nomeBusca']);
			$res[$rkey]['parecido'] = $res[$rkey]['parecido'] / strlen($r['nomeBusca']);
		}
		
		//print $string.' = ';	
		//var_dump($res);
		//print "<BR/>";
	} elseif (count($res) == 0){
		print "SEM OBRA!".$string.' ('.$unidade.') <BR/>';
	}
	
	$resposta = null;
	foreach ($res as $r) {
		if($resposta == null || $resposta['parecido'] < $r['parecido'])
			$resposta = $r;
	}
	return  $resposta['id'];
	//print $string.' = '. $resposta['nomeBusca'] .' = '. $resposta['parecido'].'<br>';
}

	function trataAditivo($string, $escopo, $data = true) {
		if ($string === null || $string === "")
			return null;
		
		$string = str_replace(array("\n", "\r", "\t", "\"", "'"), array("", "", "", "", ""), $string);
		//$string = stringBusca($string);
		
		$string = mb_ereg_replace("^[ ]+", "", mb_strtolower('  '.$string,'utf-8')); 
		
		$chars_acentuados = 
		array("Ã","ã","Õ","õ","á","Á","é","É","í","Í","ó","Ó","ú","Ú","ç","Ç","à","À","è","È","ì","Ì","ò","Ò","ù","Ù","ä","Ä","ë","Ë","ï","Ï","ö","Ö","ü","Ü","Â","Ê","Î","Ô","Û","â","ê","î","ô","û","!","?","“","”","\"","\\","/","%");
		$chars_normais =
		array("a","a","o","o","a","a","e","e","i","i","o","o","u","u","c","c","a","a","e","e","i","i","o","o","u","u","a","a","e","e","i","i","o","o","u","u","A","E","I","O","U","a","e","i","o","u",".",".",".",".","." ,"." ,".",".");
		// retira os acentos
		$string = str_replace($chars_acentuados, $chars_normais, $string);
		
		//var_dump($string);
		
		if ($data) {
			$partes = explode("-", $string);
			if (count($partes) <= 1)
				return false;
				
			foreach ($partes as $indice => $val) {
				$partes[$indice] = trim($val);
			}
			
			$parteRelevante = $partes[1];
			$q = $parteRelevante;
		}
		else {
			$q = $string;
		}
		
		//var_dump($q);
		
		$tipo = '';
		$valor = 0;
		$campo = array();
		if ($data) {
			if (stripos($q, "prazo ") !== false) {
				if (!$escopo) 
					$campo[0] = 'prazoContr';
				else
					$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "prazo ") + strlen("prazo "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "prazos") !== false) {
				if (!$escopo) {
					$campo[0] = 'prazoContr';
					$campo[1] = 'prazoProjObra';
				}
				else {
					$campo[1] = 'prazoProjObra';
				}
				
				$substr = substr($q, stripos($q, "prazos ") + strlen("prazos "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "vigencia") !== false) {
				if (!$escopo) 
					$campo[0] = 'prazoContr';
				else
					$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "vigencia") + strlen("vigencia"), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, " pe ") !== false) {
				$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, " pe ") + strlen(" pe "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "execucao") !== false) {
				$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "execucao") + strlen("execucao"), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			
			
			if (count($subpartes) > 0) {
				foreach ($subpartes as $indice => $v) {
					if ($v === null || $v === "" || $v === false || strlen($v) === 0) {
						continue;
					}
					
					if ($v + 0 !== 0) {
						$valor = $v + 0;
						if (isset($subpartes[$indice+1]) && stripos($subpartes[$indice+1], "dia") !== false) {
							$tipo = 'dias';
						}
						else {
							if (stripos($substr, "R$") !== false) 
								$tipo = 'dinheiro';
						}
						break;
					}
				}
			}
		}
		else {
			$q = trim($q);
			if (stripos($q, "alocacao") !== false) return null;
			$subpartes = explode(" ", $q);
			
			$campo = 'valorTotal';
			$tipo = 'dinheiro';
			
			$operador = '+';
			$valor = array();
			foreach ($subpartes as $indice => $v) {
				if ($v == '-') {
					$operador = '-';
				}
				else {
					$v = str_replace(".", "", $v);
					$v = str_replace(",", ".", $v);
					
					if ($v + 0 !== 0) {
						$v = $v+0;
						if ($operador == '-') {
							$valor[] = -$v;
						}
						else {
							$valor[] = $v;
						}
					}
				}
			}
			
		}
		
		
		return array("campo" => $campo, "valor" => $valor, "tipo" => $tipo);
		
	}

	
	function trataResponsavel($string, $bd) {		
		if ($string == null || $string == "") return null;
		$partes = explode("-", $string);
		
		if (count($partes) <= 1) return null;
		
		$email = $partes[1];
		
		$email = str_replace(",", ".", $email);
		$email = trim($email);
		
		//var_dump($email);
		
		$sql = "SELECT * FROM usuarios WHERE email LIKE '".$email."'";
		$res = $bd->query($sql);
		
		if (count($res) > 0) 
			return $res[0]['id'];
		else
			return null;
	}
?>
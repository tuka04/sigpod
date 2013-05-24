<?php
// includes
include_once('includeAll.php');
//include_once('sgd_modules.php');
	
// verifica se o usuario esta' conectado
checkLogin(6);
	
// cria uma nova pagina html
$html = new html($conf);
// seta o header
$html->header = "Verificação de consistência de processos/BD";
	
//completa o nome de usuario
$html->user = $_SESSION['nomeCompl'];
	
//inicia conexao com o banco de dados
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
// mostra o menu
$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],30,$bd);
	
$html->path = showNavBar(array());
	
$html->content[1] = "Verificando documentos...<br>";
	
// se report = false, esta pagina faz APENAS uma verificacao do formato dos numeros de processo
// se report = true, esta pagina procura por processos cujos numeros sao iguais (diferindo apenas de zero a esquerda) e faz merge dos dois processos, deletando a copia com formato invalido
//$report = true;
$report = false;
	
if (!$report) {
	$sql = "SELECT * FROM doc WHERE labelID = '1'";
	$res = $bd->query($sql);
	$html->content[1] .= "Existem " .count($res). " documentos na tabela doc e ";
	$sql = "SELECT * FROM doc_processo";
	$result = $bd->query($sql);
	$html->content[1] .= count($result). " na tabela doc_processo.<br><br>";
	
	// percorre os documentos da tabela doc
	$html->content[1] .= "Verificando documentos da tabela <b>doc</b>:<br>";
	for ($i = 0; $i < count($res); $i++) {
		// seta a tupla do documento atual
		$doc = $res[$i];
	
		// verifica se este documento existe tambem na tabela doc_processo
		$sql = "SELECT * FROM doc_processo WHERE id = '" .$doc['tipoID']. "'";
		$docProc = $bd->query($sql);
		if (count($docProc) < 1) $html->content[1] .= "<b>ERRO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID:" .$doc['id']. ") da tabela doc não existe na tabela doc_processo.<br>";
		elseif (count($docProc) > 1) $html->content[1] .= "<b>ERRO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID:" .$doc['id']. ") da tabela doc existe " .count($docProc). " vezes na tabela doc_processo.<br>";
	
		// pega os arquivos anexos deste doc
		if (isset($docProc['documento'])) {
			$anexos = $docProc['documento'];
			$ids = explode(",", $anexos);
			
			foreach($ids as $id) {
				if ($id) {
					$sql = "SELECT * FROM doc WHERE id = '" .$id. "' AND labelID = '1'";
					$anexo = $bd->query($sql);
					if (count($anexo) != 1) {
						$html->content[1] .= "<b>ERRO:</b> Processo <b>" .$docProc['numero_pr']. "</b> (ID:" .$docProc['id']. ") da tabela doc_processo possui anexo de ID <b>" .$id. "</b>, porém este processo não existe na tabela doc.<br>";
					}
				}
			} /* foreach */ 
		} /* if */
		
	} /* for */
	$html->content[1] .= "Fim da verificação da tabela <b>doc</b>.<br><br>";
	
	// percorre os documentos da tabela doc_processo
	$html->content[1] .= "Percorrendo os documentos da tabela <b>doc_processo</b>:<br>";
	$sql = "SELECT * FROM doc_processo";
	$res = $bd->query($sql);
	for ($i = 0; $i < count($res); $i++) {
		// seta a tupla do documento atual
		$docProc = $res[$i];
		
		// verifica se este documento existe na tabela doc
		$sql = "SELECT * FROM doc WHERE labelID = '1' AND tipoID = '" .$docProc['id']. "'";
		$doc = $bd->query($sql);
		if (count($doc) < 1) $html->content[1] .= "<b>ERRO:</b> Processo <b>" .$docProc['numero_pr']. "</b> (ID: " .$docProc['id']. ") da tabela doc_processo não existe na tabela doc.<br>";
	}
	$html->content[1] .= "Fim da verificação da tabela <b>doc_processos</b>.<br><br>";
	
	$html->content[1] .= "Verificando consistência de tabelas doc e doc_processo com questão ao <b>numero pr</b>: <br>";
	$sql = "SELECT d.id, d.tipoID, d.numeroComp, dp.numero_pr FROM doc AS d, doc_processo AS dp WHERE d.labelID = '1' AND d.tipoID = dp.id";
	$res = $bd->query($sql);
	for ($i = 0; $i < count($res); $i++) {
		$doc = $res[$i];
		if (strcmp($doc['numeroComp'], $doc['numero_pr']) != 0) {
			$html->content[1] .= "O processo de ID <b>" .$doc['id']. "</b> possui número de processo diferente na tabela doc e na doc_processo!<br>";
		}
	}
	$html->content[1] .= "Fim da verificação de <b>consistência</b>.<br><br>";
	
	// verificando o formato dos numeros dos processos
	verificaNum($html, $bd);
	
	$html->content[1] .= "<br><br>";
	
	// verificando o formato dos numeros dos processos na tabela doc_processo
	verificaProcNum($html, $bd);
}
else {
	// verifica processos de numeros pr duplicados
	verificaDuplicata($html, $bd);
}
$html->content[1] .= "<br>Fim da verificação do formato dos números de processos.<br>";
	
$html->showPage();
	
	
function verificaNum($html, $bd) {
	$html->content[1] .= "Verificando o formato dos números de processos:<br>";
	$sql = "SELECT * FROM doc WHERE labelID = '1'";
	$res = $bd->query($sql);

	for ($i = 0; $i < count($res); $i++) {
		// seta a tupla do documento atual
		$doc = $res[$i];
		
		$numPR = $doc['numeroComp'];
		// numero do processo tem formato: XX L-ZZZZZ-YYYY (XX: numero 2 digitos; L um char; ZZZZZ numero 5 digitos; YYYY ano)
		// separa os 2 primeiros digitos do resto do numero pr
		$parte = explode(" ", $numPR);
		
		if ((count($parte) != 2) || (strlen($parte[0]) != 2)) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão.<br>";
		else {
			// separa o resto do numero separado por hifens
			$pedacos = explode("-", $parte[1]);
			if (count($pedacos) != 3) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão (partes faltando).<br>";
			else {
				if (strlen($pedacos[0]) != 1) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão (<b>dígito letra</b> com mais de 1 digito).<br>";
				if (strlen($pedacos[1]) < 5) {
					$html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão (<b>número central</b> com menos de 5 dígitos). Sugestão: <b>";
					$html->content[1] .= $parte[0]. " " .$pedacos[0]. "-";
					$html->content[1] .= str_pad($pedacos[1], 5, "0", STR_PAD_LEFT);
					$html->content[1] .= "-" .$pedacos[2];
					$html->content[1] .= "</b>. ";
					
					// realiza verificação se existe outro processo com o mesmo numero, apenas com mais zeros a esquerda
					$atualizar = true;
					for ($digitos = strlen($pedacos[1])+1; $digitos <= 5; $digitos++) {
						$novoPedaco = $parte[0]. " " .$pedacos[0]. "-" .str_pad($pedacos[1], $digitos, "0", STR_PAD_LEFT). "-" .$pedacos[2];
						$sql = "SELECT id, numeroComp FROM doc WHERE labelID = '1' AND numeroComp = '" .$novoPedaco. "'";
						$igual = $bd->query($sql);
						if (count($igual) != 0) { 
							$html->content[1] .= "Já existe documento com número de processo <b>" .$novoPedaco. "</b> ID: " .$igual[0]['id']. ".";
							$atualizar = false;
						}
						$sql = "SELECT * FROM doc_processo WHERE numero_pr = '" .$novoPedaco. "'";
						$igual = $bd->query($sql);
						if (count($igual) != 0) {
							$html->content[1] .= "Já existe documento com número de processo <b>" .$novoPedaco. "</b> ID: " .$igual[0]['id']. ". (tabela doc_processo)";
							$atualizar = false;
						}
					}
					
					if ($atualizar) {
						$updateSQL = "UPDATE doc SET numeroComp = '" .$novoPedaco. "' WHERE id = '" .$doc['id']. "'";
						$html->content[1] .= "<br>Teste de SQL: " .$updateSQL. "<br>";
						$bd->query($updateSQL);
						$updateSQL2 = "UPDATE doc_processo SET numero_pr = '" .$novoPedaco. "' WHERE id = '" .$doc['tipoID']. "'";
						$html->content[1] .= "Teste de SQL2: " .$updateSQL2. "<br>";
						$bd->query($updateSQL2);
					}
					
					$html->content[1] .= "<br>";
				
				}
				if (strlen($pedacos[1]) > 5) $html->content[1] .= "<b>ERRO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão (<b>número central</b> com <b>mais</b> de 5 dígitos).<br>";
				if (strlen($pedacos[2]) != 4) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. ") da tabela doc está com número fora do padrão (<b>ano</b> com mais/menos de 4 dígitos).<br>";
			} /* else */
		} /* else */
		//$html->content[1] .= "<br>";
	} /* for */
}

// faz a mesma coisa que a funcao verificaNum, exceto que esta percorre a tabela doc_processo
function verificaProcNum($html, $bd) {
	$html->content[1] .= "Verificando o formato dos números de processos [tabela doc_processo]:<br>";
	$sql = "SELECT * FROM doc_processo";
	$res = $bd->query($sql);
	
	for ($i = 0; $i < count($res); $i++) {
		// seta a tupla do documento atual
		$doc = $res[$i];
		
		// verifica se este processo da tabela doc_processo existe na tabela doc. se não existir, ignora
		$sql = "SELECT * FROM doc WHERE labelID = '1' AND tipoID = '" .$doc['id']. "'";
		$check = $bd->query($sql);
		//$html->content[1] .= $doc['id']. " " .count($check). "<br>";
		if (count($check) != 1) continue; 
		
		$numPR = $doc['numero_pr'];
		// numero do processo tem formato: XX L-ZZZZZ-YYYY (XX: numero 2 digitos; L um char; ZZZZZ numero 5 digitos; YYYY ano)
		// separa os 2 primeiros digitos do resto do numero pr
		$parte = explode(" ", $numPR);
		
		if ((count($parte) != 2) || (strlen($parte[0]) != 2)) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão.<br>";
		else {
			// separa o resto do numero separado por hifens
			$pedacos = explode("-", $parte[1]);
			if (count($pedacos) != 3) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão (partes faltando).<br>";
			else {
				if (strlen($pedacos[0]) != 1) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão (<b>dígito letra</b> com mais de 1 digito).<br>";
				if (strlen($pedacos[1]) < 5) {
					$html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão (<b>número central</b> com menos de 5 dígitos). Sugestão: <b>";
					$html->content[1] .= $parte[0]. " " .$pedacos[0]. "-";
					$html->content[1] .= str_pad($pedacos[1], 5, "0", STR_PAD_LEFT);
					$html->content[1] .= "-" .$pedacos[2];
					$html->content[1] .= "</b>. ";
					
					// realiza verificação se existe outro processo com o mesmo numero, apenas com mais zeros a esquerda
					$atualizar = true;
					for ($digitos = strlen($pedacos[1])+1; $digitos <= 5; $digitos++) {
						$novoPedaco = $parte[0]. " " .$pedacos[0]. "-" .str_pad($pedacos[1], $digitos, "0", STR_PAD_LEFT). "-" .$pedacos[2];
						$sql = "SELECT id, numero_pr FROM doc_processo WHERE numero_pr = '" .$novoPedaco. "'";
						$igual = $bd->query($sql);
						if (count($igual) != 0) { 
							$html->content[1] .= "Já existe documento com número de processo <b>" .$novoPedaco. "</b> ID: " .$igual[0]['id']. ".";
							$atualizar = false;
						}
					}
					
					if ($atualizar) {
						//$updateSQL = "UPDATE doc SET numeroComp = '" .$novoPedaco. "' WHERE id = '" .$doc['id']. "'";
						//$html->content[1] .= "<br>Teste de SQL: " .$updateSQL. "<br>";
						//$updateSQL2 = "UPDATE doc_processo SET numero_pr = '" .$novoPedaco. "' WHERE id = '" .$doc['tipoID']. "'";
						//$html->content[1] .= "Teste de SQL2: " .$updateSQL2. "<br>";
					}
					
					$html->content[1] .= "<br>";
				
				}
				if (strlen($pedacos[1]) > 5) $html->content[1] .= "<b>ERRO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão (<b>número central</b> com <b>mais</b> de 5 dígitos).<br>";
				if (strlen($pedacos[2]) != 4) $html->content[1] .= "<b>AVISO:</b> Processo <b>" .$doc['numero_pr']. "</b> (ID: " .$doc['id']. ") da tabela doc_processo está com número fora do padrão (<b>ano</b> com mais/menos de 4 dígitos).<br>";
			} /* else */
		} /* else */
		//$html->content[1] .= "<br>";
	} /* for */
}

function verificaDuplicata($html, $bd) {
	$html->content[1] .= "Verificando processos duplicados no banco de dados...<br><br>";
	// seleciona todos os processos
	$sql = "SELECT * FROM doc WHERE labelID = '1'";
	$res =  $bd->query($sql);
	
	// percorre todos os processos
	for ($i = 0; $i < count($res); $i++) {
		// seta o documento atual a ser analisado
		$doc = $res[$i];
		
		$numPR = $doc['numeroComp'];
		// numero do processo tem formato: XX L-ZZZZZ-YYYY (XX: numero 2 digitos; L um char; ZZZZZ numero 5 digitos; YYYY ano)
		// separa os 2 primeiros digitos do resto do numero pr
		$parte = explode(" ", $numPR);
		// inicia a verificacao do formato dos numeros
		if (count($parte) == 2) {
			// separa o resto do numero separado por hifens
			$pedacos = explode("-", $parte[1]);
			if (count($pedacos) == 3) {
				// realiza verificação se existe outro processo com o mesmo numero, apenas com mais zeros a esquerda
				$duplicado = false;
				for ($digitos = strlen($pedacos[1])+1; $digitos <= 5; $digitos++) {
					$novoPedaco = $parte[0]. " " .$pedacos[0]. "-" .str_pad($pedacos[1], $digitos, "0", STR_PAD_LEFT). "-" .$pedacos[2];
					$sql = "SELECT * FROM doc WHERE numeroComp = '" .$novoPedaco. "' AND labelID = '1'";
					$igual = $bd->query($sql);
					if (count($igual) != 0) {
						if ($duplicado == false) $html->content[1] .= "O Processo <b>" .$doc['numeroComp']. "</b> (ID: " .$doc['id']. " tipoID: " .$doc['tipoID']. ") possui mesmo número PR que o(s) processo(s):  ";
						else $html->conten[1] .= ", ";
						$html->content[1] .= "<b>" .$novoPedaco. "</b> (ID: " .$igual[0]['id']. " tipoID: " .$igual[0]['tipoID']. ")";
						mergeProc($html, $doc['id'], $igual[0]['id'], $bd);
						$duplicado = true;
					}
				}
				if ($duplicado == true) $html->content[1] .= ".<br>";
			}
		}
	}
}

// junta dois processos num só. $docErrado é o processo com número pr errado e $docCerto o processo com numero pr certo
// neste caso, $docErrado vai ser eliminado
function mergeProc($html, $idProcErrado, $idProcCerto, $bd) {
	// seleciona o processo errado
	$sql = "SELECT * FROM doc WHERE id = '" .$idProcErrado. "'";
	$ProcErrado = $bd->query($sql);
	if (count($ProcErrado) != 1) {
		$html->content[1] .= "<br>Erro no ID do processo errado.<br>";
		exit();
	}
	
	// seleciona o processo certo
	$sql = "SELECT * FROM doc WHERE id = '" .$idProcCerto. "'";
	$ProcCerto = $bd->query($sql);
	if (count($ProcCerto) != 1) {
		$html->content[1] .= "<br>Erro no ID do processo certo.<br>";
		exit();
	}
	
	// atualiza historico, transferindo o historico do processo errado para o certo
	$sql = "UPDATE data_historico SET docID = '" .$idProcCerto. "' WHERE docID = '" .$idProcErrado. "'";
	$html->content[1] .= "<br>Teste SQL: " .$sql. "<br>";
	//$bd->query($sql);
	//$html->content[1] .= "Histórico transferido.<br>";
	
	// atualiza parte de anexos
	$sql = "SELECT * FROM doc_processo WHERE id = '" .$ProcErrado[0]['tipoID']. "'";
	$tempErrado = $bd->query($sql);
	$sql = "SELECT * FROM doc_processo WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
	$tempCerto = $bd->query($sql);
	$html->content[1] .= "Anexos: " .$tempErrado[0]['anexos']. " id " .$idProcErrado. " e " .$tempCerto[0]['anexos']. " id " .$idProcCerto. "<br>";
	if (($tempErrado[0]['anexos'] != NULL) && (strcasecmp($tempErrado[0]['anexos'], "nenhum") != 0)) {
		if (($tempCerto[0]['anexos'] == NULL) || (strcasecmp($tempCerto[0]['anexos'], "nenhum") == 0)) {
			$sql = "UPDATE doc_processo SET anexos = '" .$tempErrado[0]['anexos']. "' WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
			$html->content[1] .= "Teste SQL anexo1: " .$sql. "<br>";
			// rodar sql
			//$bd->query($sql);
		}
		elseif (($tempCerto[0]['anexos'] != NULL) && (strcasecmp($tempCerto[0]['anexos'], "nenhum") != 0)) {
			$html->content[1] .= "Dois não vazios: " .$tempErrado[0]['anexos']. " e " .$tempCerto[0]['anexos']. "<br>";
		}
	}
	
	// atualiza emitente
	if (($ProcErrado[0]['emitente'] != NULL) && ($ProcCerto[0]['emitente'] == NULL)) {
		$sql = "UPDATE doc SET emitente = '" .$ProcErrado[0]['emitente']. "' WHERE id = '" .$ProcCerto[0]['id']. "'";
		$html->content[1] .= "SQL Emitente: " .$sql. "<br>";
		$bd->query($sql);
	}
	
	// atualiza empreendID
	if (($ProcErrado[0]['empreendID'] != 0) && ($ProcCerto[0]['empreendID'] == 0)) {
		$sql = "UPDATE doc SET empreendID = '" .$ProcErrado[0]['empreendID']. "' WHERE id = '" .$ProcCerto[0]['id']. "'";
		$html->content[1] .= "SQL empreendID: " .$sql. "<br>";
		$bd->query($sql);
	}
	
	// atualiza unOrgProc
	if (($tempErrado[0]['unOrgProc'] != NULL) && (($tempCerto[0]['unOrgProc'] == NULL) || ($tempCerto[0]['unOrgProc'] == " "))) {
		$sql = "UPDATE doc_processo SET unOrgProc = '" .$tempErrado[0]['unOrgProc']. "' WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
		$html->content[1] .= "SQL unOrgProc: " .$sql. "<br>";
		$bd->query($sql);
	}
	
	// atualiza assunto
	if (($tempErrado[0]['assunto'] != NULL) && (($tempCerto[0]['assunto'] == NULL)) || ($tempCerto[0]['assunto'] == " ")) {
		$sql = "UPDATE doc_processo SET assunto = '" .$tempErrado[0]['assunto']. "' WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
		$html->content[1] .= "SQL assunto: " .$sql. "<br>";
		$bd->query($sql);
	}
	
	// atualiza tipoProc
	if (($tempErrado[0]['tipoProc'] != NULL) && ($tempCerto[0]['tipoProc'] == NULL)) {
		$sql = "UPDATE doc_processo SET tipoProc = '" .$tempErrado[0]['tipoProc']. "' WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
		$html->content[1] .= "SQL tipoProc: " .$sql. "<br>";
		$bd->query($sql);
	}
	
	// atualiza obra
	/*if (($tempErrado[0]['obra'] != 0) && ($tempCerto[0]['obra'] == 0)) {
		$sql = "UPDATE doc_processo SET obra = '" .$tempErrado[0]['obra']. "' WHERE id = '" .$ProcCerto[0]['tipoID']. "'";
		$html->content[1] .= "SQL obra: " .$sql. "<br>";
	}*/
	
	
	
	if ($ProcErrado[0]['anexado'] == 1) $html->content[1] .= "<b>Aviso</b>: este processo está anexado a outro documento.<br>";
	
	// remove processos com formato invalido
	$sql = "DELETE FROM doc WHERE id = '" .$idProcErrado. "'";
	$html->content[1] .= "SQL DELETE: " .$sql. "<br>";
	//$bd->query($sql);
	$sql = "DELETE FROM doc_processo WHERE id = '" .$ProcErrado[0]['tipoID']. "'";
	$html->content[1] .= "SQL DELETE2: " .$sql. "<br>";
	//$bd->query($sql);
}
?>
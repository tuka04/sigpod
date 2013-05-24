<?php

	include_once('../includeAll.php');
	include_once('../sgd_modules.php');

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

	$file = fopen("campos_faltantes.txt", "r");
	
	set_time_limit(180);
	
	$i = 0;
	
	// percorre o arquivo até chegar no fim
	while (($linha = fgets($file)) !=  false) {
		$atualizar = false;
		if (strlen($linha) > 1) {
			print("<br /><br />=============================================<br />");
			print("Linha $i (".strlen($linha).") " .$linha. "<br />");
			
			$campos = explode(";", $linha);
			
			if (count($campos) != 5) {
				 print("<b>Linha $i não possui todos os campos necessários</b><br />");
				 continue;
			}
			
			$numPr = $campos[0];
			$data = $campos[1];
			$procedencia = $campos[2];
			$interessado = $campos[3];
			$assunto = $campos[4];
			
			// trabalha o numero do processo
			$partes_pr = explode(" - ", $numPr);
			if (count($partes_pr) != 3) {
				print("<b>Linha $i não possui pr em formato correto</b><br />");
				continue;
			}
			
			$posicaoP = strpos($partes_pr[0], "P");
			if ($posicaoP === false) {
				print("<b>Linha $i não possui P em seu processo</b><br />");
				continue;
			}
			//$digito = str_pad($partes_pr[0]{$posicaoP - 1}, 2, "0", STR_PAD_LEFT);
			//$central = str_pad($partes_pr[1], 5, "0", STR_PAD_LEFT);
			$central = $partes_pr[1];
			$ano = $partes_pr[2];
			if (strlen($ano) != 4) {
				print("<b>Linha $i não possui ano com 4 dígitos!</b><br />");
				continue;
			}
			$prProv = $central . "-" . $ano;
			print("Central: $prProv<br />");
			
			$sql = "SELECT id, numeroComp, tipoID FROM doc WHERE labelID = '1' AND numeroComp LIKE '%$prProv%'";
			
			$res = $bd->query($sql);
			if (count($res) > 1) {
				print("<b>Foram achados mais de um resultados para a linha $i</b><br />");
				//$atualizar = true;
				$digito = str_pad($partes_pr[0]{$posicaoP - 1}, 2, "0", STR_PAD_LEFT);
				$central = str_pad($partes_pr[1], 5, "0", STR_PAD_LEFT);
				$prProv = $digito. " P-" .$central. "-" . $ano;
				print("<b>Novo PR:</b>$prProv<br />");
				$sql = "SELECT id, numeroComp, tipoID FROM doc WHERE labelID = '1' AND numeroComp LIKE '%$prProv%'";
				$res = $bd->query($sql);
				if (count($res) > 1) {
					print("<b>Foram achados mais de um resultados para a linha $i DENOVO</b><br />");
					continue;
				}
				if (count($res) == 0) {
					print("<b>Não foi achado nenhum documento para a linha $i ($prProv)</b><br />");
					continue;
				}
				$atualizar = true;
				//continue;
			}
			if (count($res) == 0) {
				print("<b>Não foi achado nenhum processo para a linha $i</b><br />");
				continue;
			}
			$id = $res[0]['id'];
			$aux = $res[0]['numeroComp'];
			$tipoID = $res[0]['tipoID'];
			if (count($res) == 1) {
				print("Foi achado um processo com o id<b> $id </b>e numero<b> $aux</b><br />");
			}
			$doc = new Documento($id);
			$doc->loadCampos();
			$doc->loadDados();
			print("Dados do documento $id carregados com sucesso.<br />");
			
			$sql = "SELECT * FROM unidades WHERE REPLACE(id, '.', '') LIKE '%$procedencia%'";
			$unidade = $bd->query($sql);
			if (count($unidade) > 1) {
				print("<b>Mais de uma unidade cadastrada com $procedencia</b><br />");
				//continue;
				$unOrgProc = null;
			}
			elseif (count($unidade) == 0) {
				print("<b>Nenhuma unidade cadastrada com $procedencia</b><br />");
				//continue;
				$unOrgProc = null;
			}
			else {
				$unOrgProc = $unidade[0]['id'] . " - " . $unidade[0]['nome'] . " (" .$unidade[0]['sigla']. ")";
				print("Unidade carregada com sucesso: $unOrgProc<br />");
			}
			
			$sql = "SELECT * FROM unidades WHERE nome LIKE '%$interessado%'";
			$unidadeInt = $bd->query($sql);
			if (count($unidadeInt) > 1) {
				print("<b>Mais de uma unidade $interessado interessada cadastrada no sistema.</b><br />");
				//continue;
				$unOrgInt = null;
			}
			elseif (count($unidadeInt) == 0) {
				print("<b>Nenhuma unidade cadastrada com $interessado (interessado)</b><br />");
				//continue;
				$unOrgInt = null;
			}
			else {
				$unOrgInt = $unidadeInt[0]['id'] . " - " . $unidadeInt[0]['nome'] . " (" .$unidadeInt[0]['sigla']. ")";
				print("Unidade Int carregada com sucesso: $unOrgInt<br />");
			}
			
			$sql = "UPDATE doc_processo SET";
			if ($unOrgProc != null) {
				$sql .= " unOrgProc = '$unOrgProc',";
			}
			if ($unOrgInt != null) {
				$sql .= " unOrgInt = '$unOrgInt',";
			}
			$sql .= " assunto = '$assunto' WHERE id = '$tipoID'";
			print("Teste SQL: $sql<br />");
			if ($atualizar == true) $bd->query($sql);
			if ($unOrgProc != null) {
				$sql = "UPDATE doc SET emitente = '$unOrgProc' WHERE id = '$id'";
				print("Teste SQL2: $sql<br />");
				if ($atualizar == true) $bd->query($sql);
			}
			
			if ($atualizar == true) $sql = $bd->query("INSERT INTO data_historico (data,tipo,docID,usuarioID,acao,unidade,label,despacho,volumes) VALUES (".time().",'obs',$id,53,'','','Observa&ccedil;&atilde;o','Campos completados autom&aacute;ticamente pelos dados recebidos do Centro de Computa&ccedil;&atilde;o.','')");
			$i++;
			//print("=============================================<br /><br />");
		}
	}
	
	fclose($file);
?>
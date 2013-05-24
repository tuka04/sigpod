<?php

	include_once('../includeAll.php');
	include_once('../sgd_modules.php');

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

	$file = fopen("mais_procs.txt", "r");
	
	set_time_limit(600);
	
	$i = 0;
	
	// percorre o arquivo até chegar no fim
	while (($linha = fgets($file)) !=  false) {
		$atualizar = false;
		if (strlen($linha) > 1) {
			print("<br /><br />=============================================<br />");
			print("Linha $i (".strlen($linha).") " .$linha. "<br />");
			
			$linha = trim(str_ireplace("\n", "", $linha), "\n\t");
			
			$campos = explode(";", $linha);
			
			if (count($campos) != 5) {
				 print('<b><font color="red">Linha $i não possui todos os campos necessários</font></b><br />');
				 continue;
			}
			
			$numPr = $campos[0];
			$data = $campos[1];
			$procedencia = $campos[2];
			$interessado = $campos[3];
			$assunto = $campos[4];
			$assunto = trim($assunto, "\t\n");
			
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
			if (strlen($central) != 5) {
				print("<b>Linha $i não possui central com 5 dígitos!</b><br />");
				continue;
			}
			$ano = $partes_pr[2];
			if (strlen($ano) != 4) {
				print("<b>Linha $i não possui ano com 4 dígitos!</b><br />");
				continue;
			}
			
			if (strlen($partes_pr[0]) != 3) {
				print("<b><h2>Linha $i não possui unidade com 3 dígitos!</h2></b><br />");
				continue;
			}
			
			
			$prProv = substr($partes_pr[0],0,2) . " " . substr($partes_pr[0],2,1) . "-" . $central . "-" . $ano;
			print("PR: $prProv<br />");
			
			$sql = "SELECT id, numeroComp, tipoID FROM doc WHERE labelID = '1' AND numeroComp LIKE '%$prProv%'";
			$res = $bd->query($sql);
			if (count($res) > 0) {
				print('<h1>ERRO: Processo j&aacute; inserido.</h1><br />');
				continue;
			}
			else {
				$sql = "SELECT * FROM unidades WHERE REPLACE(id, '.', '') LIKE '%$procedencia%'";
				$unidade = $bd->query($sql);
				if (count($unidade) > 1) {
					print("<b>Mais de uma unidade cadastrada com $procedencia</b><br />");
					//continue;
					$unOrgProc = null;
					if (!is_numeric($procedencia)) {
						print("Eh String!<br />");
						$unOrgProc = $procedencia;
					}
				}
				elseif (count($unidade) == 0) {
					print("<b>Nenhuma unidade cadastrada com $procedencia</b><br />");
					//continue;
					$unOrgProc = null;
					if (!is_numeric($procedencia)) {
						print("Eh String!<br />");
						$unOrgProc = $procedencia;
					}
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
					if (!is_numeric($interessado)) {
						print("Eh String!<br />");
						$unOrgInt = $interessado;
					}
				}
				elseif (count($unidadeInt) == 0) {
					print("<b>Nenhuma unidade cadastrada com $interessado (interessado)</b><br />");
					//continue;
					$unOrgInt = null;
					if (!is_numeric($interessado)) {
						print("Eh String!<br />");
						$unOrgInt = $interessado;
					}
				}
				else {
					$unOrgInt = $unidadeInt[0]['id'] . " - " . $unidadeInt[0]['nome'] . " (" .$unidadeInt[0]['sigla']. ")";
					print("Unidade Int carregada com sucesso: $unOrgInt<br />");
				}
				
				$tipoProc = '';
				$plan = stripos($assunto, "planejamento");
				if ($plan !== false && $plan == 0) {
					$tipoProc = 'plan';
				}
				$acompTec = stripos($assunto, "acompanhamento tecnico");
				if ($acompTec !== false && $acompTec == 0) {
					$tipoProc = 'acompTec';
				}
				print("Tipo do processo $tipoProc<br />");
				
				
				//$sql = "INSERT INTO doc_processo (numero_pr, unOrgProc, unOrgInt, assunto, tipoProc, documento, obra, anexos) VALUES ('$prProv', '$unOrgProc', '$unOrgInt', '$assunto', '$tipoProc', '0', '0', '')";
				$doc = new Documento(0);
				$doc->dadosTipo['nomeAbrv'] = 'pr';
				$doc->loadTipoData();
				$doc->campos['numero_pr'] = $prProv;
				//$prProv = substr($partes_pr[0],0,2) . " " . substr($partes_pr[0],2,1) . "-" . $central . "-" . $ano;
				$doc->campos['unOrgProc'] = $unOrgProc;
				$doc->campos['unOrgInt'] = $unOrgInt;
				$doc->campos['assunto'] = $assunto;
				$doc->campos['tipoProc'] = $tipoProc;
				$doc->campos['documento'] = "0";
				$doc->campos['obra'] = "0";
				$doc->campos['anexos'] = "";
				$doc->campos['guardachuva'] = "0";
				
				$sql = "SELECT * FROM doc_processo WHERE numero_pr = '" .$prProv ."'";
				$res = $bd->query($sql);
				if (count($res) == 0) { 
					if ($doc->salvaCampos()) print("Documento inserido em doc_processo com sucesso!<br />");
					else print("Erro ao inserir documento. <br/>");
				}
				else {
					print ("<h2>Processo ja cadastrado em doc_processo!</h2><br />");
				}
				
				$doc->campos['numero_pr_un'] = substr($partes_pr[0],0,2);
				$doc->campos['numero_pr_tipo'] = substr($partes_pr[0],2,1);
				$doc->campos['numero_pr_num'] = $central;
				$doc->campos['numero_pr_ano'] = $ano;
				
				if ($doc->salvaDoc(0)) print("Documento salvo com sucesso!<br />");
				else print("Erro na insercao.<br />");
				
				$doc->doLogHist(53, '', '', '', 'criacao', '', '');
				$doc->doLogHist(53, '', 'Criado automaticamente pelas informa&ccedil;&otilde;es recebidas do Centro de Computa&ccedil;&atilde;o.', '', 'obs', '', 'Observa&ccedil;&atilde;o');
				
				//print($sql);
			}
			//break;
			$i++;
			//print("=============================================<br /><br />");
		}
	}
	
	fclose($file);
?>
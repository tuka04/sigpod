<?php
include_once '/includeAll.php';
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$fp = fopen("processos_obras2.csv", "r");

set_time_limit(180);

$linha_anterior[1] = '';
$ini_aspas = false;

while (($linha = fgets($fp)) !== false) {//leitura da linha
	$obraID = 0;
	$doc_prID = 0;
	
	
	if($ini_aspas){
		if(strpos($linha, '"') ==! false) {
			$ini_aspas = false;
		}
		continue;
		
	} else{
		$linha = explode(';', $linha);
		$linha2 = $linha;
		if(!isset($linha[1])) continue;
		
		if(strpos($linha[1], '"') !== false) {
			$linha[1] = str_ireplace('"', "", $linha[1]);
			$linha[1] = substr($linha[1], 0, 11);
			$ini_aspas = true;
		} elseif (strpos($linha[1], " ") !== false) {
			$linha[1] = str_ireplace('"', "", $linha[1]);
			$linha[1] = substr($linha[1], 0, 11);
		} else {
			$linha[1] = substr($linha[1], 0, 11);
		}
	}
	
	if($linha[1] == $linha_anterior[1])
		continue;

	$linha_anterior = $linha;
	
	$linha[0] = explode("- ", $linha[0],2);
	
	//1 - identificar a obra
	$linha[0][1] = htmlentities($linha[0][1],ENT_QUOTES);
	if(substr($linha[0][1],-1,1) == ' ') {
		$linha[0][1] = substr($linha[0][1], 0, strlen($linha[0][1])-1);
	}
	
	$obra = $bd->query("SELECT id,nome,unOrg FROM obra_cad WHERE nome='".$linha[0][1]."'");
	//1.1 - se houver 2 obras com o mesmo nome (ex: reforma sanitario)
	if (count($obra) > 1){
		if ($linha[0][0] == 'PREFEITURA')     $linha[0][0] = '01.14.00.00.00.00';
		elseif ($linha[0][0] == 'CEPETRO')    $linha[0][0] = '01.02.04.24.00.00';
		elseif ($linha[0][0] == 'CEPAGRI')    $linha[0][0] = '01.02.04.05.00.00';
		elseif ($linha[0][0] == 'EDITORA')    $linha[0][0] = '01.01.21.00.00.00';
		elseif ($linha[0][0] == 'COMVEST')    $linha[0][0] = '01.04.01.00.00.00';
		elseif ($linha[0][0] == 'HEMOCENTRO') $linha[0][0] = '32.00.00.00.00.00';
		elseif ($linha[0][0] == 'REITORIA')   $linha[0][0] = '01.00.00.00.00.00';
		elseif ($linha[0][0] == 'CPO')        $linha[0][0] = '01.14.16.00.00.00';
		else {
			$res = $bd->query("SELECT id FROM unidades WHERE sigla = '{$linha[0][0]}'");
			if(count($res)){
				$linha[0][0] = $res[0]['id'];
			} else {
				$linha[0][0] = '00.00.00.00.00.00';
			}
		}
		
		$i = 0;
		while ($i < count($obra)) {
			if ($linha[0][0] == $obra[$i]['unOrg']) {
				$obraID = $obra[$i]['id'];
			}
			$i++;
		}
	} else {
		$linha[0][0] = $obra[0]['unOrg'];
		$obraID = $obra[0]['id'];	
	}

	//QUAL TIPO DE PROCESSO?
	$regex1 = preg_match("|[0-9]{2}-[0-9]{5}-[0-9]{2}|", $linha[1], $matches);
	if($regex1) {
		//processo unicamp
		$tipoPR="PR";
	}
	$regex2 = preg_match("|[0-9]{6}-[0-9]{2}|", $linha[1], $matches);
	if($regex2) {
		//processo funcamp
		$tipoPR ="FU";
	} 
	if (!$regex1 && !$regex2) {
		continue;
	}
	
	
	//DESCOBERTO ID OBRA
	print '|ObraID='.$obraID."|";
	
	//2 - identificar se o documento ja esta cadastrado no sistema
	$linha[1] = str_ireplace(" ", "", $linha[1]);
	$linha[1] = explode("-", $linha[1]);
	if($tipoPR == 'PR')
		$docIDa = $bd->query("SELECT id FROM doc_processo WHERE numero_pr = '{$linha[1][0]} P-{$linha[1][1]}-20{$linha[1][2]}'");
	
	if($tipoPR == 'FU'){
		if(strlen($linha[1][1]) > 2) $linha[1][1] = substr($linha[1][1], 0, 2);
		$linha[1][1] = str_ireplace(" ", '', $linha[1][1]);
		$docIDa = $bd->query("SELECT id FROM doc_processo WHERE numero_pr = 'FU P-{$linha[1][0]}-20{$linha[1][1]}'");
	
	}	
	
	
	if(isset($docIDa[0]['id'])) {
		$doc_prID = $docIDa[0]['id'];
	}else {
		$linha11['alt'] = intval($linha[1][1]);
		if($tipoPR == 'PR')
			$docIDa = $bd->query("SELECT id FROM doc_processo WHERE numero_pr = '{$linha[1][0]} P-{$linha11['alt']}-20{$linha[1][2]}'");
		
		if(isset($docIDa[0]['id'])) {
			$doc_prID = $docIDa[0]['id'];
		}
	}
	
	//3 - cadastrar o documento se ele n estiver cadastrado 
	if(!$doc_prID){
		if ($tipoPR == "PR"){
			$prNumCad = "{$linha[1][0]} P-{$linha[1][1]}-20{$linha[1][2]}";
			if (strlen($prNumCad) != 15) print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
		} elseif ($tipoPR == "FU") {
			$prNumCad = "FU P-{$linha[1][0]}-20{$linha[1][1]}";
			if (strlen($prNumCad) != 16) print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
		} 
		//adicionar doc no BD		
		
		$sql1 = $bd->query("SELECT nome,sigla FROM unidades WHERE id = '{$linha[0][0]}'");
		if(!count($sql1)) {
			$unOrgInt = $linha[0][0];
		} else {
			$unOrgInt = $linha[0][0].' - '.$sql1[0]['nome'].' ('.$sql1[0]['sigla'].')';
		}
		
		$sql1 = $bd->query("INSERT INTO doc_processo (numero_pr, unOrgProc, unOrgInt, assunto, tipoProc, documento, obra, anexos) VALUES ('$prNumCad', '', '$unOrgInt', '', '', '0', '$obraID', '')");
		$doc_prID = $bd->query("SELECT id FROM doc_processo WHERE numero_pr = '$prNumCad'");
		$doc_prID = $doc_prID[0]['id'];
		$sql2 = $bd->query("INSERT INTO doc (data,criadorID,ownerID,labelID,tipoID,emitente,numeroComp,anexos) VALUES (".time().",40,0,1,$doc_prID,'','$prNumCad','')");
		$docID = $bd->query("SELECT id FROM doc WHERE labelID=1 AND tipoID=$doc_prID ");
		$docID = $docID[0]['id'];
		$sql3 = $bd->query("INSERT INTO data_historico (data,tipo,docID,usuarioID,acao,unidade,label,despacho,volumes) VALUES (".time().",'criacao',$docID,40,'','','','','')");
		$sql3 = $bd->query("INSERT INTO data_historico (data,tipo,docID,usuarioID,acao,unidade,label,despacho,volumes) VALUES (".time().",'obs',$docID,40,'','','Observa&ccedil;&atilde;o','Criado automaticamente pelo controle de CI','')");
		print "docID=$docID|";
		
	} else {
		$docID = $bd->query("SELECT id FROM doc WHERE tipoID={$doc_prID} AND labelID=1");
		if (!isset($docID[0]['id']) || count($docID) > 1){ print("ERRO. PROC_ID {$doc_prID}  NAO BATE COM DOC ID"); exit();}
		else $docID = $docID[0]['id'];
		print "docID=$docID|";
	}
	
	//4 - relacionar obra-documento
	set_time_limit(0);
	
	//print_r($linha);
	print '<br />';
	$obras[] = $obraID;
	$bd->query("INSERT INTO obra_etapa (tipoID, obraID, processoID) VALUES (1, $obraID, $docID)");
	
	
}

fclose($fp);

?>
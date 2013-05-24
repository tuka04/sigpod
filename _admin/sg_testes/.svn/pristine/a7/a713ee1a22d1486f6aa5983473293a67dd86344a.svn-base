<?php
	include_once '../includeAll.php';
	
	includeModule('sgo');
	
	//conexao no BD
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	if(isset($_GET['save'])) {
		
		$success = $bd->query("UPDATE obra_obra SET empreendID = {$_GET['empreendID']} WHERE id = {$_GET['obraID']}");
		print json_encode(array(array("success" => $success)));
		
	} else {
		//
		$empreendID = $bd->query("SELECT id,nome FROM obra_empreendimento ORDER BY nome");
		$empreendArray = array();
		$html = '';
		//
		
		foreach ($empreendID as $e) {
			$empreendArray[] = array('value' => $e['id'], 'label' => $e['nome']);
		}
		
		
		foreach ($empreendID as $eID) {
			$obras = $bd->query("SELECT id, nome FROM obra_obra WHERE empreendID = {$eID['id']}");
			$empreend = new Empreendimento($bd);
			$empreend->load($eID['id']);
			
			foreach ($obras as $o) {
				$html .= '<tr class="c">
							<td class="c"><b>' . $o['nome'] . '</b><br /> <span style="font-size:10pt;">'.getVal($empreend->get('unOrg'), 'compl').'</span></td>
							<td class="c" style="vertical-align: middle">' . geraSelect('obra'.$o['id'], $empreendArray, $eID['id']) . '</td>
							<td class="c" style="vertical-align: middle">' . '<a id="link'. $o['id'] .'" href="javascript:void(0)" onclick="javascript:salva('.$o['id'].')"> Salvar </a> </td>
						</tr>';
			}
		}
		
		print 
			'<html>
			<head>
			<script type="text/javascript" src="../scripts/jquery.js"></script>
			<script type="text/javascript">
				function salva(obraID) {
					var empreendID = $("#obra"+obraID).val();
					$.get("empreendimento.php", {
						"save"     : 1,
						"obraID"     : obraID,
						"empreendID" : empreendID
					}, function(d){
						ret = eval(d);
						if(ret[0].success){ 
							$("#link"+obraID).html("Sucesso!");
						} else {
							$("#link"+obraID).html("Erro! Tentar Novamente");
						}
					});
				}
			</script>
			<link rel="stylesheet" type="text/css" href="../css/geral.css" />
			</head>
			<body style="background: #E8E8E8;">
			<form action="empreend.php?save" method="post">			
			<table>
			<tr class="c">
				<td class="c"><b>Obra</b></td>
				<td class="c"><b>Empreendimento</b></td>
				<td class="c"></td>
			</tr>' .
			$html .
			'</table>
			<!--input type="submit" value="Enviar" /-->
			</form>';
	}
?>
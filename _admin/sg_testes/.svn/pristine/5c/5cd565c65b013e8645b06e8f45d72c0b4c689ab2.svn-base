<?php
	include_once('../includeAll.php');
	
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	$dados = $bd->query("SELECT id, unOrg, nome, `nomeSolic` , `deptoSolic` , `emailSolic` , `ramalSolic` , `descricao` FROM sg.obra_obra");
	
	foreach($dados as $d){
		$bd->query("INSERT INTO obra_empreendimento (id, nome, unOrg, descricao, solicNome, solicDepto, solicEmail, solicRamal, justificativa, local )
					VALUES ('{$d['id']}','{$d['nome']}','{$d['unOrg']}','{$d['descricao']}','{$d['nomeSolic']}','{$d['deptoSolic']}','{$d['emailSolic']}','{$d['ramalSolic']}', '', '')");
	}
?>



""
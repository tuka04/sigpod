<?php


    /*Se voce esta vendo essa linha, o PHP pode nao estar instalado corretamente*/


	include_once '../conf.inc.php';	
	include_once '../classes/adLDAP/adLDAP.php';

	//TODOverificar se existe localhost
	print("1. Apache instalado com sucesso! <br>");
	
	
	
	print("2. PHP "./* NAO */" instalado com sucesso!<br>");
	
	//verificar instalacao do mysql
	
	if(!isset($conf['DBhost']['master']))
		print('<span style="color:red">ERRO! N&atilde;o h&aacute; Banco de dados master configurado!</span> <br>');
	
	if(!isset($conf['DBhost']['slave'])){
		print("<b>ALERTA: N&atilde;o h&aacute; banco de dados secund&aacute;rio configurado! N&atilde;o ser&aacute; poss&iacute;vel verificar o estado da replica&ccedil;&atilde;o. O SiGPOD pode nao funcionar caso o DB master saia do ar.</b><br />");
		$slaveOffline = true;
	} else {
		$slaveOffline = false;
	}
	$master_conn = mysql_connect($conf['DBhost']['master'], $conf['DBLogin'], $conf['DBPassword']) or die("Impossivel conectar ao master(".$conf['DBhost']['master'].":".$conf['DBport']."): ".mysql_error()); 
	print("3.1. MySQL master (".$conf['DBhost']['master'].":".$conf['DBport'].") esta acessivel!<br>");
	
	if(!$slaveOffline){
		$slave_conn = mysql_connect($conf['DBhost']['slave'], $conf['DBLogin'], $conf['DBPassword']) or die("Impossivel conectar ao slave(".$conf['DBhost']['slave']."): ".mysql_error()); 
		print("3.2. MySQL slave (".$conf['DBhost']['slave'].":".$conf['DBport'].") esta acessivel!<br>");
	}
	
	
	$repl_status = mysql_query("show slave status",$slave_conn);
	$repl_status = mysql_fetch_assoc($repl_status);
	if(count($repl_status) > 2){
		if(strcasecmp($repl_status['Slave_IO_Running'], 'Yes') !== 0 || strcasecmp($repl_status['Slave_SQL_Running'], 'Yes') !== 0) {
			print('<b>3.3. Replica&ccedil;&atilde;o <span style="background:red; color:white"> não </span> est&aacute; funcionando!</b><br>');
		} else {
			print('3.3. Replica&ccedil;&atilde;o est&aacute; funcionando!<br>');
		}
	} 
	
	$ldap2 = $conf['domainControllers'][1];
	
	$conf['domainControllers'] = array($conf['domainControllers'][0]);
	
	try {
		$adldap = new adLDAP();
		print("4.1. AD prim&aacute;rio (".$conf['domainControllers'][0].") respondendo!<br>");
	} catch (Exception $e) {
		print("<b>4.1. Falha ao conectar-se com o AD em (".$conf['domainControllers'][0].") : ".$e->getMessage()."</b><br>");
	}
		
	$conf['domainControllers'] = array($ldap2);
	
	try {
		$adldap = new adLDAP();
		print("4.2. AD secund&aacute;rio (".$conf['domainControllers'][0].") respondendo!<br>");
	} catch (Exception $e) {
		print("<b>4.2. Falha ao conectar-se com o AD em (".$conf['domainControllers'][0].") : ".$e->getMessage()."</b><br>");
	}
	
	print('--------<br><span style="background-color: #AAFFAA;">O SiGPOD est&aacute; apto para ser executado!</span><br>--------<br /><br />');
	
	mysql_select_db($conf['DBTable'],$master_conn);
	$docsID = mysql_query('SELECT d.tipoID,d.id,d.labelID,l.tabBD,d.anexos FROM doc as d INNER JOIN label_doc AS l ON d.labelID = l.id order by d.id',$master_conn);
	
	
	
	while (($d = mysql_fetch_assoc($docsID)) !== false) {
		$doctipoID = mysql_query('SELECT id FROM '.$d['tabBD'].' WHERE id='.$d['tipoID'],$master_conn);
		if(count(($x = mysql_fetch_assoc($doctipoID))) == 0)
			print('Inconsist&ecirc;ncia no BD! Os dados do documento <span style="background-color: #FFAAAA;">'.$x['id'].'</span> n&atilde;o existem no BD! <br />');
			
		foreach (explode(',', $d['anexos']) as $anex) {
			if (!file_exists('../files/'.$anex)){
				print('Arquivo <span style="background-color: #FFAAAA;">'.$anex.'</span> do doc '.$d['id'].' n&atilde;o existe. <br />');
			}
		}
	}
	
?>
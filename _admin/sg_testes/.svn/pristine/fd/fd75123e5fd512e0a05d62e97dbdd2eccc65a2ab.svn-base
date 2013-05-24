<?php
	include_once('includeAll.php');
	
	$pessoa = new Pessoa();
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	if($pessoa->logout($bd))
		header("Location: index.php");
	else
		showError(4);
?>
<?php
	include_once '../conf.inc.php';	
	
	$master_conn = mysql_connect($conf['DBhost']['master'], $conf['DBLogin'], $conf['DBPassword']) or die("Impossivel conectar ao master(".$conf['DBhost']['master'].":".$conf['DBport']."): ".mysql_error()); 
	$slave_conn = mysql_connect($conf['DBhost']['slave'], $conf['DBLogin'], $conf['DBPassword']) or die("Impossivel conectar ao slave(".$conf['DBhost']['slave'].":".$conf['DBport']."): ".mysql_error());
	
	$databases = mysql_query('SHOW DATABASES',$master_conn);
	$databases = mysql_fetch_assoc($databases);
		
	$lock = mysql_query('FLUSH TABLES WITH READ LOCK',$master_conn);
		
	$log = mysql_query('SHOW MASTER STATUS',$master_conn);
	$log = mysql_fetch_assoc($log);
	
	foreach ($databases as $db) {
		if($db['Database'] == 'information_schema' || $db['Database'] == 'mysql' || $db['Database'] == 'performance_schema')
			continue;
		
		mysql_select_db($db['Database'],$master_conn);
		
		$tables = mysql_query('SHOW TABLES',$master_conn);
		$tables = mysql_fetch_assoc($tables);
		
		foreach ($tables as $tb) {
			
			$select_from_master = mysql_query('SELECT * FROM '.$db['Database'].'.'.$tb['Tables_in_'.$db['Database']],$master_conn);
			
			
		}
		

		
		
		
		
		
	}
	
	
	
	
	
	
	
	$docsID = mysql_query('',$master_conn);
	
	
?>
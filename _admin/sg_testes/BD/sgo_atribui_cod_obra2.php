<?php

set_time_limit(0);

include_once '../includeAll.php';
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$obras = $bd->query("SELECT id FROM obra_obra");

foreach ($obras as $o) {
	$obraCod = $bd->query("SELECT cod FROM obra_obra WHERE id = {$o['id']}");
	$cod10 = substr($obraCod[0]['cod'], 0, 10);
	$i = 1;
	while(1){
		if($i<10) $idx = '0'.$i;
		else	  $idx = $i;
			 
		$mesmoCod = $bd->query("SELECT id FROM obra_obra WHERE cod='$cod10"."$idx'");
		
		if(!count($mesmoCod)){
			$update = $bd->query("UPDATE obra_obra SET cod='$cod10"."$idx' WHERE id= {$o['id']}");
			print("$obraCod -> $cod10$idx<br>");
			break;
		} elseif($mesmoCod[0]['id'] == $o['id']) {
			print("{$obraCod[0]['cod']} OKM<br>");
			break;
		}
		
		$i++;
	}
}
?>
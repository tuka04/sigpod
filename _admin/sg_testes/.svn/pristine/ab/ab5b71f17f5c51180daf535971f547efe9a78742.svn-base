<?php
	include_once('includeAll.php');
	include_once('sgd_modules.php');

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	set_time_limit(600);
	
	$geraLista = true;
	
	if ($geraLista) {
		$sql = "SELECT * FROM data_historico AS d INNER JOIN (SELECT id, docID, MAX(data) AS maxData FROM data_historico GROUP BY docID ORDER BY data DESC, id DESC) AS t ON d.docID = t.docID WHERE d.tipo = 'despIntern' AND d.unidade LIKE '% o Arquivo%' AND data = maxData ORDER BY d.docID DESC";
		$res = $bd->query($sql);
		//print(count($res));
		
		foreach ($res as $r) {
			print("Doc ".$r['docID']."<br />");
			$sql = "SELECT * FROM doc WHERE id = " .$r['docID'];
			$ver = $bd->query($sql);
			if (count($ver) <= 0) continue;
			$doc = new Documento($r['docID']);
			$doc->loadCampos();
			$doc->loadDados();
			
			if ($doc->arquivado == 1) continue;
			else {
				doArquiva($doc);
			}
		}
	}
?>
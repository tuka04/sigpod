<?php
	include_once('includeAll.php');
	include_once('sgd_modules.php');

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	set_time_limit(180);
	
	$geraLista = true;
	
	if ($geraLista) {
		$sql = "SELECT d.id, p.assunto, p.tipoProc, d.tipoID FROM doc AS d INNER JOIN doc_processo AS p ON d.tipoID = p.id WHERE d.labelID = 1 AND (p.tipoProc = '' OR p.tipoProc = 'nenhum') ORDER BY d.id DESC";
		$res = $bd->query($sql);
		//print(count($res));
		
		foreach ($res as $r) {
			print("Doc ".$r['id']."<br />");
			
			$assunto = '';
			$assunto = $r['assunto']; 
			//print("assunto $assunto<br />");
			$tipoProc = '';
			$desProj = stripos($assunto, "desenvolvimento projeto");
			if ($desProj !== false) {
				$tipoProc = 'contrProj';
			}
			$execObra = stripos($assunto, "execucao obra");
			if ($execObra !== false) {
				$tipoProc = 'contrObr';
			}
			$execObra = stripos($assunto, "execu&ccedil;ao obra");
			if ($execObra !== false) {
				$tipoProc = 'contrObr';
			}
			$execObra = stripos($assunto, "execu&ccedil;&atilde;o obra");
			if ($execObra !== false) {
				$tipoProc = 'contrObr';
			}
			$execObra = stripos($assunto, "execuc&atilde;o obra");
			if ($execObra !== false) {
				$tipoProc = 'contrObr';
			}
			$plan = stripos($assunto, "planejamento");
			if ($plan !== false && $plan == 0) {
				$tipoProc = 'plan';
			}
			$acompTec = stripos($assunto, "acompanhamento tecnico");
			if ($acompTec !== false && $acompTec == 0) {
				$tipoProc = 'acompTec';
			}
			$acompTec = stripos($assunto, "acompanhamento t&eacute;cnico");
			if ($acompTec !== false && $acompTec == 0) {
				$tipoProc = 'acompTec';
			}

			
			if ($tipoProc != "") {
				print("Tipo do processo $tipoProc<br />");
				$sql = "UPDATE doc_processo SET tipoProc = '$tipoProc' WHERE id = '".$r['tipoID']."'";
				$bd->query($sql);
				print("<h1>Atualizado!</h1><br>");
			}
		}
		set_time_limit(0);
	}
	
?>
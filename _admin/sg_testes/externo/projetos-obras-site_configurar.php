<?php
	include_once '../includeAll.php';
	includeModule('sgo');
	
	//inicialização de variaveis
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	if(!isset($_POST['envio'])){
		$html = '<form action="projetos-obras-site_configurar.php" method="post">';
		
		$obras = $bd->query("SELECT id FROM obra_cad"); 
		foreach ($obras as $id) {
			$o = new Obra();
			$o->load($id['id'], true);
			
			if($o->visivel['bool'])
				$checked = 'checked="checked"';
			else
				$checked = '';

			$html .= '<input type="checkbox" name="'.$o->get('id').'" '.$checked.' value="1">'.$o->nome.' - '.$o->unOrg['compl'].'<br />';
		}
		
		$html .= '<input name="envio" type="hidden" value="'.count($obras).'" /><input type="submit"></form>';
		print $html;
	} else {
		
		for ($i = 1; $i <= $_POST['envio']; $i++){
			if(isset($_POST["$i"]))
				$v = '1';
			else 
				$v = '0';
			
			$ok = $bd->query("UPDATE obra_cad SET visivel = $v WHERE id = ".$i);
			print("ID $i ATUALIZADO<br />");
			if (!$ok) {
				print("ERRO DE BD.");
				exit();
			}
		}
		print("Dados atualizados com sucesso");
	}
	
	
?>


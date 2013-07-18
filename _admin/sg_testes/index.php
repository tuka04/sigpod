<?php
	/**
	 * @version 0.1 16/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina inicial do sistema
	 * @global $_SESSION
	 */
	include_once('includeAll.php');
	
	//$pessoa = new Pessoa();
	$html = new html($conf);	
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	$html->title .= "Portal de Acesso";
	$html->header = "SiGPOD";
	
	//verifica se o usuario esta logado, caso nao esteja redireciona para tela de login
	checkLogin(0);
	
	if(!isset($_SESSION['nomeCompl']) || !isset($_SESSION['area']) || !isset($_SESSION['id']) || !isset($_SESSION['perm']))
		exit();
	
	$html->user = $_SESSION["nomeCompl"];
		
	$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
	
	$html->path = showNavBar(array());
	
	if(isset($_GET['acao']) && $_GET['acao'] == 'desativa_novidades') {
		$_SESSION['novidades'] = false;
		print(json_encode(array(array('success' => true))));
		exit();
	}
	
	if (!isset($_GET['alert'])) {
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		if(!isset($_SESSION['novidades'])) $_SESSION['novidades'] = false;
		$news = new QuadroNovidades($_SESSION['username'],$_SESSION['novidades']);
		
		$html->content[1] = $news->geraHTML(); 
		$html->content[1] .= showDocsPend($_SESSION['id']);
		
		
		
		// notícias temporárias: qdo houver um sistema de notificações, este código deverá ser removido
		/*$data = mktime(14, 20, 0, 03, 28, 2012);
		$sql = "SELECT * FROM data_log WHERE username = '" .$_SESSION['username']. "' AND acao LIKE '%login%' AND data > $data ORDER BY data DESC LIMIT 1";
		$res = $bd->query($sql);
		if (count($res) <= 0) {
			$html->content[1] .= '<div id="news" title="Novidades SiGPOD"><center>Novidades do SiGPOD!</center><br /><br />Agora &eacute; na sua tela inicial, &eacute; poss&iacute;vel arrastar um documento sobre o outro para realizar a anexa&ccedil;&atilde;o!<br /><br /><center><img src="img/imgtosca.png" border="1"></center><br /><br />Agora &eacute; poss&iacute;vel programar suas f&eacute;rias para que o sistema avise outras pessoas antes de despachar um documento para voc&ecirc;. Clique em <b>Registro de F&eacute;rias/Licen&ccedil;a</b> sob o menu Pessoas e boas f&eacute;rias.<br /><br /><center><img src="img/imgtosca2.png" border="1"></center></div>';
			$html->content[1] .= '<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js"></script><script type="text/javascript">$(document).ready(function() { $("#news").dialog({ modal: true, width: 500, buttons: { "Ok!": function() { $(this).dialog(\'close\'); } } }); });</script>';
		}*/
		// fim notificiação
	} else {
		$html->content[1] = $_GET['alert'];
	}
	
	
	$html->campos['codPag'] = showCodTela();
	//solicitacao 004: inserindo a tabela de alertas em um dialog
	requireSubModule(array("frontend","alerta"));
	$html->content[1].=getDialog();
	//fim 004
	$html->showPage();
	$bd->disconnect();
?>
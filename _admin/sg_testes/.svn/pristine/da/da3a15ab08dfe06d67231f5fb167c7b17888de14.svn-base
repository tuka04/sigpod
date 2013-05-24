<?php
	/**
	 * @version 1.0 20/3/2012 
	 * @package geral
	 * @author Vitor Morelatti
	 * @desc pagina que gerencia pessoas dentro do sigpod 
	 */

	include_once('includeAll.php');
	includeModule('sgp');
	
	// verifica se o usuario esta logado
	checkLogin(6);
	
	// cria uma nova pagina HTML
	$html = new html($conf);
	// seta o texto de cabecalho da pagina
	$html->header = "Ger&ecirc;ncia de Pessoas";
	// gera o codigo de tela para esta pagina
	$html->campos['codPag'] = showCodTela();
	// completa o nome de usuario
	$html->user = $_SESSION['nomeCompl'];
	
	// inicia conexao com o banco de dados
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	// monta o menu
	$html->menu = showMenu($conf['template_menu'], $_SESSION["perm"], 2310, $bd);
	
	// tratamento de variaveis $_POST
	foreach ($_POST as $campo => $val) {
		$_POST[$campo] = SGEncode($val, ENT_QUOTES, null, false);
	}
	
	// verifica se há ação especificada
	if (isset($_GET['acao'])) {
		if ($_GET['acao'] == "ferias") {
			if (!checkPermission(71)) 
				showError(10);
			$html->path = showNavBar(array(array("url" => "","name" => "F&eacute;rias")));
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			if (!isset($_GET['usuario']))
				$html->content[1] = showFerias($_SESSION['id'],$bd);
			else {
				// $_GET['usuario'] pode estar setada e pode ser o mesmo usuario atual, entao... 
				if ($_GET['usuario'] == $_SESSION['id'])
					$html->content[1] = showFerias($_SESSION['id'],$bd);
				 // verifica se não tem permissao de acessar ferias de qq usuario
				elseif (!checkPermission(75)) {
					// no caso, ele ainda talvez tenha permissao de acessar ferias de seus subordinados
					if (isManager($_SESSION['id'], $_GET['usuario'], $bd) && checkPermission(73)) {
						// usuario atual é gerente do usuário passado por parâmetro e ele tem permissao
						// para realizar esta ação
						$html->content[1] = showFerias($_GET['usuario'],$bd);
					}
					else {
						// não tem nenhuma permissão, mostra erro
						showError(10);
					}
				}
				else {
					$html->content[1] = showFerias($_GET['usuario'],$bd);
				}
			}
		}
		if ($_GET['acao'] == "salvaFerias") {
			if (!checkPermission(71)) 
				showError(10);
			$html->path = showNavBar(array(array("url" => "","name" => "Gerenciamento de F&eacute;rias")));
			$html->content[1] = showSalvaFerias($_POST, $bd);
		}
		if ($_GET['acao'] == "delFeriasAjax") {
			if (!isset($_GET['id'])) {
				return json_encode(array(array('success' => false))); exit();
			}
			if (!checkPermission(72)) { 
				return json_encode(array(array('success' => false))); exit();
			}
			print json_encode(deletaFerias($_GET['id'], $bd));
			exit();
		}
		if ($_GET['acao'] == "checkDesp") {
			if (!isset($_GET['usuario'])) {
				return json_encode(array(array('success' => false))); exit();
			}
			print json_encode(checkDesp($_GET['usuario'], $bd));
			exit();
		}
		if ($_GET['acao'] == "equipes") {
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			$html->path = showNavBar(array(array("url" => "","name" => "Gerenciamento de Equipes")));
			if (!checkPermission(77)) { 
				showError(10);
			}
			$html->content[1] = showTimes($_SESSION['id'], $bd);
			//$html->content[1] = showTimes(2, $bd);
		}
		if ($_GET['acao'] == "allTeams") {
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			$html->path = showNavBar(array(array("url" => "","name" => "Gerenciamento de Equipes")));
			$html->content[1] = showTimes($_SESSION['id'], $bd, true);
			//var_dump($html);
		}
		if ($_GET['acao'] == "docsTime") {
			$html->path = showNavBar(array(array("url" => "","name" => "Gerenciamento de Equipes")));
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			require_once 'sgd_modules.php';
			//$html->content[1] = showDocsTime($_GET['usuario'], $bd);
			$html->content[1] = showDocsPend($_GET['usuario']);
		}
		if($_GET['acao'] == 'getProfileData') {
			includeModule('sgo');
			if(!isset($_POST['userID']) || (isset($_POST['userID']) && $_POST['userID'] <= 0)){
				print json_encode(array(array("success" => false, "errorFeedback" => "userID faltando ou invalido")));
				exit();
			}
			
			print showUserProfile($_POST['userID']);
			exit();
		}
	} else {
		// caso não haja ação especificada, retorna para a página inicial
		header("Location: index.php");
	}
	
	$html->showPage();
	$bd->disconnect();
?>
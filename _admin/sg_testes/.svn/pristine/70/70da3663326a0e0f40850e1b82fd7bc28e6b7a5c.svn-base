<?php
	/**
	 * @version 0.1 16/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc efetua login do usuario ou mostra a tela para entrar com usuario e senha
	 */
	include_once('includeAll.php');
	include_once('error.php');
	$html = new html($conf,$conf['template_login']);
	$pessoa = new Pessoa();
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	//tratamento de pasta
	$url = explode('/',$_SERVER['PHP_SELF']);
	$pasta = '/'; 
	for($i = 1 ; $i < count($url) - 1 ; $i++){
		$pasta .= $url[$i] . "/";
	}
	//monta o enderecador para a proxima pagina
	if(isset($_GET['redir']) && strpos('login.php',$_GET['redir']) !== false)
		$html->content[1] = '<input type="hidden" name="redir" value="'.$pasta.$_GET['redir'].'" />';
	else
		$html->content[1] = '<input type="hidden" name="redir" value="'.$pasta.'index.php" />';
		
	$html->content[2] = "";
	
	if(isset($_POST['username']) && isset($_POST['senha']) ){
		//verifica se o login e senha sao iguais
		if($pessoa->login($_POST['username'], $_POST['senha'],$bd)) {
			if (isset($_SESSION['ativo']) && ($_SESSION['ativo'] == false)) { // verifica se o usuario esta desativado
				$html->content[2] .= '<span class="header">Usu&aacute;rio desativado.</span>';
			}
			else { // se estiver ativado, redireciona
				header("Location: ".$_POST['redir']);
			}
		}
		else{ // login falhou (senha invalida ou usuario desativado)
			if (isset($_SESSION['ativo']) && ($_SESSION['ativo'] == false)) { // verifica se o usuario esta desativado
				$html->content[2] .= '<span class="header">Usu&aacute;rio desativado.</span>';
			}
			else { // senha invalida
				$html->content[2] .= '<span class="header">Usu&aacute;rio/senha inv&aacute;lidos.</span>';
				//checklogin(1);
			}
		}
	}
	
	$html->title .= "login";
	$html->campos['codPag'] = showCodTela();
	$html->showPage();
?>
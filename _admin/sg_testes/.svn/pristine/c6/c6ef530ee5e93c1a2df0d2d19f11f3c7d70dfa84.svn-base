<?php
	/**
	 * @version 0.1 - 16/07/2012 - autosave.php
	 * @author Vitor
	 * @desc Página que gerencia o auto-save de documentos
	 */

	include_once('includeAll.php');
	includeModule('sgo');

	checkLogin(0);
	
	// tratamento de varivaveis POST
	foreach ($_POST as $indice => $valor) {
		$_POST[$indice] = SGEncode($valor, ENT_QUOTES, null, false);
	}
	
	// se a variável ação não estiver setada, não faz nada
	if (!isset($_GET['acao']) || $_GET['acao'] == '') {
		exit();
	}
	
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	// salvar doc
	if ($_GET['acao'] == 'salvar') {
		// verificação de variaveis
		if (!isset($_POST['conteudo']) || $_POST['conteudo'] === "" || $_POST['conteudo'] === null || $_POST['conteudo'] === "undefined") {
			print json_encode(array(array('success' => false, 'feedback' => 'Dados Insuficientes: Conteudo.')));
			exit();
		}
		if (!isset($_POST['tipoAcao']) || $_POST['tipoAcao'] === "") {
			print json_encode(array(array('success' => false, 'feedback' => 'Dados Insuficientes: Acao.')));
			exit();
		}
		if (!isset($_POST['tipoDoc']) || $_POST['tipoDoc'] === "") {
			print json_encode(array(array('success' => false, 'feedback' => 'Dados Insuficientes: Tipo Doc')));
			exit();
		}
		if (!isset($_POST['urlVars']) || $_POST['urlVars'] === "") {
			print json_encode(array(array('success' => false, 'feedback' => 'Dados Insuficientes: Url Vars')));
			exit();
		}
		
		// verifica se o usuário já tem doc salvo
		$res = $bd->query("SELECT userID FROM doc_autosave WHERE userID = ".$_SESSION['id']);
		if (count($res) <= 0) { // se não tiver, insere nova entrada
			$sql  = "INSERT INTO doc_autosave (userID, acao, doc, data, content, urlVars) VALUES (";
			$sql .= $_SESSION['id'].", '".$_POST['tipoAcao']."', '".$_POST['tipoDoc']."', ".time().", '".$_POST['conteudo']."', '";
			$sql .= $_POST['urlVars']."')";
			
			$res = $bd->query($sql);
		}
		else { // se já tiver, atualiza
			$sql = "
			UPDATE doc_autosave	SET 
			userID = ".$_SESSION['id'].", content = '".$_POST['conteudo']."', acao = '".$_POST['tipoAcao']."', doc = '".$_POST['tipoDoc']."',
			data = ".time().", urlVars = '".$_POST['urlVars']."' WHERE userID = ".$_SESSION['id'];
			
			$res = $bd->query($sql);
		}
		
		// retorna
		print json_encode(array(array('success' => $res)));
		exit();
	}
	// verifica se usuário já tem doc salvo
	elseif ($_GET['acao'] == 'check') {
		$ret = getAutoSavedDoc(false);
		
		// retorna
		print json_encode(array($ret));
		exit();
	}
	// retorna documento salvo
	elseif ($_GET['acao'] == 'get') {
		$ret = getAutoSavedDoc(true);
		
		print json_encode(array($ret));
		exit();
	}
	// descarta documentos auto-salvados
	elseif ($_GET['acao'] == 'descartar') {
		$ret = descartaAutoSavedDoc('any');
		
		doLog($_SESSION['username'], 'Descartou documento criado pelo auto-save.');
		
		print json_encode(array(array("success" => $ret)));
		exit();
	}
?>
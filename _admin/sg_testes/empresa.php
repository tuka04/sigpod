<?php
	include_once 'includeAll.php';
	include_once 'empresa_modules.php';
	//session_start();
	
	checkLogin(6);
	
	$html = new html($conf);
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

	if(isset($_GET['acao'])){
		$acao = $_GET['acao']; 
	}else{
		$acao = "buscar";
	}
	if(isset($_GET['onclick'])){
		$onclick = $_GET['onclick']; 
	}else{
		$onclick = "buscar";
	}
	
	// tratamento de variaveis $_POST
	foreach ($_POST as $campo => $val) {
		//$_POST[$campo] = utf8_decode($val);
		//$_POST[$campo] = mb_convert_encoding($val, 'utf-8');
		//$_POST[$campo] = mb_check_encoding($val, 'utf-8,ISO-8859-1');
		
		/*if (mb_check_encoding($_POST[$campo], 'UTF-8')) {
			$_POST[$campo] = utf8_decode($val);
		}*/
		
		$_POST[$campo] = SGEncode($val, ENT_QUOTES, null, false);
	}
	
	//retorna os dados dos funcionarios para uma requisicao ajax
	if ($acao == 'getFuncAjax') {
		if (!isset($_GET['empresaID'])) {
			print json_encode(array(array("success" => false)));
			exit();
		}
		$empresa = new Empresa($bd);
		$empresa->load($_GET['empresaID']);
		
		$funcionarios = $empresa->getFuncionarios();
		//var_dump($funcionarios);
		//gera um select com todos os funcionarios de determinada empresa
		$select = utf8_encode(geraSelect('empresaFuncID1', $funcionarios));
		//var_dump($select);
		
		$template = Empresa::showFuncBusca();//le o template de funcionarios de uma empresa
		//se o usuario tem permissao para modificar um funcionario, os links sao montados
		if(checkPermission(97)) {
			$deactivateFuncLink = $template['deactivateFuncLink'];
			$editFuncLink = $template['editFuncLink'];
		} else {//senao, nao...
			$deactivateFuncLink = '';
			$editFuncLink = '';
		}
		//se o usuario tem permissao para cadastrar um funcionario, monta o link para faze-lo
		if(checkPermission(96)) {
			$addFunc_link = $template['addFunc_link'];
			$cadFuncForm = str_replace('{$empresaID}', $empresa->get('id'), $template['cadFuncForm']);
		} else {//senao nao 
			$addFunc_link = '';
			$cadFuncForm = '';
		}
		
		//monta a tabela de funcionarios
		$func_tr = '';
		$i = 0;
		foreach ($funcionarios as $f) {
			if($f['ativo'] == 1) $ativo = 'Sim';
			else $ativo = 'N&atilde;o';
			$row = str_replace(array('{$nome_func}', '{$ativo_func}', '{$deactivateFuncLink}', '{$editFuncLink}','{$nome_func_sem_html}'), array(SGEncode($f['label'],ENT_QUOTES), $ativo, $deactivateFuncLink, $editFuncLink, $f['label']), $template['func_tr']);
			$func_tr .= str_replace(array('{$i}', '{$crea_func}'), array($i, $f['value']), $row);
			$i++; 
		}
		
		if(count($funcionarios)){
			$funcHTML = str_replace('{$func_tr}', $func_tr, $template['func_table']);
		}else
			$funcHTML = str_replace('{$func_tr}', $template['nofunc_tr'], $template['func_table']);
		
		$funcHTML = str_replace(array('{$addFunc_link}', '{$cadFuncForm}', '{$total_func}'), array($addFunc_link, $cadFuncForm, count($funcionarios)), $funcHTML);
		
		print json_encode(array(array("success" => true, "funcHTML" => $funcHTML, "funcSelect" => $select, "empresaNome" => SGDecode($empresa->get('nome')))));
		exit();
	}
	elseif ($acao == 'cadFunc') {
		// verifica permissão de cadastro de funcionário
		if (!checkPermission(96)) {
			print json_encode(array(array("success" => false, "feedback" => "Sem permissao para cadastrar funcionario")));
			exit();
		}
		
		if (!isset($_POST['empresaID'])) {
			print json_encode(array(array("success" => false, "feedback" => "EmpresaID faltante.")));
			exit();
		}
		
		$_POST['nome'] = SGEncode(urldecode($_POST['nome']),ENT_QUOTES,null,false);
		
		$empresa = new Empresa($bd);
		$empresa->load($_POST['empresaID']);
		
		$template = Empresa::showFuncBusca();//carrega o formulario de busca
		
		if(checkPermission(97)) {//se tiver permissao, mostra os links de edicao
			$deactivateFuncLink = $template['deactivateFuncLink'];
			$editFuncLink = $template['editFuncLink'];
		} else {
			$deactivateFuncLink = '';
			$editFuncLink = '';
		}
		
		$ativo = 'Sim';
		$func_tr = str_replace(array('{$nome_func}','{$nome_func_sem_html}', '{$crea_func}', '{$ativo_func}', '{$deactivateFuncLink}', '{$editFuncLink}'), array(SGEncode($_POST['nome'],ENT_QUOTES,null, false), SGDecode($_POST['nome']) ,$_POST['crea'], $ativo, $deactivateFuncLink, $editFuncLink), $template['func_tr']);
		
		print json_encode(array(array_merge(($empresa->cadastraFuncionario($_POST)),array("func_tr"=>rawurlencode($func_tr)))));
		exit();
		
	} 
	elseif ($acao == 'cadEmpresa') {
		$html->header = "Ger&ecirc;ncia de Empresas";
		//gera o codigo de tela para esta pagina
		$html->campos['codPag'] = showCodTela();
		//completa o nome de usuario
		$html->user = $_SESSION['nomeCompl'];
		$html->setTemplate($conf['template']);
		$html->path = showNavBar(array(array("url" => "","name" => "Cadastrar Empresa")));
		$html->title .= "SGE : Cadastrar Empresa";
		//menu principal
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
		
		
		// verifica permissão de cadastro
		if (!checkPermission(94)) {
			if(isset($_GET['ajax']) && $_GET['ajax'] == 'false'){
				$html->content[1] = "Sem permiss&atilde;o para cadastrar empresa.";
				$html->showPage();
			} else
				print json_encode(array(array("success" => false)));
			exit();
		}
		
		$empresa = new Empresa($bd);
		foreach ($_POST as $campo => $val) {
			$empresa->set($campo, SGEncode(urldecode($val), ENT_QUOTES, null, false));
		}
		
		if ($empresa->save() == true) {
			if(isset($_GET['ajax']) && $_GET['ajax'] == 'false'){
				$html->content[1] = 'Empresa cadastrada com sucesso!<br /><br /><a href="index.php">Voltar para o in&iacute;cio</a><br /><a href="empresa.php?acao=cadEmpresaBig">Cadastrar outra empresa</a>';
				$html->showPage();
			} else
				print json_encode(array(array("success" => true, "id" => $empresa->get('id'))));
			exit();
		}
		else {
			if(isset($_GET['ajax']) && $_GET['ajax'] == 'false'){
				$html->content[1] = "Erro ao cadastrar empresa.";
				$html->showPage();
			} else
				print json_encode(array(array("success" => false)));
			exit();
		}
	}
/**/elseif($acao == "buscar"){
		$html->setTemplate($conf['template_mini']);
		
		$html->path = showNavBar(array(array("url" => "","name" => "Empresas"),array("url" => "","name" => "Buscar")));
		$html->title = "Buscar Empresa";
		$html->menu = '<script type="text/javascript">$(document).ready(function(){$("#c2").hide();$("#c3").hide();$("#c4").hide();$("#c5").hide();$(".boxLeft").css("width","0");$(".boxRight").css("width","100%");});</script>
					   <input type="hidden" id="onclick" value="'.$onclick.'" />';
		$html->content[1] = showBuscaEmprForm();
		$html->content[2] = '<center><input type="button" id="novaBusca" value="Buscar novamente" /></center>';
		$html->content[3] = '<div id="resBusca" width="100%"></div>';
		$html->content[4] = showFormCadEmpr();
		$html->campos['codPag'] = showCodTela();
		$html->showPage();
		
	}elseif ($acao == "doBusca"){//realiza a busca de uma empresa
		if(!isset($_POST['q']) || (isset($_POST['q']) && $_POST['q'] == '') ){
			print json_encode(array(array()));
			exit();
		}
		$q = explode(' ',$_POST['q']); //var_dump($_POST);
		
		$sql = "SELECT * FROM empresa WHERE ";
		
		foreach ($q as $str) {
			$sql .= "(nome LIKE '%".$str."%' OR cnpj LIKE '%".$str."%' OR endereco LIKE '%".$str."%' OR complemento LIKE '%".$str."%' OR cidade LIKE '%".$str."%' OR estado LIKE '%".$str."%' OR cep LIKE '%".$str."%' OR telefone LIKE '%".$str."%' OR email LIKE '%".$str."%') AND ";
		}
		
		$res = $bd->query(rtrim($sql, ' AND '));
		
		foreach ($res as $k => $r) {
			$res[$k]['html'] = '';
			foreach ($q as $str) {
				$res[$k]['nome'] = htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str), 'utf-8'), '<span style="background-color:yellow">'.$str.'</span>', mb_strtoupper(SGDecode($r['nome']), 'utf-8')),ENT_QUOTES,null,false)); 
				if(stripos($r['cnpj'], $str) !== false)        $res[$k]['html'] .= "<b>CNPJ</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['cnpj']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['endereco'], $str) !== false)    $res[$k]['html'] .= "<b>Endere&ccedil;o</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['endereco']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['complemento'], $str) !== false) $res[$k]['html'] .= "<b>Complemento</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['complemento']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['cidade'], $str) !== false)      $res[$k]['html'] .= "<b>Cidade</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['cidade']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['estado'], $str) !== false)      $res[$k]['html'] .= "<b>Estado</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['estado']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['cep'], $str) !== false)         $res[$k]['html'] .= "<b>CEP</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['cep']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['telefone'], $str) !== false)    $res[$k]['html'] .= "<b>Telefone</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['telefone']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['fax'], $str) !== false)		   $res[$k]['html'] .= "<b>Fax</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['fax']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
				if(stripos($r['email'], $str) !== false)       $res[$k]['html'] .= "<b>Email</b> : ".htmlspecialchars_decode(SGEncode(str_ireplace(mb_strtoupper(SGDecode($str),'utf-8'), '<span style="background-color:yellow">'.$str.'</span>',  mb_strtoupper(SGDecode($r['email']), 'utf-8')),ENT_QUOTES,null,false))."<br />";
			}
			$res[$k]['html'] = rtrim($res[$k]['html'], '<br />');
		}
		
		$ret['results'] = $res;
		$ret['total'] = count($res);
		//$res['perm']['verEmpresa'] = $_SESSION['perm'][];
		//$res['perm']['verFunc'] = $_SESSION['perm'][];
		$ret['perm']['editarEmpresa'] = $_SESSION['perm'][95];
		$ret['perm']['editarFunc'] = $_SESSION['perm'][96];
		
		
		print(json_encode($ret));
		
/**/}elseif ($acao == "cad"){
		if (!isset($_GET['data'])){
			print '0';
			exit();
		}
		$d = explode("|", $_GET['data']);
		
		/*for ($i = 0; $i < count($d); $i++) {
			$d[$i] = SGEncode($d[$i]);
		}*/
		
		$d[5] = str_replace("-", "", $d[5]);//tratamento cep
		$d[6] = str_replace(array("(",")","-"," "), array('','','',''), $d[6]);//tratamento telefone
		
		$sql = "INSERT INTO empresa (nome,endereco,complemento,cidade,estado,cep,telefone,email) VALUES (
				'".$d['0']."','".$d['1']."','".$d['2']."','".$d['3']."','".$d['4']."','".$d['5']."','".$d['6']."','".$d['7']."')";
		
		$res = $bd->query($sql);
		
		if ($res){
			$r = $bd->query("SELECT id FROM empresa WHERE nome = '".$d['0']."' AND endereco = '".$d['1']."' LIMIT 1");
			if ($r)
				print $r[0]['id'];
			else 
				print '0';
		}else{
			print '0';
		}
		//monta a tela de cadastro de empresa na pag principal
	} elseif($acao == 'cadEmpresaBig') {
		$html->head .= '<script type="text/javascript" src="scripts/empresa_big.js?r={$randNum}"></script>';
		$html->header = "Ger&ecirc;ncia de Empresas";
		//gera o codigo de tela para esta pagina
		$html->campos['codPag'] = showCodTela();
		//completa o nome de usuario
		$html->user = $_SESSION['nomeCompl'];
		$html->setTemplate($conf['template']);
		$html->path = showNavBar(array(array("url" => "","name" => "Cadastrar Empresa")));
		$html->title .= "SGE : Cadastrar Empresa";
		//menu principal
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
		
		$html->content[1] = str_replace('{$cad_form_campos}', Empresa::showCadCampos(), Empresa::showFormCadastro());
		
		$html->showPage();
	} elseif ($acao == 'cadFuncBig' && isset($_POST['emprID']) && $_POST['emprID'] > 0) {
		
		//monta a tela de busca inicial 
	} elseif ($acao == 'buscaEmpresa' || ($acao == 'cadFuncBig' && (!isset($_POST['emprID']) || $_POST['emprID'] <= 0))) {
		$html->head .= '<script type="text/javascript" src="scripts/empresa_busca.js?r={$randNum}"></script>';
		$html->header = "Ger&ecirc;ncia de Empresas";
		//gera o codigo de tela para esta pagina
		$html->campos['codPag'] = showCodTela();
		//completa o nome de usuario
		$html->user = $_SESSION['nomeCompl'];
		$html->setTemplate($conf['template']);
		$html->path = showNavBar(array(array("url" => "","name" => "Buscar Empresa")));
		$html->title .= "SGE : Cadastrar Empresa";
		//menu principal
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
		
		$html->content[1] = str_replace(array('{$empresa_detalhes}','{$empresa_cad_form}'), array(Empresa::showDetEmpresa(), Empresa::showCadCampos()), Empresa::showFormBusca());
		
		$html->showPage();
	} elseif($acao == 'ativarFunc'){// faz o "toggle" do atributo ativo do usuario
		if(!isset($_POST['funcCrea']) || (isset($_POST['funcCrea']) && $_POST['funcCrea'] < 0)){
			print json_encode(array(array("success" => false, 'errorFeedback' => 'ID do funcionario invalido ou nao informado.')));
			exit();
		}
		
		print(json_encode(array(Empresa::ativaFuncionario($_POST['funcCrea']))));
		exit();
		
	} elseif ($acao == 'editFunc') {//edita o nome do funcionado de determinado crea passado por parametro
		if(!isset($_POST['funcCrea']) || (isset($_POST['funcCrea']) && $_POST['funcCrea'] < 0)){
			print json_encode(array(array("success" => false, 'errorFeedback' => 'ID do funcionario invalido ou nao informado.')));
			exit();
		}
		
		print(json_encode(array(Empresa::editaFuncionario($_POST['funcCrea'],SGEncode(urldecode($_POST['nome']),ENT_QUOTES,null,false)))));
		exit();
	} elseif ($acao == 'getEmprDet'){//retorna os detalhes da empresa (html entity'ed e raw) para uma requisicao AJAX
		if(!isset($_POST['empresaID']) || (isset($_POST['empresaID']) && $_POST['empresaID'] < 0)){
			print json_encode(array(array("success" => false, 'errorFeedback' => 'ID da empresa invalido ou nao informado.')));
			exit();
		}
		$empresa = new Empresa($bd);
		$empresa->load($_POST['empresaID']);
		
		$empresaArray = array(
			'nome' => $empresa->get('nome'),
			'cnpj' => $empresa->get('cnpj'),
			'endereco' => $empresa->get('endereco'),
			'complemento' => $empresa->get('complemento'),
			'cidade' => $empresa->get('cidade'),
			'estado' => $empresa->get('estado'),
			'cep' => $empresa->get('cep'),
			'telefone' => $empresa->get('telefone'),
			'fax' => $empresa->get('fax'),
			'email' => $empresa->get('email'),
			'nome_form' => rawurlencode(SGDecode($empresa->get('nome'),ENT_QUOTES,'iso-8859-1')),
			'endereco_form' => rawurlencode(SGDecode($empresa->get('endereco'),ENT_QUOTES,'iso-8859-1')),
			'complemento_form' => rawurlencode(SGDecode($empresa->get('complemento'),ENT_QUOTES,'iso-8859-1')),
			'cidade_form' => rawurlencode(SGDecode($empresa->get('cidade'),ENT_QUOTES,'iso-8859-1')),
			'estado_form' => rawurlencode(SGDecode($empresa->get('estado'),ENT_QUOTES,'iso-8859-1')),
			'cep_form' => rawurlencode(SGDecode($empresa->get('cep'),ENT_QUOTES,'iso-8859-1')),
			'telefone_form' => rawurlencode(SGDecode($empresa->get('telefone'),ENT_QUOTES,'iso-8859-1')),
			'fax_form' => rawurlencode(SGDecode($empresa->get('fax'),ENT_QUOTES,'iso-8859-1')),
			'email_form' => rawurlencode(SGDecode($empresa->get('email'),ENT_QUOTES,'iso-8859-1'))
		);
		
		print json_encode(array($empresaArray));
		exit();
	} elseif ($acao == 'saveEmpresa') {// modulo para edicao de empresa
		//ID da empresa a ser editada deve ser informado
		if(!isset($_POST['empresaID']) || (isset($_POST['empresaID']) && $_POST['empresaID'] < 0)){
			print json_encode(array(array("success" => false, 'errorFeedback' => 'ID da empresa invalido ou nao informado.')));
			exit();
		}
		//todos os atributos da empresa também devem ser passados
		if(!isset($_POST['nome']) || !isset($_POST['endereco']) || !isset($_POST['complemento']) || !isset($_POST['cidade']) || !isset($_POST['estado']) || !isset($_POST['cep']) || !isset($_POST['telefone']) || !isset($_POST['email'])){
			print json_encode(array(array("success" => false, 'errorFeedback' => 'Faltam dados!')));
			exit();
		}
		//inicializa nova instancia de empresa
		$empresa = new Empresa($bd);
		$empresa->load($_POST['empresaID']);
		//seta os atributos da empresa
		$empresa->set('nome', SGEncode(urldecode($_POST['nome'])),ENT_QUOTES,null,false);
		$empresa->set('endereco', SGEncode(urldecode($_POST['endereco'])),ENT_QUOTES,null,false);
		$empresa->set('complemento', SGEncode(urldecode($_POST['complemento'])),ENT_QUOTES,null,false);
		$empresa->set('cidade', SGEncode(urldecode($_POST['cidade'])),ENT_QUOTES,null,false);
		$empresa->set('estado', SGEncode(urldecode($_POST['estado'])),ENT_QUOTES,null,false);
		$empresa->set('cep', SGEncode(urldecode($_POST['cep'])),ENT_QUOTES,null,false);
		$empresa->set('telefone', SGEncode(urldecode($_POST['telefone'])),ENT_QUOTES,null,false);
		$empresa->set('email', SGEncode(urldecode($_POST['email'])),ENT_QUOTES,null,false);
		$empresa->set('fax', SGEncode(urldecode($_POST['fax'])),ENT_QUOTES,null,false);
		
		print json_encode(array(array('success' => $empresa->save(), 'errorFeedback' => "Erro ao salvar")));
		exit();
	}
	
	
?>
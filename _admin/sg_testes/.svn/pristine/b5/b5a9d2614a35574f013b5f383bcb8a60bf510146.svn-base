<?php
	/**
	 * @version 0.0 20/4/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina com conteudos de ajuda
	 */

	include_once('includeAll.php');
	include_once('sgd_modules.php');
	
	//inicia conexao com o banco de dados
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	//cria uma nova pagina HTML
	$html = new html($conf);
	//seta o texto de cabecalho da pagina
	$html->header = "Conte&uacute;dos da ajuda";
	//gera o codigo de tela para esta pagina, titulo, nome de usuario, caminho e menu
	$html->setTemplate($conf["template"]);
	$html->title .= "Reportar Erro";
	$html->path = showNavBar(array(array("url" => "","name" => "Ajuda")));
	$html->campos['codPag'] = showCodTela();
	
	if(isset($_SESSION['username'])){
		$html->user = $_SESSION['nomeCompl'];
	} else {
		$html->user = "visitante";
	}
	
	
	if(isset($_SESSION["perm"])){
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
	} else {
		//menu sem estar logado? formulario para login?
	}
	
	
	
	if(isset($_GET['acao']) && $_GET['acao'] == 'geraMini'){
		//gera o conteudo da barra de ajuda para a janela
		
		//conteudos
		$texto['login']['titulo'] = "Login";
		$texto['login']['cont'] = 'Para utilizar o portal, voc&ecirc; dever&aacute; estar identificado no sistema. Para se identificar, digite seu login e senha nos campos identificados.<br />
			<b>Login:</b> &Eacute; o mesmo utilizado para se identificar nos computadores dodepartamento.<br />
			<b>Senha:</b> A mesma utlizada nos computadores.<br />
			<br />
			<big><span style="font-weight: bold;">Perguntas Frequentes:</span></big><br />
			<br />
			<span style="font-weight: bold;">Eu n&atilde;o tenho um login e senha. O que devo fazer?</span><br />
			Solicite para o administrador de sistemas cadastrar seus dados na rede da CPO.<br />
			<br />
			<span style="font-weight: bold;">Esqueci meu login ou senha. O que fazer?</span><br />
			Solicite o cadastramento de outra senha para o administrador de sistemas.<br />';
		
		$texto['IND.00']['titulo'] = '&Iacute;ndice';
		$texto['IND.00']['cont'] = '<h3>Bem-vindo ao Sistema de Ger&ecirc;ncia</h3>
			<b>Por onde come&ccedil;ar?</b><br />
			1. Selecione uma op&ccedil;&atilde;o no menu &agrave; esquerda de acordo com a a&ccedil;&atilde;o que deseja executar<br />
			2. Conforme documentos forem despachados para voc&ecirc; a tabela aqui ao lado ser&aacute; preenchida com documentos sob sua guarda. N&atilde;o esque&ccedil;a de despach&aacute;-los no sistema tamb&eacute;m.
			<br /><br />
			<b>Encontrou algum erro ou tem alguma sugest&atilde;o?</b><br />
			Clique <a href="report_bug.php">aqui</a> e nos comunique!';
		
		$texto['SGD.CD']['titulo'] = 'Cadastro de Documento';
		$texto['SGD.CD']['cont'] = 'Para cadastrar um documento, preencha os campos de cadastro b&aacute;sico de documentos e clique &quot;Consultar&quot;. Uma janela para preenchimento de outros campos aparecer&aacute;.<br />
			Caso o documento j&aacute; esteja cadastrado do sistema, seus dados ser&atilde;o carregados automaticamente. Se n&atilde;o, ser&aacute; necess&aacute;rio o cadastro completo desse documento.<br /><br />
			<i>Lembre-se: Os campos marcados com * s&atilde;o obrigat&oacute;rios.</i><br /><br />
			<b>Despachando documentos</b><br /><br />
			Para despachar um documento no sistema, preencha o campo &quot;Despacho&quot; e selecione uma op&ccedil;&atilde;o no campo &quot;Despachar para&quot; selecionando uma pessoa ou digitando o destinat&aacute;rio se necess&aacute;rio.<br /><br />
			Por fim, aperte &quot;Enviar&quot; para cadastrar e registrar o despacho.';
		
		$texto['SGD.NV']['titulo'] = 'Novo Documento';
		$texto['SGD.NV']['cont'] = 'Para criar um documento, preencha os campos de cadastro ao lado. Recomenda-se o preenchimento total do formul&aacute;rio.<br /><br />
			<i>Lembre-se: Os campos marcados com * s&atilde;o obrigat&oacute;rios.</i><br /><br />
			<b>Despachando documentos</b><br /><br />
			Para despachar um documento no sistema, preencha o campo &quot;Despacho&quot; e selecione uma op&ccedil;&atilde;o no campo &quot;Despachar para&quot; selecionando uma pessoa ou digitando o destinat&aacute;rio se necess&aacute;rio.<br /><br />
			Por fim, aperte &quot;Enviar&quot; para cadastrar e registrar o despacho.<br /><br />
			<b>Perguntas Frequentes</b><br /><br />
			<b>Como usar o editor de texto?</b><br />
			O uso &eacute; parecido com o Word bastando clicar no &iacute;cone correspondente &agrave; a&ccedil;&atilde;o desejada<br />
			<i>Dica: Use os atalhos para ajudar na edi&ccedil;&atilde;o:<br />
			ctrl + B: Negrito<br />
			ctrl + I: It&aacute;lico<br />
			ctrl + U: Sublinhado<br />
			ctrl + Z: Desfazer<br />
			ctrl + Y: Refazer<br />
			ctrl + A: Selecionar Tudo</i>';
		
		$texto['SGD.BU']['titulo'] = 'Busca de Documento';
		$texto['SGD.BU']['cont'] = 'Para buscar um documento:<br />
			1. Selecione o tipo de documento a ser buscado.<br />
			2. Preencha os campos com as informa&ccedil;&otilde;es do documento.<br />
			3. Clique em "Buscar"<br /><br />
			<i>Nota: N&atilde;o preencher um campo far&aacute; com que o sistema o desconsidere na hora de efetuar a busca.</i><br /><br />
			<i>Dica: Para buscar todos os documentos de um determinado tipo, basta n&atilde;o preencher nenhum campo.</i><br /><br />
			<i><b>Aviso:</b> Efetuar a busca utilizando um crit&eacute;rio muito abrangente (ex: todas os documentos) pode causar lentid&atilde;o no sistema devido &agrave; grande quantidade de de resultados.</i>';
		
		
		if (isset($_GET['secao']) && isset($texto[$_GET['secao']]['titulo']))
			$titulo = $texto[$_GET['secao']]['titulo'];
		else
			$titulo = '';
		
		//gera a barra horizontal superior
		print '
		<div style="background-color: #BE1010; color: white">
		<table width="100%">
		<tr><td style="text-align: left;">
			<b>Ajuda - '.$titulo.'</b>
		</td><td style="text-align: right; width: 60px">
			<a onclick="fechaAjuda()" style="color: white">Fechar <b>[x]</b></a>
		</td></tr>
		</table>
		</div>';
		
		//nao ha conteudo para essa secao
		if(!isset($_GET['secao']) || !isset($texto[$_GET['secao']])){
			print '<div style="padding:5px">'.'<p align="justify">N&atilde;o foi possivel encontrar um conte&uacute;do relevante para essa p&aacute;gina.</p>
			<p align="justify">Tente solucionar seu problema nos <a href="ajuda.php">conte&uacute;dos de ajuda</a> ou contate o administrador do sistema.</p>'
			.'</div>';
			exit();
		}
		
		$topicos = '<p><b>Mais ajuda</b></p><p align="justify">Se o conte&uacute;do acima n&atilde;o resolveu seu problema, tente procurar uma solu&ccedil;&atilde;o nos <a href="ajuda.php">conte&uacute;dos de ajuda</a> ou contate o administrador do sistema.</p>';
		
		print '<div style="padding:5px; text-align: justify">'.$texto[$_GET['secao']]['cont'].$topicos.'</div>';
		
		exit();
	} else {
		$html->content[1] = '<h3>Problemas comuns</h3>
		<b>Problema:</b> Obtenho um erro ao efetuar login mesmo j&aacute; tendo verificado meu nome de usu&aacute;rio e senha.<br />
		<b>Solu&ccedil;&atilde;o:</b> Talvez seu nome de usu&aacute;rio n&atilde;o esteja atualizado ainda no sistema. Por favor, contate o administrador do sistema.<br /><br />
		<b>Problema:</b> Obtenho um erro de PHP ou Warning ao navegar pelo sistema.<br />
		<b>Solu&ccedil;&atilde;o:</b> &Eacute; necess&aacute;rio reparar o sistema. Por favor, preencha o formul&aacute;rio para reportar este erro. Se poss&iacute;vel, copie o erro obtido e cole no formul&aacute;rio.
		';
	}
	
	$html->showPage();
?>
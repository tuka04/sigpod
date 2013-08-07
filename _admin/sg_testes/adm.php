<?php
	/**
	 * @version 0.1 18/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina que lida com os modulos de gerenciamento de documentos 
	 */

	include_once('includeAll.php');
	//include_once('adm_modules.php');
	//include_once('adm_conections.php');
	if (!isset($_SESSION)) session_start();
	//verifica se o usuario esta logado
	checkLogin(6);
	//verifica se o usuario tem permissao para administrar o sistema
	checkPermission(20);
	//declara inicio de uma nova pagina HTML
	$html = new html($conf);
	//abre conexao com BD
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	//seta titulo, cabecalho, menu, cod pagina e nome do usuario no template padrao
	$html->title .= "Administra&ccedil;&atilde;o do Sistema";
	$html->header = "Administra&ccedil;&atilde;o SiGPOD";
	$html->user = $_SESSION['nomeCompl'];
	$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],1,$bd);
	$html->campos['codPag'] = showCodTela();
	
	//solicitacao 004
	if(isset($_REQUEST['getDatasAlerta'])&&$_REQUEST['getDatasAlerta']==true){
		requireSubModule("alerta");
		$s = new SysAlerta();
		echo json_encode(array("tabela"=>$s->toHtmlTable()));
		return;
	}
	else if(isset($_REQUEST['saveSysAlerta'])&&$_REQUEST['saveSysAlerta']==true){
		requireSubModule("alerta");
		//todos sao removiveis
		$_REQUEST["removable"]=1;
		$ini=intval($_REQUEST["ini"]);
		$s = new SysAlerta();
		$r = $s->insert(array(NULL,$_REQUEST["ini"],$_REQUEST["removable"]));
		echo json_encode(array("success"=>true,"msg"=>"Alerta inserido com sucesso.","id"=>$r));
		return;
	}
	else if(isset($_REQUEST["removeSysAlerta"])&&$_REQUEST["removeSysAlerta"]==true){
		requireSubModule("alerta");
		$s = new SysAlerta();
		$r = $s->remove("id",explode(",",$_REQUEST["id"])," IN ");
		echo json_encode(array("msg"=>$r));
		return;
	}
	
	//o encadeamento de condicoes abaixo redirecionam o fluxo para a secao sendo visitada.
	//ao chegar na cadeia correta, montam-se o caminho e chama-se a funcao para gerar o conteudo da pagina
	if (isset($_GET['area'])) {
		/*DOCUMENTOS*/	
		if ($_GET['area'] == 'dc'){
			
/*EDIT DOC*/if (isset($_GET['acao']) && isset($_GET['tipoDoc']) && $_GET['acao'] == 'edit') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => '', 'name' => 'Editar tipo de Documento')));
				$html->content[1] = showEditDocForm($bd,$_GET['tipoDoc']);
			
/*NOVO DOC*/} elseif (isset($_GET['acao']) && $_GET['acao'] == 'novo') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Novo tipo de Documento")));
				$html->content[1] = showEditDocForm($bd);
				
/*EXCL DOC*/} elseif (isset($_GET['acao']) && isset($_GET['tipoDoc']) && $_GET['acao'] == 'excl') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Excluir Documento")));
				$html->content[1] = excluiDoc($bd);
				
/*SALV DOC*/} elseif (isset($_GET['acao']) && $_GET['acao'] == 'salvar') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Salvar tipo de Documento")));
				$html->content[1] = salvaDoc($bd);
/*EDITANEX*/} elseif (isset($_GET['acao']) && $_GET['acao'] == 'anex') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Gerenciar Anexos")));
				$html->content[1] = showPermAnex($bd);
			} elseif (isset($_GET['acao']) && $_GET['acao'] == 'salvarAnex') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Salvar permiss&atilde;o de anexos")));
				$html->content[1] = salvaPermAnexos($bd);
/*ATRBOBRA*/} elseif (isset($_GET['acao']) && $_GET['acao'] == 'atribObra') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Gerenciar Atribui&ccedil;&atilde;o a Obras")));
				$html->content[1] = showDocAtribObra($bd);
/*SALVAATR*/}
			elseif (isset($_GET['acao']) && $_GET['acao'] == 'salvaAtr') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dc&amp;acao=geren","name" => "Gerenciar Documentos"),array("url" => "","name" => "Salvar Atribui&ccedil;&atilde;o a Obras")));
				$html->content[1] = salvaDocAtribObra($_POST, $bd);
/*GERENCIAR*/}
			else {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "","name" => "Gerenciar Documentos")));
				$html->content[1] = showTiposDocs($bd);	
			}

		/*GRUPOS*/	
	} elseif ($_GET['area'] == 'gr'){
		if(isset($_GET['acao'])){
/*EDIT GRU*/if ($_GET['acao'] == 'edit' && isset($_GET['id'])) {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Editar grupo')));
				$html->content[1] = showEditGrupoForm($bd,$_GET['id']);
				
/*NOVO GRU*/} elseif ($_GET['acao'] == 'novo') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Novo grupo')));
				$html->content[1] = showEditGrupoForm($bd);
				
/*EXCL GRU*/} elseif (isset($_GET['id']) && $_GET['acao'] == 'excl') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Excluir grupo')));
				$html->content[1] = excluiGrupo($bd);
					
/*SALV GRU*/} elseif ($_GET['acao'] == 'salvar') {
					$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Salvar dados de grupo')));
					$html->content[1] = salvaGrupo($bd);
			} elseif ($_GET['acao'] == 'updtUsers') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Atualizar dados de usu&aacute;rios')));
				$html->content[1] = atualizaUsuarios($bd);
			} elseif ($_GET['acao'] == 'setResponsaveis') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Atualizar Respons&aacute;veis pela CPO')));
				$html->content[1] = setarResponsaveis($bd);
			} elseif ($_GET['acao'] == 'salvaResponsaveis') {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=gr","name" => "Gerenciar Grupos"),array("url" => '', 'name' => 'Atualizar Respons&aacute;veis pela CPO')));
				$html->content[1] = salvarResponsaveis($_POST, $bd);
			}
 			
/*GERE*/}else {
			$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "","name" => "Gerenciar Grupos")));
			$html->content[1] = showGrupos($bd);
		}
			
		/*UNIDADES E ORGAOS*/	
		} elseif ($_GET['area'] == 'un'){
 /*ATUALIZ*/if ($_GET['acao'] == 'atual'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "","name" => "Atualizar Tabela Unidades/&Oacute;rg&atilde;os")));
				$html->content[1] = updateUnidades($bd);
 			}
						
		/*PERMISSOES*/
		} elseif ($_GET['area'] == 'pe'){
 /*GERENCI*/if ($_GET['acao'] == 'geren'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "","name" => "Gerenciar Permiss&otilde;es")));
				$html->content[1] = showPermGroups($bd);
				
 /*SALVAR */} elseif ($_GET['acao'] == 'salvar'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=pe&amp;acao=geren","name" => "Gerenciar Permiss&otilde;es"),array("url" => "","name" => "Salvar Permiss&otilde;es")));
				$html->content[1] = salvaPermissoes($bd);
			}
			else if($_REQUEST['acao']=='gerenAlerta'){//solicitacao 004
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "","name" => "Gerenciar Alertas")));
				$html->content[1] = gerenciarAlertas();
			}

		/*EMPRESAS*/
		} elseif ($_GET['area'] == 'em'){				
/*ADD EMPR*/if (isset($_GET['acao']) && $_GET['acao'] == 'nova'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=em","name" => "Gerenciar Empresas"),array("url" => "","name" => "Nova Empresa")));
				$html->content[1] = showEditEmprForm($bd);
				
/*EDIT EMP*/} elseif(isset($_GET['acao']) && $_GET['acao'] == 'editar' && isset($_GET['id'])){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=em","name" => "Gerenciar Empresas"),array("url" => "","name" => "Editar Empresa")));
				$html->content[1] = showEditEmprForm($bd,$_GET['id']);
				
/*SALV EMP*/} elseif(isset($_GET['acao']) && $_GET['acao'] == 'salvar'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=em","name" => "Gerenciar Empresas"),array("url" => "","name" => "Salvar dados")));
				$html->content[1] = salvaEmpr($bd);
				
/*EXCL EMP*/} elseif(isset($_GET['acao']) && $_GET['acao'] == 'excl'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=em","name" => "Gerenciar Empresas"),array("url" => "","name" => "Salvar dados")));
				$html->content[1] = excluiEmpr($bd);
/*GERENC*/	} else {
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=em","name" => "Gerenciar Empresas"),array("url" => "","name" => "Gerenciar Empresas")));
				$html->content[1] = showEmpr($bd);
			}
/*DATA*/} elseif ($_GET['area'] == 'dt') {
			if(isset($_GET['acao']) && $_GET['acao'] == 'setFeriados'){
				$html->path = showNavBar(array(array("url" => "adm.php","name" => "Administra&ccedil;&atilde;o do Sistema"),array("url" => "adm.php?area=dt&amp;acao=setFeriados","name" => "Gerenciar Datas"),array("url" => "","name" => "Definir Feriados")));
				$html->content[1] = showPickFeriados($bd);
			}
			if (isset($_GET['acao']) && $_GET['acao'] == 'toggleFeriado') {
				print toggleFeriado($_GET['data'], false, $bd);
				exit();
			}
/*INDX*/} else{
			$html->path = showNavBar(array(array("url" => "","name" => "Administra&ccedil;&atilde;o do Sistema")));
			$html->content[1] = geraIndiceAdm();
		}
/**/} else {
		$html->path = showNavBar(array(array("url" => "","name" => "Administra&ccedil;&atilde;o do Sistema")));
		$html->content[1] = geraIndiceAdm();
	}
		
	$html->showPage();
	
	/**
	 * gera menu principal com os links para administrar
	 * @return string codigo HTML da pagina
	 */
	function geraIndiceAdm(){
		// adicionar: adm.php?area=dc&amp;acao=nov
		$html = '
		<script type="text/javascript" src="scripts/jquery.tools.min.js?r={$randNum}"></script>
		<h3>Administrar Documentos</h3>
		&nbsp;&nbsp;<a href="adm.php?area=dc" title="Funcionando (em tese)">Gerenciar tipos de documentos</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=dc&amp;acao=novo" title="Modificado para &uacute;ltimas altera&ccedil;&otilde;es de doc. Por&eacute;m, necessita testes mais profundos. Use por conta e risco. :P">Adicionar novo tipo de documento</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=dc&amp;acao=anex">Gerenciar anexa&ccedil;&otilde;es</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=dc&amp;acao=atribObra">Gerenciar atribui&ccedil;&otilde;es a Obras</a><br />
		<h3>Administrar Grupos</h3>
		&nbsp;&nbsp;<a href="adm.php?area=gr">Gerenciar grupos</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=gr&amp;acao=novo">Adicionar novo grupo</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=gr&amp;acao=updtUsers">Sincronizar dados de usu&aacute;rios do SiGPOD com dados do Active Directory</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=gr&amp;acao=setResponsaveis">Definir Respons&aacute;veis na CPO</a><br />
		<h3>Administrar Unidades/&Oacute;rg&atilde;os</h3>
		&nbsp;&nbsp;<a href="adm.php?area=un&amp;acao=atual">Atualizar lista de Unidades/&Oacute;rg&atilde;os</a><br />
		<h3>Administrar Permiss&otilde;es</h3>
		&nbsp;&nbsp;<a href="adm.php?area=pe&amp;acao=geren">Gerenciar Permiss&otilde;es</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=pe&amp;acao=gerenAlerta">Gerenciar Alertas</a><br />
		<h3>Administrar Empresas</h3>
		&nbsp;&nbsp;<a href="adm.php?area=em&amp;acao=geren">Gerenciar empresas</a><br />
		&nbsp;&nbsp;<a href="adm.php?area=em&amp;acao=addnv">Adicionar nova empresa</a><br />
		<h3>Administrar Datas</h3>
		&nbsp;&nbsp;<a href="adm.php?area=dt&amp;acao=setFeriados">Definir Feriados do ano atual</a><br />
		<script type="text/javascript">$(document).ready(function() {
		$("a[title]").tooltip({ offset: [-10, 2], effect: "slide", delay: 0 }).dynamic({ bottom: { direction: "down", bounce: true } });});</script>';

		return $html;
	}
	
	/**
	 * gera tabela com os tipos de documentos existentes
	 * @param mysql_link $bd
	 */
	function showTiposDocs($bd){
		$html = '<h3>Tipos de documento</h3>
		<table width="500">
		<tbody>
		<tr><td><b>Nome</b></td><td></td><td></td></tr>';
		//consulta os tipos de documentos cadastrados
		$docs = $bd->query("SELECT nome,nomeAbrv,cadAcaoID,novoAcaoID FROM label_doc");
		//para cada documento consultado
		foreach ($docs as $d) {
			//gera codigo HTML da tabela
			$html .= '<tr class="c">
				<td>'.$d['nome'].'</td>
				<td><a href="adm.php?area=dc&amp;acao=edit&amp;tipoDoc='.$d['nomeAbrv'].'">Editar</a></td>';
			//se o doc tiver sido excluido, nao monta o link para exclusao
			//if(!$d['cadAcaoID'] && !$d['novoAcaoID'])
			if($d['cadAcaoID'] < 0 && $d['novoAcaoID'] < 0)
				$html .= '<td>Excluido</td>';
			else
				$html .= '<td><a href="adm.php?area=dc&amp;acao=excl&amp;tipoDoc='.$d['nomeAbrv'].'">Excluir</a></td>';
			$html .= '</tr>';
		}
		//fecha tags de tabela
		$html .= '</tbody>
		</table>
		';
		
		return $html;
	}
	
	/**
	 * Gera o formulario para edicao do tipo de documento
	 * @param mysql_link $bd
	 * @param string $tipo
	 */
	function showEditDocForm($bd,$tipo = null){
		global $conf;
		//se tipo eh null, entao deve ser gerado formulario em branco para cadastro de novo tipo de documento
		if ($tipo == null){
			//inicializa as variaveis em vazias para cadastro de novo documento
			$res = array('nome' => '', 'template' => '', 'numeroComp' => '');
			$anex = '';
			$campos = array();
			$campoHTML = '';			
			$campoInput = '<input type="hidden" name="campos" id="campos" value="" />';
			$nomeAbrv = '<input type="text" name="nomeAbrv" size="5" maxlength="5" value="" />';
			$cad = '';
			$novo = '';
			$anex = '';
			$anexDoc = '';
			$anexObr = '';
			$anexEmp = '';
		//se o tipo de cadastro foi passado, entao gera o formulario com os dados atuais do documento
		} else {
			//consulta os dados do documento no BD
			$res = $bd->query("SELECT * FROM label_doc WHERE nomeAbrv = '".$tipo."'");
			//se ha resultados
			if(count($res) == 1){
				//seleciona o primeiro e unico
				$res = $res[0];
			} else {
				//retorna erro.
				return "Erro. Tipo n&atilde;o v&aacute;lido.";
			}
			//inicializacao de variaveis
			$cad = '';
			$novo = '';
			$anex = '';
			$anexDoc = '';
			$anexObr = '';
			$anexEmp = '';
			$resposta = '';
			$nomeAbrv = '<input type="text" size="5" maxlength="5" value="'.$res['nomeAbrv'].'" disabled="disabled" />'.'<input type="hidden" name="nomeAbrv" value="'.$res['nomeAbrv'].'" />';
			//carrega o conteudo do aquivo de modelo do documento
			if($res['template'] != ''){
				$res['template'] = file_get_contents('templates/'.$res['template']);
			}
			//checa o campo de cadastravel se o documento eh cadastravel
			if($res['cadAcaoID'] != 0) {
				$cad = 'checked="checked"';
			}
			//checa o campo de criavel se o documento eh criavel
			if($res['novoAcaoID'] != 0) {
				$novo = 'checked="checked"';
			}
			//checa o campo de anexavel se ha a acao de anexar
			if(strpos($res['acoes'], "13") !== false){
				$anex = 'checked="checked"';
			}
			//checa o campo de possivel anexar documentos
			if($res['docAnexo'] == 1){
				$anexDoc = 'checked="checked"';
			}
			if($res['docResp'] == 1) {
				$resposta = 'checked="checked"';
			}
			//checa o campo de possivel anexar obras
			if($res['obra']){
				$anexObr = 'checked="checked"';
			}
			//checa o campo de possivel anexar empresa
			if($res['empresa']){
				$anexEmp = 'checked="checked"';
			}
			
			//campos
			$campoInput = '<input type="hidden" name="campos" id="campos" value="'.$res['campos'].'," />';
			$campoHTML = '';
			$campos = explode(',',$res['campos']);
			//para cada campo adicionado ao documento
			foreach ($campos as $c) {
				$emi = '';
				$emiPrin = '';
				$cBusca = '';
				$cIndice = '';
				//verifica se o campo consta como emitente
				if ($res['emitente'] != ''    && strpos($res['emitente'], $c) !== false) {
					$emi = 'checked="checked"';
				}
				//verifica se eh o primeiro emitente (que vai aparecer na busca/doc pendentes)
				if ($res['emitente'] != ''    && strpos($res['emitente'], $c) === 0) {
					$emiPrin = 'checked="checked"';
				}
				//verifica se o campo eh de busca
				if ($res['campoBusca'] != ''  && strpos($res['campoBusca'], $c) !== false) {
					$cBusca = 'checked="checked"';
				}
				//verifica se o campo eh indice
				if ($res['campoIndice'] != '' && strpos($res['campoIndice'], $c) !== false) {
					$cIndice = 'checked="checked"';
				}
				//seleciona os dados do campo
				$campoData = $bd->query("SELECT * FROM label_campo WHERE nome = '$c'");
				$campoData = $campoData[0];
				//gera uma linha da tabela com os dados lidos
				$campoHTML .= '<tr id="'.$campoData['nome'].'Det" class="c">
								<td class="c"><b>'.$campoData['label'].'</b></td>
								<td class="c" style="text-align: center;">'.$campoData['tipo'].'</td>
								<td class="c" style="text-align: center;"> <input type="checkbox" name="'.$campoData['nome'].'_emi" value="1" '.$emi.' /> </td>
								<td class="c" style="text-align: center;"> <input type="radio"    name="emiPrinc" value="'.$campoData['nome'].'" '.$emiPrin.' /> </td>
								<td class="c" style="text-align: center;"> <input type="checkbox" name="'.$campoData['nome'].'_campoBusca" value="1" '.$cBusca.' /> </td>
								<td class="c" style="text-align: center;"> <input type="radio"    name="campoIndice" value="'.$campoData['nome'].'" '.$cIndice.' /> </td>
								<td class="c"><a href="javascript:void(0);" onclick="excluirCampo(\''.$campoData['nome'].'\')">[Excluir]</a></td>
								</tr>';
			}
		}
		//solicitacao 004: campo alerta
		$campoAlerta = "";
		if($tipo=='contr'){
			//submodulo
			requireSubModule(array("frontend","alerta"));
			//campo alerta, link para gerenciar alertas (abrir dialog)
			$campoAlerta = new HtmlTag("a", "", "","Gerenciar Alertas");
			$campoAlerta->setAttr(array("href","onclick"), array("#","javascript:gerenciarContratoAlerta();"));
			//preparando dialog para gerenciar alertas
			$dialog = new HtmlTag("div", "dialogGerenciarAlerta", "");
			$dialog->setStyle("display", "none");
			//campos para adicionar um novo alerta de sistema
			$sa = new SysAlerta();
			$dialog->setChildren(new HtmlTag("span", "respServerSysAlerta", "","",new HtmlTagStyle(array("color","text-align","margin-left"),array("red","center","20%"))));
			$dialog->setChildren($sa->getAddHtmlFields());
			//dialog esta compreendido no campo alerta
			$campoAlerta->setNext($dialog);
			//link campo alerta 	
			$campoAlerta = $campoAlerta->toString();
		}
		//fim 004
		//solicitacao 005 : campo estado
		$campoEstado="";
		if($tipo=='contr'){
			//submodulo
			requireSubModule(array("frontend","contrato_estado"));
			//campo alerta, link para gerenciar alertas (abrir dialog)
			$campoEstado = new HtmlTag("a", "", "","Gerenciar Estados");
			$campoEstado->setAttr(array("href","onclick"), array("#","javascript:gerenciarContratoEstado();"));			//preparando dialog para gerenciar alertas
			$dialog = new HtmlTag("div", "dialogGerenciarEstado", "");
			$dialog->setStyle("display", "none");
			//campos para adicionar um novo alerta de sistema
			$sa = new SysContratoEstado();
			$dialog->setChildren(new HtmlTag("div", "respServerSysContratoEstado", "ui-state-error ui-corner-all","",new HtmlTagStyle(array("text-align","display"),array("center","none"))));
			$dialog->setChildren($sa->getDAOHtml());
			//dialog esta compreendido no campo alerta
			$campoEstado->setNext($dialog);
			//link campo alerta
			$campoEstado = $campoEstado->toString();
		}
		//fim 005
		//codigo HTML do formulario de edicao do tipo de documento
		$html = '<script type="text/javascript" src="scripts/adm.js?r={$randNum}"></script>
			<form accept-charset="'.$conf['charset'].'" action="adm.php?area=dc&amp;acao=salvar" method="post"><input type="hidden" name="action" value="'.$_GET['acao'].'" />
			<table style="width: 100%; border: 0" cellpadding="2" cellspacing="2">
				<tbody>
					<tr>
						<td width="230"><b>Nome do Documento:</b></td>
						<td>'.$campoInput.'
						<input type="text" name="nome" size="35" maxlength="200" value="'.$res['nome'].'" /></td>
						<td rowspan="6" width="300"><div id="tdajuda"></div></td>
					</tr>
					<tr>
						<td><b>Nome Abreviado</b> (at&eacute; 5 caracteres):</td>
						<td>'.$nomeAbrv.'</td>
					</tr>
					<tr>
						<td><b>Alerta</b>:</td>
						<td>'.$campoAlerta.'</td>
					</tr>		
					<tr>
						<td><b>Estado</b>:</td>
						<td>'.$campoEstado.'</td>
					</tr>				
					<tr>
						<td><b>Campos:</b></td>
						<td>
							<table width="100%" id="camposDet"><tbody>
								<tr>
									<td class="c"><b>Nome</b></td>
									<td class="c" style="text-align: center; width:100px;"><b>tipo</b></td>
									<td class="c" style="text-align: center; width:60px;"><b>Emitente</b></td>
									<td class="c" style="text-align: center; width:60px;"><b>Emitente Princ.</b></td>
									<td class="c" style="text-align: center; width:60px;"><b>Busca</b></td>
									<td class="c" style="text-align: center; width:60px;"><b>&Iacute;ndice</b></td>
									<td class="c" style="text-align: center; width:60px;"></td>
								</tr>
								'.$campoHTML.'
							</tbody></table>
							<div style="display:block; width:100%"></div>
							<div id="novoCampo"><a href="javascript:void(0)" onclick="javascript:addCampo(1);">Adicionar</a></div>
							<br />
						</td>
					</tr>
					<tr>
						<td><b>Composi&ccedil;&atilde;o do n&uacute;mero:</b></td>
						<td><input type="text" name="numComp" size="40" value="'.$res['numeroComp'].'" /></td>
					</tr>
					<tr>
						<td><b>A&ccedil;&otilde;es:</b></td>
						<td>
							<input name="cad" type="checkbox"  '.$cad.'     value="1" /> &Eacute; poss&iacute;vel cadastrar esse documento.<br />
							<input name="new" type="checkbox"  '.$novo.'    value="1" /> &Eacute; poss&iacute;vel criar esse documento.<br />
							<input name="anex" type="checkbox" '.$anex.'    value="1" /> &Eacute; poss&iacute;vel anexar arquivos a esse documento.<br />
							<input name="doc" type="checkbox"  '.$anexDoc.' value="1" /> &Eacute; poss&iacute;vel anexar outros documentos a este.<br />
							<input name="resp" type="checkbox"  '.$resposta.' value="1" /> &Eacute; poss&iacute;vel criar resposta / informa&ccedil;&atilde;o (Doc. Inf.) a este documento.<br />
							<input name="obr" type="checkbox"  '.$anexObr.' value="1" /> &Eacute; poss&iacute;vel anexar obras a esse documento.<br />
							<input name="emp" type="checkbox"  '.$anexEmp.' value="1" /> &Eacute; poss&iacute;vel anexar empresas esse documento.<br />
						</td>
					</tr>
					<tr>
						<td><b>Template:</b> (apenas cria&ccedil;&atilde;o de doc)</td>
						<td><script type="text/javascript" src="CKEditor/ckeditor.js"></script>
							<textarea name="template" class="ckeditor" style="width:100%" rows="20" cols="10">'.$res['template'].'</textarea></td>
					</tr>
					<tr>
						<td colspan="2"><center><input type="submit" value="Salvar" /></center></td>
					</tr>
				</tbody>
			</table>
			</form>			
			<div id="configCampo" style="display:none; border: 2px solid #BE1010; background-color: #D8D8D8; position: fixed; top: 150px; left: 35%; width: 500px;">
			<table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td style="background-color:#BE1010; color: white;"><b>Adicionar Campo</b></td><td style="background-color:#BE1010; text-align:right; width: 50px;"><b><a href="javascript:closeCampo();" style="color:#D8D8D8;">Fechar</a></b></td><td style="background-color:#D8D8D8; border: 1px solid #BE1010; width:12px;text-align: center;"><b><a href="javascript:closeCampo();">X</a></b></td></tr></tbody></table>
			<div id="configCampoCont" style="padding: 5px;"></div>
			</div>';
		return $html;
	}
	/**
	 * Efetua a gravacao dos dados do novo documento e cria/modifica as tabelas no BD
	 * @param mysql_link $bd
	 */
	function salvaDoc($bd){
		//faz a leitura dos dados enviados
		$nome = SGEncode($_POST['nome'], ENT_QUOTES, null, false);
		$nomeAbrv = $_POST['nomeAbrv']; 
		$campos = $_POST['campos'];
		//inicializacao das variaveis
		if(isset($_POST['emiPrinc'])) {
			$emitente = $_POST['emiPrinc'].',';
		} else {
			$emitente = '';
		}
		if(isset($_POST['cIndice'])){
			$campoIndice = $_POST['cIndice'];
		} else {
			$campoIndice = '';
		}
		//se o documento for novo, cadastra a acao de ver o documento
		if($_POST['action'] == 'novo')
			$bd->query("INSERT INTO label_acao (nome, abrv) VALUES ('Ver ".$nome."','ver')");
		// consulta o ID da acao de ver o documento
		$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Ver ".$nome."'");
		$verID = $res[0]['id'];
		//se o documento for novo, cadastra a acao de despachar o documento
		if($_POST['action'] == 'novo')
			$bd->query("INSERT INTO label_acao (nome, abrv) VALUES ('Despachar ".$nome."','desp')");
		//consulta o ID da acao de despachar o documento
		$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Despachar ".$nome."'");
		if ($res[0]['id'] != "") $despID = $res[0]['id'];
		else $despID = 0;
		
		if(isset($_POST['new'])) {
			//se foi marcado que o documento eh criavel, consulta o ID de novo documento
			$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Novo ".$nome."'");
			if(count($res) == 0){
				//se nao houver ID de novo documento, cria nova acao
				$bd->query("INSERT INTO label_acao (nome, abrv) VALUES ('Novo ".$nome."','novo')");
				$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Novo ".$nome."'");
			}
			//seleciona o ID de criacao
			$newID = $res[0]['id'];
		} else {
			//se nao for criavel, id de criacao = 0
			$newID = 0;
		}
		
		if(isset($_POST['cad'])) {
			//consulta o id da acao de cadastrar documento
			$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Cadastrar ".$nome."'");
			if(count($res) == 0){
				//se nao houver, cadastra acao
				$bd->query("INSERT INTO label_acao (nome, abrv) VALUES ('Cadastrar ".$nome."','cad')");
				$res = $bd->query("SELECT id FROM label_acao WHERE nome = 'Cadastrar ".$nome."'");
			}
			//seleciona o ID de cadastro
			$cadID = $res[0]['id'];
		} else {
			//senao ID de cadastro = 0
			$cadID = 0;
		}
		//inicializa variavel de anexo
		if(isset($_POST['anex'])) {
			$acoes = '13';
		} else {
			$acoes = '';
		}
		//inicializa variaveis de anexos
		$documento = 0;
		$obra = 0;
		$empresa = 0;
		$resp = 0;
		
		if(isset($_POST['doc'])) {
			$documento = 1;
		}
		if(isset($_POST['obr'])) {
			$obra = 1;
		}
		if(isset($_POST['emp'])) {
			$empresa = 1;
		}
		if(isset($_POST['resp'])) {
			$resp = 1;
		}
		
		//salvando template
		if($_POST['template'] != '') {
			if(file_put_contents('templates/modelo_'.$nomeAbrv.'.html', $_POST['template']) === false) {
				return false;
			}
			$template = 'modelo_'.$nomeAbrv.'.html';
		} else {
			$template = '';
		}
		//inicializacao de variaveis
		$numComp = $_POST['numComp'];
		
		$campoBusca = '';
		
		$campos = rtrim($campos, ",");
		$campo = explode(',', $campos);
		foreach ($campo as $c) {
			if(isset($_POST[$c.'_emi']) && $_POST[$c.'_emi'] == 1 && strpos($emitente, $c) === false){
				$emitente .= $c.',';
			}
			if(isset($_POST[$c.'_campoBusca']) && $_POST[$c.'_campoBusca'] == 1){
				$campoBusca .= $c.','; 
			}
		}
		//retira a virgula
		$emitente = rtrim($emitente,",");
		$campoBusca = rtrim($campoBusca,",");
		//se for adicao de novo tipo de documento
		if($_POST['action'] == 'novo'){
			//insere o novo tipo de documento
			$sql = "INSERT INTO label_doc (nome,nomeAbrv,campos,emitente,numeroComp,cadAcaoID,novoAcaoID,verAcaoID,despAcaoID,tabBD,CampoIndice,campoBusca,acoes,obra,empresa,docAnexo,docResp,template)
					VALUES ('$nome','$nomeAbrv','$campos','$emitente','$numComp',$cadID,$newID,$verID,$despID,'doc_$nomeAbrv','$campoIndice','$campoBusca','$acoes',$obra,$empresa,$documento,$resp,'$template')";
			//cria tabela do banco de dados
			$createTable = "CREATE TABLE doc_$nomeAbrv (
							id int(5) NOT NULL AUTO_INCREMENT,";
			//para cada campo do documento, cria um atributo na tabela do BD dependendo do tipo.
			foreach ($campo as $c){
				$r = $bd->query("SELECT tipo FROM label_campo");
				$tipo = $r[0]['tipo'];
				$createTable .= $c;
				if($tipo == 'documentos' || $tipo == 'textarea' || $tipo == 'composto') $createTable .= " text NOT NULL,";
				if($tipo == 'input') $createTable .= " varchar(200) NOT NULL DEFAULT '',";
				if($tipo == 'select') $createTable .= " varchar(50) NOT NULL DEFAULT '',";
				if($tipo == 'userID' || $tipo == 'autoincrement' || $tipo == 'anoSelect') $createTable .= " int(5) NOT NULL DEFAULT 0,";
				if($tipo == 'yesno' || $tipo == 'checkbox') $createTable .= " int(1) NOT NULL DEFAULT 0,";
			}
			$createTable .= "PRIMARY KEY (id) ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
			//cria nova tabela
			$bd->query($createTable);
		//se for edicao de um tipo de documento novo
		} elseif ($_POST['action'] == 'edit'){
			//le os campos atuais
			$cols = $bd->query("SELECT campos FROM label_doc WHERE nomeAbrv='".$nomeAbrv."'");
			//atualiza a tupla deste tipo de documento no BD
			/*$sql = "UPDATE label_doc
					SET nome='$nome',campos='$campos',emitente='$emitente',numeroComp='$numComp',cadAcaoID=$cadID,novoAcaoID=$newID,verAcaoID=$verID,despAcaoID=$despID,
					tabBD='doc_$nomeAbrv',CampoIndice='$campoIndice',campoBusca='$campoBusca',acoes='$acoes',obra=$obra,empresa=$empresa,docAnexo=$documento,template='$template'
					WHERE nomeAbrv = '$nomeAbrv'";*/
			
			// removido atualizacao do campo acoes e inserido atualizacao em resposta
			$sql = "UPDATE label_doc
					SET nome='$nome',campos='$campos',emitente='$emitente',numeroComp='$numComp',cadAcaoID=$cadID,novoAcaoID=$newID,verAcaoID=$verID,despAcaoID=$despID,
					tabBD='doc_$nomeAbrv',CampoIndice='$campoIndice',campoBusca='$campoBusca',obra=$obra,empresa=$empresa,docAnexo=$documento,docResp=$resp,template='$template'
					WHERE nomeAbrv = '$nomeAbrv'";
			//altera a tabela no bd para criar atributos para eventuais novos campos
			$sqlAlter = "ALTER table doc_$nomeAbrv ";
			//para cada campo enviado
			foreach (explode(",", $campos) as $c) {
				//se for campo novo, cria atributo no BD para ele
				if(strpos($cols[0]['campos'], $c) === false){
					$r = $bd->query("SELECT tipo FROM label_campo");
					$tipo = $r[0]['tipo'];
					//tipo do atributo depende do tipo de campo
					if($tipo == 'documentos' || $tipo == 'textarea' || $tipo == 'composto') $sqlAlter .= "ADD $c text, ";
					if($tipo == 'input') $sqlAlter .= "ADD $c varchar(200) NOT NULL DEFAULT '', ";
					if($tipo == 'select') $sqlAlter .= "ADD $c varchar(50) NOT NULL DEFAULT '', ";
					if($tipo == 'userID' || $tipo == 'autoincrement' || $tipo == 'anoSelect') $sqlAlter .= "ADD $c int(5) NOT NULL DEFAULT 0, ";
					if($tipo == 'yesno' || $tipo == 'checkbox') $sqlAlter .= "ADD $c int(1) NOT NULL DEFAULT 0, ";
				}
			}
			//altera a tabela
			$bd->query($sqlAlter);
		}
		//executa a consulta de atualizacao/insercao
		$add = $bd->query($sql);
		//feedback
		if($add)
			return "Documento modificado com sucesso.";
		else
			return "Erro durante a opera&ccedil;&atilde;o.";
	}
	
	function excluiDoc($bd) {
		//excluir um tipo de documento apenas bloqueia a cracao e cadastro de documentos daquele tipo
		//os documentos ja cadastrados nao serao excluidos.
		if($bd->query("UPDATE label_doc SET cadAcaoID=-1,novoAcaoID=-1 WHERE nomeAbrv='".$_GET['tipoDoc']."'"))
			return 'Tipo de documento excluido. Os Documentos desse tipo j&aacute; cadastrados n&atilde;o ser&atilde;o excluidos.';
			
	}
	
	function showPermGroups($bd){
		global $conf;
		//carrega os nomes de grupos
		$nomes = $bd->query("SELECT * FROM label_grupos ORDER BY id");
		//carrega os nomes de acoes e permissoes
		//$acao = $bd->query("SELECT * FROM label_acao");
		//criacao do cod HTML do cabecalho
		$html = '<h3>Tipos Permiss&atilde;o por Grupo</h3>
		<form accept-charset="'.$conf['charset'].'" action="adm.php?area=pe&amp;acao=salvar" method="post">';
		
		
		
		$cat = $bd->query("SELECT cat FROM label_acao GROUP BY cat ORDER BY cat ASC");
		$cat_nomes = array("sga" => "Administra&ccedil;&atilde;o", "sgd" => "Documentos", "sge" => "Empresas", "sgo" => "Empreendimentos e obras", "sgp" => "Funcion&aacute;rios");
		$subcat_nomes['sgd']['proc'] = 'Processo'; $subcat_nomes['sgd']['ofi'] = 'Oficio CPO'; $subcat_nomes['sgd']['ofe'] = 'Oficio Externo';
		$subcat_nomes['sgd']['it'] = 'I.T.'; 
		$subcat_nomes['sgd']['contr'] = 'Contrato'; 
		$subcat_nomes['sgd']['sap'] = 'SAP';
		$subcat_nomes['sgd']['memo'] = 'Memorando'; $subcat_nomes['sgd']['resp'] = 'Resposta'; $subcat_nomes['sgd']['rr'] = 'Relacao de Remessa';
		
		foreach ($cat as $c) {
			$html .= '<br /><br /><h3>'.$cat_nomes[$c['cat']].'</h3>
				<table><tbody>
				<tr><td class="c"><b>A&ccedil;&atilde;o</b></td>';
			//cria 1a linha com os nomes de grupos
			for ($i = 0; $i < count($nomes); $i++) {
				$html .= '<td class="c"><b>'.$nomes[$i]["nome"]."</b></td>";
			}
			$html .= '</tr>';
			
			$acao = array();
			
			//primeiro, carrega as acoes gerais de uma cat
			$acao = array_merge($acao, $bd->query("SELECT * FROM label_acao WHERE cat='{$c['cat']}' AND subcat='geral' ORDER BY nome ASC"));
			$acao = array_merge($acao, $bd->query("SELECT * FROM label_acao WHERE cat='{$c['cat']}' AND subcat!='geral' ORDER BY subcat ASC, nome ASC"));
			
			$ultima_subcat = 'geral';
			
			$linhas = 1;
			
			//para cada acao, cria uma linha com os checkboxes de permissoes
			foreach ($acao as $a) {
				// repete o cabeÃ§alho a cada 20 linhas
				if ($linhas % 20 == 0) {
					$html .= '<tr><td class="c"><b>A&ccedil;&atilde;o</b></td>';
					//cria 1a linha com os nomes de grupos
					for ($i = 0; $i < count($nomes); $i++) {
						$html .= '<td class="c"><b>'.$nomes[$i]["nome"]."</b></td>";
					}
					$html .= '</tr>';
				}
				
				if($ultima_subcat != $a['subcat']){
					$html .= '<tr><td class="c" colspan="'.(count($nomes)+1).'"><b>'.$subcat_nomes[$a['cat']][$a['subcat']].'</b></td></tr>';
					$ultima_subcat = $a['subcat'];
				}
				
				//coloca o nome da acao
				$html .= '<tr class="c"><td class="c" width="200">'.$a['nome'].'</td>';
				for ($i = 0; $i < count($nomes); $i++) {
					//e as colunas com os checkboxes checados se tal grupo tem permissao
					
					// mas primeiro, consulta tabela de permissoes
					$permissoes = $bd->query("SELECT * FROM permissoes WHERE grupoID = '" .$nomes[$i]['id']."' AND acaoID = " .$a['id']);
					// cria checkboxes
					//if($a['G'.$nomes[$i]['id']] == 1) {
					if (count($permissoes) > 0 && $permissoes[0]['permissao'] == 1) {
						//$checkbox = '<input type="checkbox" name="'.($aNum+1).'G'.$nomes[$i]['id'].'" value="1" checked />';
						$checkbox = '<input type="checkbox" name="'.$a['id'].'G'.$nomes[$i]['id'].'" value="1" checked />';
					} else {
						$checkbox = '<input type="checkbox" name="'.$a['id'].'G'.$nomes[$i]['id'].'" value="1" />';
					}
					$html .= '<td class="c" style="text-align:center">'.$checkbox."</td>";
				}
				$html .= '</tr>';
				$linhas++;
			}
			
			$html .= '</table>';
		}
		
		//cria ultima linha com o botao para enviar
		$html .= '<center> <input type="submit" value="Salvar" /></center></form>';
		//retorna o codigo HTML
		return $html;
	}
	/**
	 * Salva as permissoes editadas no formulario no banco de dados
	 */
	function salvaPermissoes($bd){
		/*
		//consulta a quantidade de grupos cadastrados
		$qgrupos = count($bd->query("SELECT id FROM label_grupos"));
		//consulta a quantidade de acoes cadastradas
		$qacoes = count($bd->query("SELECT id FROM label_acao"));
		//para cada acao, se o grupo tiver permissao
		for ($i = 1; $i <= $qacoes; $i++) {
			$sql = "UPDATE label_acao SET ";
			for ($j = 1; $j <= $qgrupos; $j++) {
				//se o grupo tiver permissao, seta Gx = 1 na coluna da acao
				if(isset($_POST[$i."G".$j]) && $_POST[$i."G".$j]) $sql .= " G$j=1,";
				else $sql .= " G$j=0,";
			}
			//retira virgula sobresalente
			$sql = rtrim($sql,",");
			//finaliza a consulta
			$sql .= " WHERE id=$i";
			//realiza a consulta
			$res = $bd->query($sql);
			//se a consulta for mal sucedida, retorna feedback
			if(!$res){
				return "Erro ao atualizar dados no Banco de Dados";
			}
		}
		//senao, retorna feedback positivo
		return "Dados atualizados com sucesso!";*/
		
		$sql = "SELECT * FROM label_grupos";
		$grupos = $bd->query($sql);
		$sql = "SELECT * FROM label_acao";
		$acoes = $bd->query($sql);
		
		// percorre as acoes
		foreach($acoes as $a) {
			// pra cada acao, percorre os grupos
			foreach($grupos as $g) {
				$permVal = 0;
				if (isset($_POST[$a['id'] . "G" . $g['id']]) && $_POST[$a['id'] . "G" . $g['id']] == 1) $permVal = 1;
				else $permVal = 0;
				
				$sql = "SELECT * FROM permissoes WHERE grupoID = ".$g['id']." AND acaoID = " .$a['id'];
				if (count($bd->query($sql)) <= 0) {
					$sql = "INSERT INTO permissoes (grupoID, acaoID,permissao) VALUES (".$g['id'].",".$a['id'].",$permVal)";
				}
				else {
					$sql = "UPDATE permissoes SET permissao = $permVal WHERE grupoID = ".$g['id']." AND acaoID = " .$a['id'];
				}
					
				$res = $bd->query($sql);
				if (!$res) return "Erro ao tentar atualizar permissoes no Banco de Dados do grupo de ID " .$g['id']. " na acao de ID " .$a['id'];
			}
		}
		return "Dados atualizados com sucesso!";
	}
	
	/**
	 * Gera a tabela para gerenciamento de grupos
	 * @param mysql_link $bd
	 */
	function showGrupos($bd){
		//monta as primeiras tags da tabela da tabela/cabecalho
		$html = '<h3>Grupos de Permiss&atilde;o</h3>
		<a href="adm.php?area=gr&amp;acao=novo">Adicionar Grupo</a><br /><br />
		<table width="500">
		<tbody>
		<tr><td><b>Nome</b></td><td></td><td></td></tr>';
		//seleciona o nome de todos os grupos
		$grupos = $bd->query("SELECT nome,id FROM label_grupos");
		//para cada grupo cadastrado
		foreach ($grupos as $g) {
			//cria a tag HTML de uma linha: nome|editar|exluir
			$html .= '<tr class="c">
			<td>'.$g['nome'].'</td><td><a href="adm.php?area=gr&amp;acao=edit&amp;id='.$g['id'].'">Editar</a></td>
			<td><a href="adm.php?area=gr&amp;acao=excl&amp;id='.$g['id'].'">Excluir</a></td>
			</tr>';
		}
		//fecha as tags
		$html .= '</tbody>
		</table>
		';
		//retorna o codigo
		return $html;
	}
	
	/**
	 * Mostra formulario para editar os dados dos grupos
	 * @param mysql_link $bd
	 * @param int $id
	 */
	function showEditGrupoForm($bd, $id = null){
		global $conf;
		//se id eh passado
		if($id){
			//retira dados desse ID
			$res = $bd->query("SELECT nome FROM label_grupos WHERE id = $id");
			//se consulta foi bem sucedida (aka. ID eh valido)
			if(count($res)) {
				//preenche o nome do grupo e id
				$nome = $res[0]['nome'];
				$id = $id;
			} else {
				//se ID invalido, cria formulario em banco
				$nome = '';
				$id = 0;
			}
		} else {
			//se id = null,cria formulario em branco
			$nome = '';
			$id = 0;
		}
		//monta o cod HTML do formulario
		$html = '<form accept-charset="'.$conf['charset'].'" action="adm.php?area=gr&amp;acao=salvar" method="post">
		<table>
		<tr class="c"><td>ID:</td><td><input type="text" name="id" value="'.$id.'" size=2 disabled="disabled" /></td></tr>
		<tr class="c"><td>Nome:</td><td><input type="text" name="nome" value="'.$nome.'" size=30 /><input type="hidden" name="id" value="'.$id.'" /></td></tr>
		<tr class="c"><td colspan="2"><input type="submit" value="Enviar" /></td></tr>
		</table>
		</form>'; 
		//retorna codigo HTML
		return $html;
	}
	
	/**
	 * realiza exclusao de um grupo
	 * @param mysql_link $bd
	 */
	function excluiGrupo($bd){
		//se nao houver um ID especificado, retorna erro
		if(!isset($_GET['id'])) {
			return "Faltam dados para salvar os dados corretamente. Nenhum dado foi salvo.";
		}
		$res = $bd->query("SELECT * FROM label_grupos WHERE id = ".$_GET['id']);
		if (count($res) <= 0) return "Grupo inexistente.";
		
		//para excluir um grupo sao realizados os seguintes procedimento:
		//1-Retira a coluna relativa as permissoes do grupo da tabela de acoes
		//2-Exclui a tupla correspondente aquele grupo na tabela de grupos
		//3-Coloca permissao minima para os integrantes do grupo exluido
		/*if($bd->query("ALTER TABLE label_acao DROP G".$_GET['id']) &&
			$bd->query("DELETE FROM label_grupos WHERE id=".$_GET['id']) &&
			$bd->query("UPDATE usuarios SET gid=1 WHERE gid=".$_GET['id'])){*/
		if ($bd->query("DELETE FROM label_grupos WHERE id=".$_GET['id']) &&
			$bd->query("DELETE FROM permissoes WHERE grupoID = ".$_GET['id']) &&
			$bd->query("UPDATE usuarios SET gid=1 WHERE gid=".$_GET['id'])) {
			//retorna feedback positivo se as 3 partes forem bem sucedidas
			return "Grupo exclu&iacute;do com sucesso.";
		}
		//senao, retorna mensagem de erro
		return "Erro ao deletar as bases de dado.";
		
	}
	
	/**
	 * salva os dados do grupo
	 * @param mysql_link $bd
	 */
	function salvaGrupo($bd){
		//se nao foi enviado nome ou id do grupo(mesmo que seja 0) retorna erro
		if(!isset($_POST['id']) || !isset($_POST['nome'])){
			return "Faltam dados para salvar os dados corretamente. Nenhum dado foi salvo.";
		}
		//se id=0 faz criacao do documento
		if ($_POST['id'] == 0){
			//insere a tupla correspondente na tabela de grupos
			$res = $bd->query("INSERT INTO label_grupos (nome) VALUES ('".$_POST['nome']."')");
			//se insercao bem sucedida
			/*if(count($res)){
				//consulta o ID do grupo criado
				$res = $bd->query("SELECT id FROM label_grupos WHERE nome = '".$_POST['nome']."'");
				$id = $res[0]['id'];
				//adiciona a coluna desse grupo na tabela de acoes (permissoes)
				$res = $bd->query("ALTER TABLE label_acao ADD G".$id." BOOLEAN NOT NULL DEFAULT 0");
			}*/
		//se id!=0 eh a edicao de um grupo
		} else {
			//apenas atualiza o nome no BD
			$res = $bd->query("UPDATE label_grupos SET nome = '".$_POST['nome']."' WHERE id = ".$_POST['id']);
		}
		//se tudo deu certo, retorna feedback positivo
		if($res){
			return 'Dados salvos com sucesso. Para editar as permiss&otilde;es, <a href="adm.php?area=pe&amp;acao=geren">aqui</a>.';
		//senao, retorna mensagem de erro
		} else {
			return "Erro ao salvar os dados";
		}
	}
	
	/**
	 * Mostra formulario para edicao de emresas
	 * @param mysql_link $bd
	 * @param int $id
	 */
	function showEditEmprForm($bd, $id = 0){
		global $conf;
		//se ID = 0, cria um formulario em branco para criacao de uma nova empresa
		if($id == 0){
			$empr = array(
			'nome'  => '' ,
			'end'   => '' ,
			'compl' => '' ,
			'cid'   => '' ,
			'est'   => '' ,
			'cep'   => '' ,
			'tel'   => '' ,
			'email' => '' );
		//senao, pre-prenche os campos com dados da empresa cujo id eh passado
		} else {
			$empr = $bd->query("SELECT * FROM empresa WHERE id = ".$id);
			if(count($empr)){
				$empr   =  $empr[0];
				$empr   =  array('nome' => $empr['nome'] ,
				'end'   => $empr['endereco'] ,
				'compl' => $empr['complemento'] ,
				'cid'   => $empr['cidade'] ,
				'est'   => $empr['estado'] ,
				'cep'   => $empr['cep'] ,
				'tel'   => $empr['telefone'] ,
				'email' => $empr['email'] );
			}
		}
		//monta o codigo HTML do formulario de edicao
		return '<form accept-charset="'.$conf['charset'].'" action="adm.php?area=em&amp;acao=salvar" method="post">
		<table width="100%" border="0"> <input type="hidden" name="id" value="'.$id.'" />
		<tr><td><b>Nome da Empresa:</b> <input id="nome" name="nome" value="'.$empr['nome'].'" size="50" /></td></tr>
		<tr><td><b>EndereÃ§o:</b> <input id="end" name="end" value="'.$empr['end'].'" size="60" /></td></tr>
		<tr><td><b>Complemento:</b><input id="compl" name="compl" value="'.$empr['compl'].'" size="55"></td></tr>
		<tr><td><b>Cidade:</b> <input id="cid" name="cid" size="22" value="'.$empr['cid'].'" /> <b>Estado:</b> <input id="est" name="est" size="2" value="'.$empr['est'].'" /> <b>CEP:</b> <input id="cep" name="cep" size="10" value="'.$empr['cep'].'" /></td></tr>
		<tr><td><b>Telefone:</b> <input id="tel" name="tel" size="15" value="'.$empr['tel'].'" /> <b>e-mail:</b> <input id="email" name="email" size="30" value="'.$empr['email'].'" /></td></tr>
		<tr><td><center><input type="submit" value="Cadastrar" /></center></td></tr>
		</table></form>';
	}
	
	/**
	 * Mostra tabela de empresas com links para edicao/exclusao
	 * @param mysql_link $bd
	 */
	function showEmpr($bd){
		$empr = $bd->query("SELECT * FROM empresa");
		//cria perimeira linha da tabela
		$html = '<a href="adm.php?area=em&amp;acao=nova">Adicionar Empresa</a>
			<table><tbody>
			<tr>
			<td class="c"><b>Nome</b></td>
			<td class="c"><b>EndereÃ§o</b></td>
			<td class="c"><b>Complemento</b></td>
			<td class="c"><b>Cidade</b></td>
			<td class="c"><b>Estado</b></td>
			<td class="c"><b>CEP</b></td>
			<td class="c"><b>Telefone</b></td>
			<td class="c"><b>E-mail</b></td>
			<td class="c"></td>
			<td class="c"></td>
			</tr>';
		//para cada empresa cadastrada, cria uma linha na tabela
		foreach ($empr as $e) {
			$html .= '<tr class="c"><td class="c">'.$e['nome'].'</td>
			<td class="c">'.$e['endereco'].'</td>
			<td class="c">'.$e['complemento'].'</td>
			<td class="c">'.$e['cidade'].'</td>
			<td class="c">'.$e['estado'].'</td>
			<td class="c">'.$e['cep'].'</td>
			<td class="c">'.$e['telefone'].'</td>
			<td class="c">'.$e['email'].'</td>
			<td class="c"><a href="adm.php?area=em&amp;acao=editar&amp;id='.$e['id'].'">Editar</a></td>
			<td class="c"><a href="adm.php?area=em&amp;acao=excl&amp;id='.$e['id'].'">Remover</a></td>
			</tr>';
		}
		//fecha as tags de tabela abertas
		$html .= "</tbody></table>";
		//retorna o codigo html
		return $html;
	}
	
	/**
	 * rotina que salva os dados da empresa
	 * @param mysql_link $bd
	 */
	function salvaEmpr($bd){
		//se for cadastro de nova empresa, insere no BD
		if($_POST['id'] == 0) $sql = "INSERT INTO empresa (nome,endereco,complemento,cidade,estado,cep,telefone,email) VALUES ('".$_POST['nome']."','".$_POST['end']."','".$_POST['compl']."','".$_POST['cid']."','".$_POST['est']."','".$_POST['cep']."','".$_POST['tel']."','".$_POST['email']."')";
		//senao, atualiza os dados da empresa no BD
		else $sql = "UPDATE empresa SET nome = '".$_POST['nome']."' , endereco = '".$_POST['end']."' , complemento = '".$_POST['compl']."' , cidade = '".$_POST['cid']."' , estado = '".$_POST['est']."' , cep = '".$_POST['cep']."' , telefone = '".$_POST['tel']."' , email = '".$_POST['email']."' WHERE id = ".$_POST['id'];
		//se consulta bem sucedida, mostra feedback positivo.
		if($bd->query($sql)){
			return "Dados salvos com sucesso";
		//senao, mostra mensagem de erro
		} else {
			return "Erro ao salvar dados.";
		}
	}
	
	/**
	 * exclui entrada de uma empresa
	 * @param mysql_link $bd
	 */
	function excluiEmpr($bd){
		//se ID nao for passado por parametro, mostra mensagem de erro
		if(!isset($_GET['id'])) {
			return "Faltam dados para salvar os dados corretamente. Nenhum dado foi salvo.";
		}
		//senao, eclui empresa do BD e retorna mensagem de feedback.
		if($bd->query("DELETE FROM empresa WHERE id=".$_GET['id'])){
			return "Empresa exclu&iacute;da com sucesso.";
		}
	}
	
	/**
	 * Funcao para atualizar unidades a partir de arquivo CSV
	 * @param mysql_link $bd
	 */
	function updateUnidades($bd) {
         //mostra tela de confirmacao se nao achar flag de confirmacao
                if(!isset($_GET['confirm']))
                //retorna mensagem de confirmacao
                        return '<h3>Instru&ccedil;&otilde;es para atualiza&ccedil;&atilde;o da tabela de unidades</h3>
                        1. Converta o arquivo de unidades/&oacute;rg&atilde;os para um CSV com a seguinte estrutura em codifica&ccedil;&atilde;o Latin1:<br />
                        codigo;sigla;nome da unidade/&oacute;rg&atilde;o;data de desativa&ccedil;&atilde;o<br />
                        (n&atilde;o h&aacute; obrigatoriedade de colocar aspas e <b>n&atilde;o &eacute; permitido ponto-e-v&iacute;rgula na sigla</b>)
                        <br />
                        Utilizar ponto-e-virgula (;) como separador de c&eacute;lulas e linhas
                        <br /><br />
                        2. Coloque o CSV na pasta BD/ com o nome &quot;unidades.csv&quot;
                        <br /><br />
                        3. Clique <a href="adm.php?area=un&amp;acao=atual&amp;confirm">aqui</a> para iniciar a atualiza&ccedil;&atilde;o.
                        ';
                //inicializacao de variaveis
                $novos = 0;
                $alterados = 0;
                //array para retirar acentos
                $ca = array("Ã¡","Ã£","Ã ","Ã¢","Ã©","Ãª","Ã¨","Ã­","Ã®","Ã¯","Ã³","Ãµ","Ã²","Ã´","Ãº","Ã»","Ã¼","Ã§",
                            "Ã�","Ãƒ","Ã€","Ã‚","Ã‰","ÃŠ","Ãˆ","Ã�","ÃŽ","Ã�","Ã“","Ã•","Ã’","Ã”","Ãš","Ã›","Ãœ","Ã‡");
                $sa = array("a","a","a","a","e","e","e","i","i","i","o","o","o","o","u","u","u","c",
                                    "A","A","A","A","E","E","E","I","I","I","O","O","O","O","U","U","U","C");
                //le os dados do arquivo de unidades e guarda em um array
                $unidades = file('BD/unidades.csv');
                //para cada unidade lida
                foreach ($unidades as $un) {
                        //separa os campos separados por virgula
                        $un = explode(';', str_replace(array('"',"'"), array('',''), str_replace($ca,$sa,$un)),4);
                        //adiciona os zeros para completar o tamanho correto do cod da unidade
                        $un[0] = addZero($un[0]);
                        //se nao houver nome da unidade, coloca vazio
                        if(!isset($un[2])) $un[2] = ''; 
                        //verifica se ja tem alguma unidade com aquele codigo cadastrada
                        $unbd = $bd->query("SELECT * FROM unidades WHERE id = '".$un[0]."'");
                        
                        //verifica se ha necessidade de desativar/reativar a unidade
                        if(rtrim($un[3], " \n\r") === ''){
                                $ativo = '1';
                        } else {
                                $ativo = '0';
                        }
                        //se ha unidade com esse codigo cadastrada
                        if(count($unbd)){
                                //seleciona a primeira tupla retornada
                                $unbd = $unbd[0];
                                //se o nome e a sigla lidas foresm iguais ao que esta cadastrado no BD
                                if($un[1] == $unbd['sigla'] && rtrim($un[2]," \n") == $unbd['nome'] && $unbd['ativo'] == $ativo){
                                        //entao nao precisa modificar nada e passa para a proxima unidades
                                        continue;
                                //senao deve-se atualizar a base de dados
                                } else {
                                        //realiza a atualizacao da unidade
                                        $un[2] = rtrim($un[2],"\n");
                                        print ("UPDATE unidades SET sigla='".$un[1]."', nome='".$un[2]."' ativo={$ativo} WHERE id = '".$un[0]."'<br>");
                                        $ok = $bd->query("UPDATE unidades SET sigla='".$un[1]."', nome='".$un[2]."', ativo={$ativo} WHERE id = '".$un[0]."'");
                                        //marca que mais uma unidade foi modificada
                                        $alterados += 1;
                                }
                        //senao achar um documento com aquele codigo no BD
                        } else {
                                if(rtrim($un[3], " \n\r") === ''){
                                        //insere umanova tupla no BD com o codigo lido
                                        $un[2] = rtrim($un[2],"\n");
                                        print "INSERT INTO unidades (id,sigla,nome) VALUES ('".$un[0]."','".$un[1]."','".$un[2]."')<br>";
                                        $ok = $bd->query("INSERT INTO unidades (id,sigla,nome) VALUES ('".$un[0]."','".$un[1]."','".$un[2]."')");
                                        //marca que mais uma unidade foi marcada
                                        $novos += 1;
                                }
                        }
                        //fclose($unidades);
                        //se atualizacao/insercao foi bem sucedida
                        /*if($ok) {
                                //algoritmo para montar o 'caminho'
                                $subs = explode(".", $un[0]);
                                //inicializa a variavel do 'caminho'
                                $sub = '';
                                //e a de concatenacao dos codigos
                                $subsConcat = '';
                                //para cada 'area' do codigo
                                foreach ($subs as $s) {
                                        //concartena o cod da area
                                        $subsConcat .= $s;
                                        //completa o cod com zeros para match no BD
                                        if(addZero($subsConcat) == $un[0])
                                                //se for igual ao codigo 'total', para o algoritmo
                                                break;
                                        //verifica qual a sigla da 'area' selecionada
                                        $r2 = $bd->query("SELECT sigla FROM unidades WHERE id = '".addZero($subsConcat)."'");
                                        //se nao achar a sigla da 'area' acima, para o algoritmo
                                        if(count($r2) == 0)
                                                break;
                                        //senao, monta o caminho
                                        $sub .= $r2[0]['sigla'].' / ';
                                        //e recria o codigo colocando o 'ponto'
                                        $subsConcat .= '.';
                                }
                                //ao final, se gerar caminho diferente, grava o caminho gerado
                                if (!isset($unbd['sub']) || $sub != $unbd['sub']) {
                                        $bd->query("UPDATE unidades SET sub = '$sub' WHERE id='".$un[0]."'");
                                } 
                        //se insercao/atualizacao mal sucedida, entao mostra mesagem de erro
                } else {
                                return "ERRO nas consultas ao banco de dados. <br /> Tuplas alteradas: $alterados <br /> Tuplas adicionadas: $novos";
                        }*/
                }
                //se tudo ocorreu ok, mostra mensagem de sucesso.
                return "Dados atualizados com sucesso. <br /> Tuplas alteradas: $alterados <br /> Tuplas adicionadas: $novos";
        }
	
	/**
	 * Funcao auxiliar para colocar zeros de modo a todas as unidades terem o mesmo numero de digitos
	 * @param string $id
	 */
	function addZero($id) {
		if (strlen($id) == 1)  return '0'.$id.'.00.00.00.00.00';
		if (strlen($id) == 2)  return $id.'.00.00.00.00.00';
		if (strlen($id) == 5)  return $id.'.00.00.00.00';
		if (strlen($id) == 8)  return $id.'.00.00.00';
		if (strlen($id) == 11) return $id.'.00.00';
		if (strlen($id) == 14) return $id.'.00';
		return $id;	
	}
	
	function showPermAnex($bd){
		global $conf;
		// carrega os tipos de doc
		$docs = $bd->query("SELECT * FROM label_doc ORDER BY id");
		// cria cabecalho
		$html = '<h3>Permiss&atilde;o de anexar por tipo de documento</h3>
		<form accept-charset="'.$conf['charset'].'" action="adm.php?area=dc&amp;acao=salvarAnex" method="post">
		<table><tbody>
		<tr><td class="c"><b>Tipo Doc. Aceita -></b></td>';
		// cria 1a linha com nomes
		foreach($docs as $doc) {
			$html .= '<td class="c"><b>'.$doc['nome'].'</b></td>';
		}
		$html .= '</tr>';
		foreach($docs as $doc) {
			$html .= '<td class="c"><b>'.$doc['nome'].'</b></td>';
			foreach($docs as $temp) {
				$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = ".$doc['id']." AND tipoAnexoID = ".$temp['id'];
				$res = $bd->query($sql);
				
				if (count($res) > 0 && $res[0]['aceitaAnexo'] == 1)
					$checkbox = '<input type="checkbox" name="'.$doc['id'].'A'.$temp['id'].'" value="1" checked />';
				else
					$checkbox = '<input type="checkbox" name="'.$doc['id'].'A'.$temp['id'].'" value="1" />';
					
				$html .= '<td class="c">'.$checkbox.'</td>'; 
			}
			$html .= '</tr>';
		}
		$html .= '<tr><td align="center"> <input type="submit" value="Salvar" /></td></tr>
		</tbody></table></form>';
		return $html;
	}
	
	function salvaPermAnexos($bd){	
		$sql = "SELECT * FROM label_doc ORDER BY id";
		$docs = $bd->query($sql);
		
		foreach ($docs as $d) {
			foreach ($docs as $a) {
				$permVal = 0;
				if (isset($_POST[$d['id'] . "A" . $a['id']]) && $_POST[$d['id'] . "A" . $a['id']] == 1) $permVal = 1;
				
				$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = ".$d['id']." AND tipoAnexoID = ".$a['id'];
				if (count($bd->query($sql)) <= 0) {
					$sql = "INSERT INTO label_doc_anexo (tipoDocID,tipoAnexoID,aceitaAnexo) VALUES (".$d['id'].",".$a['id'].",".$permVal.")";
				}
				else {
					$sql = "UPDATE label_doc_anexo SET aceitaAnexo = $permVal WHERE tipoDocID = ".$d['id']." AND tipoAnexoID = ".$a['id'];
				}
				$res = $bd->query($sql);
				if (!$res) return "Erro ao tentar atualizar permissoes de Anexo no Banco de Dados.";
			}
		}
		return "Dados atualizados com sucesso!";
	}
	
	function atualizaUsuarios(BD $bd) {
		require_once('classes/adLDAP/adLDAP.php');
		
		$sql = "SELECT * FROM usuarios ORDER BY id";
		$users = $bd->query($sql);
		
		$pessoa = new Pessoa();
		$adldap = new adLDAP();
		
		doLog($_SESSION['username'], 'Iniciou atualização de dados de usuários.');
		
		$ret = '';
		
		foreach($users as $u) {
			//$BDdata = $u;
			$BDdata = $pessoa->getUserData($u['username'], $bd);
			$ADdata = $adldap->user()->info($u['username'],array('displayname','samAccountName','sn','GivenName','userPrincipalName','telephoneNumber','mail','title','department','description','initials','AccountDisabled','enabled','useraccountcontrol','manager'));
			
			if ($ADdata === false) {
				$ret .= 'Usu&aacute;rio n&atilde;o encontrado no AD: '.$u['username'].'<br />';
				$bd->query("UPDATE usuarios SET ativo = 0 WHERE username = '".$u['username']."'");
				continue;
			}
			
			$pessoa->updateUserData($ADdata,$BDdata,$bd);
		}
		
		if ($ret != '') $ret .= '<br />';
		
		doLog($_SESSION['username'], 'Atualizou dados dos usuários com sucesso.');
		
		return $ret . "Dados dos usu&aacute;rios atualizados com sucesso!";
		
	}
	
	/**
	 * Mostra a tela de setar responsáveis da CPO para contratos
	 * @param BD $bd
	 */
	function setarResponsaveis(BD $bd) {
		global $conf;
		// seleciona todos os usuÃ¡rios para gerar um select
		$sql = "SELECT id AS value, nomeCompl AS label FROM usuarios WHERE ativo = 1 ORDER BY nomeCompl ASC";
		$users = $bd->query($sql);
		
		/*
		 * Seleciona diretor
		 */
		$sql = "SELECT id FROM usuarios WHERE flagRespContr = 1 AND ativo = 1";
		$diretor = $bd->query($sql);
		
		$diretorID = 0;
		if (count($diretor) > 0) {
			$diretorID = $diretor[0]['id'];
		}
		
		// gera o select
		$diretorSelect = geraSelect('diretorID', $users, $diretorID);
		
		/*
		 * Seleciona Coordenador
		 */
		$sql = "SELECT id FROM usuarios WHERE flagRespContr = 2 AND ativo = 1";
		$coordenador = $bd->query($sql);
		
		$coordenadorID = 0;
		if (count($coordenador) > 0) {
			$coordenadorID = $coordenador[0]['id'];
		}
		
		// gera select
		$coordenadorSelect = geraSelect('coordenadorID', $users, $coordenadorID);
		
		$html = '
		<form accept-charset="'.$conf['charset'].'" action="adm.php?acao=salvaResponsaveis&area=gr" id="salvaRespForm" method="post">
			<table width="100%">
				<tr class="c">
					<td class="c" width="100"><b>Diretor</b>: </td>
					<td class="c">'.$diretorSelect.'</td>
				</tr>
				<tr class="c">
					<td class="c"><b>Coordenador</b>: </td>
					<td class="c">'.$coordenadorSelect.'</td>
				</tr>
				<tr class="c">
					<td class="c" colspan="2"><input type="submit" value="Salvar"></td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			$(document).ready(function() {				
				$("#salvaRespForm").bind("submit", function(e) {
					if ($("#diretorID").val() == undefined || $("#diretorID").val() == "" || $("#diretorID").val() == 0) {
						alert("Selecione um diretor!");
						e.preventDefault();
					}
					
					if ($("#coordenadorID").val() == undefined || $("#coordenadorID").val() == "" || $("#coordenadorID").val() == 0) {
						alert("Selecione um coordenador!");
						e.preventDefault();
					}
					
					if ($("#coordenadorID").val() == $("#diretorID").val()) {
						alert("Diretor e Coordenador devem ser duas pessoas diferentes!");
						e.preventDefault();
					}
				});
			});
		</script>
		';
		
		return $html;
	}
	
	/**
	 * Salva os responsÃ¡veis passados pelo formulÃ¡rio
	 * @param mixed $post
	 * @param BD $bd
	 */
	function salvarResponsaveis($post, BD $bd) {
		if (!isset($post['coordenadorID']) || $post['coordenadorID'] == "" || $post['coordenadorID'] == 0) {
			return "Erro: Coordenador inv&aacute;lido.";
		}
		if (!isset($post['diretorID']) || $post['diretorID'] == "" || $post['diretorID'] == 0) {
			return "Erro: Diretor inv&aacute;lido.";
		}
		
		if ($post['diretorID'] == $post['coordenadorID']) {
			return "Erro: Coordenador e Diretor devem ser pessoas diferentes!";
		}
		
		$sql = "SELECT id FROM usuarios WHERE id = ".$post['coordenadorID']. " OR id = ".$post['diretorID'];
		$res = $bd->query($sql);
		if (count($res) < 2) {
			return "Erro: Diretor ou Coordenador n&atilde;o encontrados no Banco de Dados.";
		}
		
		$sql = "UPDATE usuarios SET flagRespContr = 0 WHERE flagRespContr = 1 OR flagRespContr = 2";
		$bd->query($sql);
		
		$sql = "UPDATE usuarios SET flagRespContr = 1 WHERE id = ".$post['diretorID'];
		if (!$bd->query($sql)) {
			return "Erro ao salvar diretor. Tente novamente.";
		}
		
		$sql = "UPDATE usuarios SET flagRespContr = 2 WHERE id = ".$post['coordenadorID'];
		if (!$bd->query($sql)) {
			return "Erro ao salvar Coordenador. Tente novamente.";
		}
		
		return "Dados atualizados com Sucesso!";
	}
	
	/**
	 * Mostra interface de selecao de feriados
	 */
	function showPickFeriados(BD $bd) {
		$html = '';
		
		$sql = "SELECT * FROM feriados WHERE ano = ".date('Y', time());
		$res = $bd->query($sql);
		
		$html .= '
			<br />
			Considerar Segundas e Sextas antes e ap&oacute;s (respectivamente) feriados como sem expediente: 
			<input type="radio" name="ponto_fac" value="1" checked="checked"> Sim
			<input type="radio" name="ponto_fac" value="0"> N&atilde;o
			<br /><br />
			Clique <a onclick="selectAllHolidays()">aqui</a> para setar os seguintes feriados autom&aacute;ticamente:<br />
			1. Ano novo (01 de Janeiro)<br />
			2. Tiradentes (21 de Abril)<br />
			3. Dia do Trabalho (1 de Maio)<br />
			4. Revolu&ccedil;&atilde;o Constitucionalista de 1932 (9 de Julho)<br />
			5. Independ&ecirc;ncia do Brasil (7 de Setembro)<br />
			6. Nossa Senhora Aparecida (12 de Outubro)<br />
			7. Finados (2 de Novembro)<br />
			8. Dia da Consci&circ;ncia Negra (20 de Novembro)<br />
			9. Proclama&ccedil;&atilde;o da Rep&uacute;blica (15 de Novembro)<br />
			10. Nossa Senhora da Concei&ccedil;&atilde;o (8 de Dezembro)<br />
			11. Natal (25 de Dezembro)<br />
			<br /><br /> 
			<script type="text/javascript" src="scripts/jquery.ui.datepicker-pt-BR.js?r={$randNum}"></script>
			<script type="text/javascript">
				$(document).ready(function() {
					var ano = '.date('Y', time()).';
					
					hashFeriados = new Object();
					for (var i = 0; i < 12; i++) {
						hashFeriados[i + 1] = new Object(); 
					
						var dataIni = new Date();
						dataIni.setFullYear(ano, i, 1);
						var dataFim = new Date();
						
						var ultimoDia = 31;
						if (new Date(ano, i, ultimoDia).getMonth() != i) ultimoDia = 30;
						if (i == 1) {
							var isLeap = new Date(ano, 1, 29).getMonth() == 1;
							if (isLeap)
								ultimoDia = 29;
							else
								ultimoDia = 28;
						}
						
						dataFim.setFullYear(ano, i, ultimoDia);
						
						$("#c1").append(\'<div id="mes\'+i+\'" style="display: inline-block;"></div>\');
						$("#mes"+i).datepicker({
							minDate: dataIni,
							maxDate: dataFim,
							onSelect: function(dateText, inst) {
								var ponto_fac = $("input:radio[name=\'ponto_fac\']").filter(":checked").val();
								
								$.get("adm.php", {
									area: "dt",
									acao: "toggleFeriado",
									data: dateText,
									ponto_fac: ponto_fac
								}, function(d) {
									try {
										d = eval(d);
									}
									catch(e) {
										if (e instanceof SyntaxError) {
											alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
										}
									}
									
									if (d[0].success == true) {
										var dia = dateText.split("/");

										var novoFeriado = false;
										$("#mes"+(dia[1]-1)+" a").each(function() {
										
											if ($(this).html().localeCompare(parseInt(dia[0], 10)) == 0) {
												$(this).removeClass("ui-state-active");
												if (d[0].disable == true) {
													$(this).removeClass("Feriado");
													hashFeriados[parseInt(dia[1], 10)][parseInt(dia[0], 10)] = false;
												}
												else {
													novoFeriado = true;
													$(this).addClass("Feriado");
													hashFeriados[parseInt(dia[1], 10)][parseInt(dia[0], 10)] = true;
												}
												return true;
											}
										});
										
										$("#mes"+(dia[1]-1)+" a").each(function() {
											if (hashFeriados[parseInt(dia[1], 10)][parseInt($(this).html(), 10)] == true) {
												$(this).addClass("Feriado");
											}
												
										});
										
										if (novoFeriado) verificaTercaQuinta(dia[0], dia[1], dia[2]);
										
										$(".ui-state-hover").removeClass("ui-state-hover");
									}
									else {
										alert("Erro. Por favor, tente novamente.");
									}
								});
								
							}
						});
			
						
						if (i == 5) 
							$("#c1").append("<br />");
					}
					';
		if (count($res) > 0) {
			foreach ($res as $r) {
				$dia = date('j', $r['data']);
				$mes = date('n', $r['data']);
				$ano = $r['ano'];
				
				$html .= '
					hashFeriados['.$mes.']['.$dia.'] = true;';
			}
			
			$html .= '
					for (var j = 0; j < 12; j++) {
						$("#mes"+j+" a").each(function() {
							if (hashFeriados[j+1][parseInt($(this).html(), 10)] == true) {
								$(this).addClass("Feriado");
							}
						});
					}
			';
		}
		
		$html .= '
					$(".ui-datepicker-header").css("height", "21px");
					$(".ui-state-highlight").removeClass("ui-state-highlight");
					$(".ui-state-active").removeClass("ui-state-active");
					$(".ui-state-hover").removeClass("ui-state-hover");
			});
			</script>
			<script type="text/javascript" src="scripts/feriados.js?r={$randNum}"></script>	
			';
		
		return $html;
	}
	
	function toggleFeriado($data, $ponto_fac, BD $bd) {
		$dataArray = explode("/", $data);
		$timestamp = mktime(0, 0, 0, $dataArray[1], $dataArray[0], $dataArray[2]);
		
		$sql = "SELECT * FROM feriados WHERE ano = ".$dataArray[2]." AND data = ".$timestamp;
		$res = $bd->query($sql);
		if (count($res) > 0) {
			$sql = "DELETE FROM feriados WHERE ano = ".$dataArray[2]." AND data = ".$timestamp;
			$ret = $bd->query($sql);
			
			return json_encode(array(array('success' => $ret, 'disable' => true)));
		}
		else {
			$sql = "INSERT INTO feriados (ano, nome, data) VALUES (".$dataArray[2].", '', ".$timestamp.")";
			$ret = $bd->query($sql);
			
			return json_encode(array(array('success' => $ret, 'disable' => false)));
		}
		
		//return json_encode(array(array('success' => true, 'disable' => true)));
	}
	
	function showDocAtribObra(BD $bd) {
		$html = '
		<h3>Atribui&ccedil;&atilde;o de Documentos a Obras</h3>
		<br />Selecione os tipos de documentos que podem ser atribuidos a Obras:<br /><br />';
		
		$sql = "SELECT id, nome, nomeAbrv, atribObra FROM label_doc ORDER BY id";
		$docTypes = $bd->query($sql);
		
		if (count($docTypes) > 0) {
			$html .= '
				<form action="adm.php?area=dc&amp;acao=salvaAtr" method="POST">
				<table>
				<tr><td class="c">Tipo Documento</td><td class="c">Atribuivel?</td></tr>
				';
			
			foreach($docTypes as $t) {
				$checked = '';
				
				if ($t['atribObra'] == 1) {
					$checked = 'checked="checked"';
				}
				
				$html .= '<tr class="c"><td class="c"><b>'.$t['nome'].'</b></td><td class="c">';
				$html .= '<input type="checkbox" name="'.$t['nomeAbrv'].'" value="1" '.$checked.'>';
				$html .= '</td></tr>'; 
			}
			
			$html .= '<tr class="c"><td class="c" colspan="2"><input type="submit" value="Salvar"></td></tr></table></form>';
		}
		else {
			$html .= '<b>Nenhum tipo de Documento encontrado.</b>';
		}
		
		return $html;
	}
	
	function salvaDocAtribObra($post, BD $bd) {
		$sql = "SELECT id, nome, nomeAbrv, atribObra FROM label_doc ORDER BY id";
		$docTypes = $bd->query($sql);
		
		if (count($docTypes) > 0) {
			$sql = 'UPDATE label_doc SET atribObra = 0';
			$bd->query($sql);
			
			$sql = 'UPDATE label_doc SET atribObra = 1 ';
			$where = 'WHERE ';
			$algumAtribuivel = false;
			
			foreach($docTypes as $t) {
				if (isset($post[$t['nomeAbrv']]) && $post[$t['nomeAbrv']] == 1) {
					$algumAtribuivel = true;
					
					$where .= 'nomeAbrv = "'.$t['nomeAbrv'].'" OR ';
				}
			}
			
			if ($algumAtribuivel) {
				$where = rtrim($where, ' OR ');
				
				$sql .= $where;
				
				$ret = $bd->query($sql);
			}
			
			if ($ret == true) {
				return 'Dados atualizados com sucesso!';
			}
		}
		
		return 'N&atilde;o existe nenhum tipo de documento cadastrado no banco de dados.';
	}
	/**
	 * Funcao que trz o front da gerencia de alertas
	 * @param BD $bd
	 */
	function gerenciarAlertas(){
		requireSubModule(array("alerta","frontend"));
		$divParent = new HtmlTag("div", "gerenAlertas", "");
		$divDAO = new HtmlTag("div", "daoAlertas", "");
		$input = new HtmlTag("input", "autoUserNome", "");
		$input->setAttr(array("attr","name"), array("autocomplete","usuario.nome"));
		$salvar = new HtmlTag("a", "", "","[Salvar]");
		$salvar->setAttr(array("href","onclick"), array("#","javascript:salvarGerenciaAlertas();"));
		$input->setNext($salvar);
// 		$divDAO->setChildren($input);
		$span = new HtmlTag("span", "", "");
		$link = new HtmlTag("a", "", "", "[Remover]");
		$link->setAttr(array("href","onclick"), array("#","javascript:removerGerenciaAlertas();"));
		$span->setVar("content", "Para todos os usu&aacute;rios selecionados: ".$link->toString());
		$table = new HtmlTable("gerenAlertasUsers", "tablesorter", 1);
		$table->enableCheckbox();
		$table->setHead("Nome","c header ");
		//selecionando os users
		$u = new UsuarioAlerta();
		$users = $u->select();
		foreach ($users as $vu){
			$hidden = new HtmlTag("input", "", "");
			$hidden->setAttr(array("type","name","value"), array("hidden","usuarioAlertaID",$vu["uaid"]));
			$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","lid","gerenAlertasUsers_".($table->getNumLines())))));
			$table->appendLine($vu["nomeCompl"],"",$hidden);
		}
		$divDAO->setChildren($input);
		$divDAO->setChildren(new HtmlTag("br", "", ""));
		$divDAO->setChildren(new HtmlTag("br", "", ""));
		$divDAO->setChildren($span);
		$divDAO->setChildren(new HtmlTag("br", "", ""));		
		$divDAO->setChildren($table);
		return $divDAO->toString();
	}
?>
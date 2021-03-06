<?php
class SGO {
	private $bd;
	
	/**
	 * Construtor. Apenas inicializa a variavel de BD
	 */
	function __construct() {
		global $bd;
		$this->bd = $bd;
	}
	
	/**
	 * Retorna os scripts para sripts a serem incluidos
	 */
	function getHeadScripts() {
		return '<script type="text/javascript" src="scripts/sgo_cad.js?r={$randNum}"></script>
				<script type="text/javascript" src="scripts/sgo_bus.js?r={$randNum}"></script>
				<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>
				<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />
				<!--<script type="text/javascript" src="scripts/jquery.autocomplete.js?r={$randNum}"></script>
				<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />-->';
	}
	
	/**
	 * Monta tela para busca de obras
	 * @param HTML $html
	 * @param Array $conf
	 */
	function montaBuscaObra($html, $conf) {
		$html->head .= $this->getHeadScripts();
		$html->menu  = showMenu($conf['template_menu'],$_SESSION["perm"],2,$this->bd);
		$html->content[1] = showBuscaObrasForm();
		
		return $html;
	}
	
	/**
	 * Monta tela de formulario para cadastro de obra.
	 * @param HTML $html pagina HTML
	 * @param Array $conf
	 */
	function montaCadEmpreend($html, $conf) {
		$html->head .= $this->getHeadScripts();
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2,$this->bd);
		
		if(isset($_GET['docOrigemID']) && $_GET['docOrigemID'])
			$ofirID = $_GET['docOrigemID'];
		else
			$ofirID = 0;
		$html->content[1] = montaEmpreendCadForm($ofirID);
		
		return $html;
	}
	
	/**
	 * Monsta a tela para salvar a edicao de um empreendimento
	 * @param unknown_type $_POST
	 */
	function montaEmpreendSalva(){

	}
	
	/**
	 * Monta feedback para cadastro de obra
	 * @param HTML $html
	 * @param Array $conf
	 */
	function montaSalvaObra($html, $conf) {
		$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2,$this->bd);
		
		$html->setTemplate('templates/template_obra_mini.php');
		
		$html->content[1] = salvaNovaObra();
		
		return $html;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param HTML $html
	 * @param Array $conf
	 */
	function montaSalvaEmpreend(HTML $html, $conf, $perm, $empreendID, $post, $newEmpreend = false) {
		if(!$newEmpreend) {
			$empreend = new Empreendimento($this->bd);
			$empreend->load($empreendID, false, true);
			
			$html->setTemplate('templates/template_obra_mini.php');
			$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&amp;empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => '', 'name' => 'Salvar Empreendimento')), 'mini');
			$html->menu = showEmpreendActionMenu($empreend, null, array('voltar'));
			
			$html->content[1] = $empreend->showTopMenu();
			/*$html->content[3] = $empreend->showDocs();
			$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
			$html->content[5] = $empreend->showLivroDeObras();
			$html->content[6] = $empreend->showQuestionamentos();
			$html->content[7] = $empreend->showContratos();
			$html->content[8] = $empreend->showMedicoes();
			$html->content[9] = $empreend->showMensagens();
			$html->content[10] = $empreend->showNovoContratoForm();*/
			
			if(!$perm[7]) {
				$html->content[2] = verObraFeedback(array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Usuario nao tem permissoes suficientes para realizar esta acao.'));
			} else {
				$html->content[2] = showEmpreendSalva($empreend, $post).$empreend->showResumo();
			}
			
			//$html->content[11] = $empreend->showHistorico();
		} else {
			$html->path = showNavBar(array(array('url' => '', 'name' => 'Novo Empreendimento')));
			$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2,$this->bd);
			$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
			
			/*$html->content[1] = $empreend->showTopMenu();
			$html->content[3] = $empreend->showDocs();
			$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
			$html->content[5] = $empreend->showLivroDeObras();
			$html->content[6] = $empreend->showQuestionamentos();
			$html->content[7] = $empreend->showContratos();
			$html->content[8] = $empreend->showMedicoes();
			$html->content[9] = $empreend->showMensagens();*/
			
			if(!$perm[7]) {
				$html->content[1] = verObraFeedback(array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Usuario nao tem permissoes suficientes para realizar esta acao.'));
			} else {
				$html->content[1] = salvaNovoEmpreend()/*.$empreend->showResumo()*/;
			}
			
			//var_dump($html->content[1]);
		}
	
		return $html;
	}
	
	function montaSalvaResponsavelEtapa(HTML $html, $empreendID, $obraID, $tipoEtapa, $post) {
		global $bd;
				
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		$empreend->loadEtapas();
		
		$fb = $empreend->salvaEtapaResponsavel($tipoEtapa, $post);

		$html = $this->montaVerEmpreend($html, $empreendID);
		$html->content[2] = verObraFeedback($fb).$html->content[2];
	}
	
	/**
	 * Monta a tela para visualizar o feedback sobre o salvamento da fase
	 * @param HTML $html
	 * @param int $empreendID
	 * @param int $obraID
	 * @param int $etapaID
	 * @param int $faseTipoID
	 * @param array $post
	 */
	function montaSalvaFase($html, $empreendID, $obraID, $etapaID, $faseTipoID, $post){
		global $bd;
		
		if ($etapaID == 0) {
			$etapa = new Etapa(0, $empreendID, $obraID, $post['etapaTipoID']);
			$etapa->save();
			$etapaID = $etapa->getID();
		} 
		else {
			$etapa = new Etapa($etapaID, $empreendID, $obraID, $post['etapaTipoID']);
			$etapa->load();
		} 
		
		//carregar fase a ser atualizada ou inserida e carregar
		$fase = new Fase();
		$fase->load($etapaID, $faseTipoID);
		
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, false, true);
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		
		//$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => 'sgo.php?acao=ver&obraID='.$obra->get('id'), 'name' => $obra->nome), array('url' => '', 'name' => 'Salvar Fase')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();*/
		
		
		if (!checkPermission(98) && $fase->responsavelID != $_SESSION['id'] && $empreend->get('responsavel') != $_SESSION['id'] && $etapa->responsavelID != $_SESSION['id']) {
			$salvo = array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Voc&ecirc; n&atilde;o possu&iacute; permiss&atilde;o para salvar uma fase.');
			
			$html->content[2] = verObraFeedback($salvo);
			$html->content[2] .= $empreend->showResumo();
			//$html->content[11] = $empreend->showHistorico();
		}
		
		//para cada campo, setar o campo
		if (!isset($post['newDoc']) || $post['newDoc'] == 0) {
			foreach ($fase->dadosTipo['campos'] as $campo) {
				//print $campo['nomeAbrv'].' = '. $post[$campo['nomeAbrv']].'|'. $post[$campo['nomeAbrv'].'_observacao'].'<br>';
				$fase->setCampo($campo, $post[$campo['nomeAbrv']], $post[$campo['nomeAbrv'].'_observacao'], $empreendID);
			}
			
		} else {
			$doc;
			//var_dump($_POST);
			
			if (count($fase->dadosTipo['campos']) == 1) {
				$campo = $fase->dadosTipo['campos'][0];
			}
			else {
				$fb = array("success" => false, "errorFeedback" => 'Esta fase n&atilde;o aceita ITs. Contacte seu Administrador.');
				$html->content[2] = verObraFeedback($fb);
				$html->content[2] .= $empreend->showResumo();
				
				return $html;
			}
			
			salvaDados($_POST, $bd, $doc);
			//$fase->setCampo('docID', $doc, "");
			$fase->setCampo($campo, $doc, "");
			
			$doc = new Documento($doc);
			$doc->loadDados();
			$doc->update('empreendID', $empreendID);
			
			$empreend->logaHistorico('criarDoc', '', $doc->id, '', '');
			doLog($_SESSION['username'], 'Criou documento '.$doc->id.' no empreendimento '.$empreendID.' na fase '.$fase->get('id'));
		}
		
		$salvo = $fase->save();
		
		$html->content[2] = verObraFeedback($salvo);
		$html->content[2] .= $empreend->showResumo();
		//$html->content[11] = $empreend->showHistorico();
		
		return $html;
	}
	
	function salvaContrato($html, $post) {
		if (!checkPermission(85)) {
			showError(10);
			return;
		}
		
		global $bd;
		global $conf;
		$empreend = new Empreendimento($bd);
		$empreend->load($post['empreendID'], true, true);

		$html->setTemplate('templates/template_obra_mini.php');
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		
		$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$docProcesso = new Documento($post['_numProcContr']);
		$docProcesso->loadCampos();
		//var_dump($docProcesso);
		if ($docProcesso->dadosTipo['nomeAbrv'] != 'pr') {
			$salvo = array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Documento selecionado não é um processo. Ele &eacute; do tipo '.$docProcesso->dadosTipo['nome']);
		}
		else {
			$doc;
			
			foreach ($post as $campo => $val) {
				if ($campo == 'valorProj' || $campo == 'valorMaoObra' || $campo == 'valorMaterial' || $campo == 'valorTotal') {
					$val = str_replace('-', '', $val);
					$val = str_replace('.', '', $val);
					$val = str_replace(',', '.', $val);
					if ($val == "" || $val == 0) {
						$val = 0;
					}
					$post[$campo] = $val;
				}
			}

			if (!isset($post['valorProj']) || $post['valorProj'] == "") {
				$post['valorProj'] = 0;
			}
			if (!isset($post['valorMaoObra']) || $post['valorMaoObra'] == "") {
				$post['valorMaoObra'] = 0;
			}
			if (!isset($post['valorMaterial']) || $post['valorMaterial'] == "") {
				$post['valorMaterial'] = 0;
			}
			if (!isset($post['valorTotal']) || $post['valorTotal'] == "") {
				$post['valorTotal'] = 0;
			}
			
			if (!isset($post['prazoContr']) || $post['prazoContr'] == "") {
				$post['prazoContr'] = 0;
			} 
			
			if (!isset($post['prazoProjObra']) || $post['prazoProjObra'] == "") {
				$post['prazoProjObra'] = 0;
			}
						
			salvaDados($post, $bd, $doc);
			$doc = new Contrato($doc);
			$doc->loadCampos();

			$empresaID = 0;
			if (isset($post['empresaID']) && $post['empresaID'] != "") {
				$empresaID = $post['empresaID'];
			}
			
			// loga no histórico
			$empreend->logaHistorico('criarContr', '', $doc->id);
			doLog($_SESSION['username'], 'Criou contrato '.$doc->id.' no processo '.$post['_numProcContr']);
			
			$doc->update('empreendID', $post['empreendID']);
			//$doc->updateCampo('empresaID', $empresaID);

			$doc->anexarContrato($post['_numProcContr']);
			$salvo = array(
				'success' => true, 
				'errorNo' => 0, 
				'errorFeedback' => ''
			);
			
			$procPai = new Documento($post['_numProcContr']);
			
			if (isset($post['inclObras']) && $post['inclObras'] != "" && $post['inclObras'] != 'undefined') {
				$obras = explode(",", $post['inclObras']);
				
				$doc->salvaObras($obras);
				$procPai->salvaObras($obras);
				
			}
			else {
				$obras = $procPai->getObrasId();
				$doc->salvaObras($obras);
			}
			
			if (isset($post['inclRecursos'])) {
				$rec = json_decode($post['inclRecursos']);
				$recursos = array();
				foreach($rec as $r) {
					$r->valor = str_replace('-', '', $r->valor);
					$r->valor = str_replace('.', '', $r->valor);
					$r->valor = str_replace(',', '.', $r->valor);
					$recursos[$r->id] = $r->valor;
					if ($r->valor == "" || $r->valor < 0)
						$recursos[$r->id] = 0;
				}
				
				$doc->salvaRecursos($recursos);
			}
			
			$doc->salvaFunc($post);
			
			//var_dump($doc->docPaiID);
			//exit();
			
			$doc->salvaRespCPO();
		}
		
		$html->content[1] = $empreend->showTopMenu();
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$post['empreendID'], false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();
		$html->content[11] = $empreend->showHistorico();*/
		
		$msg = '';
		if ($salvo['success']) {
			$msg = 'Clique <a href="javascript:window.open(\'sgd.php?acao=geraREP&docID='.$doc->id.'&novaJanela=1\', \'doc\', \'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">aqui</a> para imprimir a Rela&ccedil;&atilde;o de Entrada de Protocolo.';
		}
		
		$html->content[2] = verObraFeedback($salvo, $msg);
		$html->content[2] .= $empreend->showResumo();
		
		return $html;
	}

	/**
	 * Monta a tela para visualizar uma obra (detalhes em nova janela)
	 * @param HTML $html
	 * @param Array $conf
	 */
	function montaVerObra($html, $conf, $mini = 0) {
		global $bd;
		
		$obra = new Obra($bd);
		$obra->load($_GET['obraID']);
		
		$empreend = new Empreendimento($bd);
		$empreend->load($obra->get('empreendID'));
		
		if (!$mini) $html->setTemplate('templates/template_obra_mini.php');
		else $html->setTemplate('templates/template_obra_resumo.php');
		
		if (!$mini) {
			$html->menu = showObraActionMenu($obra, $_SESSION['perm'], array(9, 10));
			$html->head .= '<script type="text/javascript" src="scripts/sgo_ver.js?r={$randNum}"></script>';
			$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => '', 'name' => $obra->nome)), 'mini');
		}
		
		$html->content[1] = showObraTopMenu($mini);
		$html->content[2] = showObraResumo($obra, $empreend);
		$html->content[3] = showObraDetalhes($obra, $empreend);
		//$html->content[4] = showRecursos($obra->recursos, $empreend->get('id'), true);
		//$html->content[5] = showObraEtapas($obra, $empreend);
		$html->content[4] = showObraEtapas($obra, $empreend);
		$html->content[5] = showRecursos($obra->recursos, $empreend->get('id'), true);
		$html->content[6] = showObraHistorico($obra, $empreend);
		$html->content[7] = showObraEditForm($obra, $obra->get('empreendID'));
		//$html->content[7] = $this->montaEditObra($html, $_GET['obraID'], $obra->get('empreendID'), $conf, null);
		//var_dump($this->montaEditObra($html, $_GET['obraID'], $obra->get('empreendID'), $conf, null));
		return $html;
	}
	
	/**
	 * Monta tela de exibicao de empreendimento
	 * @param HTML $html
	 * @param int $empreendID
	 */
	function montaVerEmpreend($html,$empreendID){
		global $bd;

		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, true, true);
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		// conteudo antigo
		/*$html->content[1] = $empreend->showTopMenu();
		$html->content[2] = $empreend->showResumo();
		$html->content[3] = "";//$empreend->showObras();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showHistorico();*/		
		
		$html->content[1] = $empreend->showTopMenu();
		$html->content[2] = $empreend->showResumo();
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();
		$html->content[11] = $empreend->showHistorico();*/
		
		return $html;
	}
	
	/**
	 * Monta formulario para edição de detalhes da obra
	 * @param HTML $html
	 * @param Array $conf
	 * @param Array $perm
	 */
	function montaEditObra($html, $obraID, $empreendID, $conf, $perm) {
		global $bd;
		
		$obra = new Obra($bd);
		$obra->load($obraID);
		
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, false, false);
		
		$html->setTemplate('templates/template_obra_cad.php');
		//unset($html->path);
		//$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => 'sgo.php?acao=ver&obraID='.$obra->get('id'), 'name' => $obra->nome), array('url' => '', 'name' => 'Editar Obra')), 'mini');
		//$html->menu = showObraActionMenu($obra, null, array('voltar'));
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver.js?r={$randNum}"></script>';
		
		$html->content[1] = '';
		$html->content[2] = showObraEditForm($obra, $empreendID);
		
		return $html;
	}
	
	function montaEditEmpreend($html, $empreendID, $perm){
		$empreend = new Empreendimento($this->bd);
		$empreend->load($empreendID, false, true);
		
		$html->setTemplate('templates/template_obra_mini.php');
		
		$html->content[1] = $empreend->showTopMenu();
		$html->content[2] = showEmpreendEditForm($empreend);
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();
		$html->content[11] = $empreend->showHistorico();*/
		
		$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => '', 'name' => 'Editar Empreendimento')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, null, array('voltar'));
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver.js?r={$randNum}"></script>';
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		return $html;
		
	}
	
	function montaEditEquipe($html, $empreendID, $perm){
		$empreend = new Empreendimento($this->bd);
		$empreend->load($empreendID, false, false);
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => '', 'name' => 'Editar Equipe')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, null, array('voltar'));
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver.js?r={$randNum}"></script>';
		$html->head .= '<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>';
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = showEquipEdit($empreend);
		$html->content[2] = '<script type="text/javascript">$(document).ready(function() { $("#c2").hide() });</script>';
		
		return $html;
		
	}
	
	function montaSalvaEquipe($html, $empreendID, $perm, $post) {
		$ret = array(array('success' => false));
	
		$equipe = explode(',', $post['equipe']);
		
		$sql = "SELECT userID FROM obra_equipe WHERE empreendID = {$empreendID}";
		$res = $this->bd->query($sql);
		
		if (count($res) > 0) {
			foreach ($res as $uid) {
				$userID[$uid['userID']] = true;
			}
		}
			
		$sql = "DELETE FROM obra_equipe WHERE empreendID = ".$empreendID;
		$this->bd->query($sql);
		
		foreach($equipe as $e) {
			if ($e == '0' || $e == "") continue;
			if(!isset($userID[$e])){
				doLog($_SESSION['username'], "Adicionou usuario {$e} a equipe do empreend {$empreendID}");
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,user_targetID) VALUES ({$empreendID}, ".time().", {$_SESSION['id']}, 'addEquipe', {$e})");
			} elseif (isset($userID[$e])) {
				unset($userID[$e]);
			}
			
			$sql = "INSERT INTO obra_equipe (empreendID, userID) VALUES (".$empreendID.", ".$e.")";
			$this->bd->query($sql);
		}
		
		if (count($res) > 0) {
			foreach ($userID as $uid) {
				doLog($_SESSION['username'], "Removeu o usuario {$uid} da equipe do empreendimento {$empreendID}");
			}
		}
		
		$ret = array(array('success' => true));
		
		return $ret;
	}

	/**
	 * Chama o metodo para salvar obra apos cadastro
	 * @param Html $html
	 * @param Array $conf
	 * @param Array $perm
	 * @param int $obraID
	 * @param Array $post
	 */
	function montaObraSalva($html, $conf, $perm, $obraID, $empreendID, $post){
		
		$obra = new Obra($this->bd);
		$obra->load($obraID);
		$empreend = new Empreendimento($this->bd);
		$empreend->load($empreendID, false, true);
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		
		$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => 'sgo.php?acao=ver&obraID='.$obra->get('id'), 'name' => $obra->nome), array('url' => '', 'name' => 'Salvar Obra')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();
		$html->content[11] = $empreend->showHistorico();*/
		
		if(!$perm[9]) {
			$html->content[2] = verObraFeedback(array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Usuario nao tem permissoes suficientes para realizar esta acao.'));
		} else {
			$html->content[2] = salvaObra($obra, $empreend, $post);
		}
		
		$html->content[2] .= $empreend->showResumo();
		
		return $html;
	}
	
	function salvaRecAJAX($recID, $empreendID, $obraID, $rec_dados, $bd){
		$rec = new Recurso($bd);
		
		if($recID)
			$rec->load($recID);
		
		$rec->montante = round(str_ireplace(',','.', str_ireplace('.', '', $rec_dados['montante'])),2); 
		$rec->origem = $rec_dados['origem'];
		$rec->prazo = trataData($rec_dados['prazo']);
		$rec->justificativa = $rec_dados['justificativa'];
			
		if(!$recID){
			$ret = $rec->insertRecursoInEmpreend($empreendID);
			doLog($_SESSION['username'], 'Editou recurso '.$recID.' do empreendimento '.$empreendID);
		} else {
			$ret =  $rec->save();
			doLog($_SESSION['username'], 'Cadastrou recurso financeiro de '.$rec->montante.' no empreendimento '.$empreendID);
		}
		$html = showRecursosTemplate();
		//var_dump($rec->montante);
		if($_SESSION['perm'][21])
			$editLink = str_replace('{$rec_id}', $rec->get('id'), $html['editar_link']);
		else
			$editLink = '';
			
		if($rec->prazo === 'NULL')
			$prazo = '';
		else
			$prazo = date("j/n/Y",$rec->prazo);
		
		$ret['html'] = str_ireplace(array('{$rec_id}'    ,'{$rec_montante}','{$rec_origem}','{$rec_prazo}' ,'{$rec_justif}'    ,'{$rec_mod_user}'           ,'{$rec_mod_data}'                      ,'{$editar_link}'),
									array($rec->get('id'),number_format((float)$rec->montante, 2, ',', '.')   ,$rec->origem   ,$prazo         ,$rec->justificativa, $rec->respUser['nomeCompl'],date("j/n/Y G:i",$rec->dataUltimaModif), $editLink),
									$html['recurso_tr']);
		$ret['newResponsavelName'] = $rec->respUser['nomeCompl'];
		$ret['lastModDate'] = date("j/n/Y G:i",$rec->dataUltimaModif);
		$ret['valor'] = number_format((float)$rec->montante, 2, ',', '.');
		
		return $ret;
	}
	
	function salvaEtapaAJAX($obraID, $etapa_dados) {
		$etapa = new Etapa(0, $obraID, $etapa_dados['tipoID'], $etapa_dados['procID']);
		$etapa->responsavel = $etapa_dados['respID'];
		$ret = $etapa->save();
		$ret['etapaID'] = $etapa->getID();
		
		return $ret;
	}
	
	/**
	 * Salva a mensagem
	 * @param html $html
	 * @param int $empreendID id do empreendimento
	 * @param array $perm array de permissões
	 * @param $post dados passados por post
	 * @param BD $bd
	 */
	function salvaMsg(html $html, $empreendID, $perm, $post, $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, true, true);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&amp;empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => '', 'name' => 'Salvar Mensagem')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, null, array('voltar'));
		
		// acentuação
		$post['conteudo'] = SGEncode($post['conteudo'], ENT_QUOTES, null, false);
		$post['assunto'] = SGEncode($post['assunto'], ENT_QUOTES, null, false);
		
		// remove quebra de linhas do campo de conteudo e substitui por <br />s
		$post['conteudo'] = str_ireplace(chr(10), '<br />', $post['conteudo']); 
		
		// é uma resposta a outra mensagem ? se sim, seta $respTo para o ID da mensagem a qual ela irá responder
		$respTo = 0;
		if (isset($post['replyTo']) && ($post['replyTo'] != '0' && $post['replyTo'] != ""))
			$respTo = $post['replyTo'];
		
		$anexarFB = $this->doUploadAnexos($empreendID);	
	
		//var_dump($anexarFB);
		$files = $anexarFB['arquivos'];
		$anexos = $anexarFB['anexos'];
		
		$html->content[1] = '';
		
		if ((isset($files['success']) && count($files['success']) > 0) || (isset($files['failure']) && count($files['failure']) > 0)) {
			$html->content[1] .= 'Relat&oacute;rio de Anexa&ccedil;&atilde;o de arquivos:<br />';
		}
		
		// percorre arquivos anexados com sucesso
		foreach($files['success'] as $file) {
			$html->content[1] .= '<i>'.$file.'</i>: Arquivo foi anexado com sucesso.<br />';	
		}
		// percorre arquivos com falha em anexação
		foreach($files['failure'] as $file) {
			$html->content[1] .= '<i>'.$file['name'].'</i>: Erro ao anexar arquivo (Erro '.$files['errorID'].').<br />';
		}
		
		if ((isset($files['success']) && count($files['success']) > 0) || (isset($files['failure']) && count($files['failure']) > 0)) {
			$html->content[1] .= '<br /><br />';
		}
		
		// gera array de anexos
		$listaAnexos = implode(",", $anexos);
		
		// insere mensagem nova no bd
		$sql = "INSERT INTO obra_mensagem (usuarioID, empreendID, replyTo, data, assunto, conteudo, anexos) 
				VALUES (".$_SESSION['id'].", $empreendID, $respTo, ".time().", '".$post['assunto']."', '".$post['conteudo']."', '".$listaAnexos."')";
		$msgID = $this->bd->query($sql,null,true);
		if ($msgID) {
			//return array(array('success' => true));
			$html->content[1] .= 'Mensagem salva com sucesso.';
			if($respTo == 0){
				doLog($_SESSION['username'], "Adicionou nova mensagem ao empreendimento {$empreendID} ({$post['assunto']})");
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,msg_targetID) VALUES ({$empreendID}, ".time().", {$_SESSION['id']}, 'newMensagem', {$msgID})");
			} else {
				doLog($_SESSION['username'], "Respondeu mensagem {$respTo} no empreendimento {$empreendID}");
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,msg_targetID) VALUES ({$empreendID}, ".time().", {$_SESSION['id']}, 'respMensagem', {$respTo})");
			}
		}
		
		return $html;
	}
	
	/**
	 * Realiza upload de arquivos para mensagem/empreendimento
	 * @param int $empreendID
	 */
	function doUploadAnexos($empreendID){
		$success = array();
		$failure = array();
		$anexo = array();
		
		for ($i = 1; isset($_FILES["arq".$i]); $i++) {
			
			if ($_FILES["arq".$i]['name'] == '')
				continue;
			
			if($_FILES["arq".$i]['error'] > 0 && $this->id == 0){
				$failure[] = array("name" => $_FILES["arq".$i]['name'], "errorID" => $_FILES["arq".$i]['error']);
				continue;
			}
			
			$fileName = "[".$empreendID."]".$_FILES["arq".$i]['name'];
			$fileName = stringBusca($fileName,true);
		
			
			if (file_exists("files/msgs/" . $fileName)){
				//tratamento de nomes duplicados
				$j = 2;
				
		    	do  {//verifica se o nome do documento ja existe, se sim, adiciona (j) estilo windows para nao sobrescrever
			    	$oldName = explode(".", $fileName);
					
					if($oldName[count($oldName)-2])
						$oldName[count($oldName)-2] .= "(".$j.")";
					else
						$oldName[count($oldName)-1] .= "(".$j.")";
					
					$newName = implode(".", $oldName);
		    		$j++;
		    	} while (file_exists("files/msgs/".$newName));
		    	
		    	move_uploaded_file($_FILES["arq".$i]["tmp_name"], "files/msgs/" . $newName);
		    	$success[] = $newName;
		    	$anexo[] = $newName;
				// TODO: logar no histórico, quando houver
		    			    	
		    } else {
		    	move_uploaded_file($_FILES["arq".$i]["tmp_name"], "files/msgs/" . $fileName);
				$success[] = $fileName;
				$anexo[] = $fileName;
				// TODO: logar no histórico, quando houver
			}
		}
		$files['success'] = $success;
		$files['failure'] = $failure;
		$ret = array("arquivos" => $files, "anexos" => $anexo);
		return $ret;
	}

	function montaPlanejamento($id, $procIT) {
		global $bd;
		$empreend = new Empreendimento($bd);
		$empreend->load($id, true, true);

		return $empreend->showEtapa(1, $procIT, $bd); //. "teste!1 ".$id;
		
	}
	
	function montaSalvaITSuplementar(html $html, $post, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($post['empreendID'], false, true);
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		
		//$html->path = showNavBar(array(array('url' => 'sgo.php?acao=verEmpreend&empreendID='.$empreend->get('id'), 'name' => $empreend->get('nome')), array('url' => 'sgo.php?acao=ver&obraID='.$obra->get('id'), 'name' => $obra->nome), array('url' => '', 'name' => 'Salvar Fase')), 'mini');
		$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		/*$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$post['empreendID'], false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();*/
		
		
		
		//para cada campo, setar o campo
		$msg = null;
		$doc;
		salvaDados($post, $bd, $doc);
			
		$doc = new Documento($doc);
		$doc->loadDados();
		$salvo = array("success" => $doc->update('empreendID', $post['empreendID']));
		
		if ($salvo['success']) {
			$empreend->logaHistorico('criarDoc', '', $doc->id);
			doLog($_SESSION['username'], "Criou IT ".$doc->id." no empreendimento ".$post['empreendID']);
		}
		
		//$salvo = $fase->save();
		//var_dump($fase);exit();
		
		$html->content[2] = verObraFeedback($salvo, $msg);
		$html->content[2] .= $empreend->showResumo();
		//$html->content[11] = $empreend->showHistorico();
		
		//var_dump($fase);exit();
		return $html;
	}
	
	function getObrasGuardaChuva($procID) {
		global $bd;
		$proc = new Documento($procID);
		$proc->loadCampos();
		
		if ($proc->labelID != 1)
			return 1;
			
		if ($proc->campos['guardachuva'] != 1)
			return 2;
			
		$sql = "SELECT empreendID FROM guardachuva_empreend WHERE docID = ".$procID;
		$empreends = $bd->query($sql);
		
		if (count($empreends) <= 0)
			return 3;
		
		$retorno = array();
		$retorno['empreend'] = array();
		$retorno['financas'] = array();
		$retorno['obras'] = array();
		
		foreach($empreends as $e) {
			$eID = $e['empreendID'];
			$sql = "SELECT nome FROM obra_empreendimento WHERE id = ".$eID;
			$nome = $bd->query($sql);
			if (count($nome) <= 0)
				continue;
				
				
			$nome = $nome[0]['nome'];
			$retorno['empreend'][] = array('id' => $eID, 'nome' => $nome);
			
			$sql = "SELECT id, nome FROM obra_obra WHERE empreendID = ".$eID;
			$obras = $bd->query($sql);
			
			$retorno['financas'][$eID] = array();
			$retorno['obras'][$eID] = array();
			if (count($obras) > 0) {
				foreach ($obras as $o) {
					$retorno['obras'][$eID][] = array('id' => $o['id'], 'nome' => $o['nome']);
				}
			}
			
			$sql = "SELECT id, origem FROM obra_rec WHERE empreendID = ".$eID;
			$financas = $bd->query($sql);
			
			if (count($financas) > 0) {
				foreach ($financas as $f) {
					$retorno['financas'][$eID][] = array('id' => $f['id'], 'origem' => $f['origem']);
				}
			}
			
		}
		
		return $retorno;
	}
	
	function salvaEditContrObra($docID, $post) {
		$doc = new Contrato($docID);
		$doc->loadCampos();
		
		return $doc->salvaEditObras($post['obras']);
	}
	
	function salvaEditContrRec($docID, $post) {
		$doc = new Contrato($docID);
		$doc->loadCampos();
		
		//$rec = json_decode($post['recursos']);
		
		if (isset($post['recursos'])) {
			$rec = json_decode($post['recursos']);
			$recursos = array();
			foreach($rec as $r) {
				$r->valor = str_replace('-', '', $r->valor);
				$r->valor = str_replace('.', '', $r->valor);
				$r->valor = str_replace(',', '.', $r->valor);
				$recursos[$r->id] = $r->valor;
				if ($r->valor == "" || $r->valor < 0) {
					$recursos[$r->id] = 0;
				}	
			}
				
			return $doc->salvaEditRec($recursos);
		}
		else {
			return array(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
		}
		//return $doc->salvaEditRec($post['recursos']);
	}
	
	/**
	 * Adiciona um aditivo a um determinado campo de um determinado contrato
	 * @param $contratoID ID do contrato
	 * @param $campo Campo a ser aditivado
	 * @param $valor Valor do aditivo
	 * @param $motivo Moptivo do aditivo ja HTMLEntit'ed 
	 */
	function aditivaContrato($contratoID, $campo, $valor, $motivo){
		$contrato = new Contrato($contratoID);
		$contrato->loadCampos();
		return $contrato->novoAditivo($campo,$valor,$motivo);
	}
	
	/**
	 * Edita um aditivo de um de determinado contrato
	 * @param $contratoID ID do contrato
	 * @param $aditivoID ID do aditivo
	 * @param $campo campo cujo aditivo sera editado
	 * @param $valor novo valor do aditivo
	 * @param $motivo novo motivo do aditivo
	 */
	function editarAditivoContrato($contratoID, $aditivoID, $campo, $valor, $motivo) {
		$contrato = new Contrato($contratoID);
		$contrato->loadCampos();
		return $contrato->editarAditivo($aditivoID,$campo,$valor,$motivo);
	}
	
	
	
	function montaVerFase($empreendID, $obraID, $etapaTipoID, $faseTipoID, $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		if ($obraID != '0' && $obraID != 0) {
			$obra = new Obra($bd);
			$obra->load($obraID);
			// TODO: montar código para exibição de fases para etapas de obras
		}
		else {
			$empreend->loadEtapas();
			$etapas = $empreend->get('etapa'); 
			
			$etapa = null;
			$fases = null;
			foreach ($etapas as $e) {
				if ($e->tipo['id'] == $etapaTipoID) {
					$fases = $e->fases;
					$etapa = $e;
				}
			}
			
			if (!$fases) {
				return "Etapa não encontrada";
			}
			
			$fase = null;
			foreach ($fases as $f) {
				//var_dump($f->tipoID);
				//var_dump($f->dadosTipo['id']);
				if ($f->dadosTipo['id'] == intval($faseTipoID)) {
					$fase = $f;
					//var_dump('q');
					//var_dump($faseTipoID);
				}
			}
			
			if ($fase) {
				$html = $fase->showResumo(0);
				
				$html = str_replace('{$obraID}', $etapa->get('obraID'), $html);
				$html = str_replace('{$etapa_id}', $etapa->getID(), $html);
				$html = str_replace('{$etapa_tipoID}', $etapaTipoID, $html);
				$html = str_replace('{$empreendID}', $empreendID, $html);
				
				return $html;
			}
		}
		//return $obraID;
		return "Fase de Obras ainda não implementada.";
	}
	
	function montaVerRespEtapa($empreendID, $obraID, $etapaTipoID, $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$empreend->loadEtapas();
		$etapas = $empreend->get('etapa'); 
			
		$etapa = null;
		foreach ($etapas as $e) {
			if ($e->tipo['id'] == $etapaTipoID) {
				$etapa = $e;
			}
		}
			
		if (!$etapa) {
			return "Etapa não encontrada.";
		}
			
			
		$responsavelID = 0;
		if ($etapa->tipo['refEmpreend'] == 1) {
			$responsavelID = $this->responsavel;
		}
		else {
			$obra = $etapa->get('obraID');
			$o = new Obra($bd);
			$o->load($obra);
			//foreach ($obras as $o) {
				if ($o->get('id') == $obra)
					$responsavelID = $o->responsavel;
			//}
				
		}
			
		return $etapa->showResponsaveisInterface($responsavelID);
	}
	
	function showItSuplementarForm($empreendID, BD $bd) {
		global $conf;
		$it_supl = '<p style="text-align: center; font-size: 14pt; color:#BE1010;">Informa&ccedil;&atilde;o T&eacute;cnica Suplementar</p>';
		$it_supl .= '<form accept-charset="'.$conf['charset'].'" id="formFase" action="sgo.php?acao=novaITsuplementar" enctype="multipart/form-data" method="post">';
		//$it_supl .= '<input type="hidden" id="empreendID" name="empreendID" value="'.$empreendID.'">';
		$it_supl .= str_replace('{$empreendID}', $empreendID, showForm('novo_it', 'it', 1, $bd));
		$it_supl .= '<center><input type="submit" value="Salvar"></center>';
		$it_supl .= '</form>';
		
		return $it_supl;
	}
	
	function montaVerDocsPend(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		return $empreend->showDocs();
	}
	
	function montaVerFinancas(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, true, false);
		
		$html->content[1] = $empreend->showTopMenu();
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		return showRecursos($empreend->get('recursos'),$empreendID, false);
	}
	
	function montaVerLivroObras(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		
		return $empreend->showLivroDeObras();
	}
	
	function montaVerQuestionamentos(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		
		return $empreend->showQuestionamentos();
	}
	
	function montaVerContrato(HTML $html, $empreendID, BD $bd, $restaurar = false) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, true, true);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		$html->content[3] = $empreend->showNovoContratoForm($restaurar);
		/*$html->content[1] = $empreend->showTopMenu();
		$html->content[2] = $empreend->showResumo();
		$html->content[3] = $empreend->showDocs();
		$html->content[4] = showRecursos($empreend->get('recursos'),$empreendID, false);
		$html->content[5] = $empreend->showLivroDeObras();
		$html->content[6] = $empreend->showQuestionamentos();
		$html->content[7] = $empreend->showContratos();
		$html->content[8] = $empreend->showMedicoes();
		$html->content[9] = $empreend->showMensagens();
		$html->content[10] = $empreend->showNovoContratoForm();
		$html->content[11] = $empreend->showHistorico();*/
		
		return $empreend->showContratos();
	}
	
	function montaVerMedicoes(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		
		return $empreend->showMedicoes();
	}
	
	function montaVerMensagens(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		
		return $empreend->showMensagens();
	}
	
	function montaVerHistorico(HTML $html, $empreendID, BD $bd) {
		$empreend = new Empreendimento($bd);
		$empreend->load($empreendID, true, true);
		
		$html->head .= '<script type="text/javascript" src="scripts/sgo_ver_empreend.js?r={$randNum}"></script>';
		//$html->menu = showEmpreendActionMenu($empreend, $_SESSION['perm'], array(7,8));
		
		$html->setTemplate('templates/template_obra_mini.php');
		$html->path = showNavBar(array(array('url' => '', 'name' => $empreend->get('nome'))), 'mini');
		$html->head .= '<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />';
		
		$html->content[1] = $empreend->showTopMenu();
		
		return $empreend->showHistorico();
	}
}
?>
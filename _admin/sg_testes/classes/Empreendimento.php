<?php
define('DEBUG', false);

class Empreendimento {
	private $id;
	private $nome;
	private $justificativa;
	private $local;
	private $recursos;
	private $descricao;
	
	private $unOrg;
	private $solicitante;
	
	private $estado;
	
	private $obras;
	
	private $etapa;
	
	private $docs;
	
	private $historico;
	
	private $responsavel;
	
	/**
	 * Equipe que trabalha neste empreendimento
	 * array de ids de usuários
	 * @var array int
	 */
	private $equipe;
	
	public $ofir = 0;
	
	private $bd;
	
	/**
	 * Construtor da classe
	 * @param BD $bd
	 */
	function __construct($bd) {
		$this->id = 0;
		$this->nome = '';
		$this->justificativa = '';
		$this->local = '';
		$this->recursos = array();
		$this->descricao = '';
		$this->estado = array('id' => 0, 'obras_proj' => 0, 'obras_exec' => 0, 'label' => '');
		
		$this->unOrg = array('compl' => '', 'id' => '', 'nome' => '', 'sigla' => '');
		$this->solicitante = array('nome' => '', 'depto' => '', 'email' => '', 'ramal' => '');
		
		$this->obras = array();
		$this->etapa = array();
		$this->historico = array();
		
		$this->responsavel = 0;
		
		$this->bd = $bd;
	}
	
	/**
	 * Salva novo empreendimento no BD
	 * @return boolean $success
	 */
	function saveNew() {
		$this->getVars();
		
		//tratamento UnOrg
		if(preg_match("|[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}|", $this->unOrg['compl'], $matches)) {
			$this->unOrg['id'] = substr($this->unOrg['compl'], 0, 17);
			$this->unOrg['nome'] = substr($this->unOrg['compl'], 18, strpos($this->unOrg['compl'], ' ('));
			
		}
		
		//monta a consulta SQL
		$sql = "INSERT INTO obra_empreendimento (nome, nomeBusca, unOrg, justificativa, local, descricao, solicNome, solicDepto, solicEmail, solicRamal, ofirID, responsavelID)
		VALUES ('{$this->nome}', '".stringBusca(SGDecode($this->nome))."','{$this->unOrg['id']}','{$this->justificativa}','{$this->local}','{$this->descricao}','{$this->solicitante['nome']}','{$this->solicitante['depto']}','{$this->solicitante['email']}','{$this->solicitante['ramal']}','{$this->ofir}','{$this->responsavel}')";
				
		$insert = $this->bd->query($sql); 
		if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
		
		//se for inserido com sucesso 
		if($insert) {
			//consulta o empreendimento
			$sql = "SELECT id FROM obra_empreendimento WHERE nome = '{$this->nome}' AND unOrg = '{$this->unOrg['id']}'";
			if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
			
			$selectID = $this->bd->query($sql);
			if(count($selectID) == 1){
				$this->id = $selectID[0]['id'];
				
			//cria a etapa padrao (Geral)
			//$etapa = new Etapa($this->id, 0, $this->bd);
			//$etapa->save();
			
			//adicao de fase/documento oficio de requisicao
			if(isset($_POST['ofir']) && $_POST['ofir'] > 0) {
			//	$etapa->addFase(1,1,$_POST['ofir']);
				
				$ofir = new Documento($_POST['ofir']);
				$ofir->loadCampos();
				atribEmpreend($_POST['ofir'], $this->id, $this->bd, 0);
				$ofir->update("OwnerArea", null);
				$ofir->update("ownerID", 0);
				//if(isset($_POST['abrirSAP']) && $_POST['abrirSAP'] > 0)
				//	$despOfir = showDespStatus($ofir, array('para' => 'Protocolo' ,"outro" => '', 'funcID' => '_todos', 'despExt' => '', 'despacho' => 'Despachado automaticamente pelo sistema para anexar ao processo referente a obra '.$this->nome),'hideFB');
			}
			
			//adicao de fase/documento solicitacao de abertura de processo
			if(isset($_POST['abrirSAP']) && $_POST['abrirSAP'] > 0) {
				$doc = new Documento(0);
				$doc->dadosTipo['nomeAbrv'] = 'sap';
				$doc->loadTipoData();
				
				//seleciona o attr da tabela
				$sql = 'SELECT numero_sap FROM '. $doc->dadosTipo['tabBD']. ' WHERE anoE='.date("Y"). ' ORDER BY numero_sap DESC LIMIT 1';
				$r2 = $this->bd->query($sql);
				//incrementa o valor do attr e guarda no valor do campo
				if(isset($r2[0]['numero_sap']))
					$numero = $r2[0]['numero_sap'];
				else 
					$numero = 0;
				//seta os campos a serem preenchidos
				$doc->campos['numero_sap']  = ($numero + 1);
				$doc->campos['anoE'] = date("Y");
				$doc->campos['contato'] = $_SESSION['id'];
				$doc->campos['unOrgIntSAP'] = $this->unOrg['compl'];
				$doc->campos['pessoaIntSAP'] = '';
				$doc->campos['assunto'] = SGEncode(strtoupper('Planejamento '.SGDecode($this->nome)), ENT_QUOTES, null, false);
				$doc->campos['tipoProc'] = 'plan';
				$doc->campos['justificativa'] = $this->justificativa; 
				// associa esta nova sap ao empreendimento
				$doc->empreendID = $this->id;
				//salva novo documento
				$doc->salvaCampos();
				$doc->salvaDoc(0);
				if (!$doc->doLogHist($_SESSION['id'],"","",'','criacao','','')) {
					return array("success" => false, "errorNo" => 2, "errorFeedback" => "Erro ao salvar o despacho de criacao.");
				}
				//gera PDF da SAP
				geraPDF($doc->id);
				//gera feedback
				$despStatus = showDespStatus($doc, array('para' => 'Protocolo' ,"outro" => '', 'funcID' => '_todos', 'despExt' => '', 'despacho' => 'Documento gerado automaticamente para abertura de processo de planejamento referente a obra '.$this->nome.'. Favor providenciar a abertura de processo para esta obra e anexar este documento a ele assim como o of&iacute;cio correspondete (se houver)'),'hideFB');
				//adiciona fase: solicitacao de abertura de processo de contratacao
				//$etapa->addFase(1,2,$doc->id);
				
				//Adiciona os recursos
				/*if(isset($_POST['recursos']) && $_POST['recursos'] && isset($_POST['montanteRec']) && $_POST['montanteRec']){
					//adicao de recurso
					$rec = new Recurso($this->bd);
					$insert = $rec->insertRecursoInEmpreend($this->id);
					if (!$insert['success']) return array("success" => false, "errorNo" => 2, "errorFeedback" => "Erro ao salvar recursos no BD".$insert['errorFeedback']);
					$this->getRecursos();
				}*/
			}
				return array('success' => true, 'errorID' => 0, 'errorFeedback' => '');
			}
		}
		return array('success' => false, 'errorID' => 1, 'errorFeedback' => 'Erro ao salvar novo Empreendimento/duplicidade encontrada');
	}
	
	/**
	 * Salva dados do empreendimento
	 * @return boolean true se consulta bem sucedida, false caso contrario
	 */
	function save() {
		//se ja tiver carregado alguma obra
		if($this->id > 0){
			//atualiza o registro no BD
			if (!isset($this->responsavel) || $this->responsavel == null) {
				$this->responsavel = 0;
			} else {
				$respID = $this->bd->query("SELECT responsavelID FROM obra_empreendimento WHERE id = {$this->id}");
				if($this->responsavel != $respID[0]['responsavelID']) {
					$this->logaHistorico('editarResp', $this->responsavel);
					doLog($_SESSION['username'], "Modificou o responsavel pelo empreendimento {$this->nome} de {$this->responsavel} para {$respID}");
				}
			}
			
			
			$sql = "UPDATE obra_empreendimento SET nome='{$this->nome}', nomeBusca='".stringBusca(SGDecode($this->nome))."', unOrg='{$this->unOrg['id']}', justificativa='{$this->justificativa}', local='{$this->local}', descricao='{$this->descricao}', solicNome='{$this->solicitante['nome']}', solicDepto='{$this->solicitante['depto']}', solicEmail='{$this->solicitante['email']}', solicRamal='{$this->solicitante['ramal']}', ofirID='{$this->ofir}', responsavelID='{$this->responsavel}' WHERE id = $this->id";
			if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
			
			//realiza consulta ao BD
			$update = $this->bd->query($sql);
			if($update) {
				return array('success' => true, 'errorID' => 0, 'errorFeedback' => '');
				doLog($_SESSION['username'], 'Editou o empreendimento');//TODO especificar campos modificados
			}
		}
		return array('success' => false, 'errorID' => 4, 'errorFeedback' => 'Erro ao salvar empreendimento.');
	}
	
	/**
	 * Carrega os dados de uma obra
	 * @param int $id
	 * @param boolean $recurso
	 * @param boolean $load_obras
	 */
	function load($id, $recurso = false, $load_obras = false) {
		//se houver empreendimento carregado
		if($id > 0){
			//constroi consulta sql
			$sql = "SELECT * FROM obra_empreendimento WHERE id=$id";
			if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
			//realiza a consulta
			$empr = $this->bd->query($sql);
			if(count($empr) == 1 && $empr) {
				//se retornar exatamente um resultado nao vazio
				//faz as atribuicoes
				$this->id = $id;
				$this->nome = $empr[0]['nome'];
				$this->justificativa = $empr[0]['justificativa'];
				$this->local = $empr[0]['local'];
				$this->descricao = $empr[0]['descricao'];
				$this->ofir = $empr[0]['ofirID'];
				$this->responsavel = $empr[0]['responsavelID'];
			
								
				//conulta a unidade da obra
				$sql = "SELECT id,nome,sigla FROM unidades WHERE id='".$empr[0]['unOrg']."'";
				if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
				//consulta unidade
				$unOrg = $this->bd->query($sql);
				if (count($unOrg) && $unOrg){//atribui unidade
					$this->unOrg['compl'] = $unOrg[0]['id'].' - '.$unOrg[0]['nome'].' ('.$unOrg[0]['sigla'].')';
					$this->unOrg['id']    = $unOrg[0]['id'];
					$this->unOrg['nome']  = $unOrg[0]['nome'];
					$this->unOrg['sigla'] = $unOrg[0]['sigla'];
					
				} else {
					$this->unOrg = $empr[0]['unOrg'];
				}
				$this->solicitante = array('nome' => $empr[0]['solicNome'] ,'depto' => $empr[0]['solicDepto'] ,'email' => $empr[0]['solicEmail'] ,'ramal' => $empr[0]['solicRamal']);
				//consulta obras referentes a esse empreendimento
				if($load_obras) {
					$sql="SELECT id FROM obra_obra WHERE empreendID = ".$this->id;
				
					$obrasID = $this->bd->query($sql);
				
					foreach ($obrasID as $oID) {
						$obra = new Obra($this->bd);
						$obra->load($oID['id']);
						$this->obras[] = $obra;
					}
				}
				if($recurso) {
					$sql = "SELECT id FROM obra_rec WHERE empreendID = {$this->id}";
					
					$recID = $this->bd->query($sql);
					
					foreach ($recID as $rID) {
						$rec = new Recurso($this->bd);
						$rec->load($rID['id']);
						
						$this->recursos[] = $rec;
					}
					
				}
				
				$this->loadEstado();
				
				return true;
			}
			
		}
		return false;
	}
	
	/**
	 * Consulta os recursos alocados a um determinado empreendimento
	 * @return boolean true se consulta bem sucedida e false caso contrario
	 */
	function getRecursos() {
		if($this->id > 0) {
			//se houver empreendimento carregado
			//monta consulta SQL
			$sql = "SELECT id FROM obra_rec WHERE empreendID=$this->id";
			if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
			//realiza consulta
			$recID = $this->bd->query($sql);
			
			if(count($recID) > 0) {
				//se foram retornados resultados para a consulta
				//para cada resultado
				foreach ($recID as $rID) {
					//carrega o recursoo
					$r = new Recurso($this->bd);
					if(getVal($r->load($rID['id']),'success'))
						//coloca no atributo recurso
						$recursos[] = $r;
				}
				$this->recursos = $recursos;
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Realiza transferencia de um recurso de um empreendimento para uma obra dentro do empreendimento
	 * @param int $recursoID
	 * @param float $valor
	 * @param int $obraID
	 * @return bool
	 */
	function transferRecursoToObra($recursoID,$valor,$obraID){
		//instancia recurso a ser transferido
		$rec = new Recurso($this->bd);
		//carrega recurso a ser transferido
		$rec->load($recursoID);
		
		if($rec->montante == $valor) {
			//se for transferido o total de recursos, apenas atribui a obra
			$rec->obraID = $obraID;
			$rec->save();
			
		} elseif ($rec->montante > $valor) {
			//senao, debita o montante a ser transferido
			$rec->montante = $rec->montante - $valor;
			$rec->save();
			
			//e cria um novo recurso
			$rec2 = new Recurso($this->bd);
			$rec2->montante = $valor;
			$rec2->origem = $rec->origem;
			$rec2->prazo = $rec->prazo; 
			$rec2->insertRecursoInObra($obraID);
			
		} else {
			//se o montante for menor do que valor a ser transferido
			return false;
		}
		//TODO return
		
	}
	
	/**
	 * Carrega as obras de  determinado empreendimento
	 * @return boolean true se operacao bem sucedida ou false caso contrario
	 */
	function getObras(){
		//se houver empreendimento carregado
		if ($this->id > 0){
			//monta consulta sql
			$sql = "SELECT id FROM obra_obra WHERE empreendID=$this->id";
			if(constant('DEBUG')) print '<BR />'.$sql.'<BR />';
			//executa consulta por obras do empreendimento
			$obraID = $this->bd->query($sql);
			//se houver alguma obra
			if(count($obraID) > 0) {
				foreach ($obraID as $oID) {
					//carrega a obra e coloca no array
					$obra = new Obra($this->bd);
					$obra->load($oID['id']);
					$this->obras[] = $obra;
				}
				
			}
			return true;
			
		}
		return false;
	}
	
	/**
	 * Le todas as etapas adicionadas a esta obra
	 */
	function loadEtapas() {
		if(!$this->id) return array("success" => false);
		//seleciona IDs de todos as etapas relacionadas a esta obra
		$etapasTipoID = Etapa::getEtapaPorEmpreend($this->id);// print_r($etapasTipoID);
		//se houver IDs retornados
		if(count($etapasTipoID)) {
			//pra cada ID, le os dados da etapa
			foreach ($etapasTipoID as $e) {
				$etapa = new Etapa(0, $this->id, 0, $e['id']);
				$load = $etapa->load();
				if($load['success']){
					//adiciona a etapa lida a array de etapas
					$etapas[] = $etapa;
				} else {
					return $load;
				}
			}
			//atribui as etapas lidas a obra
			$this->etapa = $etapas;
		} else {
			return array('success' => false);
		}
		
	}
	
	/**
	 * Funcao para modificar o valor de alguma variavel da classe
	 * @param string $attr
	 * @param mixed $val
	 */
	function set($attr, $val){
		if(isset($this->$attr) && $attr != 'id'){
			$this->$attr = $val;
			return true;
		}
		return false;
	}
	
	/**
	 * Funcao para resgatar alguma variavel da classe
	 * @param string $attr
	 * @return mixed attributo or null se atributo nao existir
	 */
	function get($attr) {
		if(isset($this->$attr))
			return $this->$attr;
		else
			return null;
	}
	
	/**
	 * Lê a variaveis enviadas via POST, trata e coloca no respectivo atributo.
	 */
	function getVars(){
		if(isset($_POST['solicUnOrg']))
			$this->unOrg['compl'] = $_POST['solicUnOrg'];
		if(isset($_POST['solicNome']))
			$this->solicitante['nome'] = SGEncode($_POST['solicNome'], ENT_QUOTES, null, false);
		if(isset($_POST['solicDepto']))
			$this->solicitante['depto'] = SGEncode($_POST['solicDepto'], ENT_QUOTES, null, false);
		if(isset($_POST['solicEmail']))
			$this->solicitante['email'] = $_POST['solicEmail'];
		if(isset($_POST['solicRamal']))
			$this->solicitante['ramal'] = $_POST['solicRamal'];
		
		if(isset($_POST['nome']))
			$this->nome = SGEncode($_POST['nome'], ENT_QUOTES, null, false);
		if(isset($_POST['justificativa']))
			$this->justificativa = SGEncode($_POST['justificativa'], ENT_QUOTES, null, false);
		if(isset($_POST['local']))
			$this->local = SGEncode($_POST['local'], ENT_QUOTES, null, false);
		if(isset($_POST['descricao']))
			$this->descricao = SGEncode($_POST['descricao'], ENT_QUOTES, null, false);
		if(isset($_POST['responsavel']))
			$this->responsavel = SGEncode($_POST['responsavel'], ENT_QUOTES, null, false);
		// esta variavel deve conter a associacao do empreendimento ao oficio usado para o cadastramento do empreendimento
		// enquanto nao for aberto o primeiro processo. depois disso, este campo deve ficar zerado (?)
		if (isset($_POST['ofir']) && $_POST['ofir'] != "")
			$this->ofir = $_POST['ofir'];
		else
			$this->ofir = 0;
	}
	
	function showTopMenu() {
		//carrega o template basico
		return str_replace('{$empreendID}', $this->id, showEmpreendTopMenuTemplate());
	}
	
	function showResumo() {
		//carrega o template basico
		$template = showEmpreendResumoTemplate();
		
		$usuario = "";
		if (isset($this->responsavel) && $this->responsavel != 0) {
			$usuario = getUsers($this->responsavel);
			$usuario = $usuario[0]['nomeCompl'];
		}
		
		$tabsObras = '';
		foreach ($this->obras as $o) {
			$tabsObras .= '<li><a href="sgo.php?acao=ver&obraID='.$o->get('id').'&mini=1">'.$o->nome.'</a></li>';
		}
		
		if(isset($_SESSION['perm'][7]) && $_SESSION['perm'][7] == 1)
			$template['template'] = str_ireplace('{$tr_editEmpreendLink}', $template['tr_editEmpreendLink'], $template['template']);
		else 
			$template['template'] = str_ireplace('{$tr_editEmpreendLink}', '', $template['template']);
		
		// procIT é a variável que deve indicar a qual processo de planejamento as ITs geradas devem ser anexadas
		$procIT = '';
		// pega os processos de Planejamento
		$procPlan = $this->getProcPlan();
		// escolhe o processo. É necessário que haja apenas 1 processo de planejamento associado a este empreendimento,
		// caso contrário, o sistema não tem como saber a qual processo anexar as its geradas
		if (count($procPlan) == 1) {
			$procIT = '&procIT=' . $procPlan[0]['id'];
		}
		else {
			$procIT = '&procIT=0';
		}
			
			
		$vars = array('{$empreendID}','{$nome_empreend}','{$unorg_empreend}'                ,'{$descr_empreend}'    ,'{$justif_empreend}'       ,'{$local_empreend}','{$solicitante_nome_empreend}'          ,'{$solicitante_depto_empreend}'           ,'{$solicitante_email_empreend}'          ,'{$solicitante_ramal_empreend}'          ,'{$info_obras}'   , '{$responsavel}','{$empreend_estado}'  ,'{$equipe}'         ,'{$tabs_obras}', '{$procIT}', '{$tab_plan}');
		$vals = array($this->get('id'),$this->get('nome'),getVal($this->get('unOrg'),'compl'),$this->get('descricao'),$this->get('justificativa'),$this->get('local'),getval($this->get('solicitante'),'nome'),getval($this->get('solicitante'), 'depto'),getval($this->get('solicitante'),'email'),getval($this->get('solicitante'),'ramal'),$this->showObras(), $usuario        ,$this->estado['label'],$this->showEquipe(),$tabsObras     , $procIT, $this->showEtapa(1, $procIT, $this->get('bd')));
		$html = str_replace($vars, $vals, $template['template']);
		
		//TODO: colocar estado do empreendimento
		
		return $html;
	}
	
	function showObras() {
		//carrega o template basico
		$template = showEmpreendObrasTemplate();
		$obrasHTML = '';
		
		$i = 2;
		foreach ($this->obras as $obra) {
			$resp = '';
			if (isset($obra->responsavel['nomeCompl'])) $resp = $obra->responsavel['nomeCompl'];
			$obrasHTML .= str_replace(
				array('{$obra_id}'    , '{$obra_nome}',/* '{$obra_cod}',*/ '{$obra_resp}' , '{$obra_etapa}'                 , '{$obra_fase}'                , '{$obra_estado}'      , '{$obra_obs}', '{$obra_index}'), 
				array($obra->get('id'), $obra->nome   ,/* $obra->codigo,*/ $resp          ,  $obra->estado['etapa']['label']['nome'], $obra->estado['fase']['label'], $obra->estado['label'], $obra->observacao, $i),
				$template['obra_tr']
			);
			
			$i++;
		}
		
		if(count($this->obras) == 0){
			$obrasHTML = $template['obra_noTR'];
		}
		$html = str_replace('{$obra_tr}', $obrasHTML, $template['template']);
		
		return $html;
	}
	
	function showHistorico() {
		//carrega o template basico
		$template = Historico::getTemplate();
		$historico_html = '';
		
		$historico = Historico_Empreend::getAllHistID($this->id, $this->bd);
		foreach ($historico as $h) {
			$hist = HistFactory::novoHist('empreend', $this->bd);
			$hist->load($h['id']);
			$historico_html_h = $hist->printHTML();
			$historico_html .= $historico_html_h['table_row'];
		}
		if (!count($historico))
			$historico_html = '<tr><td> Sem historico </td></tr>';
		
		return str_replace('{$linhas_historico}', $historico_html, $template);
	}
	
	function showDocs() {
		$template = showDocumentosTemplate();
		$docsHTML = '';
		$this->getDocs();
		
		// carrega todos os tipos de doc do bd e deixa em memória
		// a tabela é pequena, então guardar esta tabela em memória é vantajoso
		$docTypes = getAllDocTypes();
		$tipoDoc = array();
		foreach($docTypes as $dt) {
			// cria array que representará a tabela
			$tipoDoc[$dt['id']] = $dt;
		}
		$filtros = '';
		// monta checkbox de filtros
		foreach($tipoDoc as $dt) {
			if($dt['nomeAbrv'] == 'rr')
				continue;
			$filtros .= ' <input type="checkbox" id="check_'.$dt['nomeAbrv'].'" onclick="filtraDocPend(\''.$dt['nomeAbrv'].'\', false)" checked="checked"> <a onclick="filtraDocPend(\''.$dt['nomeAbrv'].'\', true)">'.$dt['nome'].'</a>';
		}
		$filtros .= ' <input type="checkbox" id="cb_selectAll" checked="checked" onclick="toggleSelectAll(false)"> <a onclick="toggleSelectAll(true)">Marca/Desmarca Todos</a>';
		
		$documentos = $this->docs;
		if (count($documentos) > 0) {
			foreach ($documentos as $doc) {
				$id = $doc->id;
				$num = $doc->dadosTipo['nome'].' '.$doc->numeroComp;
				$emitente = $doc->emitente;
				if(isset($doc->campos['assunto'])) $assunto = $doc->campos['assunto'];
				else $assunto = '---';
				$link = getDocLink($id, $doc->dadosTipo['nomeAbrv']);
				
				$r = $this->bd->query("SELECT id FROM doc WHERE docPaiID = {$doc->id}");

				if(count($r)) {
					$link_filho = str_ireplace('{$doc_id}', $id, $template['link_mostra_filho']);
				} else {
					$link_filho = '';
				}
				
				$docsHTML .= str_replace(array('{$doc_id}', '{$doc_link}', '{$doc_num}', '{$doc_emitente}', '{$doc_assunto}', '{$link_mostra_filho}'), array($id, $link, $num, $emitente, $assunto, $link_filho), $template['doc_tr']);
				
				
					
				foreach ($r as $docFilhoID) {
					$docFilho = new Documento($docFilhoID['id']);
					$docFilho->loadCampos();
					
					$num = $docFilho->dadosTipo['nome'].' '.$docFilho->numeroComp;
					if(isset($docFilho->campos['assunto'])) $assunto = $docFilho->campos['assunto'];
					else $assunto = '---';
					$docsHTML .= str_replace(
						array('{$doc_id}','{$docpai_id}', '{$doc_link}', '{$doc_num}', '{$doc_emitente}', '{$doc_assunto}'),
						array($docFilhoID['id'],$id, getDocLink($docFilhoID['id'], $docFilho->dadosTipo['nomeAbrv']), $num, $docFilho->emitente, $assunto), $template['docfilho_tr']
					);
				}
			}
		}

		if (!count($this->docs)) $docsHTML = $template['semDoc_tr'];
		
		$tabelaObrasDoc = $template['tabela_obras'];
		$todasTabelas = '';
		
		$this->getObras();
		
		if (count($this->obras) > 0) {
			//var_dump($this->obras);
			foreach($this->obras as $o) {
				$obraDocs = $o->getDocs();
				
				if (count($obraDocs) == 0)
					continue;
					
				$docsObraHTML = '';
				$tabela = '';
				
				foreach($obraDocs as $docID) {
					//var_dump($docID);
					$newDoc = new Documento($docID['docID']);
					$newDoc->loadCampos();
					
					$id = $newDoc->id;
					$num = $newDoc->dadosTipo['nome'].' '.$newDoc->numeroComp;
					$emitente = $newDoc->emitente;
					if(isset($newDoc->campos['assunto'])) $assunto = $newDoc->campos['assunto'];
					else $assunto = '---';
					$link = getDocLink($id, $newDoc->dadosTipo['nomeAbrv']);
					
					$r = $this->bd->query("SELECT id FROM doc WHERE docPaiID = {$newDoc->id}");
				
					if(count($r)) {
						$link_filho = str_ireplace('{$doc_id}', $id, $template['link_mostra_filho']);
					} else {
						$link_filho = '';
					}
					
					$docsObraHTML .= str_replace(array('{$doc_id}', '{$doc_link}', '{$doc_num}', '{$doc_emitente}', '{$doc_assunto}', '{$link_mostra_filho}'), array($id, $link, $num, $emitente, $assunto, $link_filho), $template['doc_tr']);
					
					foreach ($r as $docFilhoID) {
						$docFilho = new Documento($docFilhoID['id']);
						$docFilho->loadCampos();
						
						$num = $docFilho->dadosTipo['nome'].' '.$docFilho->numeroComp;
						if(isset($docFilho->campos['assunto'])) $assunto = $docFilho->campos['assunto'];
						else $assunto = '---';
						$docsHTML .= str_replace(
							array('{$doc_id}','{$docpai_id}', '{$doc_link}', '{$doc_num}', '{$doc_emitente}', '{$doc_assunto}'),
							array($docFilhoID['id'],$id, getDocLink($docFilhoID['id'], $docFilho->dadosTipo['nomeAbrv']), $num, $docFilho->emitente, $assunto), $template['docfilho_tr']
						);
					}
				}
				
				//var_dump($docsObraHTML);
				
				$tabela = str_replace('{$docs_tr_obra}', $docsObraHTML, $tabelaObrasDoc);
				$tabela = str_replace('{$obra_nome}', $o->get('nome'), $tabela);
				//var_dump($tabela);
				$todasTabelas .= $tabela;
			}
		}
		

		$ret = str_replace('{$docs_tr}', $docsHTML, $template['template']);
		$ret = str_replace('{$tabelas_obras}', $todasTabelas, $ret);
		$ret = str_replace('{$filtros}', $filtros, $ret);
		return $ret;
	}
	
	/**
	 * carrega os docs associados a este empreendimento 
	 */
	function getDocs() {
		// se o empreendimento já estiver setado
		if ($this->id > 0) {
			// faz busca para listar todos os documentos vinculados a este empreendimento
			//$sql = "SELECT * FROM doc WHERE empreendID = '" . $this->id . "' ORDER BY data DESC";
			//$sql = "SELECT d.id FROM doc AS d (SELECT docID FROM guardachuva_empreend WHERE empreendID = ".$this->id.") AS g WHERE d.empreendID = ".$this->id." OR d.id = g.docID ORDER BY data DESC";
			$sql = "SELECT d.id FROM doc AS d LEFT OUTER JOIN (SELECT * FROM guardachuva_empreend WHERE empreendID = ".$this->id.") AS g ON d.id = g.docID WHERE d.empreendID = ".$this->id." OR g.empreendID = ".$this->id." ORDER BY data DESC";
			$res = $this->bd->query($sql);
			// se nao encontrou nenhum documento vinculado, cria array vazia de documentos
			if (count($res) == 0) $this->docs = array();
			else { // senao, percorre resultados e concatena documento ao fim do array
				foreach ($res as $r) {
					// carrega dados do documento
					$doc = new Documento($r['id']);
					$doc->loadDados();
					$doc->loadCampos();
					if($doc->docPaiID) continue;
					if(count($doc->getObras()) > 0) continue;
					$this->docs[] = $doc;
				}
			}
		}
	}
	
	/**
	* Retorna todos docs de Contratação associados a esse empreendimento
	*/
	function getProcsContr() {
		$sql = "SELECT d.id FROM doc AS d INNER JOIN doc_processo AS p ON d.tipoID = p.id LEFT OUTER JOIN
				(SELECT * FROM guardachuva_empreend WHERE empreendID = ".$this->id.") AS g ON d.id = g.docID
				WHERE d.labelID = 1 AND (d.empreendID = ".$this->id." OR g.empreendID = ".$this->id.") AND
				(p.tipoProc = 'contrObr' OR p.tipoProc = 'contrProj') ORDER BY d.id DESC";
		
		/*$sql = "SELECT id FROM doc WHERE labelID = 1 AND empreendID = ".$this->id;*/
		$ret1 = $this->bd->query($sql);
		
		//$ret = $ret1;
		$sql = "SELECT d.docID AS id FROM 
					obra_doc AS d INNER JOIN 
					doc AS dc ON dc.id = d.docID INNER JOIN 
					obra_obra AS o ON o.id = d.obraID 
				WHERE o.empreendID = ".$this->id." AND dc.tipoID = 1";
		
		$ret2 = $this->bd->query($sql);
		
		$ret = array_merge($ret1, $ret2);
		
		return $ret;
	}
	
	function showLivroDeObras() {
		$html = showLivroDeObraTemplate();
		
		// TODO: completar esta funcao
		
		return $html;
	}
	
	function showQuestionamentos() {
		$html = showQuestionamentosTemplate();
		
		// TODO: completar esta funcao
		
		return $html;
	}
	
	function showContratos() {
		
		global $conf;
		$html = showContratosTemplate();
		
		$linkNovoContrato = '';
		if (checkPermission(85)) {
			$linkNovoContrato = '<center><a id="linkNovoContrato" onclick="showNovoContrato()">[Novo Contrato]</a></center>';
		}
		
		$tipoContrato = getDocTipo('contr');
		$tipoProcesso = getDocTipo('pr');

		$contratos = $this->getContratos();

		$tabela = '';
		if (count($contratos) <= 0) {
			$tabela .= '<tr class="c"><td class="c" colspan="8"><b><center>Nenhum contrato cadastrado para este empreendimento.</center></b></td></tr>';
		}
		else {
			foreach($contratos as $c) {
				$doc = new Contrato($c['id']);
				$doc->dadosTipo = $tipoContrato[0];
				$doc->loadDados();
				$doc->loadCampos();
				
				$obras = '';
				$obrasList = $doc->getObras();
				foreach ($obrasList as $o) {
					$obras .= '<a href="sgo.php?acao=verObra&obraID='.$o['id'].'">'.$o['nome'].'</a>';
					$obras .= '<br />';
				}
				
				$proc = new Documento($doc->campos['numProcContr']);
				$proc->dadosTipo = $tipoProcesso[0];
				$proc->loadDados();
				$proc->loadCampos();
				
				$empresa = new Empresa($this->bd);
				$empresa->load($doc->campos['empresaID']);
				
				$tipo = 'Contrata&ccedil;&atilde;o de Obra';
				if ($proc->campos['tipoProc'] == 'contrProj') {
					$tipo = 'Contrata&ccedil;&atilde;o de Projeto';
				}
				
				$vigencia = "---";
				$dataTermino = "---";
				
				if (isset($doc->campos['vigenciaContr']) && $doc->campos['vigenciaContr'] > 0) {
					$vigencia = date("d/m/Y", $doc->campos['vigenciaContr']);
				}
				
				if (isset($doc->campos['dataTermino']) && $doc->campos['dataTermino'] > 0) {
					$dataTermino = date("d/m/Y", $doc->campos['dataTermino']);
				}
				
				$tabela .= '<tr class="c">';
				$tabela .= '<td class="c"><center>'.$doc->id.'</center></td>';
				$tabela .= '<td class="c"><center><a onclick="window.open(\'sgd.php?acao=ver&docID='.$doc->id.'\',\'doc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">'.$doc->numeroComp.'</a></center></td>';
				$tabela .= '<td class="c"><center>'.$obras.'</center></td>';
				$tabela .= '<td class="c"><center>'.$tipo.'</center></td>';
				$tabela .= '<td class="c"><a onclick="window.open(\'sgd.php?acao=ver&docID='.$proc->id.'\',\'doc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">'.$proc->numeroComp.'</a>';
				$tabela .= ': '.$proc->campos['assunto'].'</td>';
				$tabela .= '<td class="c">'.SGDecode($empresa->get('nome')).'</td>';
				$tabela .= '<td class="c">'.$vigencia.'</td>';
				$tabela .= '<td class="c">'.$dataTermino.'</td>';
				
				$tabela .= '</tr>';
			}
		}
		
		$html = str_replace('{$link_novo_contrato}', $linkNovoContrato, $html);
		$html = str_replace('{$tabela_contratos}', $tabela, $html);
		if (checkPermission(85)) {
			$html = str_replace('{$novo_contrato}', Contrato::showCadEmprForm($this), $html);
		}
		else {
			$html = str_replace('{$novo_contrato}', '', $html);
		}
		
		return $html;
	}
	
	function getContratos() {
		//$sql = "SELECT id FROM doc WHERE labelID = 10 AND empreendID = ".$this->id;
		$procs = $this->getProcsContr();
//		var_dump($procs);exit();
		$contratos = array();
		/** Arrumando bug de contratos que n�o aparecem
		 *  @author: Leandro K�mmel T. Mendes
		 *  Corre��o bug 001-contratos
		 *  Nova query para pegar por empreendID e um remo�ao dos duplicados 
		 *  */
		$sql = "SELECT id FROM doc WHERE labelID = 10 AND empreendID = ".$this->id;			
		$res = $this->bd->query($sql);
		foreach($res as $c) {
			$flag=true;
			foreach ($contratos as $fc){
				if($fc['id']==$c['id'])//n�o queremos ocorrencias repetidas
					$flag=false;
			}
			if($flag)
				$contratos[] = $c;
		}
		foreach ($procs as $p) {
			//query para os filhos
			$sql = "SELECT id FROM doc WHERE docPaiID = ".$p['id']." AND labelID = 10";
			$res = $this->bd->query($sql);
			foreach($res as $c) {
				$flag=true;
				foreach ($contratos as $fc){
					if($fc['id']==$c['id'])//n�o queremos ocorrencias repetidas
						$flag=false;
				}
				if($flag)
					$contratos[] = $c;
			}
		}
		return $contratos;//fix bug-001
	}
	
	function showMedicoes() {
		$html = showMedicoesTemplate();
		
		// TODO: completar esta funcao
		
		return $html;
	}
	
	function showNovoContratoForm($restaurar) {
		global $bd;
		$html = Contrato::showForm($this, 1, $bd, $restaurar);
		return $html;
	}
	
	
	/** 
	 * Retorna as msgs atribuidas a este empreendimento
	 * @return html
	 */
	function showMensagens() {
		$template = showMensagensTemplate();
		// monta header da tabela
		$tabela = '<script type="text/javascript" src="scripts/jquery.tablesorter.min.js"></script>
		<table id="tableMsgs" width="100%">
			<thead>
				<th class="c" width="150">Data:</th>
				<th class="c">De:</th>
				<th class="c">Assunto:</th>
				<td class="c"><b>A&ccedil;&otilde;es:</b></td>
			</thead>
			<tbody>';
		
		if(isset($_SESSION['perm'][92]) && $_SESSION['perm'][92])
			$html = str_replace('{$novaMsg_link}', $template['novaMsg_link'], $template['template']);
		else
			$html = str_replace('{$novaMsg_link}', '', $template['template']);
		// pega as msgs
		$msgs = $this->getMsgs();
		
		// percorre lista de mensagens e gera html da tabela
		$tabela .= $this->percorreMsgs($msgs);
		
		// não encontrou msgs
		if (count($msgs) <= 0) {
			$tabela .= '<tr><td colspan="4"><center><b>Nenhuma mensagem para este empreendimento.</b></center></td></tr>';
		}
		
		// fecha tabela
		$tabela .= '</tbody></table>';
		
		// monta os campos de nova mensagem
		$campo['remetente'] = $_SESSION['username'];
		$campo['assunto'] = geraInput('assunto', array('style' => 'width: 500px;'));
		$campo['conteudo'] = geraTextarea('conteudo', 15, 15, '', array('style' => 'width: 500px; height: 300px;'));
		
		// coloca html em seus devidos lugares
		$html = str_ireplace('{$remetente}', $campo['remetente'], $html);
		$html = str_ireplace('{$assunto}', $campo['assunto'], $html);
		$html = str_ireplace('{$conteudo}', $campo['conteudo'], $html);
		$html = str_ireplace('{$msg_rows}', $tabela, $html);
		$html = str_ireplace('{$empreendID}', $this->id, $html);
		// retorna html
		return $html;
	}
	
	/**
	 * Percorre array de mensagens e retorna html da tabela de msgs resultante
	 * @param array $lista
	 * @return html
	 */
	function percorreMsgs($lista) {
		$html = '';
		
		// percorre a lista
		foreach($lista as $l) {
			// monta html desta msg
			$html .= $this->montaMsg($l);
			
			// pega as respostas a esta mensagem
			$resps = $this->getAnswers($l['id']);
			
			// se houver resposta, percorre esta lista de respostas também
			if (count($resps) > 0) $html .= $this->percorreMsgs($resps);
		}
		
		// retorna html
		return $html;
		
	}
	
	/**
	 * monta linha da tabela de mensagens
	 * @param array $m conteudo da mensagem (mesmos campos da tabela obra_mensagem do bd)
	 * @return html
	 */
	function montaMsg($m) {
		$html = '';

		// preenche com emitente
		$remetente = getUsers($m['usuarioID']);
		if (count($remetente) <= 0) {
			$remetente = 'Usu&aacute;rio desconhecido/deletado';
		}
		else
			$remetente = $remetente[0]['username'];
				
		// preenche/transforma a data
		$data = $m['data'];
		$data = date("d/m/Y H:i", $data);
		
		// lista de anexos
		$anexos = SGDecode($m['anexos']);
		$anexIcon = '';
		
		// preenche assunto
		$assunto = SGDecode($m['assunto']);
		if ($anexos != "" && count($anexos) > 0) {
			//$anexIcon .= ' <div title="Esta mensagem possu&iacute; anexos" class="ui-icon ui-icon-document" style="display: block;"></div>';
			$anexIcon = '<img src="img/clip.png" title="Esta mensagem possu&iacute; anexos." />';
		}
		
		// preenche conteudo
		$conteudo = $m['conteudo'];
		
		if ($anexos != "" && count($anexos) > 0) {
			$conteudo .= '<br /><br /><b>Arquivos Anexos</b>:<br />';
			$arrayAnex = explode(",", $anexos);
			foreach($arrayAnex as $a) {
				$conteudo .= '<a href="files/msgs/'.$a.'" target="_blank">'. $a .'</a><br />';
			}
		}
			
		$html .= '<tr class="c">';
		$html .= '<td class="c"  onclick="abreMsg('.$m['id'].')">'.$data.'</td><td class="c" onclick="abreMsg('.$m['id'].')"><center>'.$remetente.'</center></td>';
		//$html .= '<td class="c">'.$anexIcon.'</td>';
		$html .= '<td class="c"  onclick="abreMsg('.$m['id'].')"><a id="assunto'.$m['id'].'">'.$assunto.'</a> '.$anexIcon.'</td>';
		$html .= '<td class="c">';
		if(isset($_SESSION['perm'][93]) && $_SESSION['perm'][93])
			$html .= '<a onclick="resp('.$m['id'].')">Responder</a>';
		$html .= '</td></tr>';
		$html .= '<tr class="c" id="m'.$m['id'].'" style="display: none;"><td class="c" colspan="4">'.$conteudo.'</td></tr>';

		return $html;
	}
	
	
	/**
	 * Retorna as mensagens deste empreendimento (que não são respostas à outras mensagens)
	 * @return array
	 */
	function getMsgs() {
		$sql = "SELECT * FROM obra_mensagem WHERE empreendID = ".$this->id." AND replyTo = 0 ORDER BY data DESC";
		return $this->bd->query($sql);
	}
	
	/**
	 * Retorna as mensagens que são respostas à outras deste empreendimento
	 * @param int $id id da mensagem 
	 * @return array
	 */
	function getAnswers($id) {
		$sql = "SELECT * FROM obra_mensagem WHERE replyTo = ".$id." ORDER BY id ASC";
		return $this->bd->query($sql);
	}
	
	/**
	 * exibe display da equipe
	 * @return html
	 */
	function showEquipe() {
		$html = '<b>Equipe</b>:<br /><br />';
		
		// pega equipe
		$this->getEquipe();
		
		// percorre array de membros
		foreach($this->equipe as $u) {
			// preenche com nome do usuario
			$usuario = getUsers($u);
			if (count($usuario) <= 0) continue;
			$usuario = $usuario[0];
			
			// exibe nome
			$html .= $usuario['nomeCompl'] . '<br />';
		}

		// não há membros nesta equipe
		if (count($this->equipe) <= 0) {
			$html .= 'Empreendimento sem equipe.<br />';
		}
		
		// adição de link para pessoas que até agora podem editar equipe...
		// TODO: achar jeito mais elegante de fazer isto
		if (isset($_SESSION['id']) && $_SESSION['id'] == $this->responsavel) {
			// é o responsável por este empreendimnto
			$html .= '<br /><a href="sgo.php?acao=editEquipe&empreendID='.$this->id.'">[editar equipe]</a>';
		}
		else {
			if (isset($_SESSION['perm'][90]) && $_SESSION['perm'][90]) {
				// não é o responsável, mas tem permissao
				$html .= '<br /><a href="sgo.php?acao=editEquipe&empreendID='.$this->id.'">[editar equipe]</a>';
			}
		}
		
		return $html;
	}
	
	/**
	 * Pega a equipe atribuida a este empreendimento no bd
	 */
	function getEquipe() {
		// se este empreendimento não está salvo, ele ainda não tem equipe
		if (!isset($this->id) || $this->id == 0) {
			$this->equipe = array();
			return;
		}
			
		// seleciona todos os membros da equipe atribuida a este empreendimento
		$sql = "SELECT e.userID FROM obra_equipe AS e INNER JOIN usuarios AS u ON e.userID = u.id WHERE empreendID = ".$this->id." ORDER BY u.nomeCompl";
		$res = $this->bd->query($sql);
		
		$team = array();
		
		// monta array
		foreach($res as $r) {
			$team[] = $r['userID'];
		}
		
		$this->equipe = $team;
	}
	
	/**
	 * @desc determina o estado do empreendimento atraves do estado das obras
	 */
	function loadEstado(){
		//inicializa o estado como 0. Desse modo, caso nao seja porssivel determinar o estado (ex: nao foram criadas etapas ainda) sera mostrado desconhecido
		$this->estado['id'] = 0;
		//inicializa a contgem de obras concluidas para determinar se todas as obras ja foram concluidas
		$obras_finalizadas = 0;
		//itera sobre as obras para verificar o estado de cada uma
		if(count($this->obras)){
			foreach ($this->obras as $obra) {
				//se houver alguma obra em planejamento mas nao em proj ou exec, o estado eh planejamento
				if($obra->estado['etapa']['id'] == 1) {
					$this->estado['id'] = 1;
				//se houver obras em proj ou exec, entao o estado eh plan/obra
				} elseif($obra->estado['etapa']['id'] == 2) {
					$this->estado['id'] = 2;
					$this->estado['obras_proj']++;
				} elseif($obra->estado['etapa']['id'] == 3) {
					$this->estado['id'] = 2;
					$this->estado['obras_exec']++;
				}
				//se a obra estiver finalizada, apenas conta
				//o empreendimento so estara finalizado quando todas as obras estiverem finalizadas
				if($obra->estado['id'] == 3)
					$obras_finalizadas++;
			}
		
			//se todas as obras estiverem finalizadas, coloca o empreendimento como finalizado
			if($obras_finalizadas == count($this->obras))
				$this->estado['id'] = 3;
		}
		
		//atribui o label correto de acordo com o estado do empreendimento 'calculado' anteriormente
		if($this->estado['id'] == 0) {
			$this->estado['label'] = "Desconhecido";
		} elseif($this->estado['id'] == 1) {
			$this->estado['label'] = "Em planejamento";
		} elseif($this->estado['id'] == 2) {
			$this->estado['label'] = "Em andamento: ".$this->estado['obras_proj']." obra(s) em projeto e ".$this->estado['obras_exec'].' obra(s) em execu&ccedil;&atilde;o';
		} elseif($this->estado['id'] == 3) {
			$this->estado['label'] = "Conclu&iacute;do";
		}
	}
	
	/**
	 * Mostra a etapa do tipo $tipoID deste empreendimento.
	 * @param int $tipoID o tipo da etapa (id da tabela label_obra_etapa)
	 * @param int $procIT o processo a qual as its geradas nesta etapa devem ser anexadas
	 * @param BD $bd
	 */
	function showEtapa($tipoID, $procIT, BD $bd) {
		// inicializa variáveis
		$etapa = null;
		$fases = null;
		$html = "";
		$cont = "";
		
		$template = showEtapaTemplate();
		$menu = $template['menu'];
		$menu_item = $template['menu_item'];
		$cont_div = $template['divContent'];
		$html = $template['template'];
		
		// se as estapas não estiverem carregadas, carrega-as.
		if (count($this->etapa) <= 0) {
			$this->loadEtapas();
		}
		
		//coloca link para adicionar obra se tiver permissao
		if(isset($_SESSION['perm'][8]) && $_SESSION['perm'][8]) {
			$menu = str_replace('{$addObra_link}', $template['addObra_link'], $menu);
		} else {
			$menu = str_replace('{$addObra_link}', '', $menu);
		}
		
		// percorre as etapas
		foreach($this->etapa as $e) {
			if ($e->tipo['id'] == $tipoID)
				$etapa = $e;
		}
		
		if ($etapa == null) {
			return array("success" => false, "errorNo" => 0, "errorFeedback" => "Erro ao carregar etapa.");
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
		
		// montando menu lateral
		$item = 2;
		$menu_cont = '';
		
		//colocar link de atribuir responsaveis
		
		$atribuir = $template['atribuirResponsaveis'];
		$aux = str_replace('{$attribuirResponsaveis_id}', $item, $atribuir);
		$aux = str_replace('{$empreendID}', $etapa->get('empreendID'), $aux);
		$aux = str_replace('{$obraID}', $etapa->get('obraID'), $aux);
		$aux = str_replace('{$etapaTipoID}', $tipoID, $aux);
		$menu = str_replace('{$atribuirResponsaveis}', $aux, $menu);
		
		$div = str_replace('{$content_id}', 'r'.$item, $cont_div);
		$div = str_replace('{$content}', $etapa->showResponsaveisInterface($responsavelID), $div);
		$cont .= $div;
		//$item++;
		
		// pega as fases desta etapa
		$fases = $etapa->fases;
		
		foreach($fases as $f) {
			$nome = $f->dadosTipo['nome'];
			//var_dump($nome);
			//$aux = str_replace('{$item_id}', $item, $menu_item);
			$aux = str_replace('{$item_label}', $nome, $menu_item);
			$aux = str_replace('{$empreendID}', $etapa->get('empreendID'), $aux);
			$aux = str_replace('{$etapaTipoID}', $tipoID, $aux);
			$aux = str_replace('{$obraID}', 0, $aux);
			$aux = str_replace('{$faseTipoID}', $f->dadosTipo['id'], $aux);
			$menu_cont .= $aux;
			
			//$div = str_replace('{$content_id}', 'r'.$item, $cont_div);
			//$div = str_replace('{$content}', $f->showResumo($procIT), $div);
			
			//$div = str_replace('{$obraID}', $etapa->get('obraID'), $div);
			//$div = str_replace('{$etapa_id}', $etapa->getID(), $div);
			//$div = str_replace('{$etapa_tipoID}', $tipoID, $div);
			
			//$cont .= $div;
			
			//$item++;
		}
		//$it_supl = '';
		/*$it_supl = '<p style="text-align: center; font-size: 14pt; color:#BE1010;">Informa&ccedil;&atilde;o T&eacute;cnica Suplementar</p>';
		$it_supl .= '<form accept-charset="utf-8" action="sgo.php?acao=novaITsuplementar" enctype="multipart/form-data" method="post">';
		$it_supl .= '<input type="hidden" name="empreendID" value="'.$this->id.'">';
		$it_supl .= showForm('novo_it', 'it', 1, $bd);
		$it_supl .= '<center><input type="submit" value="Salvar"></center>';
		$it_supl .= '</form>';*/
		
		
		$menu = str_replace('{$menu_itens}', $menu_cont, $menu);
		$html = str_replace('{$menu}', $menu, $html);
		$html = str_replace('{$conteudo}', $cont, $html);
		
		//$html = str_replace('{$obraCont_id}', "r".$item, $html);
		$html = str_replace('{$menuObra_id}', 2, $html);
		//$html = str_replace('{$addIT_id}', "r".($item+1), $html);
		//$html = str_replace('{$menuIT_id}', $item+1, $html);
		//$html = str_replace('{$it_suplementar}', $it_supl, $html);
		$html = str_replace('{$empreendID}', $this->id, $html);
		
		return $html;
	}
	
	/**
	 * Atualiza um atributo da Etapa com ID passado por parametro
	 * @param int $etapaID
	 * @param int $etapaTipoID
	 * @param string $attrName
	 * @param mixed $attrVal
	 */
	function updateEtapaAttr($etapaID, $etapaTipoID, $attrName, $attrVal, $faseLabelID = 0) {
		//precorre todos os atributos a procura da etapa
		foreach ($this->etapa as $e) {
			//se achou a etapa com o ID ou o tipo
			if($e->getID() == $etapaID || $e->tipo['id'] == $etapaTipoID) {
				//verifica se o nome da variavel eh valido
				if(isset($e->$attrName)){
					//verifica se a variavel eh publica
					$refl = new ReflectionProperty($e, $attrName);
					if($refl->isPublic() && $attrName != 'tipo' && $attrName != 'fases'){
						if($attrName == "estado"){
							$faseEstado = $this->bd->query("SELECT * FROM label_etapa_estado WHERE id= $attrVal");
							if(count($faseEstado) > 0) {
								$e->$attrName = $faseEstado[0];
								return array("success" => true, "errorNo" => "", "errorFeedback" => "");
							}
						} else {
							$e->$attrName = $attrVal;
							return array("success" => true, "errorNo" => "", "errorFeedback" => "");
						}
					} elseif ($attrName == "fases") {
						$e->updateFaseAttr($faseLabelID,$attrName,$attrVal);
						return array("success" => true, "errorNo" => "", "errorFeedback" => "");
					}
				}
				return array("success" => false, "errorNo" => "updateEtapaAttr1", "errorFeedback" => "Atributo inexistente");
			}
		}
		return array("success" => false, "errorNo" => "updateEtapaAttr1", "errorFeedback" => "Etapa inexistente");
	}
	
	function salvaEtapaResponsavel($tipoID, $post) {
		global $bd;
		
		$this->loadEtapas();
		
		$etapa = null;
		foreach($this->etapa as $e) {
			//var_dump($e->tipo);
			if ($e->tipo['id'] == $tipoID) {
				$etapa = $e;
				break; 
			}
			//var_dump($etapa);
			//var_dump($tipoID);
		}
		
		if ($etapa == null) {
			return array("success" => false, "errorNo" => "", "errorFeedback" => "Etapa inexistente");
		}
		
		$sql = "SELECT userID FROM obra_equipe WHERE empreendID = ".$this->id;
		$equipe = $bd->query($sql);
		if (count($equipe) <= 0) $equipe = array();
		/*$arqTemp = fopen("qqweqweqwe.txt", "w");
		fwrite($arqTemp, "dasdasdasd");
		fclose($arqTemp);*/
		
		$etapa->responsavelID = $post['resp'];
		$fb = $etapa->save();
		if ($fb['success'] == false) return $fb;
		
		$achou = false;
		foreach($equipe as $eq) {
			if ($eq['userID'] == $post['resp']) {
				$achou = true;
				break;	
			}
		}
		if ($achou == false) {
			$sql = "INSERT INTO obra_equipe (empreendID, userID) VALUES (".$this->id.", ".$post['resp'].")";
			$bd->query($sql);
			$equipe[]['userID'] = $post['resp'];
		}
		
		/*$arqTemp = fopen("qqweqweqwe.txt", "w");
		fwrite($arqTemp, "etapa ".$etapa->get('id'));
		fclose($arqTemp);
		var_dump("=--=-=-=-=-=-=-=-=-=-=-=-=-=");*/
		//$etapa->loadFases();
		//var_dump($etapa);
		/*var_dump($etapa->fases);
		exit();*/
		
		foreach ($etapa->fases as $f) {
			$nomeCampo = 'resp' . $f->dadosTipo['id'];
			//var_dump($nomeCampo);
			$f->responsavelID = $post[$nomeCampo];
			$f->save();
			
			$achou = false;
			foreach($equipe as $eq) {
				if ($eq['userID'] == $post[$nomeCampo]) {
					$achou = true;
					break;	
				}
			}
			if ($achou == false) {
				$sql = "INSERT INTO obra_equipe (empreendID, userID) VALUES (".$this->id.", ".$post[$nomeCampo].")";
				$bd->query($sql);
				$equipe[]['userID'] = $post[$nomeCampo];
			}
		}
		
		return array("success" => true, "msg" => "Respons&aacute;veis atualizados com sucesso!");
	}
	
	/**
	 * Retorna os processos de planejamento associados a este empreendimento
	 * 
	 */
	function getProcPlan() {
		$bd = $this->bd;
		
		$sql = "SELECT d.id FROM doc AS d INNER JOIN doc_processo AS p ON d.tipoID = p.id LEFT OUTER JOIN
				(SELECT * FROM guardachuva_empreend WHERE empreendID = ".$this->id.") AS g ON d.id = g.docID
				WHERE d.labelID = 1 AND (d.empreendID = ".$this->id." OR g.empreendID = ".$this->id.") AND
				(p.tipoProc = 'plan') ORDER BY d.id DESC";
		
		return $bd->query($sql);
	}
	
	function logaHistorico($tipo, $usr = '', $doc = '', $msg = '', $obra = ''){
		$hist = HistFactory::novoHist('empreend', $this->bd);
		$hist->set('empreendID', $this->id);
		$hist->set('tipo',$tipo);
		$hist->set('user_targetID', $usr);
		$hist->set('msg_targetID', $msg);
		$hist->set('doc_targetID', $doc);
		$hist->set('obra_targetID', $obra);
		
		$hist->save();
	}
	
	static public function geraLink($acao, $empreendID){
		return "window.open('sgo.php?acao={$acao}&empreendID={$empreendID}','empreend','width='+screen.width*0.95+',height='+screen.height*0.9+',scrollbars=yes,resizable=yes').focus()";
	}
}

?>
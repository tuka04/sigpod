<?php

class Historico_Empreend extends Historico {
	private static $frases = array(
		"cadastro" => 'Cadastrou este empreendimento',
		"cadObra" => 'Cadastrou obra {$obra_nome} neste empreendimento',
		"editarResp" => 'Atribuiu {$user_nomeCompl} como respons&aacutevel por este empreendimento',
		"addEquipe" => 'Adicionou {$user_nomeCompl} &agrave; equipe deste empreendimento',
		"rmEquipe" => 'Respondeu {$user_nomeCompl} da equipe deste empreendimento',
		"newMensagem" => 'Postou nova mensagem ({$msg_assunto})',
		"respMensagem" => 'Respondeu a mensagem {$msg_assunto}',
		"atribDoc" => 'Atribuiu o documento {$doc_nome} a este empreendimento',
		"criarDoc" => 'Criou novo(a) {$doc_tipo} sobre {$doc_assunto}',
		"criarContr" => 'Criou novo contrato {$doc_nome}',
		"newFase" => 'Iniciou a fase {$fase_nome} deste empreendimento',
		"atribRespFase" => 'Atribuiu {$user_nomeCompl} como respons&aacute;vel pela fase {$fase_nome} deste empreendimento',
		"atribRespEtapa" => 'Atribuiu {$user_nomeCompl} como respons&aacute;vel pela etapa {$etapa_nome} deste empreendimento'
	);
	
	protected $empreendID;
	protected $user_targetID;
	protected $doc_targetID;
	protected $msg_targetID;
	protected $obra_targetID;
	protected $etapa_targetID;
	protected $fase_targetID;
	
	/**
	 * 
	 */
	function __construct($bd){
		$this->empreendID = 0;
		$this->user_targetID = '';
		$this->doc_targetID = '';
		$this->msg_targetID = '';
		$this->obra_targetID = '';
		$this->etapa_targetID = '';
		$this->fase_targetID = '';
		
		parent::__construct($bd);
	}
	
	
	/**
	 * Carrega um historico do BD
	 * @param int $id
	 */
	function load($id) {
		if($id > 0) {
			$bd = $this->get('bd');
			$res = $bd->query("SELECT empreendID, id, data, userID, tipo, user_targetID, doc_targetID, msg_targetID, obra_targetID,etapa_targetID,fase_targetID FROM empreend_historico WHERE id = $id");
			
			if(is_array($res) && count($res) == 1){
				$this->set('empreendID', $res[0]['empreendID']);
				$this->set('obra_targetID', $res[0]['obra_targetID']);
				$this->set('user_targetID', $res[0]['user_targetID']);
				$this->set('msg_targetID', $res[0]['msg_targetID']);
				$this->set('doc_targetID', $res[0]['doc_targetID']);
				$this->set('fase_targetID', $res[0]['fase_targetID']);
				$this->set('etapa_targetID', $res[0]['etapa_targetID']);
				$this->set('id', $res[0]['id']);
				$this->set('data', $res[0]['data']);
				$this->set('userID', $res[0]['userID']);
				$this->set('tipo', $res[0]['tipo']);
			
				return array("success" => true, "errorID" => 121, "errorFeedback" => "");
			} else {
				return array("success" => false, "errorID" => 122, "errorFeedback" => "Erro ao ler historico: Nenhum historico com esse nome");
			}
		} else {
			return array("success" => false, "errorID" => 122, "errorFeedback" => "Erro ao ler historico: ID {$id} invalido!");
		}
	}
	
	/**
	 * Salva o objeto atual no BD
	 */
	function save(){
		if($this->get('id') == 0){
			$bd = $this->get('bd');
			//salva historico
			$this->set('data', time());
			$this->set('userID' ,$_SESSION['id']);
			
			if($this->get('obra_targetID') == '') $obra = 'NULL';
			else $obra = $this->get('obra_targetID');
			if($this->get('doc_targetID') == '') $doc = 'NULL';
			else $doc = $this->get('doc_targetID');
			if($this->get('msg_targetID') == '') $msg = 'NULL';
			else $msg = $this->get('msg_targetID');
			if($this->get('user_targetID') == '') $usr = 'NULL';
			else $usr = $this->get('user_targetID');
			if($this->get('fase_targetID') == '') $fase = 'NULL';
			else $fase = $this->get('fase_targetID');
			if($this->get('etapa_targetID') == '') $etapa = 'NULL';
			else $etapa = $this->get('etapa_targetID');
			
			$res = $bd->query("INSERT INTO empreend_historico (empreendID, data, userID, tipo, user_targetID, doc_targetID, msg_targetID, obra_targetID, etapa_targetID, fase_targetID) VALUES ({$this->get('empreendID')}, {$this->get('data')},{$this->get('userID')},'{$this->get('tipo')}',{$usr},{$doc},{$msg},{$obra},{$etapa},{$fase})",null,true);
			if($res === false)
				return array("success" => false, "errorID" => 123, "errorFeedback" => "Erro ao salvar entrada de historico: ID retornado invalido");
			else
				$this->set('id', $res);
			return array("success" => true, "errorID" => '', "errorFeedback" => "");
			
		} else {
			return array("success" => false, "errorID" => 124, "errorFeedback" => "Erro ao salvar entrada de historico: Nao eh possivel editar historico");
		}
		
	}
	
	/**
	 * Retorna o cod HTML dessa entrada de historico a ser mostrada na pagina
	 */
	function printHTML() {
		$template['table_row'] = '<tr class="c"><td class="c">{$data}</td><td class="c"><a href="javascript:void(0)" onclick="showUserProfile('.$this->get('userID').')">{$userName}</a></td><td class="c">{$texto}</td></tr>';
		
		$texto = Historico_Empreend::$frases;
		$texto = $texto[$this->get('tipo')];
		
		if(strpos($texto, '{$user_nomeCompl}') !== false) {
			$user = getNamesFromUsers($this->get('user_targetID'));
			$texto = str_replace('{$user_nomeCompl}', $user[0]['nomeCompl'], $texto);
		}
		
		if(strpos($texto, '{$msg_assunto}') !== false) {
			$bd = $this->get('bd');
			$msg = $bd->query("SELECT assunto FROM obra_mensagem WHERE id = {$this->get('msg_targetID')}");
			$texto = str_replace('{$msg_assunto}', $msg[0]['assunto'], $texto);
		}
		
		if(strpos($texto, '{$doc_nome}') !== false || strpos($texto, '{$doc_assunto}') !== false || strpos($texto, '{$doc_tipo}') !== false) {
			$doc = new Documento($this->get('doc_targetID'));
			$doc->loadCampos();
			
			$assunto = '';
			if (isset($doc->campos['assunto'])) $assunto = $doc->campos['assunto'];
			
			$texto = str_replace('{$doc_nome}', $doc->dadosTipo['nome'].' '.$doc->numeroComp, $texto);
			$texto = str_replace('{$doc_tipo}', $doc->dadosTipo['nome'], $texto);
			$texto = str_replace('{$doc_assunto}', $assunto, $texto);
		}
		
		if(strpos($texto, '{$obra_nome}') !== false) {
			$bd = $this->get('bd');
			$msg = $bd->query("SELECT nome FROM obra_obra WHERE id = {$this->get('obra_targetID')}");
			$texto = str_replace('{$obra_nome}', $msg[0]['nome'], $texto);
		}
		
		if(strpos($texto, '{$etapa_nome}')) {
			$bd = $this->get('bd');
			$etapa = $bd->query("SELECT nome FROM label_obra_etapa AS l INNER JOIN obra_etapa as e ON l.id = e.tipoID WHERE e.id = {$this->get('etapa_targetID')}");
			$texto = str_replace('{$etapa_nome}', $etapa[0]['nome'], $texto);
		}
		
		if(strpos($texto, '{$fase_nome}')) {
			$bd = $this->get('bd');
			$fase = $bd->query("SELECT nome FROM label_obra_fase AS l INNER JOIN obra_fase as f ON l.id = f.labelID WHERE f.id = {$this->get('fase_targetID')}");
			$texto = str_replace('{$fase_nome}', $fase[0]['nome'], $texto);
		}
		
		
		$usuario = getNamesFromUsers($this->get('userID'));
		
		$template['table_row'] = str_replace(array('{$data}','{$userName}','{$texto}'), array(date('j/m/Y H:i', $this->get('data')), $usuario[0]['nome'], $texto), $template['table_row']);
		$template['plain_text'] = array('date' => date('j/m/Y H:i', $this->get('data')), 'userName' => $usuario[0]['nome'], 'text' => $texto);
		
		return $template;
	}
	
	function printHTMLAjax(){
		
	}
	
	/**
	 * Retorna todos os ids dos históricos vinculados ao empreend de id $empreendID
	 * @param int $empreendID
	 * @param BD $bd
	 * @return array [id]
	 */
	public static function getAllHistID($empreendID, BD $bd) {
		//$bd = $this->get('bd');
		
		$sql = "SELECT id FROM empreend_historico WHERE empreendID = ".$empreendID." ORDER BY id DESC";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna as 5 ultimas entradas do usuario passado por parametro
	 * @param int $userID
	 */
	public static function get5Ultimos($userID){
		global $bd;
		
		$sql = "SELECT id FROM empreend_historico WHERE userID = {$userID} ORDER BY data DESC LIMIT 5";
		return $bd->query($sql);
	}
	
}

?>
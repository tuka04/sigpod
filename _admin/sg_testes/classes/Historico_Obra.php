<?php

class Historico_Obra extends Historico {
	private static $frases = array(
		"cadastro" => 'Cadastrou esta obra',
		"atribResp" => 'Atribuiu {$user_nomeCompl} como respons&aacutevel pela obra'
	);
	
	protected $obraID;
	protected $user_targetID;
	protected $doc_targetID;
	
	function __construct($bd){
		$this->obraID = 0;
		$this->user_targetID = 0;
		$this->doc_targetID = 0;
		
		parent::__construct($bd);
	}
	
	function load($id) {
	if($id > 0) {
			$bd = $this->get('bd');
			$res = $bd->query("SELECT id, obraID, data, userID, tipo, user_targetID, doc_targetID FROM obra_historico WHERE id = $id");
			
			if(is_array($res) && count($res) == 1){
				$this->set('obraID', $res[0]['obraID']);
				$this->set('user_targetID', $res[0]['user_targetID']);
				//$this->set('msg_targetID', $res[0]['msg_targetID']);
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
	
	function save(){
		if($this->get('id') == 0){
			$bd = $this->get('bd');
			//salva historico
			$this->set('data', time());
			$this->set('userID' ,$_SESSION['id']);
			
			if($this->get('doc_targetID') == '') $doc = 'NULL';
			else $doc = $this->get('doc_targetID');
			if($this->get('user_targetID') == '') $usr = 'NULL';
			else $usr = $this->get('user_targetID');
			
			$res = $bd->query("INSERT INTO obra_historico (obraID, data, userID, tipo, user_targetID, doc_targetID) VALUES ({$this->get('obraID')}, {$this->get('data')},{$this->get('userID')},'{$this->get('tipo')}',{$usr},{$doc})",null,true);
			if($res === false)
				return array("success" => false, "errorID" => 123, "errorFeedback" => "Erro ao salvar entrada de historico: ID retornado invalido");
			else
				$this->set('id', $res);
			return array("success" => true, "errorID" => '', "errorFeedback" => "");
			
		} else {
			return array("success" => false, "errorID" => 124, "errorFeedback" => "Erro ao salvar entrada de historico: Nao eh possivel editar historico");
		}
	}
	
	function printHTML(){
		$template['table_row'] = '<tr class="c"><td class="c">{$data}</td><td class="c"><td class="c"><a href="javascript:void(0)" onclick="showUserProfile('.$this->get('userID').')">{$userName}</a></td><td class="c">{$texto}</td></tr>';
		
		$texto = Historico_Obra::$frases;
		$texto = $texto[$this->get('tipo')];
		
		if(strpos($texto, '{$user_nomeCompl}') !== false) {
			$user = getNamesFromUsers($this->get('user_targetID'));
			$texto = str_replace('{$user_nomeCompl}', $user[0]['nomeCompl'], $texto);
		}
		
		if(strpos($texto, '{$doc_nome}') !== false || strpos($texto, '{$doc_assunto}') !== false || strpos($texto, '{$doc_tipo}') !== false) {
			$doc = new Documento($this->get('doc_targetID'));
			$doc->loadCampos();
			$texto = str_replace('{$doc_nome}', $doc->dadosTipo['nome'].' '.$doc->numeroComp, $texto);
			$texto = str_replace('{$doc_tipo}', $doc->dadosTipo['nome'], $texto);
			$texto = str_replace('{$doc_assunto}', $doc->campos['assunto'], $texto);
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
	public static function getAllHistID($obraID, BD $bd) {
		$sql = "SELECT id FROM obra_historico WHERE obraID = ".$obraID." ORDER BY data DESC";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna as 5 ultimas entradas do usuario passado por parametro
	 * @param int $userID
	 */
	public static function get5Ultimos($userID){
		global $bd;
		
		$sql = "SELECT id FROM obra_historico WHERE userID = {$userID} ORDER BY data DESC LIMIT 5";
		return $bd->query($sql);
	}
}
?>
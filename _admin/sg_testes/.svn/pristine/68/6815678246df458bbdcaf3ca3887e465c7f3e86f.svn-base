<?php

/*class EntradaHistoricoObra {
	private $id;
	
	private $obraID;
	
	private $label;
	
	private $dados;
	
	private $data;
	
	private $empreendID;
	
	private $user;
	
	function __construct($empreendID, $obraID = null, $labelID = null, $dados = null) {
		$this->id          = 0;
		$this->empreendID  = $empreendID;
		$this->obraID      = $obraID;
		$this->label['id'] = $labelID;
		$this->dados       = $dados;
		$this->user['id']  = $_SESSION['id'];
	}
	
	function save() {
		global $bd;print_r($this);
		
		if(!$this->id) {
			if($bd->query("INSERT INTO obra_historico (obraID, empreendID, labelID, dados, userID, data) VALUES ({$this->obraID}, {$this->empreendID}, {$this->label['id']}, '".json_encode($this->dados)."', {$this->user['id']}, ".time().")")) {
				$id = $bd->query("SELECT id FROM obra_historico WHERE obraID = {$this->obraID} AND labelID = {$this->label['id']} AND dados = '".json_encode($this->dados)."'");
				if(count($id)) {
					$this->id = $id[0]['id'];
					return true;
				} else {
					return false;
				} 
			} else {
				return false;
			}
		} else {
			if($bd->query("UPDATE obra_historico SET dados = '".json_encode($this->dados)."' WHERE id = $id")) {
				return true; 
			} else {
				return false;
			}
		}
	}
	
	function load($id){
		global $bd;
		
		$entr = $bd->query("SELECT id, obraID, labelID, dados, userID, data FROM obra_historico WHERE id = $id");
		
		if (count($entr)) {
			$lbl = $bd->query("SELECT text FROM label_obra_historico WHERE id = {$entr[0]['labelID']}");
			if (!count($lbl)) {
				return false;
			}
			
			$this->id     = $entr[0]['id'];
			$this->obraID = $entr[0]['obraID'];
			$this->label  = array('id' => $entr[0]['labelID'], 'text' => $lbl[0]['text']);
			$this->dados  = json_decode($entr[0]['dados'], true);
			$this->data   = array('unixtimestamp' => $entr[0]['data'], 'amigavel' => date('d/n/Y H:i', $entr[0]['data']));
			
			$user = $bd->query("SELECT * FROM usuarios WHERE id = {$entr[0]['userID']}");
			if(count($user)) {
				$this->user = $user[0];
			} else {
				$this->user = array('id' => 0, 'nome' => '', 'nomeCompl' => '', 'sobrenome' => '', 'username' => '');
			}
			
			if($this->dados == ''){
				$this->dados = array();
			}
			
			foreach ($this->dados as $attr => $val) {
				$this->label['text'] = str_replace('{$'.$attr.'}', $val, $this->label['text']);
			}
			return true;
			
		} else {
			return false;
		}
		
	}
	
	function set($attr, $val) {
		if(isset($this->$attr) || $attr != 'id') {
			$this->$attr = $val;
			return true;
		} else {
			return null;
		}
	}
	
	function get($attr){
	if(isset($this->$attr)) {
			return $this->$attr;
		} else {
			return null;
		}
	}
}*/

?>
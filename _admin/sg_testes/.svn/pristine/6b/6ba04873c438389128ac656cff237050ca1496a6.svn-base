<?php

class Etapa {
	private $id;
	
	/**
	 * tipo
	 * @var array
	 */
	public $tipo;
	private $empreendID;
	private $obraID;
	public $processoID;
	public $responsavelID;
	public $estado;
	public $enabled;
	public $fases;
	private $bd;

	/**
	 * Construtor da classe. Inicializa as variaveis
	 * @param int $id
	 * @param int $empreendID
	 * @param int $obraID
	 * @param int $tipoID
	 * @param int $procID
	 **/
	function __construct($id = 0, $empreendID = 0, $obraID = 0, $tipoID = 0, $procID = 0, $enabled = 1) {
		global $bd;
		
		//verif do ID da obra
		if (!$obraID && !$empreendID) return array("success" => false, "errorNo" => 1, "errorFeedback" => "Obra nula");
		
		//atribuicao das variaveis
		$this->id = $id;
		$this->obraID = $obraID;
		$this->enabled = $enabled;
		$this->responsavelID = 0;
		$this->processoID = $procID;
		$this->estado = array("id" => 0, "label" => "Desconhecido");
		$this->bd = $bd;
		$this->fases = array();
		
		if($obraID && !$empreendID){
			$sql = "SELECT empreendID FROM obra_obra WHERE id = {$this->obraID}";
			$r = $bd->query($sql);
			if(isset($r[0]['empreendID'])){
				$this->empreendID = $r[0]['empreendID'];
			} else {
				return array("success" => false, "errorNo" => 1, "errorFeedback" => "Obra nula");
			}
		} else {
			$this->empreendID = $empreendID;
		}
		
		$tp = $this->bd->query("SELECT * FROM label_obra_etapa WHERE id={$tipoID}");
		if (count($tp)) {
			$this->tipo = $tp[0];
		} else {			
			$this->tipo = array( "id" => $res[0]['tipoID'], "label" => "Desconhecido");
		}
	}
	
	/**
	 * Carrega os dados da etapa cujo ID eh passado por parametro 
	 */
	function load(){
		if($this->id == 0 && ($this->empreendID == 0 && $this->obraID = 0)){
			return array("success" => false, "errorNo" => 9, "errorFeedback" => "Impossivel acessar Etapa 0");
		}
		
		if($this->id){
			//seleciona o registro com o id passado
			$res = $this->bd->query("SELECT * FROM obra_etapa WHERE id=$this->id");
			//se houver apenas 1 etapa com esse ID
			if (count($res) != 1) 
				return array("success" => false, "errorNo" => "6A", "errorFeedback" => "Etapa com ID=$this->id Inexistente");
		} else {
			$res = $this->bd->query("SELECT * FROM obra_etapa WHERE tipoID = {$this->tipo['id']} AND obraID = {$this->obraID} AND empreendID = {$this->empreendID}");
			if (count($res) == 0) {

				$this->enabled = 1;
				
				$this->loadFases();
				return array("success" => true, "errorNo" => "", "errorFeedback" => "");
			}elseif(count($res) > 1){
				return array("success" => false, "errorNo" => "6B", "errorFeedback" => "Etapa {$this->tipo['id']} da obra $this->obraID do empreend $this->empreendID duplicada");
			}
		} 
		
		//atribui variaveis
		$this->id = $res[0]['id'];
		$this->obraID = $res[0]['ObraID'];
		$this->empreendID = $res[0]['empreendID'];
		$this->enabled = $res[0]['enabled'];
		$this->processoID = $res[0]['processoID'];
		$this->responsavelID = $res[0]['responsavel'];
		
		$tp = $this->bd->query("SELECT * FROM label_obra_etapa WHERE id={$res[0]['tipoID']}");
		if (count($tp)) {
			$this->tipo = $tp[0];
		} else {			
			$this->tipo['id'] = $res[0]['tipoID'];
			return array("success" => false, "errorNo" => 7, "errorFeedback" => "Erro ao ler o tipo de Etapa");
		}
		
		
		if($res[0]['estado']) {
			$res2 = $this->bd->query("SELECT label FROM label_etapa_estado WHERE id={$res[0]['estado']}");
			if(count($res2)) {
				$this->estado['label'] = $res2[0]['label'];
				$this->estado['id'] = $res[0]['estado'];
			}
		}
		/*
		if ($this->processoID) {
			$this->processo->loadCampos();
		}
		
		if($res[0]['responsavelID']){
			$resp = $this->bd->query("SELECT * FROM usuarios WHERE id={$res[0]['responsavelID']}");
			
			if(count($resp)) {
				$this->responsavel['id'] = $resp[0];
			} else {
				return array("success" => false, "errorNo" => 9, "errorFeedback" => "Erro ao ler o Responsavel");
			}
			
		}*/
		
		$this->loadFases();
		
		return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
	}
	
	/**
	 * Carrega as Fases da Etapa
	 */
	function loadFases(){
		//seleciona ID das fases
		$fasesID = Fase::getFasesPorEtapa($this->tipo['id']);
		//se houver fases retornadas
		if(count($fasesID)) {
			$this->fases = array();
			//para cada fase, carrega os attributos
			foreach ($fasesID as $f) {
				$fase = new Fase();
				$fase->load($this->id, $f['id']);
				//var_dump("ID -------------------- ".$this->id);
				$this->fases[] = $fase;
			}
		}
	}
	
	/**
	 * Metodo para salvar a Etapa no BD
	 */
	function save() {
		//$bd = $this->bd;
		global $bd;
		
		//verifica se a etapa ja esta cadastrada
		$res = $bd->query("SELECT id,responsavel FROM obra_etapa WHERE tipoID={$this->tipo['id']} AND obraID={$this->obraID} AND empreendID = {$this->empreendID}");
		
		if(count($res) == 1) {
		//esta cadastrado - UPDATE
			$this->id = $res[0]['id'];
			if($res[0]['responsavel'] > 0 && $this->responsavelID > 0 && $this->responsavelID !== null && $this->obraID == 0)
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,etapa_targetID,user_targetID) VALUES ({$this->empreendID}, ".time().", {$_SESSION['id']}, 'atribRespEtapa', {$this->id}, {$this->responsavelID})");
							
			if ($bd->query("UPDATE obra_etapa SET responsavel='{$this->responsavelID}', estado={$this->estado['id']}, processoID={$this->processoID}, enabled={$this->enabled}  WHERE id={$this->id}")) {
				return array("success" => true, "errorNo" => 0, "errorFeedback" => '');
			} else {
				return array("success" => false, "errorNo" => 1, "errorFeedback" => "Obra nula");
			}
			
		} elseif(!count($res)) {
		//nao esta cadastrado - INSERT
			if ($bd->query("INSERT INTO obra_etapa (obraID, tipoID, responsavel, processoID, empreendID, estado, enabled) VALUES ({$this->obraID}, {$this->tipo['id']}, {$this->responsavelID}, {$this->processoID}, {$this->empreendID}, {$this->estado['id']}, {$this->enabled})")) {
				$res = $bd->query("SELECT id FROM obra_etapa WHERE tipoID={$this->tipo['id']} AND obraID={$this->obraID} AND empreendID={$this->empreendID}");
				$this->id = $res[0]['id'];
				
				// recarrega fases
				//if (count($this->fases) > 0)
				$this->loadFases();
				
				return array("success" => true, "errorNo" => 0, "errorFeedback" => '');
			} else {
				return array("success" => false, "errorNo" => 3, "errorFeedback" => "Erro ao inserir Etapa da Obra");
			}
			
		} else {
		//algo esta errado 2 etapas iguais para uma mesma obra
			return array("success" => false, "errorNo" => 2, "errorFeedback" => "Erro de consistencia: Ha mais de uma etapa com essas caracteristicas.");
		}
	}
	
	/**
	 * Retorna do ID da Etapa
	 * @return number
	 */
	function getID(){
		return $this->id;
	}
	
	function updateFaseAttr($faseLabelID, $attr, $value) {
		//itera sobre as fases dessa etapa
		foreach ($this->fases as $fase) {
			//seleciona a fase dessa etapa
			if($fase->labelID == $faseLabelID){
				if(isset($fase->$attr))	{
					$fase->$attr = $value;
					return array("success" => true);
				}
			return array("success" => false, "errorNo" => 11, "errorFeedback" => "Atributo Inexistente");
			}
		}
		return array("success" => false, "errorNo" => 12, "errorFeedback" => "Fase Invalida");
	}
	
	static function getEtapaPorEmpreend($empreendID){
		global $bd;
		return $bd->query("SELECT id FROM label_obra_etapa WHERE refEmpreend = 1");
		//$bd->query("SELECT id FROM obra_etapa WHERE obraID=0 AND empreendID=$empreendID");
	}
	
	static function getEtapaPorObra($obraID){
		global $bd;
		return $bd->query("SELECT id FROM label_obra_etapa WHERE refObra = 1");
		//return $bd->query("SELECT id FROM obra_etapa WHERE obraID=$obraID");
	}
	
	function showResponsaveisInterface($responsavelID) {
		require_once 'sgp_modules.php';
		global $bd;
		$template = showRespEtapaTemplate();
		$html = $template['template'];
		$campos = '';
		
		$r = getAllUsersName();
		foreach ($r as $u) {
			$users[] = array('value' => $u['id'] , 'label' => $u['nomeCompl']);
		}
		
		if ($_SESSION['id'] == $responsavelID || isIndirectManager($_SESSION['id'], $responsavelID, $bd) || $_SESSION['grupo'] == 2 || checkPermission(91)) {
			$select = geraSelect('resp', $users, $this->responsavelID, 0);
		}
		else {
			$user = getUsers($this->responsavelID);
			if (count($user) <= 0) {
				$select = "Desconhecido.";
			}
			else {
				$select = $user[0]['nomeCompl'];
			}
		}
		
		$aux = str_replace('{$campo_html}', $select, $template['campo']);
		$aux = str_replace('{$campo_nome}', "Respons&aacute;vel pelo(a) ".$this->tipo['nome'], $aux);
		
		$aux = str_replace('{$estado}', $this->estado['label'], $aux);
		
		$campos .= $aux;

		foreach ($this->fases as $f) {
			if ($_SESSION['id'] == $this->responsavelID ||
			    isIndirectManager($_SESSION['id'], $this->responsavelID, $bd) ||
			    $_SESSION['id'] == $responsavelID ||
			    isIndirectManager($_SESSION['id'], $responsavelID, $bd) ||
			    $_SESSION['grupo'] == 2) 
			{
				$select = geraSelect('resp'.$f->dadosTipo['id'], $users, $f->responsavelID, 0);
			}
			else {
				$user = getUsers($f->responsavelID);
				if (count($user) <= 0) {
					$select = "Desconhecido.";
				}
				else {
					$select = $user[0]['nomeCompl'];
				}
			}
			
			//$select = geraSelect('resp'.$f->dadosTipo['id'], $users, $f->responsavelID, 0);
				
			$aux = str_replace('{$campo_html}', $select, $template['campo']);
			$aux = str_replace('{$campo_nome}', $f->dadosTipo['nome'], $aux);
			
			$estado = "N&atilde;o iniciado.";
			if($f->responsavelID){
				$estado = "Iniciado";
				if($f->concluido)
					$estado = "Finalizado";
			}
			
			$aux = str_replace('{$estado}', $estado, $aux);
			
			$campos .= $aux;
		}
		
		$select = geraSelect('todosSelect', $users, 0, 0);
		
		if ($_SESSION['id'] == $responsavelID || isIndirectManager($_SESSION['id'], $responsavelID, $bd) || $_SESSION['grupo'] == 2) {
			$select = geraSelect('todosSelect', $users, 0, 0);
		}
		else {
			$select = '<script type="text/javascript">$(document).ready(function() { $("#todosSelectTr").hide(); $("#submitResp").hide(); });</script>';
		}
		
		$html = str_replace('{$empreendID}', $this->empreendID, $html);
		$html = str_replace('{$obraID}', $this->obraID, $html);
		$html = str_replace('{$tipoID}', $this->tipo['id'], $html);
		$html = str_replace('{$nome_etapa}', $this->tipo['nome'], $html);
		$html = str_replace('{$todos_select}', $select, $html);
		$html = str_replace('{$campos}', $campos, $html);
		return $html;
	}
	
	function get($attr){
		if(isset($this->$attr))
			return $this->$attr;
		else
			return null;
	}
}
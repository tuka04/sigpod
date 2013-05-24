<?php

class Recurso {
	
	private $id;
	
	public $empreendID;
	
	public $obraID;
	
	public $montante;
	
	public $origem;
	
	public $prazo;
	
	public $tipo;//pode ser c=credito ou d=debito
	
	public $justificativa;
	
	public $respUser;
	
	public $dataUltimaModif;
	
	private $bd;
	
	/**
	 * Metodo construtor. Preenche com os dados de formulario enviado ou apenas cria um Rercurso em branco
	 */
	function __construct($bd) {
		//na epoca de construido, o recurso ainda nao tem ID
		$this->id = 0;
		//se ha dados de formulario enviado, le e constroi a classe com esses dados
		if (isset($_POST['montanteRec']) && isset($_POST['origemRec']) && isset($_POST['prazoRec']) && $_POST['montanteRec'] && $_POST['jutifRec'] != '') {
			
			$this->montante = str_ireplace(',','.', str_ireplace('.', '', $_POST['montanteRec'])); 
						
			$this->origem   = SGEncode($_POST['origemRec'],ENT_QUOTES, null, false);
			//se o prazo estiver em branco, atribui NULL, senao, tenta ler a data entrada
			$this->setPrazo($_POST['prazoRec']);
		
			$this->justificativa = SGEncode($_POST['justifRec'],ENT_QUOTES, null, false);
			
		}
		if(isset($_SESSION['id']))
			$this->respUser['id'] = $_SESSION['id'];
		$this->dataUltimaModif = time();
		
		if($this->montante > 0) $this->tipo = 'c';
		else $this->tipo = 'd';
		
		$this->bd = $bd;
	}
	
	/**
	 * Metodo que carrega os dados do recurso com o id passado por parametro
	 * @param int $id
	 */
	function load($id) {
		//seleciona a coluna do BD
		$rec = $this->bd->query("SELECT obraID, montante, origem, prazo, empreendID, tipo, ultimaModData,responsavelUserID,justificativa FROM obra_rec WHERE id={$id}");
		//se houver exatamente 1 recurso encontrado
		if(count($rec) == 1) {
			//atribui os valores as variaveis
			$this->id              = $id;
			$this->empreendID      = $rec[0]['empreendID'];
			$this->obraID          = $rec[0]['obraID'];
			$this->montante        = $rec[0]['montante'];
			$this->origem          = $rec[0]['origem'];
			$this->prazo           = $rec[0]['prazo'];
			$this->tipo            = $rec[0]['tipo'];
			$this->dataUltimaModif = $rec[0]['ultimaModData']; 
			$this->justificativa   = $rec[0]['justificativa'];			
		
			$this->setUser($rec[0]['responsavelUserID']);
			
			//retorna sucesso
			return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
		} else {
			//senao, algo muito estranho aconteceu
			return array("success" => false, "errorNo" => 6, "errorFeedback" => "Recurso Inexistente");
		}
	}
	
	/**
	 * Metodo para inserir recurso em uma determinada empreendimento com um ID passado por parametro
	 * @param int $empreendID
	 */
	function insertRecursoInEmpreend($empreendID) {
		//se foi inserido um ID inválido
		if(!$empreendID) return array("success" => false, "errorNo" => 5, "errorFeedback" => "ID invalido");
		//se foi inserido montante em branco
		if(!$this->montante) return array("success" => false, "errorNo" => 5, "errorFeedback" => "Nao e possivel adicionar recurso igual a zero ou vazio");
		
		$this->setUser($_SESSION['id']);
		
		//insercao no BD
		$r = $this->bd->query("INSERT INTO obra_rec (empreendID, obraID,montante,origem,prazo,justificativa,tipo,responsavelUserID,ultimaModData) VALUES ($empreendID, 0,{$this->montante},'{$this->origem}',{$this->prazo},'{$this->justificativa}','c','{$this->respUser['id']}','{$this->dataUltimaModif}')");
		
		//descoberta do id
		//EH FALSO SE HA 2 RECURSOS IGUAIS! DIFERENCIACAO OU SEM ID??
		$sql = "SELECT id FROM obra_rec WHERE empreendID=$empreendID AND montante={$this->montante} AND origem = '{$this->origem}' AND ";
		if($this->prazo !== 'NULL')
			$sql .= "prazo={$this->prazo}";
		else
			$sql .= "prazo IS NULL ";
		$sql .= " ORDER BY id DESC";
		
		$recID = $this->bd->query($sql);
		//achou id
		if (isset($recID[0])) {
			$this->id = $recID[0]['id'];
			return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
			doLog($_SESSION['username'], 'Adicionou recurso de R$ '.$this->montante.' ao empreendimento '.$this->empreendID);
		} else {
			return array("success" => false, "errorNo" => 2, "errorFeedback" => "ID invalido");
			doLog($_SESSION['username'], 'Erro ao adicionar recurso de R$ '.$this->montante.' ao empreendimento '.$this->empreendID);
		}
	}
	
	/**
	 * Salva no BD as atualizacoes de recurso
	 */
	function save() {
		//se hao ha id para selecionar registro
		if(!$this->id) return array("success" => false, "errorNo" => 5, "errorFeedback" => "ID invalido");
		$this->dataUltimaModif = time();
		$this->setUser($_SESSION['id']);
		
		//atualizacao do BD
		$res = $this->bd->query("UPDATE obra_rec SET montante={$this->montante}, origem='{$this->origem}', prazo={$this->prazo}, obraID={$this->obraID}, justificativa='{$this->justificativa}', responsavelUserID = {$this->respUser['id']}, ultimaModData = ".time()." WHERE id={$this->id}");
		
		//retorno
		if($res) {
			return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
			doLog($_SESSION['username'], 'Editou recurso '.$this->id.' ao empreendimento '.$this->empreendID);
		} else {
			return array("success" => false, "errorNo" => 4, "errorFeedback" => "Falha ao atualizar recurso na base de dados");
			doLog($_SESSION['username'], 'Erro ao editar recurso '.$this->id.' ao empreendimento '.$this->empreendID);
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
	 * Seta responsavel
	 * @param $userID
	 */
	function setUser($userID){
		$user = $this->bd->query("SELECT * FROM usuarios WHERE id={$userID}");
		if(count($user) == 1) {
			$this->respUser = $user[0];
		} else {
			$this->respUser = null;
		}
	}
	
	/**
	 * Seta prazo
	 * @param $data
	 */
	function setPrazo($data){
		if (preg_match("|[0-9]{2}/[0-9]{2}/[0-9]{2}|", $data, $matches) ||
			preg_match("|[0-9]{2}/[0-9]{2}|", $data, $matches) ||
			preg_match("|[0-9]{2}/[0-9]{2}/[0-9]{4}|", $data, $matches)) {
			$prazo = explode('/',$data);
			$this->prazo = mktime(0,0,0,$prazo[1],$prazo[0],$prazo[2]);
		} else {
			$this->prazo = 'NULL';
		}
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
}
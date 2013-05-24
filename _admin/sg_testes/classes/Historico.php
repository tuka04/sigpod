<?php
abstract class Historico {
	private $id;
	private $data;
	private $userID;
	private $tipo;
	private $bd;
	
	/**
	 * Construtor
	 * @param int $categoria identificador da categoria do histórico a ser carregado
	 * @param BD $bd
	 */
	function __construct(BD $bd) {
		$this->id = 0;
		$this->data = 0;
		$this->tipo = 0;
		//$this->msg = array();
		//$this->extra = null;
		
		$this->bd = $bd;
		
		$this->userID = 0;
		if (isset($_SESSION['id'])) {
			$this->userID = $_SESSION['id'];
		}
	}
	
	/**
	 * Seta o valor do campo
	 * @param string $campo nome do campo
	 * @param mixed $value valor a ser setado do campo
	 */
	public function set($campo, $value) {
		if ($campo == null)
			return null;
			
		if (isset($this->$campo)) {
			$this->$campo = $value;
			return true;
		}
		
		return null;
	}
	
	/**
	 * Retorna valor do campo
	 * @param string $campo nome do campo
	 */
	public function get($campo) {
		if (isset($this->$campo)) {
			return $this->$campo;
		}
		
		return null;
	}
	
	/**
	 * Carrega o histórico em sí
	 * @param int $id
	 */
	abstract public function load($id);
	
	
	/**
	 * Salva o histórico
	 * @return ?
	 */
	abstract public function save();
	
	
	/**
	 * Imprime o HTML
	 * @return html
	 */
	abstract public function printHTML();
	
	/**
	 * Imprime o html formatado para ajax
	 * @return JSON
	 */
	abstract function printHTMLAjax();
	
	
	public static function getTemplate(){
		return '<table width="100%">
		<thead>
			<tr class="c">
				<td class="c" width="100"><b>Data</b></td>
				<td class="c" width="150"><b>Usu&aacute;rio</b></td>
				<td class="c"><b>Hist&oacute;rico</b></td>
			</tr>
		</thead>
		<tbody>
			{$linhas_historico}
		</tdody>
		</table>';
	}
	
	public function getUsername() {
		if ($this->userID == 0) return null;
		
		$sql = "SELECT username FROM usuarios WHERE id = ".$this->userID;
		$ret = $this->bd->query($sql);
		
		if (count($ret) > 0)
			return $ret[0]['username'];
		else
			return null;
	}
	
	public function getName() {
		if ($this->userID == 0) return null;
		
		$sql = "SELECT nome FROM usuarios WHERE id = ".$this->userID;
		$ret = $this->bd->query($sql);
		
		if (count($ret) > 0)
			return $ret[0]['nome'];
		else
			return null;
	}
	
	/*
	 * public static function getAllFromID($id);
	 *
	 * @return [id][label]
	 * public static function getAllTypes();
	 */
}

?>
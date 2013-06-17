<?php
require_once 'interfaces/AditivoIF.class.php';
require_once 'dao/AditivoDAO.class.php';
class Aditivo extends AditivoDAO implements AditivoIF{
	/**
	 * @var string
	 */
	private $nome;
	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var AditivoTipo
	 */
	private $tipo;
	/**
	 * @var ArrayObject (double ou integer)
	 */
	private $valor;
	/**
	 * @var ArrayObject
	 */
	private $motivo;
	/**
	 * Id do documento
	 * @var int
	 */
	private $docId;
	
	public function Aditivo($bd,$docId,$nome="",$label="",$tipo="",$valor="",$motivo=""){
		parent::AditivoDAO($bd);
		if(count(func_get_args())==0){
			$this->instantiate();//instacie os objetos apenas.
			return;
		}
		$this->docId=$docId;
		$this->nome=$nome;
		$this->label=$label;
		$this->tipo=$tipo;
		if(is_array($valor))
			$this->valor=new ArrayObject($valor);
		if(is_array($motivo))
			$this->motivo=new ArrayObject($motivo);
		else {
			$this->instantiate();
			$this->valor->append($valor);
			$this->motivo->append($motivo);
		}
	}
	/**
	 * instacia novos objetos (atributos q sao objetos)
	 */
	private function instantiate(){
		$this->valor = new ArrayObject();
		$this->motivo = new ArrayObject();
	}
	
	public function getVar($var){
		return (property_exists("Aditivo", $var))?$this->$var:null;
	}
	
	public function setVar($var,$val){
		if(property_exists("Aditivo", $var)){
			if(is_object($this->$var) && ($this->$var instanceof ArrayObject))//entao eh arrayObject
				$this->$var->append($val);
			else 
				$this->$var=$val;
		}
	}
	/**
	 * Retorna um html com os aditivos (valor e motivos)
	 */
	public function toHtml(){
		
	}
	/**
	 * Constroi esse objeto setando valores
	 * @param unknown $arr
	 */
	private function load($arr){
		foreach ($arr as $v){
			$this->setVar("valor", $v["valor"]);
			$this->setVar("motivo", $v["motivo"]);
		}
	}
	/**
	 * Retorna aditivos para esse docID (com o $this->nome a partir de uma consulta mysql
	 * @return array
	 */
	public function getAditivos(){
		$this->instantiate();
		$where = "WHERE contratoID = ".$this->docId;
		$limit = "";
		$order = "";
		if(!empty($this->nome))
			$where .= " AND contrato_aditivo.campo LIKE '".$this->nome."'";
		$qr = str_replace(DAO::TOKEN_TABELA, self::TABELA, DAO::SELECT);
		$qr = str_replace(DAO::TOKEN_CAMPOS, self::CAMPOS, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $where, $qr);
		$rqr = $this->bd->query($qr);
		return $rqr;
	}
	/**
	 * Retorna a soma de todos os valores desse aditivo
	 * @return mixed: double int
	 */
	public function getSum(){
		$total = 0.00;
		$valores = $this->getVar("valor")->getArrayCopy();
		foreach ($valores as $v)
			$total+=$v;
		return $total;
	}
} 
/**
 * Tipos de aditivos
 */
class AditivoTipo{
	const Monetario=1;
	const Diario=2;
}
?>
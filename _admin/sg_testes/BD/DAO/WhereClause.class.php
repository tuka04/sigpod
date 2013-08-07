<?php
/**
 * WhereClause
 * @version 0.1 (01/08/2013)
 * @package geral
 * @subpackage BD.DAO
 * @author Leandro Kümmel Tria Mendes
 * @desc classe que manipula uma clausula WHERE de uma query no banco 
 * @see Acesso e querys aos bancos de dados
 * 
 * **** -> Abaixo temos a classe OrderedBy
 */
class WhereClause {
	/**
	* @var string
	*/
	private $campo;
	/**
	* @var string
	*/
	private $valor;
	/**
	 * Clausula de Interseccao (and ou or) entre where_clauses 
	 * @var string
	 */
	private $inter;
	/**
	 * @var string
	 */
	private $comp;
	/**
	 * @param string $campo
	 * @param string|ArrayObj $valor
	 * @param string $comp
	 * @param string $inter
	 */
	public function __construct($campo="",$valor="",$comp=" = ",$inter="AND"){
		if(is_array($campo)){
			if(!is_array($valor) || count($campo)!=count($valor))
				die("WhereClause: ".__FILE__." ".__LINE__." Erro na entrada array campo != array valor");
			$this->campo=$campo;
			$this->valor=$valor;
			$this->comp=$comp;
			$this->inter=$inter;
			return;
		}
		$this->comp=$comp;
		if(is_array($valor)){
			$this->comp="IN";
			if(!is_object($valor))
				$valor = new ArrayObj($valor);
			$valor->putQuotation(false);
			$this->valor="(".$valor->toString().")";
		}
		else 
			$this->valor="'".$valor."'";
		$this->campo=$campo;
		
	}
	/**
	 * @param string|array $campo
	 * @param string|array|ArrayObj $valor
	 * @param string|array $comp
	 * @param string $inter
	 * @return string
	 */
	private function getManyClauses(){
		$res = "";
		if(is_array($this->comp)&& count($this->comp)!=count($this->campo))
			die("WhereClause: ".__FILE__." ".__LINE__." Erro na entrada count (array campo) != count(array comp)");
		else{
			foreach ($this->campo as $i=>$c){
				if(is_array($this->comp))
					$compare = $this->comp[$i];
				else
					$compare=$this->comp;
				if(is_array($this->valor[$i])||is_object($this->valor[$i])){
					if(!is_object($this->valor[$i]))
						$this->valor[$i] = new ArrayObj($this->valor[$i]);
					$this->valor[$i]->putQuotation(true);
					$res .= $c." IN (".$this->valor[$i]->toString().") ".$this->inter." ";
				}
				else{
					$res .= $c." ".$compare." '".$this->valor[$i]."' ".$this->inter." ";
				}
			}
		}
		return rtrim($res,$this->inter." ");
	}
	/**
	 * @return string
	 */
	private function getSingleClause(){
		return $this->campo." ".$this->comp." ".$this->valor." ";
	}
	/**
	 * @return string
	 */
	public function getClause(){
		if($this->campo=="")
			return "";
		if(is_array($this->campo))
			return $this->getManyClauses();
		return $this->getSingleClause();
	}
	/**
	 * @return string
	 */
	public function toString(){
		if(empty($this->campo)||empty($this->valor))
			return "";
		return " WHERE ".$this->getClause();
	}
	
}

class OrderedBy {
	/**
	 * @var string
	 */
	private $campo;
	/**
	 * @var string
	 */
	private $order;
	/**
	 * @param string $campo
	 * @param string $order
	 */
	public function __construct($campo="",$order="ASC"){
		$this->campo=$campo;
		$this->order=$order;
	}
	/**
	 * @return string
	 */
	public function toString(){
		if(empty($this->order)||empty($this->campo))
			return "";
		return " ORDER BY ".$this->campo." ".$this->order;
	}
}

class LimitQuery{
	/**
	 * @var int
	 */
	private $ini;
	/**
	 * @var int
	 */
	private $end;
	/**
	 * @param string $ini
	 * @param string $end
	 */
	public function __construct($ini="",$end=""){
		$this->ini=$ini;
		$this->end=$end;
	}
	/**
	 * @return string
	 */
	public function toString(){
		if($this->ini==""||$this->end=="")
			return "";
		return " LIMIT ".$this->ini." , ".$this->end;
	}
}
?>
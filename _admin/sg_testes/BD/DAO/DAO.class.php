<?php
/**
 * Data Access Object
 * @version 0.1 (28/6/2013)
 * @package geral
 * @subpackage BD
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd
 * @see Acesso e querys aos bancos de dados
 */
require_once "interfaces/DAOIF.class.php";
require_once "JoinSQL.class.php";
require_once "WhereClause.class.php";

abstract class DAO extends BD implements DAOIF{
	const SELECT = "SELECT {campos} FROM {tabela} {where} {order} {limit};";
	const SELECT_JOIN = "SELECT {campos} FROM {tabela} {join} {where} {order} {limit};";
	const SELECT_DISTINCT = "SELECT DISTINCT {campos} FROM {tabela} {where} {order} {limit};";
	const INSERT= "INSERT INTO {tabela} ({campos}) VALUES ({valores});";
	const INSERT_DUPLICATE= "INSERT INTO {tabela} ({campos}) VALUES ({valores}) ON DUPLICATE KEY UPDATE {duplicate};";
	const UPDATE= "UPDATE {tabela} SET {campos} = '{valores}';";
	const DELETE = "DELETE FROM {tabela} {where};";
	const TOKEN_INSERT_VALORES = "{valores}";
	const TOKEN_INSERT_DUPLICATE = "{duplicate}";
	const TOKEN_UPDATE_VALOR = self::TOKEN_INSERT_VALORES;//alias
	const TOKEN_CAMPOS = "{campos}";
	const TOKEN_UPDATE_CAMPOS = self::TOKEN_CAMPOS;//alias
	const TOKEN_TABELA = "{tabela}";
	const TOKEN_WHERE = "{where}";
	const TOKEN_JOIN = "{join}";
	const TOKEN_ORDER = "{order}";
	const TOKEN_LIMIT = "{limit}";
	/**
	 * tabela
	 * @var String
	 */
	private $tabela;
	/**
	 * array com os campos
	 * @var array
	 */
	private $campos;
	
	public function __construct($tabela,$campos,$prod=false){
		$this->DAO($tabela,$campos,$prod);
	}
	/**
	 * se prod == true entao usa banco de producao
	 * @param unknown $tabela
	 * @param unknown $campos
	 * @param string $prod
	 */
	public function DAO($tabela,$campos,$prod=false){
		global $conf;
		include_once('conf.inc.php');
		if($prod){
			$conf["DBLogin"]="root";
			$conf["DBPassword"]="18RxMr31";
			$conf["DBhost"]=array('master' => 'arquiteto.cpo.unicamp.br', 'slave' => 'engenheiro.cpo.unicamp.br');
			$conf["DBTable"]="sg";
		}
		parent::__construct($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"], new ArrayObj($campos));
		$this->tabela=$tabela;
		$this->campos=$campos;
	}
	/**
	 * Cria uma tabela no banco de dados, se ela não existir
	 * @see DAOIF::create()
	 */
	public function create(){
		
	}
	
	public function insert($valores){
		$qr = DAO::INSERT;
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_CAMPOS, implode(',',$this->campos), $qr);
		foreach ($valores as &$v){
			if($v!=NULL)
				$v = '"'.$v.'"';
			else 
				$v = 'NULL';
		}
		$qr = str_replace(DAO::TOKEN_INSERT_VALORES, implode(',',$valores), $qr);
		return $this->query($qr,null,true);
	}
	
	/**
	 * Caso não exista do registro então ele será inserido se não sofre update
	 * Utiliza o statement ON DUPLICATE KEY...do MySQL 5.0 >
	 * @param $valores : valores a serem inseridos
	 */
	public function insertDuplicatedKey($valores){
		$qr = DAO::INSERT_DUPLICATE;
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_CAMPOS, implode(',',$this->campos), $qr);
		foreach ($valores as &$v){
			if($v!=NULL)
				$v = '"'.$v.'"';
			else
				$v = 'NULL';
		}
		$dpl='';
		foreach ($this->campos as $k=>$c)
			$dpl .= " ".$c." = ".$valores[$k]." ,";
		$dpl=substr($dpl, 0,-2);
		$qr = str_replace(DAO::TOKEN_INSERT_VALORES, implode(',',$valores), $qr);
		$qr = str_replace(DAO::TOKEN_INSERT_DUPLICATE, $dpl, $qr);
		return $this->query($qr);
	}
	
	public function updateField($campo,$valor){
		$qr = DAO::UPDATE;
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_CAMPO,$campo, $qr);
		$qr = str_replace(DAO::TOKEN_UPDATE_VALOR,$valor, $qr);
		return $this->query($qr);
	}
	
	public function selectJoin(ArrayObj $j, WhereClause $w=null, OrderedBy $ord = null){
		$campos = new ArrayObj($this->campos);
		if($ord==null)
			$ord=new OrderedBy();
		else if($w==null)
			$w=new WhereClause();
		$qr = DAO::SELECT_JOIN;
		$qr = str_replace(DAO::TOKEN_CAMPOS, $campos->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w->toString(), $qr);		
		$qr = str_replace(DAO::TOKEN_JOIN, $j->toString(" "), $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, $ord->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
	/**
	 * @param OrderedBy $order : campo a ser ordenado
	 * @param WhereClause $w = null
	 * @param LimitQuery $l = null
	 * @return Ambigous <mixed, boolean, number, multitype:multitype: >
	 */
	public function selectOrderedBy(OrderedBy $order, WhereClause $w=null, LimitQuery $l=null){
		$qr = DAO::SELECT;
		if($w==null)
			$w=new WhereClause();
		if($l==null)
			$l=new LimitQuery();
		$qr = str_replace(DAO::TOKEN_CAMPOS, "*", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, $order->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, $l->toString(), $qr);
		return $this->query($qr);
	}
	
	public function select($campo="",$valor="",$comp=" = "){
		$w = new WhereClause($campo, $valor, $comp);
		$qr = DAO::SELECT;
		$campos = new ArrayObj($this->campos);
		$qr = str_replace(DAO::TOKEN_CAMPOS, $campos->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
	
	public function selectDistinct($campo="",$valor="",$comp=" = "){
		$w = new WhereClause($campo, $valor, $comp);
		$qr = DAO::SELECT_DISTINCT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, implode(",",$this->campos), $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w->toString(), $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
	
	public function remove($campo="",$valor="",$comp = " = "){
		$qr = DAO::DELETE;
		if(empty($campo)||empty($valor))
			$w = "";
		elseif(is_array($valor))
			$w = "WHERE ".$campo." ".$comp." (".implode(",",$valor).") ";
		else
			$w = "WHERE ".$campo." ".$comp." '".$valor."'";
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w, $qr);
		return $this->query($qr);
	}
	
	public function setVar($var,$val){
		if(isset($this->$var)){
			$this->$var=$val;
			return true;
		}
		return false;
	}
	/**
	 * Retorna os campos no estilo tabela.campo1 as "tabela.campo1", tabela.campo2 as "tabela.campo2" ...
	 * @return string
	 */
	public function getCamposSelect(){
		$str = "";
		foreach ($this->campos as $c)
			$str .= $this->tabela.".".$c." as '".$this->tabela.".".$c."', ";
		return rtrim($str,", ");
	}
	/**
	 * @return multitype:
	 */
	public function getCampos(){
		return $this->campos;
	}
}
?>
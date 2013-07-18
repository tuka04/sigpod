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

abstract class DAO extends BD implements DAOIF{
	const SELECT = "SELECT {campos} FROM {tabela} {where} {order} {limit};";
	const INSERT= "INSERT INTO {tabela} ({campos}) VALUES ({valores});";
	const UPDATE= "UPDATE {tabela} SET {campos} = '{valores}';";
	const DELETE = "DELETE FROM {tabela} {where};";
	const TOKEN_INSERT_VALORES = "{valores}";
	const TOKEN_UPDATE_VALOR = self::TOKEN_INSERT_VALORES;//alias
	const TOKEN_CAMPOS = "{campos}";
	const TOKEN_UPDATE_CAMPOS = self::TOKEN_CAMPOS;//alias
	const TOKEN_TABELA = "{tabela}";
	const TOKEN_WHERE = "{where}";
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
	
	public function __construct($tabela,$campos){
		$this->DAO($tabela,$campos);
	}
	
	public function DAO($tabela,$campos){
		global $conf;
		include_once('conf.inc.php');
		parent::__construct($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
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
	
	public function updateField($campo,$valor){
		$qr = DAO::UPDATE;
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_CAMPO,$campo, $qr);
		$qr = str_replace(DAO::TOKEN_UPDATE_VALOR,$valor, $qr);
		return $this->query($qr);
	}
	
	public function select($campo="",$valor="",$comp=" = "){
		if(empty($campo)||empty($valor))
			$w = "";
		elseif(!is_array($valor))
			$w = "WHERE ".$campo." ".$comp." '".$valor."'";
		elseif(is_array($valor))
			$w = "WHERE ".$campo." ".$comp." (".implode(",",$valor).") ";
		$qr = DAO::SELECT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, "*", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, $this->tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w, $qr);
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
}
?>
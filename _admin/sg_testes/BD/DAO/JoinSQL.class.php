<?php 

class JoinSQL {
	/**
	 * tabela
	 * @var string
	 */
	private $table;
	/**
	 * campo estrangeiro
	 * @var string
	 */
	private $foreingKey;
	/**
	 * campo
	 * @var string
	 */
	private $key;
	
	private $type;
	/**
	 * alias para a tabela
	 * @var string
	 */
	private $asTable;
	
	public function __construct($table,$foreingKey,$key,$type="INNER"){
		$this->table=$table;
		$this->foreingKey=$foreingKey;
		$this->key=$key;
		$this->type=$type;
		$this->asTable="";
	}
	/**
	 * @param string $asTable
	 */
	public function setAsTable($asTable=""){
		$this->asTable=$asTable;
	}
	/**
	 * @return string
	 */
	public function getAsTable(){
		return $this->asTable;
	}
	/**
	 * Retorna a parte do sql com join 
	 * @return string
	 */
	public function getSQL(){
		$as="";
		if($this->asTable!="")
			$as = " AS ".$this->asTable;
		return $this->type." JOIN ".$this->table.$as." ON ".$this->table.".".$this->foreingKey." = ".$this->key;
	}
	/**
	 * @alias: $this->getSQL()
	 * @return string
	 */
	public function toString(){
		return $this->getSQL();
	}
	/**
	 * Retorna a table join
	 * @return string
	 */
	public function getTable(){
		return $this->table;
	}
}
?>
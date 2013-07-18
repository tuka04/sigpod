<?php
	/**
	 * @version 0.1 17/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc lida com o requisicoes para o BD
	 */
require_once "BD/DAO/DAO.class.php";
class BD {
	/**
	 * string que contem o host
	 * @var string
	 */
	private $host;
	private $port;
	
	/**
	 * login do BD
	 * @var string
	 */
	private $login;
	
	/**
	 * senha do BD
	 * @var string
	 */
	private $password;
	
	/**
	 * tabela a ser selecionada
	 * @var string
	 */
	private $table;
	
	/**
	 * variavel de conexao
	 * @var connection
	 */
	private $conn;
	
	/**
	 * @desc construtor da classe. inicia a conexao com o BD
	 * @param string $login 
	 * @param string $password
	 * @param string $host 
	 * @param string $table
	 * @return variavel da conexao
	 */
	public function __construct($login = '', $password = '', $host = '', $table = '') {
		global $conf;
		
		$this->host = $conf['DBhost'];
		$this->port = $conf['DBport'];
		$this->login = $conf['DBLogin'];
		$this->password = $conf['DBPassword'];
		$this->table = $conf['DBTable'];
		
		$success = false;
		
		if($conf['debugMode']){ print "Selecionando BD"; }

		if(@fsockopen($this->host['master'], $this->port, $erroNo, $erroMsg, 1)) {
			$this->conn = mysql_connect($this->host['master'].':3306', $this->login, $this->password) or die("Impossivel conectar ao master: ".mysql_error()); 
			if($this->conn) {
				
				$success = true;
				if($conf['debugMode']){print 'master';}
			}
		} elseif (@fsockopen($this->host['slave'], $this->port, $erroNo, $erroMsg, 1)) {
			$this->conn = mysql_connect($this->host['slave'], $this->login, $this->password) or die("Impossivel conectar ao slave: ".mysql_error());
			
			$success = true;
			if($conf['debugMode']){print 'slave';}
		}
		if (!$this->conn) {
			showError(2);
		}
		
		
	}
	
	/**
	 * @desc fecha a conexao com o bd
	 */
	public function disconnect(){
		mysql_close($this->conn);
	}
	
	/**
	 * Solicitacao 003
	 * Método que faz o replace de tokens que estao na query
	 * @param string $sql
	 */
	private function checkToken($sql){
		$sql = str_replace(DAO::TOKEN_CAMPOS, "", $sql);
		$sql = str_replace(DAO::TOKEN_TABELA, "", $sql);
		$sql = str_replace(DAO::TOKEN_WHERE, "", $sql);
		$sql = str_replace(DAO::TOKEN_LIMIT, "", $sql);
		return str_replace(DAO::TOKEN_ORDER, "", $sql);
	}
	
	/**
	 * @desc executa uma query na tabela passada por parametro
	 * @param string $sql SQL query a ser executada
	 * @param string $table tabela para ser procurada
	 * @param boolean $returnIDafterInsert indica se o método deve retornar o ID da inserção (INSERT)
	 * @return mixed associativa dos resultados ou true (dependendo da consulta)
	 */
	public function query($sql, $table = null, $returnIDafterInsert = false) {
		$sql=$this->checkToken($sql);
		if($table == null){
			global $conf;
			$table = $conf['DBTable'];
		}
		$selectedDB = mysql_select_db($table,$this->conn) or showError(3);
		$r = mysql_query($sql);
		if ($r === true) {
			if (!$returnIDafterInsert) return TRUE;
			else return mysql_insert_id($this->conn);
		}
		if ($r === false){
			print 'Erro na consulta: '.$sql.' Erro obtido: '.mysql_error().' <br>
			<span style="color: red;">Esse &eacute; um erro grave. Informe o Administrador do sistema o quanto antes</span> <a href="report_bug.php">clicando aqui.</a>';			
			exit();
			doLog($_SESSION['username'], 'Erro na consulta: '.$sql.' Erro obtido: '.mysql_error());
		}
		$ret = array();
		while ($res = mysql_fetch_assoc($r)){
			$ret[] = $res;
		}
		return $ret;
	}
}
?>
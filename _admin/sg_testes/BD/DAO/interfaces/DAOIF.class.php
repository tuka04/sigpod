<?php
/**
 * Data Access Object
 * @version 0.1 (13/6/2013)
 * @package geral
 * @subpackage BD
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd
 * @see Acesso e querys aos bancos de dados
 */
interface DAOIF {
	/**
	 * Construtor
	 * @param string $tabela ; nome da tabeka
	 * @param array $campos ; array com campos
	 */
	public function DAO($tabela,$campos);
	/**
	 * Cria a tabela
	 */
	public function create();
	
	/**
	 * Seta uma variavel, que existe, desse objeto
	 * @param string $var
	 * @param string $val
	 * @return boolean
	 */
	public function setVar($var,$val);
	
} 
?>
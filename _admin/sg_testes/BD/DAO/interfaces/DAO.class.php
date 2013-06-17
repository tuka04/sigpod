<?php
/**
 * Data Access Object
* @version 0.1 (13/6/2013)
* @package geral
* @subpackage BD
* @author Leandro Kümmel Tria Mendes
* @desc classe que manipula os aditivos de um contrato
* @see Acesso e querys aos bancos de dados
*/
interface DAO {
	const SELECT = "SELECT {campos} FROM {tabela} {where} {order} {limit};";
	const TOKEN_CAMPOS = "{campos}";
	const TOKEN_TABELA = "{tabela}";
	const TOKEN_WHERE = "{where}";
	const TOKEN_ORDER = "{order}";
	const TOKEN_LIMIT = "{limit}";
} 

?>
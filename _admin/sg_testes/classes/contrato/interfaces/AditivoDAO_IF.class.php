<?php
/**
 * Data Access Object
* @version 0.1 (13/6/2013)
* @package geral
* @subpackage contrato
* @author Leandro Kümmel Tria Mendes
* @desc classe que manipula os aditivos de um contrato
* @see Acesso e querys aos bancos de dados
*/
require_once "BD/DAO/interfaces/DAOIF.class.php";
interface AditivoDAO_IF extends DAOIF{
	const CAMPOS = "id,contratoID,campo,valor,motivo";//campos separados por virgula
	const TABELA = "contrato_aditivo";
} 
?>
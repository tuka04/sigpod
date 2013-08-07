<?php
/**
 * @version 0.1 (12/6/2013)
 * @package geral
 * @subpackage contrato
 * @author Leandro Kümmel Tria Mendes
 * @desc classe que manipula os aditivos de um contrato
 * @see Aditivos: Podem ser tipificados em 2. I) Monetário II) Inteiro (Dia)
 */
require_once "AditivoDAO_IF.class.php";
interface AditivoIF extends AditivoDAO_IF{
	/**
	 * Construtores
	 * @param BD $bd
	 * @param int $docId
	 * @param strin $nome
	 * @param string $label
	 * @param string $tipo
	 * @param string $valor
	 * @param string $motivo
	 */
	public function Aditivo($bd,$docId,$nome="",$label="",$tipo="",$valor="",$motivo="");
	/**
	 * @param string $var
	 * @return array()
	 */
	public function getVar($var);
	/**
	 * Seta $val a um atributo $var
	 * @param string $var
	 * @param mixed $val
	 */
	public function setVar($var,$val);
} 
?>
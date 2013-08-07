<?php
/**
 * @version 0.1 (12/6/2013)
 * @package geral
 * @subpackage html.interfaces
 * @author Leandro Kümmel Tria Mendes
 * @desc classe que manipula atributos de um objeto HtmlTag
 */
interface HtmlTagAttrIF {
	/**
	 * Construtor
	 * @param string $attr
	 * @param string $value
	 */
	public function HtmlTagAttr($attr="",$value="");
	/**
	 * Seta um [atributo]=valor;
	 * @param string $attr
	 * @param string $value
	 */
	public function setAttr($attr,$value);
	/**
	 * Retorna valor de um atributo 
	 * @return string
	 */
	public function getAttr($style);
	/**
	 * Retorna uma string para os atributos desse objeto
	 * @example attr1='valor1' attr2='valor2' ... 
	 * @return string
	 */
	public function toString();
	
} 
?>
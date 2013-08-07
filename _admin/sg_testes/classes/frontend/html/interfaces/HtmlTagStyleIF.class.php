<?php
/**
 * @version 0.1 (12/6/2013)
 * @package geral
 * @subpackage html.interfaces
 * @author Leandro Kümmel Tria Mendes
 * @desc classe que manipula style de um objeto HtmlTag
 */
interface HtmlTagStyleIF {
	/**
	 * Construtor
	 * @param string $style
	 * @param string $value
	 */
	public function HtmlTagStyle($style="",$value="");
	/**
	 * Seta um estilo:valor;
	 * @param string $style
	 * @param string $value
	 */
	public function setStyle($style,$value);
	/**
	 * Retorna valor de um estilo css
	 * @example getStyle('background-color') retorna #000000, se esse for o valor.
	 * @return string
	 */
	public function getStyle($style);
	/**
	 * Retorna uma string para os estilos desse objeto
	 * @example style='estilo_1:valor_1;estilo_2:valor_2;....'
	 * @return string
	 */
	public function toString();
	/**
	 * Retorna array[style]=valor
	 * @return array
	 */
	public function toArray();
} 
?>
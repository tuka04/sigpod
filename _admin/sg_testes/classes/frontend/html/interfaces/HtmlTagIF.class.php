<?php
/**
 * @version 0.1 (12/6/2013)
 * @package geral
 * @subpackage frontend.html
 * @author Leandro Kümmel Tria Mendes
 * @desc classe que manipula uma tag html
 */
interface HtmlTagIF{
	/**
	 * Construtor
	 * @param string $type
	 * @param string $id
	 * @param string $class
	 * @param string $content
	 */
	public function HtmlTag($type,$id,$class,$content="");
	/**
	 * Seta um atributo
	 * @param string $attr
	 * @param string $value
	 */
	public function setAttr($attr,$value);
	/**
	 * Seta um estilo
	 * @param string $attr
	 * @param string $attr
	 */
	public function setStyle($style,$valor);
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
	/**
	 * Retorna o codigo html
	 * @example <tag id="" class="" attr1="valor1"...attrN="valorN" style="estilo1:valor1;...;estiloN:valorN;"
	 * @return string
	 */
	public function toString();
} 
?>
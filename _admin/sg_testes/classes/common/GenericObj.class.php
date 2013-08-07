<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 23/07/2013
 * @version 1.1.1.5
 * @desc Classe abstrata de um objeto generico
 */

abstract class GenericObj {
	/**
	 * @param ArrayObj $campos : array com os nomes dos campos
	 */
	public function __construct(ArrayObj $campos=null){
		if($campos==null)
			return;
		foreach ($campos->getArrayCopy() as $c){
			if(!isset($this->$c)){
				$this->$c = "";
			}
		}
	}	
	/**
	 * Retorna null em caso da varivavel nao existir
	 * @param string $var
	 * @return NULL|mixed
	 */
	public function __get($var){
		if(isset($this->$var))
			return $this->$var;
		return null;
	}
	/**
	 * Retorna false em caso da varivavel nao existir
	 * @param string $var
	 * @param mixed $val
	 * @return boolean
	 */
	public function __set($var,$val){
		if(!isset($this->$var))
			return false;
		$this->$var=$val;
		return true;
	}
	
}
?>

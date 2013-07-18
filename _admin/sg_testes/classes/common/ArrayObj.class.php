<?php

class ArrayObj extends ArrayObject{
	
	public function __construct($array=array()){
		parent::__construct($array);
	}
	
	public function toString($glue=","){
		return implode($glue,$this->getArrayCopy());
	}
	/**
	 * Remove valores que não são unicos nessa estrutura
	 */
	public function makeUnique(){
		$this->exchangeArray(array_unique($this->getArrayCopy()));
	}
	
	/**
	 * Remove os valores $v, presentes nesse array
	 * @param unknown $v
	 */
	public function removeValue($v){
		$aux = new ArrayObject();
		foreach ($this->getArrayCopy() as $a){
			if($a!=$v)
				$aux->append($a);
		}
		$this->exchangeArray($aux->getArrayCopy());
	}
}

?>
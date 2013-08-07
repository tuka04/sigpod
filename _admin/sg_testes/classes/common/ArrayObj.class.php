<?php

class ArrayObj extends ArrayObject{
	
	public function __construct($array=array()){
		parent::__construct($array);
	}
	/**
	 * @param string $glue
	 * @return string
	 */
	public function toString($glue=","){
		$str = "";
		foreach ($this->getArrayCopy() as $a){
			if(is_object($a) && method_exists($a, "toString"))
				$str .= $a->toString().$glue;
			elseif(is_array($a))
				$str .= implode($glue,$a).$glue; 
			else
				$str .= $a.$glue; 
		}
		return rtrim($str,$glue);
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
	/**
	 * converte esse array em json
	 * @return json
	 */
	public function toJson(){
		return json_encode($this->getArrayCopy());
	}
	
	/**
	 * Filtra, remove, as posicoes em branco de um array
	 */
	public function filterEmpty(){
		$this->removeValue("");
	}
	/**
	 * Coloca aspas em todos os registros do array, se for aspas simples $single = true
	 * @param boolean $single
	 */
	public function putQuotation($single=false){
		($single)?$this->putIniEndMark("'"):$this->putIniEndMark('"');
	}
	/**
	 * Coloca a marca no inicio e final de cada registro do array
	 * @param mixed $mark
	 */
	public function putIniEndMark($mark){
		$aux = new ArrayObj();
		foreach ($this->getArrayCopy() as $k=>$a){
			if(!is_array($a)&&!is_object($a)){
				$aux->offsetSet($k, $mark.$a.$mark);
			}
		}
		$this->exchangeArray($aux->getArrayCopy());
	}
	
	/**
	 * Coloca a marca no inicio cada registro do array
	 * @param mixed $mark
	 */
	public function putIniMark($mark){
		$aux = new ArrayObj();
		foreach ($this->getArrayCopy() as $k=>$a){
			if(!is_array($a)&&!is_object($a)){
				$aux->offsetSet($k, $mark.$a.$mark);
			}
		}
		$this->exchangeArray($aux->getArrayCopy());
	}
	/**
	 * Espelha os valores que sao numerico ou string dentro de sua posicao
	 * @param string $glue
	 */
	public function mirrorValues($mirror=" "){
		$aux = new ArrayObj();
		foreach ($this->getArrayCopy() as $k=>$a){
			if(!is_array($a)&&!is_object($a)){
				$aux->offsetSet($k, $a.$mirror.$a);
			}
		}
		$this->exchangeArray($aux->getArrayCopy());
	}
}

?>
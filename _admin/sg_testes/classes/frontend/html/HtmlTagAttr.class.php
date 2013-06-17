<?php
require_once "interfaces/HtmlTagAttrIF.class.php";
class HtmlTagAttr implements HtmlTagAttrIF{
	/**
	 * nome do atributo
	 * @var string
	 */
	private $attr;
	/**
	 * valor do atributo
	 * @var string
	 */
	private $value;
	
	/**
	 * Construtor
	 * @param string $attr
	 * @param string $value
	 */
	public function HtmlTagAttr($attr="",$value=""){
		$this->attr = new ArrayObject();
		if(is_array($attr)){
			if(count($attr)!=count($value))
				die("Error: Numero de elementos devem ser iguais nos dois arrays");
			foreach ($attr as $i=>$a)
				$this->attr->offsetSet($a, $value[$i]);
		}
		else if(!empty($attr))
			$this->attr->offsetSet($attr, $value);
	}
	
	public function setAttr($attr,$value){
		if(!is_array($attr)){
			if(!is_object($this->attr))
				$this->HtmlTagAttr($attr, $value);
			else
				$this->attr->offsetSet($attr, $value);
		}
		else{
			if(!is_object($this->attr))
				$this->HtmlTagAttr($attr, $value);
			else{
				foreach ($attr as $i=>$a)
					$this->attr->offsetSet($a, $value[$i]);
			}
		}
	}
	
	public function getAttr($attr){
		if($this->attr->offsetExists($attr))
			return $this->attr->offsetGet($attr);
		else 
			return null;
	}
	
	public function toString(){
		if($this->attr->count()==0)
			return '';
		else{
			$str = '';
			foreach ($this->attr->getArrayCopy() as $k=>$s){
				$str .= "$k='$s'";
			}
		}
		return $str;
	}
}
?>
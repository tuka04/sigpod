<?php
require_once 'interfaces/HtmlTagStyleIF.class.php';
class HtmlTagStyle implements HtmlTagStyleIF{
	/**
	 * array com estilos e valores , estilos sao keys do ArrayObject
	 * @var ArrayObject
	 */
	private $style;
	
	public function HtmlTagStyle($style="",$value=""){
		$this->style = new ArrayObject();
		if(is_array($style)){
			if(count($style)!=count($value))
				die("Error: Numero de elementos devem ser iguais nos dois arrays");
			foreach ($style as $i=>$s)
				$this->style->offsetSet($s, $value[$i]);
		}
		else if(!empty($style))
			$this->style->offsetSet($style, $value);
	}
	
	public function setStyle($style,$value){
		//temos um ArrayObject()?
		if(!is_array($style)){
			if(!is_object($this->style))
				$this->HtmlTagStyle($style, $value);
			else 
				$this->style->offsetSet($style, $value);
		}
		else{
			if(!is_object($this->style))
				$this->HtmlTagStyle($style, $value);
			else{
				foreach ($style as $i=>$s)
					$this->style->offsetSet($s, $value[$i]);
			}
		}
	}
	
	public function getStyle($style){
		if($this->style->offsetExists($style))
			return $this->style->offsetGet($style);
		else 
			return null;
	}
	
	public function toString(){
		if($this->style->count()==0)
			return '';
		else{
			$str = "style='";
			foreach ($this->style->getArrayCopy() as $k=>$s)
				$str .= $k.":".$s.";";
			$str .= "'";
		}
		return $str;
	}
	
	public function toArray(){
		return $this->style->getArrayCopy();
	}
} 
?>
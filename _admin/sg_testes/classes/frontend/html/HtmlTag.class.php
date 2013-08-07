<?php
require_once "interfaces/HtmlTagIF.class.php";
class HtmlTag implements HtmlTagIF {
	/**
	 * tipo da tag, div, span, input
	 * @var string
	 */
	private $type;
	/**
	 * id da tag html
	 * @var string
	 */
	private $id;
	/**
	 * .class da tag html
	 * @var string
	 */
	private $class;
	/**
	 * Conteudo entre tags html
	 * @var string
	 */
	private $content;
	/**
	 * Atributos html 
	 * @var HtmlTagAttr
	 */
	private $attr;
	/**
	 * Estilos dessa tag html
	 * @var HtmlTagStyle
	 */
	private $style;
	/**
	 * Proxima tag html apos essa
	 * @var HtmlTag
	 */
	private $next;
	/**
	 * Tag html filha apos essa
	 * @var HtmlTag
	 */
	private $children;
	/**
	 * atributos extras q vao direto para dentro da tag
	 * @var string
	 */
	private $extra;
	public function HtmlTag($type,$id,$class="",$content="",$style="",$attr="",$extra=""){
		$this->load();
		$this->type=$type;
		$this->id=$id;
		$this->class=$class;
		$this->content=$content;
		$this->next=null;
		$this->children=null;
		$this->extra="";
		if(is_object($style) && ($style instanceof HtmlTagStyle))
			$this->style=$style;
		else
			$this->style=new HtmlTagStyle();
		if(is_object($attr) && ($attr instanceof HtmlTagAttr))
			$this->attr=$attr;
		else 
			$this->attr=new HtmlTagAttr();
		
	}

	private function load(){
		require_once 'HtmlTagAttr.class.php';
		require_once 'HtmlTagStyle.class.php';
	}
	
	public function toString(){
		if(empty($this->type))
			return "";
		$id = (empty($this->id))?"":" id='".$this->id."' ";
		$class = (empty($this->class))?"":" class='".$this->class."' ";
		$str = "<".$this->type." ".$id.$class." ".$this->attr->toString()." ".$this->style->toString()." ".$this->extra." >";
		if(is_object($this->children))
			$str .= $this->children->toString();
		if(is_object($this->next)&&$this->next!=null){
			if(is_object($this->content) && ($this->content instanceof HtmlTag)){
				$str .= $this->content->toString().$this->next->toString();
			}
			else 
				$str .= $this->content.$this->getEndTag().$this->next->toString();
		}
		else 
			$str .= $this->content.$this->getEndTag();
		return $str;
	}
	
	public function setStyle($style,$valor){
		$this->style->setStyle($style, $valor);
	}
	
	public function setAttr($attr,$valor){
		$this->attr->setAttr($attr, $valor);
	}
	
	public function getVar($var){
		return (isset($this->$var))?$this->$var:null;
	}
	
	public function setVar($var,$val){
		if(property_exists("HtmlTag", $var)){
			if(is_object($this->$var) && ($this->$var instanceof ArrayObject))//entao eh arrayObject
				$this->$var->append($val);
			else 
				$this->$var=$val;
		}
	}
	/**
	 * Caso ja tenha um filho, pega o ultimo e insere depois (next)
	 * @var HtmlTag $tag
	 */
	public function setChildren($tag){
		$ch = $this->getVar("children");
		if($ch==null)
			$this->setVar("children", $tag);
		else{
			$nch = $ch->getVar("next");
			if($nch==null)
				$ch->setVar("next",$tag);
			else{
				while($nch->getVar("next")!=null)
					$nch=$nch->getVar("next");
				$nch->setVar("next",$tag);
			}
		}
	}
	
	/**
	 * Retorna a n-esimo filho , null se nao encontrou o n-esimo
	 */
	public function getChildren($n){
		$ch = $this->getVar("children");
		$ret = null;
		while($ch!=null && $n>0){
			$ch = $ch->getVar("next");
			$n--;
		}
		if($n==0)
			$ret = $ch;
		return $ret;	
	}
	/**
	 * Retorna o total de filhos
	 * @return int $n
	 */
	public function getChildrenSize(){
		$n=0;//total de filhos
		$ch = $this->getVar("children");
		while($ch!=null){
			$ch = $ch->getVar("next");
			$n++;
		}
		return $n;
	}
	/**
	 * Caso ja tenha um next, pega o ultimo e insere depois estilo Fila
	 * @var HtmlTag $tag	
	 */
	public function setNext($tag){
		$nx = $this->getVar("next");
		if($nx==null)
			$this->setVar("next", $tag);
		else{
			while($nx->getVar("next")!=null)
				$nx=$nx->getVar("next");
			$nx->setVar("next",$tag);
		}
	}
	/**
	 * Retorna o final, se existir, de uma tag html estilo Fila
	 * @example </div> , </span>
	 * @return string
	 */
	private function getEndTag(){
		switch ($this->type){
			case 'input':
				return '';
			case 'img':
				return '';
			case 'br':
				return '';
			default:
				return "</$this->type>";
		}
	}
} 
?>
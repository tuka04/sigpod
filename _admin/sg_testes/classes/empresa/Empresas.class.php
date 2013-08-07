<?php
/**
 * Empresas
 * @version 0.1 (30/7/2013)
 * @package classes
 * @subpackage empresa
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela empresa
 * @see Acesso e querys aos bancos de dados
 */
class Empresas extends DAO{
	const Tabela = "empresa";
	
	private static $campos = array("id","nome","cnpj",
			"endereco","complemento","cidade",
			"estado","cep","telefone",
			"fax","email","servicos");
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	/**
	 * @param string $id
	 * @param string $class
	 * @param array $campos : deve ser array(id,campo_qualquer);
	 * @param string $selected
	 * @return HtmlTag
	 */
	public function getHtmlTagSelect($id="",$class="",array $campos, $selected=""){
		$tag = new HtmlTag("select",$id,$class,"",null,new HtmlTagAttr("name",$id));
		$tag->setChildren($this->getHtmlTagOption($campos,$selected));
		return $tag;
	}
	/**
	 * @param array $campos : deve ser array(id,campo_qualquer);
	 * @param string $selected
	 * @return HtmlTag
	 */
	public function getHtmlTagOption(array $campos, $selected=""){
		if(count($campos)!=2)
			die("Error: HtmlTagOption necessita de 2 campos, geralmente id e um campo qualquer");
		$tag = new HtmlTag("option","",""," -- Selecione -- ",null,new HtmlTagAttr("value",0));
		$valores = $this->getByCampo($campos);
		foreach ($valores->getArrayCopy() as $v){
			if($v[$campos[1]]==$selected)
				$tag->setNext(new HtmlTag("option","","",$v[$campos[1]],null,new HtmlTagAttr(array("value","selected"),array($v[$campos[0]],"true"))));
			else 
				$tag->setNext(new HtmlTag("option","","",$v[$campos[1]],null,new HtmlTagAttr("value",$v[$campos[0]])));
		}
		return $tag;				
	}
	
	/**
	 * @param array $campo
	 * @return ArrayObj
	 */
	public function getByCampo(array $campos){
		$ret = new ArrayObj();
		$this->setVar("campos", $campos);
		$ret = new ArrayObj($this->selectOrderedBy(new OrderedBy("nome","ASC")));
		$ret = new ArrayObj(CommonMethods::arrayToHash($ret->getArrayCopy(), "id"));
		return $ret;
	}
}
?>

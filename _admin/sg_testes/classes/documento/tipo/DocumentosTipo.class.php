<?php
/**
 * DocumentoTipo
 * @version 0.1 (29/7/2013)
 * @package classes
 * @subpackage documento.tipo
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela doc_tipo
 * @see Acesso e querys aos bancos de dados
 */

require_once "BD/DAO/DAO.class.php";
class DocumentosTipo extends DAO{
	const Tabela = "doc_tipo";
	
	private static $campos = array("id","nome");
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();
	}
	
	public function create(){
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela." ( 
				  id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  			  nome varchar(100) NOT NULL 
				) ENGINE=InnoDB;";
		$this->query($qr);
	}
	/**
	 * apenas para inserir um $arr, utilizada na transicao para 2.0
	 * @param unknown $arr
	 */
	public function loadDocs($arr){
		foreach ($arr as $k=>$v)
			$this->insert(array($k,$v["nome"]));	
	}
	/**
	 * @return HtmlTag
	 */
	public function getHtmlTableLines(){
		$valores = $this->select();
		foreach ($valores as $i=>$v){
			if(!isset($tr)){
				$tr = new HtmlTag("tr", "doc".$i."_tr","c doc_tr");
				$tr->setChildren(new HtmlTag("td", "","c",$i));
				$td = new HtmlTag("td", "","c","",new HtmlTagStyle("align","center"));
				$td->setChildren(new HtmlTag("input", "doc".$i."_cb","tipoDoc","",null,new HtmlTagAttr(array("type","name","value"),array("checkbox","doc".$i,"1"))));
				$tr->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("span", "doc".$i."_nome","",$v["nome"]));
				$tr->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("input", "doc".$i."_numero","doc_numero","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size"),array("text","doc".$i."_numero","","10"))));
				$tr->setChildren($td);

				$td = new HtmlTag("td", "","c");
				$data = new DataObj("today");
				$td->setChildren(new HtmlTag("input", "doc".$i."_ano","doc_ano","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size","maxlength"),array("text","doc".$i."_ano",$data->getAno(),"4","4"))));
				$tr->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("input", "doc".$i."_assunto","doc_assunto","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size","maxlength"),array("text","doc".$i."_assunto",$data->getAno(),"35","140"))));
				$tr->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("textarea", "doc".$i."_obs","doc_obs","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("name","value","cols","rows"),array("doc".$i."_obs","","20","4"))));
				$tr->setChildren($td);
			}
			else{
				$tag = new HtmlTag("tr", "doc".$i."_tr","c doc_tr");
				$tag->setChildren(new HtmlTag("td", "","c",$i));
				$td = new HtmlTag("td", "","c","",new HtmlTagStyle("align","center"));
				$td->setChildren(new HtmlTag("input", "doc".$i."_cb","tipoDoc","",null,new HtmlTagAttr(array("type","name","value"),array("checkbox","doc".$i,"1"))));
				$tag->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("span", "doc".$i."_nome","",$v["nome"]));
				$tag->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("input", "doc".$i."_numero","doc_numero","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size"),array("text","doc".$i."_numero","","10"))));
				$tag->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$data = new DataObj("today");
				$td->setChildren(new HtmlTag("input", "doc".$i."_ano","doc_ano","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size","maxlength"),array("text","doc".$i."_ano",$data->getAno(),"4","4"))));
				$tag->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("input", "doc".$i."_assunto","doc_assunto","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("type","name","value","size","maxlength"),array("text","doc".$i."_assunto",$data->getAno(),"35","140"))));
				$tag->setChildren($td);
				
				$td = new HtmlTag("td", "","c");
				$td->setChildren(new HtmlTag("textarea", "doc".$i."_obs","doc_obs","",new HtmlTagStyle("display","none"),new HtmlTagAttr(array("name","value","cols","rows"),array("doc".$i."_obs","","20","4"))));
				$tag->setChildren($td);
				
				$tr->setNext($tag);
			}
			return $tr;
		}
	}
	
	
}
?>
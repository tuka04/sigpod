<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 26/06/2013
 * @version 1.1.1.4
 * @desc DAO com o controle de sys_alerta
 */

class SysAlerta extends DAO{

	const Tabela = "sys_alerta";
	
	private static $campos = array("id","ini","removable");
	/**
	 * id do contrato
	 * @var int
	 */
	private $id;
	/**
	 * inicio do intervalor em dias
	 * @var int
	 */
	private $ini;
	/**
	 * fim do intervalor em dias
	 * @var int
	 */
	private $fim;

	public function SysAlerta(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();//remover se a tabela se estiver criada
	}

	public function create(){
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela."
				( id int NOT NULL AUTO_INCREMENT,
	  			  ini int NOT NULL ,
				  removable boolean default 0 ,
				  PRIMARY KEY (id)
				) ENGINE=InnoDB;";
		$this->query($qr);
	}
	/**
	 * metodo que retorna uma consulta em formato de tabela html
	 */
	public function toHtmlTable(){
		requireSubModule("frontend");
		//requisicao dos dados
		$d = $this->select();
		//iniciando tabela
		$br = new HtmlTag("br", "", "");
		$span = new HtmlTag("span", "", "");
		$remover = new HtmlTag("a", "rmAlerta", "","[Remover]");
		$remover->setAttr(array("href","onclick"), array("#","javascript:removeSysAlerta();"));
		$span->setVar("content", "Para todos os documentos selecionados: ".$remover->toString());
		$tb = new HtmlTable("tableAlerta", "tablesorter", 2);
		$tb->enableCheckbox();
		$tb->setHead(array("C&oacute;digo","Dias")," c header ");
		$i=0;
		foreach ($d as $v){
			$val = array();
			$val[] = $v["id"];
			$val[] = $v["ini"];
			$hidden = new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","aid",$v["id"])));
			$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","lid","tableAlerta_".($tb->getNumLines())))));
			$tb->appendLine($val,"",$hidden);
			$i++;
		}
		$span->setNext(new HtmlTag("br", "", ""));
		$span->setNext($tb);
		$br->setNext($span);
		return $br->toString();
	}
	/**
	 * retorna um HtmlTag com os campos para adicionar um novo elem no banco
	 * @return HtmlTag
	 */
	public function getAddHtmlFields(){
		$divID="sys_alerta_campos";
		$divSCID="bsc_sys_alerta";
		$addLinkID="addSysAlerta";
		//div para botoes salvar e cancelar
		$divSC = new HtmlTag("div", $divSCID, "");
		$divSC->setStyle("display", "none");
		//link salvar
		$linkS = new HtmlTag("a", "salvarSysAlerta", "", "[Salvar]");
		$linkS->setAttr(array("onclick","href"), array("javascript:gerenciarContratoSave(\"sys_alerta.ini\",\"sys_alerta.diario\");","#"));
		//link cancelar
		$linkC = new HtmlTag("a", "cancelarSysAlerta", "", "[Cancelar]");
		$linkC->setAttr(array("onclick","href"), array("javascript:gerenciarContratoCancel(\"".$divID."\",\"".$divSCID."\",\"".$addLinkID."\",\"sys_alerta.ini\");","#"));
		$divSC->setChildren($linkS);
		$divSC->setChildren($linkC);
		//link para novo sys alerta
		$link = new HtmlTag("a", $addLinkID, "");
		$link->setVar("content", "[Novo]");
		$link->setAttr(array("onclick","href"), array("javascript:gerenciarContratoOpenAdd(\"".$divID."\",\"".$divSCID."\",\"".$addLinkID."\");","#"));
		//campos
		$campos = new stdClass();
		$campos->div = new HtmlTag("div", $divID, "");
		$campos->div->setStyle("display","none");
		$campos->text = new HtmlTag("input", "", "");
		$campos->text->setAttr(array("type","name","value","onfocus"), array("text","sys_alerta.ini","Dias","javascript:gerenciarContratoClearValue(\"sys_alerta.ini\")"));
		$campos->text->setStyle(array("opacity","width"),array("0.5","50px"));
		$campos->div->setChildren($campos->text);
		$link->setNext($divSC);
		$link->setNext($campos->div);
		return $link;
	}
}
?>
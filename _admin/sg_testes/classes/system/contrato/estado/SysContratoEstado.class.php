<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 18/07/2013
 * @version 1.1.1.5
 * @desc DAO com o controle de sys_contrato_estado
 */

class SysContratoEstado extends DAO{

	const Tabela = "sys_contrato_estado";
	
	const TabelaHtmlID = "tableSysContratoEstado";

	private static $campos = array("id","nome","motivo","dias","data");
	/**
	 * id do contrato
	 * @var int
	*/
	private $id;
	/**
	 * Nome do estado
	 * @var string
	 */
	private $nome;
	/**
	 * Se deve ter um motivo para esse estado
	 * @var boolean
	 */
	private $motivo;
	/**
	 * Numero maximo de dias de um estado 
	 * @example: No caso de suspenso temos um máximo de 120 dias.
	 * @var int
	 */
	private $dias;
	
	public function SysContratoEstado(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();//remover se a tabela se estiver criada
	}

	public function create(){
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela."
				( id int NOT NULL AUTO_INCREMENT,
	  			  nome varchar(50) NOT NULL ,
				  motivo boolean default 0 ,
				  dias int default 0 ,
				  data boolean default 0 ,
				  PRIMARY KEY (id)
				) ENGINE=InnoDB;";
		$this->query($qr);
	}
	/**
	 * metodo que retorna uma consulta em formato de tabela html
	 * @return HtmlTable
	 */
	public function toHtmlTable(){
		requireSubModule("frontend");
		//requisicao dos dados
		$d = $this->select();
		//iniciando tabela
		$tb = new HtmlTable(self::TabelaHtmlID, "tablesorter", 5);
		$tb->enableCheckbox();
		$tb->setHead(array("C&oacute;digo","Nome","Motivo","Dias","Data")," c header ");
		foreach ($d as $v){
			$val = array();
			$val[] = $v["id"];
			$val[] = $v["nome"];
			$val[] = $v["motivo"]?"Sim":"Não";
			$val[] = $v["dias"];
			$val[] = $v["data"]?"Sim":"Não";
			$hidden = new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","eid",$v["id"])));
			$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","lid",$tb->getVar("id")."_".($tb->getNumLines())))));
			$tb->appendLine($val,"",$hidden);
		}
		return $tb;
	}
	/**
	 * retorna as tags html responsáveis pelos botoes do DAO
	 * @return HtmlTag
	 */
	public function getDAOHtml(){
		//div pai
		$divDAO = new HtmlTag("div","daoContratoEstado");
		
		//div inserir
		$divI = new HtmlTag("div","daoInserirContratoEstado");
		$tableI = $this->getDAOInserirTable();
		$tableI->setStyle("display", "none");
		$linkN = new HtmlTag("a", "linkNovoContratoEstado","","[Novo]");
		$linkN->setAttr(array("href","onclick"), array("#","javascript:gerenciarEstadoOpenAdd(this,\"".$tableI->getVar("id")."\");"));
		$divI->setChildren($linkN);
		$divI->setChildren($tableI);
		//deletar
		$divD = new HtmlTag("div","daoDeletarContratoEstado");
		$span = new HtmlTag("span","");
		$link = new HtmlTag("a", "","","[Remover]");
		$link->setAttr(array("href","onclick"), array("#","javascript:removeSysContratoEstado(\"".self::TabelaHtmlID."\");"));
		$span->setVar("content", "Para todos os itens selecionados:".$link->toString());
		$divD->setChildren($span);
		//juncao das divs com a divDAO
		$divDAO->setChildren($divI);
		$divDAO->setChildren($divD);
		return $divDAO;
	}
	/**
	 * @return HtmlTable
	 */
	private function getDAOInserirTable(){
		$tableI = new HtmlTable("tableDaoInserirContratoEstado", "tabelaGenerica", 2);
		$tableI->setCaption("Campos com * são obrigatórios.");
		$tableI->setStyle("margin", "5px 10px 20px 20%");
		$val = array();
		//nome
		$val[0] = "<b>* Nome :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.nome");
		$input->setAttr(array("type","name","value","obr"), array("text","sysContratoEstado.nome","",1));
		$val[1] = $input->toString();
		$tableI->appendLine($val,array("classSysContratoEstado",""));
		//motivo
		$val[0] = "<b>Motivo :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.motivo");
		$input->setAttr(array("type","name","value"), array("checkbox","sysContratoEstado.motivo",""));
		$val[1] = $input->toString();
		$tableI->appendLine($val,array("classSysContratoEstado","classSysContratoEstadoCheck"));
		//dias
		$val[0] = "<b>Dias :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.dias");
		$input->setAttr(array("type","name","value"), array("text","sysContratoEstado.dias",""));
		$input->setStyle("width", "50px");
		$val[1] = $input->toString();
		$tableI->appendLine($val,array("classSysContratoEstado",""));
		//tem data
		$val[0] = "<b>Data :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.data");
		$input->setAttr(array("type","name","value"), array("checkbox","sysContratoEstado.data",""));
		$val[1] = $input->toString();
		$tableI->appendLine($val,array("classSysContratoEstado",""));
		
		$link = new HtmlTag("a", "","","[Salvar]");
		$link->setAttr(array("href","onclick"), array("#","javascript:gerenciarContratoEstadoSave(\"".$tableI->getVar("id")."\");"));
		$val[0] = $link->toString();
		$link = new HtmlTag("a", "","","[Cancelar]");
		$link->setAttr(array("href","onclick"), array("#","javascript:gerenciarContratoEstadoClearValue(\"linkNovoContratoEstado\",\"".$tableI->getVar("id")."\");"));
		$val[1] = $link->toString();
		$tableI->appendLine($val,"noBackground",null,new HtmlTagStyle("background-color","#FFFFFF"));
		
		return $tableI;
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
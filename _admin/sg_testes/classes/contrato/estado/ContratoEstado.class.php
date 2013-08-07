<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 26/06/2013
 * @version 1.1.1.4
 * @desc Controla os alertas dos contratos 
 */
require_once 'classes/contrato/dao/estado/ContratoEstadoDAO.class.php';

class ContratoEstado {
	
	const DialogInsereID = "dialogInserirContratoEstado";
	
	private $docID;
	
	public function __construct($docID=0){	
		$this->docID=$docID;
	}
	
	/**
	 * @return HtmlTag
	 */
	public function getAddDialog(){
		$dialog = new HtmlTag("div", self::DialogInsereID);
		$dialog->setStyle("display", "none");
		$dialog->setChildren($this->getHtmlAddTable());
		return $dialog;
	}
	/**
	 * @return HtmlTable
	 */
	public function getHtmlAddTable(){
		$tableI = new HtmlTable("tableDaoInserirContratoEstado", "tabelaGenerica", 2);
		$tableI->setCaption("Campos com * são obrigatórios.");
		$tableI->setStyle("margin", "5px 10px 20px 20%");
		$val = array();
		//nome
		$val[0] = "<b>* Nome :</b>";
		$input = new HtmlTag("select", "sysContratoEstadoNome");
		$input->setAttr(array("name","obr"), array("sysContratoEstado.nome",1));
		//buscando os valores possiveis de estado de contrato
		$sce = new SysContratoEstado();
		$estados = $sce->select();
		//setando options, motivo, data e dias
		$motivo = new ArrayObj();
		$data = new ArrayObj();
		$dias = new ArrayObj();
		foreach ($estados as $e){
			if($e["motivo"])
				$motivo->append($e["id"]);
			if($e["data"])
				$data->append($e["id"]);
			if($e["dias"])
				$dias->append($e["id"]);
			if(!isset($opt)||$opt==null)
				$opt = new HtmlTag("option", "","",$e["nome"],null,new HtmlTagAttr("value",$e["id"]));
			else{
				$opt->setNext(new HtmlTag("option", "","",$e["nome"],null,new HtmlTagAttr("value",$e["id"])));
			}
		}
		$input->setChildren($opt);
		$val[1] = $input->toString();
		$tableI->appendLine($val);
		//motivo
		$val[0] = "<b>* Motivo :</b>";
		$input = new HtmlTag("textarea", "sysContratoEstado.motivo");
		$input->setAttr(array("name","value","enable"), array("sysContratoEstado.motivo","",$motivo->toString()));
		$val[1] = $input->toString();
		$tableI->appendLine($val);
		$tableI->setLineStyle(new HtmlTagStyle("display","none"),$tableI->getNumLines()-1);
		$tableI->setLineAttr(new HtmlTagAttr("enable",$motivo->toString()),$tableI->getNumLines()-1);
		//dias
		$val[0] = "<b>* Dias :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.dias");
		$input->setAttr(array("type","name","value","enable"), array("text","sysContratoEstado.dias","",$dias->toString()));
		$input->setStyle("width", "50px");
		$val[1] = $input->toString();
		$tableI->appendLine($val);
		$tableI->setLineStyle(new HtmlTagStyle("display","none"),$tableI->getNumLines()-1);
		$tableI->setLineAttr(new HtmlTagAttr("enable",$dias->toString()),$tableI->getNumLines()-1);
		//tem data
		$val[0] = "<b>Data :</b>";
		$input = new HtmlTag("input", "sysContratoEstado.data");
		$input->setAttr(array("type","name","value","datepicker","enable"), array("text","sysContratoEstado.data","",true,$data->toString()));
		$val[1] = $input->toString();
		$tableI->appendLine($val);
		$tableI->setLineStyle(new HtmlTagStyle("display","none"),$tableI->getNumLines()-1);
		$tableI->setLineAttr(new HtmlTagAttr("enable",$data->toString()),$tableI->getNumLines()-1);
		return $tableI;
	}
	/**
	 * Edita o estado de um contrato
	 * @return boolean
	 */
	public function edit($valores){
		$dao = new ContratoEstadoDAO();		
		$r = $dao->insertDuplicatedKey($valores);
		if(!$r)
			return false;
		//gravar no historico
		$doc = new Documento($this->docID);
		$r = $doc->doLogHist($_SESSION["id"], "editEstado", "", "", "editEstado", "", "");
		if(!$r)
			return false;
		return true;
	}
	/**
	 * Retorna um trecho de codigo html com o nome e demais valores escondidos
	 * @return string
	 */
	public function getEstadoHtml(){
		$dao = new ContratoEstadoDAO();
		$e = $dao->select("docID",$this->docID);
		if(count($e)>0){
			$e=$e[0];//apenas pegando a primeira posicao
			$hidden = new HtmlTag("input","estadoMotivo","","","",new HtmlTagAttr(array("type","value"),array("hidden",$e["motivo"])));
			$hidden->setNext(new HtmlTag("input","estadoData","","","",new HtmlTagAttr(array("type","value"),array("hidden",date("d/m/Y",$e["data"])))));
			$hidden->setNext(new HtmlTag("input","estadoDias","","","",new HtmlTagAttr(array("type","value"),array("hidden",$e["dias"]))));
			$hidden->setNext(new HtmlTag("input","estadoID","","","",new HtmlTagAttr(array("type","value"),array("hidden",$e["sysContratoEstadoID"]))));
			return ucfirst($e["nome"]).$hidden->toString();
		}
		return "";
	}
}
?>

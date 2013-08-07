<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 26/06/2013
 * @version 1.1.1.4
 * @desc Controla os alertas dos contratos 
 */
require_once 'classes/contrato/dao/alerta/AlertaDAO.class.php';

class Alerta {
	
	/**
	 * @var ArrayObject
	 */
	private $contratos;
	/**
	 * @var ArrayObject
	 */
	private $alertas;
	/**
	 * json com os alertas
	 * @var string
	 */
	private $json;
	/**
	 * tabela html com os alertas
	 * @var HtmlTable
	 */
	private $table;
	/**
	 * Validade
	 * @var ArrayObj
	 */
	private $validade;
	
	public function __construct($c=true){	
		if(!checkPermission(103)){
			$u = new UsuarioAlerta();
			$r = $u->select("usuarioID",$_SESSION["id"]);
			if(count($r)<1)
				return;
		}
		if($c){
			$this->load();
			$this->build();
			$this->toTable();
		}
	}
	/**
	 * Carrega todas as datas de conclusao previstas dos contratos
	 */
	private function load(){
		$dao = new AlertaDAO();
		$this->contratos = $dao->getContratos();
	}
	/**
	 * Retorna os prazos(dias) de validade do SysAlerta
	 * @return ArrayObject
	 */
	private function getValidade(){
		$sa = new SysAlerta();
		$val = $sa->select();
		$this->validade = new ArrayObject();
		foreach ($val as $v)
			$this->validade->append(array("id"=>$v["id"],"dias"=>$v["ini"]));
		return $this->validade; 
	}
	/**
	 * Monta uma estrutura que contém os contratos que, possivelmente,
	 * serão emitidos alertas.
	 * @return ArrayObject
	 */
	private function getVencidos(){
		//pega validade
		$valid = $this->getValidade();
		$vencidos = new ArrayObject();
		$v = new ArrayObject();
		//para cada contrato verifique se esta vencido ou proximo de
		foreach ($this->contratos->getArrayCopy() as $c){
			$aux = new Vencimento($c["id"],$c["dataTermino"],$valid);
			//se esta entao vai para a estrutura de vencidos
			if($aux->estaProximoVencimento() || $aux->estaVencido())
				$v->append($aux);
		}		
		return $v;
	} 
	/**
	 * Método que monta a estrutura de alerta, apenas para os contratos
	 * que estao próximos ou vencidos
	 */
	private function build(){
		//pega os vencidos
		$venc = $this->getVencidos()->getArrayCopy();
		//prepara o array com os ids dos vencidos para a interseccao
		$vids = new ArrayObject();
		foreach ($venc as $v)
			$vids->append($v->getVar("id"));
		$dao = new AlertaDAO();
		$r = $dao->select("doc_numProcContr",$vids->getArrayCopy()," IN ");
		$alerta = new ArrayObj();
		if(count($r)==0)
			$alerta = new ArrayObj($vids->getArrayCopy());
		foreach ($venc as $valid){
			$flag=true;
			foreach ($r as $v){
				if($v["doc_numProcContr"]==$valid->getVar("id") && $v["sys_alertaID"]==$valid->getVar("idValidade"))
					$flag=false;
			}
			if($flag)
				$alerta->append($valid->getVar("id"));
		}
		$this->setAlertas($alerta);
	}
	/**
	 * Retorna uma tabela html (em json: table=>html_string)
	 * @return string
	 */
	public function toTable(){
		//iniciando tabela
		requireSubModule("frontend");
		includeModule("sgo");
		$table = new HtmlTable("docsAlerta", "tablesorter", 4);
		$table->enableCheckbox();
		$table->setHead(array("N&uacute;mero do Contrato","Obras","Empresa","Data de Conclusão"),"c header ");
		//pegando docs a receberem alerta
		$a = $this->getAlertas()->getArrayCopy();
		//todos os contratos, listados
		$contr = $this->contratos;
		foreach ($contr->getArrayCopy() as $c){
			//se o ano for < 2013 nda a fazer
			$c = $c->getArrayCopy();
			if(substr($c["dataTermino"],6,4) < '2013')
				continue;
			//se for de 2013 mas anterior a julho, tbm nao queremos
			if(intval(substr($c["dataTermino"],4,2) < 7))
				continue;
			if(array_search($c["id"], $a)!==false && $c["dataTermino"]){
				//numero
				$d = new Contrato($c["id"]);
				$d->loadCampos();
				$num = $d->numeroComp;
				$lobras = new ArrayObj();//link das obras
				$enome = new ArrayObj();//nome das empresas
				$e = new Empresa($d->bd);
				$id=-1;
				foreach ($c["contratos"]->getArrayCopy() as $did){
					$d = new Contrato($did);
					$d->loadCampos();
					if(isset($d->campos["numeroContr"])){
						$id=$d->id;
						$num = new HtmlTag("a", "", "",$d->id);
						$num->setAttr(array("href","onclick"), array("#","javascript:".str_replace("'", "\"", $d->geraLinkDoc("ver", $d->id))));
						$num = $num->toString();
					}
					$obras = $d->getObras();
					foreach ($obras as $o){
						$ao = new HtmlTag("a", "", "",$o["nome"]);
						$ao->setAttr(array("href","onclick"), array("#","javascript:".str_replace("'", "\"", Obra::geraLink("verObra", $o["id"]))));
						$lobras->append($ao->toString());
					}
					if(isset($d->campos["empresaID"]))
						$e->load($d->campos["empresaID"]);
					$enome->append($e->get("nome"));//nome da empresa
				}
				$enome->makeUnique();
				if($enome->count()>1)
					$enome->removeValue("Desconhecida");
				$v = new Vencimento($c["docPaiID"],$c["dataTermino"],$this->validade);
				$class = "";
				if($v->estaProximoVencimento())
					$class = "alerta_yellow_line";
				else if($v->estaVencido())
					$class = "alerta_red_line";
				//campos hidden para remocao do(s) alerta(s)
				$id=($id<0?$c["id"]:$id);
				$hidden = new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","cid",$id)));
				$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","did",$c["id"]))));
				$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","aid",$v->getVar("idValidade")))));
				$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","name","value"), array("hidden","lid","docsAlerta_".($table->getNumLines())))));
				if(!empty($class))
					$table->appendLine(array($num,$lobras->toString("<br>"),$enome->toString("<br>"),$c["dataTermino"]),$class,$hidden);
			}
		}	
		$this->setJSON(array("table"=>$table->toString()));
		$this->setTable($table);
		return $this->getJSON();
	}
	
	public function removeAlerta(){
		$contrato = explode(",",$_REQUEST["contratoID"]);
		$docs = explode(",",$_REQUEST["docID"]);
		$alerta = explode(",",$_REQUEST["alertaID"]);
		$user = $_SESSION["id"];
		$dao = new AlertaDAO();
		foreach ($contrato as $k=>$c){
			$d = new Contrato($c);
			//salvar no historico
			$r = $d->doLogHist($user, "rmAlerta", "", "", "rmAlerta", "", "");
			//registrar na tabela
			$dao->insert(array($docs[$k],$alerta[$k],"0",$user));
		}
		return array("msg"=>"Alerta removido com sucesso.");
	}
	
	/**
	 * @param ArrayObject $a
	 */
	private function setAlertas(ArrayObject $a){
		$this->alertas = $a;
	}
	/**
	 * @return json
	 */
	public function getJSON(){
		return $this->json;
	}
	/**
	 * @param json $a
	 */
	private function setJSON($j){
		$this->json = $j;
	}
	/**
	 * @return ArrayObject
	 */
	public function getAlertas(){
		return $this->alertas;
	}
	/**
	 * @return HtmlTable
	 */
	public function getTable(){
		return $this->table;
	}
	/**
	 * @param HtmlTable $t
	 */
	private function setTable(HtmlTable $t){
		$this->table = $t;
	}
}
?>

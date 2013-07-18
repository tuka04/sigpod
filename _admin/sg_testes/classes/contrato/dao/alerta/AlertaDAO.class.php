<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 26/06/2013
 * @version 1.1.1.4
 * @desc DAO com o controle de alerta
 */

require_once 'classes/common/ArrayObj.class.php';
class AlertaDAO extends DAO{
	
	const Tabela = "contrato_alerta";
	/**
	 * array com os campos
	 * @var array
	 */
	static private $campos = array("doc_numProcContr","sys_alertaID","alerta","usuariosID");
	/**
	 * id do contrato
	 * @var int
	 */
	private $doc_contratoID;
	/**
	 * id do alerta do sistema (sys_alerta)
	 * @var int
	 */
	private $sys_alertaID;
	/**
	 * se o esta com alerta
	 * @var boolean
	 */
	private $alerta;
	/**
	 * id do usuario q, possivelmente, removeu o alerta
	 * @var int
	 */
	private $usuariosID; 
	
	public function AlertaDAO(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();//remover após criar as tabelas
		requireSubModule("aditivo");
	}
	
	public function create(){
		//cria a tabela
// 		$qr = "CREATE INDEX IF NOT EXISTS numProcContr ON doc_contrato(numProcContr)";
// 		$this->query($qr);//executa query acima
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela."
				( 
				  id int PRIMARY KEY AUTO_INCREMENT ,
				  doc_numProcContr int NOT NULL ,
	  			  sys_alertaID int NOT NULL ,
	  			  alerta boolean NOT NULL default 0 ,
				  usuariosID int NULL default NULL,
				  INDEX c_id (doc_numProcContr),
				  INDEX s_id (sys_alertaID),
				  INDEX u_id (usuariosID),
				  FOREIGN KEY (doc_numProcContr) REFERENCES doc_contrato(numProcContr) ON DELETE NO ACTION ON UPDATE NO ACTION,
				  FOREIGN KEY (sys_alertaID) REFERENCES sys_alerta(id) ON DELETE NO ACTION ON UPDATE NO ACTION,
				  FOREIGN KEY (usuariosID) REFERENCES usuarios(id) ON DELETE NO ACTION ON UPDATE NO ACTION
				) ENGINE=InnoDB;";
		$this->query($qr);//executa query acima
	}
	/**
	 * Retorna um ArrayObject com a data de todos os contratos
	 * @return ArrayObject
	 */
	public function getContratos(){
		//prepara query para selecionar TODOS os contratos
		//a partir de 2013 apenas!!!
		$qr = DAO::SELECT;
		//soh queremos o id(do documento) e dataTermino
		//lembrando que numProcContr eh o docPaiID da tabela doc, precisamos dele para referencia
		$qr = str_replace(DAO::TOKEN_CAMPOS, "doc_contrato.dataTermino, doc_contrato.prazoProjObra, doc.docPaiID, doc.id as id", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, "doc_contrato", $qr);
		//fazendo um LEFT JOIN das tabelas doc_contrato e doc
		$join = " LEFT JOIN doc ON doc.id = doc_contrato.numProcContr LEFT JOIN doc_processo ON doc_processo.numero_pr = doc.numeroComp";
		//selecionando a partir de 2013 apenas
		$where = " WHERE YEAR(FROM_UNIXTIME(doc_contrato.dataTermino)) >= 2012";
		$qr = str_replace(DAO::TOKEN_WHERE, $join.$where, $qr);	
		//nda para order e limit
		$qr = str_replace(DAO::TOKEN_ORDER, " ORDER BY doc_contrato.dataTermino DESC", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		$c = $this->query($qr);//contratos
		//transformar timestamp para data
		if(is_array($c)){
			$ids = new ArrayObj();
			$arr = new ArrayObj();
			foreach ($c as &$v){
				$v["dataTermino"]=date("d/m/Y",$v["dataTermino"]);
				$ids->append($v["id"]);
				$arr->offsetSet($v["id"], $v);
			}
		}
		else{
			die("Error: Erro ao selecionar os contratos. Por favor entre em contato com o administrador. ".__FILE__."(".__LINE__.")");
		}
		//pegando doc filhos
		$children = $this->getChildrenDocs($ids);
		//aditivos
		$ads = Aditivo::getAllAditivos();
		$c = $this->mergeAditivosDoc($arr,$children, $ads);
		return new ArrayObject($c);
	}
	/**
	 * Retorna os dos filhos dos $docs pais
	 * @return ArrayObj
	 */
	private function getChildrenDocs(ArrayObj $docs){
		$qr = DAO::SELECT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, "doc.id,doc.docPaiID",$qr);
		$qr = str_replace(DAO::TOKEN_TABELA, "doc_contrato",$qr);
		$join = " INNER JOIN doc ON doc.docPaiID = doc_contrato.numProcContr";
		$where = " WHERE doc.docPaiID IN (".$docs->toString().")";
		$qr = str_replace(DAO::TOKEN_WHERE, $join.$where, $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return new ArrayObj($this->query($qr));//contratos
	}
	
	/**
	 * método que agrupa um array por um indice, $ind, dado.
	 * @param mixed(string,int) $ind
	 */
	private function groupBy($arr,$ind){
		$ret = array();
		foreach($arr as $v){
			$ret[$v[$ind]][]=$v;
		}
		$r = array();
		$i=0;
		foreach($ret as $v){
			$r[$i]=array();
			$r[$i]["dataTermino"]=$v[count($v)-1]["dataTermino"];
			$r[$i]["docPaiID"]=$v[count($v)-1]["docPaiID"];
			$r[$i]["docID"]=$v[count($v)-1]["id"];
			$aux=array();
			foreach ($v as $v1){
				$aux[]=$v1["id"];
			}
			$r[$i]["id"]=$aux;
			$i++;
		}
		return $r;
	}
	/**
	 * metodo auxiliar que pega dois arrays q tem em comum um indice
	 * e faz o merge deles, mas antes mas o append dos docs filhos aos docs pai
	 * @param array(mixed) $d
	 * @param array(mixed) $c
	 * @param array(mixed) $a
	 */
	private function mergeAditivosDoc(ArrayObj $d,ArrayObj $c,$a){
		//merge dos docs com seus filhos
		$aux = new ArrayObj();
		foreach ($c as $v){
			if(!$d->offsetGet($v["docPaiID"]))
				continue;
			if(!is_object($d->offsetGet($v["docPaiID"])))
				$aux = new ArrayObj($d->offsetGet($v["docPaiID"]));
			else 
				$aux = $d->offsetGet($v["docPaiID"]);
			if(!$aux->offsetExists("contratos"))
				$aux->offsetSet("contratos",new ArrayObj());
			$aux->offsetGet("contratos")->append($v["id"]);
			$d->offsetSet($v["docPaiID"], $aux);
		}
		$val = array();
		foreach ($a as $v)
			$val[$v["contratoID"]][] = $v["valor"];
		//& necessario para mudar na mem do array
		foreach ($d->getArrayCopy() as $v){
			$sum=0;
			$v->offsetSet("aditivos", new ArrayObj());
			foreach($v->offsetGet("contratos")->getArrayCopy() as $id){
				if(array_key_exists($id, $val)){
					foreach ($val[$id] as $s){
						$sum+=$s;
						$v->offsetGet("aditivos")->append($s);
					}
				}
			}
			$v->offsetSet("dataTermino",date('d/m/Y',strtotime("+".$sum." days",strtotime(str_replace("/", "-", $v->offsetGet("dataTermino"))))));
		}
		return $d;
	}	
}
?>
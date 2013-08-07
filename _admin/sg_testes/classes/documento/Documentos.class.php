<?php
/**
 * @version 2.0 30/7/2012
 * @package geral
 * @subpackage documento
 * @author Leandro Kümmel Tria Mendes
 * @desc contem os DAO para a tabela doc
 */
class Documentos extends DAO {
	const Tabela = "doc";
	
	private static $campos = array("id","anexado","docPaiID",
			"data","criadorID","ownerID",
			"OwnerArea","anexos","labelID",
			"tipoID","emitente","numeroComp",
			"empreendID","obraID","arquivado","solicDesarquivamento",
			"solicitante","solicitado","ultimoHist");
	
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	/**
	 * @param ArrayObj $docs
	 * @param ArrayObj $user
	 * @param ArrayObj $histo
	 * @return mixed|NULL|Ambigous <NULL, unknown, mixed>|unknown
	 */
	public function getOwner(ArrayObj $docs, ArrayObj &$user=null, ArrayObj $histo=null){
		if($docs == null || $docs->count()==0)
			return null;
		if($histo==null){
			$temp=new Historicos();
			$histo = $temp->selectOrderedBy(new OrderedBy("data","DESC"),
									        new WhereClause("docID", $docs->getArrayCopy()),
					                        new LimitQuery(0,1));
			$temp->groupHistoByCampo($histo, "docID");
			$histo = new ArrayObj(CommonMethods::arrayToHash($histo,"docID"));			
		}
		if($user==null){
			$temp=new Usuario();
			$user = new ArrayObj(CommonMethods::arrayToHash($temp->select(),"id"));
		}
		foreach ($docs->getArrayCopy() as $d){
			if(is_object($d))
				$d = $d->getArrayCopy();
			if($d["ownerID"]==0 && !$d["anexado"]){
				if(!$histo->offsetExists($d["id"])){
					if(!$docs->offsetExists("id"))
						return null;
					$temp=new Historicos();
					$aux = $temp->selectOrderedBy(new OrderedBy("data","DESC"),
									        new WhereClause("docID", $docs->offsetGet("id")),
					                        new LimitQuery(0,1));
					$histo->offsetSet($temp->groupHistoByCampo($histo, "docID"));
				}
				$h = $histo->offsetGet($d["id"]);
				if(isset($h["unidade"])&&$h["tipo"]==HistoricosTipo::SAIDA)
					return $h["unidade"];
				return null;
			}
			if($d["ownerID"] > 0 && !$d["anexado"]){
				$u = $user->offsetGet($d["ownerID"]);
				return $u["username"];
			}
			if($d["anexado"])
				return $this->getOwner($this->getDocPai($docs->getArrayCopy()),$user,$histo);
			if(isset($d["areaOwner"]))
				return $d["areaOwner"];
		}
		return null;
	}
	/**
	 * @param array $doc
	 * @return ArrayObj|NULL
	 */
	public function getDocPai(array $doc){
		if(!isset($doc["docPaiID"]))
			die("getDocPai: ".__LINE__." Erro na entrada");
		if(empty($doc["docPaiID"])||intval($doc["docPaiID"])==0){
			return new ArrayObj(array($doc));
		}
		$campos = $this->getCampos();
		foreach ($campos as &$c){
			if($c=="docPaiID")	
				$c = "@pv:=".$c." as '".$c."'";
		}
		$qr = "SELECT ".implode(",",$campos)." FROM ".self::Tabela." JOIN "."(SELECT @pv:=".$doc["docPaiID"].")tmp WHERE id=@pv";
		$aux = $this->query($qr);
		foreach ($aux as $a){
			if($a["docPaiID"]=="0"||!$a["docPaiID"])
				return new ArrayObj($a);
		}
		return new ArrayObj();
	}
}
?>

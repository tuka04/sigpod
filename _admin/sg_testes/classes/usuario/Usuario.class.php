<?php
/**
 * Usuario
 * @version 0.1 (29/7/2013)
 * @package classes
 * @subpackage usuario
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela usuarios
 * @see Acesso e querys aos bancos de dados
 */
class Usuario extends DAO{

	const Tabela = "usuarios";

	private static $campos = array("id","ativo","matr",
			"username","gid","gerente",
			"nome","sobrenome","nomeCompl",
			"cargo","area","ramal",
			"email","descr","flagRespContr",
			"ultimoLogin");
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	/**
	 * Retorna uma lista de todos os nomes do usuarios ativos
	 * @param boolean $ativo : se devemos selecionar ou nao users ativos
	 * @return ArrayObj
	 */
	public function getNomesCompleto($ativo=true){
		$this->setVar("campos", array("id","nomeCompl"));
		if($ativo)
			$ret = new ArrayObj($this->select("ativo","1"));
		else
			$ret = new ArrayObj($this->select());
		$ret = new ArrayObj(CommonMethods::arrayToHash($ret->getArrayCopy(), "id"));
		return $ret;
	}
	/**
	 * Retorna uma tag html (option) de todos os nomes do usuarios ativos
	 * @param boolean $ativo : se devemos selecionar ou nao users ativos
	 * @return NULL|HtmlTag
	 */
	public function getNomesCompletosHtmlTagOption($ativo=true){
		$nomes = $this->getNomesCompleto($ativo);
		if($nomes->count()==0)
			return null;
		$aux = $nomes->getArrayCopy();
		foreach ($aux as $id=>$n){
			if(isset($tag))
				$tag->setNext(new HtmlTag("option", $id,"",$n["nomeCompl"],null,new HtmlTagAttr("value",$id)));
			else 
				$tag = new HtmlTag("option", $id,"",$n["nomeCompl"],null,new HtmlTagAttr("value",$id));
		}
		return $tag;
	}
	/**
	 * @return ArrayObj
	 */
	public function getAreas(){
		$this->setVar("campos", array("area"));
		$r = new ArrayObj($this->selectDistinct("ativo","0"," > "));
		$ret = new ArrayObj();
		foreach ($r->getArrayCopy() as $d)
			$ret->append($d["areas"]);
		return $ret;
	}
	/**
	 * @return NULL|HtmlTag
	 */
	public function getAreasHtmlTagOption(){
		$areas = $this->getAreas();
		if($areas->count()==0)
			return null;
		$aux = $areas->getArrayCopy();
		$tag = new HtmlTag("option", $aux[0],"",$aux[0],null,new HtmlTagAttr("value",$aux[0]));
		for($i=1;$i<$areas->count();$i++)
			$tag->setNext(new HtmlTag("option", $aux[$i],"",$aux[$i],null,new HtmlTagAttr("value",$aux[$i])));
		return $tag;
	}
} 
?>
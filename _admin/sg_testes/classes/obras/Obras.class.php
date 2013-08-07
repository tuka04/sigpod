<?php
/**
 * Obras
 * @version 0.1 (30/7/2013)
 * @package classes
 * @subpackage obras
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela obra_obra
 * @see Acesso e querys aos bancos de dados
 */

class Obras extends DAO{
	const Tabela = "obra_obra";

	private static $campos = array("id","empreendID","cod",
			"nome","nomeBusca","descricao",
			"caract","tipo","lat",
			"lng","campus","dimensao",
			"dimensaoUn","responsavelProjID","responsavelObraID",
			"estadoID","ocupacao","amianto",
			"residuos","pavimentos","elevador",
			"custo","desc_img","visivel",
			"observacoes");

	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	/**
	 * @param array $campo
	 * @return ArrayObj
	 */
	public function getByCampo(array $campos, WhereClause $w=null){
		$ret = new ArrayObj();
		$this->setVar("campos", $campos);
		$ret = new ArrayObj($this->selectOrderedBy(new OrderedBy("nome","ASC")));
		$ret = new ArrayObj(CommonMethods::arrayToHash($ret->getArrayCopy(), "id"));
		return $ret;
	}
	
	
	
}
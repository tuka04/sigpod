<?php
/**
 * @version 2.0 30/7/2012
 * @package geral
 * @subpackage historico
 * @author Leandro Kümmel Tria Mendes
 * @desc contem os DAO para a tabela data_historico
 */

class HistoricosTipo {
	const SAIDA = "saida";
	const DESPACHO_INTERNO = "despIntern";
	const ENTRADA = "entrada";
}
class Historicos extends DAO {
	const Tabela = "data_historico";

	private static $campos = array("id","data","tipo",
			"docID","usuarioID","acao",
			"unidade","label","despacho",
			"volumes","doc_targetID");

	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	/**
	 * Retorna hash com merge dos campos, onde o campo eh a key
	 * @param array $histo
	 * @param string $campo
	 * @return multitype:|Ambigous <multitype:multitype: , unknown>
	 */
	public static function groupHistoByCampo($histo,$campo){
		$r = array();
		if(!is_array($histo))
			return $r;
		foreach ($histo as $h){
			if(!isset($r[$h[$campo]]))
				$r[$h[$campo]]=array();
			$r[$h[$campo]][]=$h;
		}
		return $r;
	}
}
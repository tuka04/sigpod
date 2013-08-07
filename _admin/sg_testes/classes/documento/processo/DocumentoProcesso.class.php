<?php
/**
 * @version 2.0 06/08/2012
* @package geral
* @subpackage documento.processo
* @author Leandro Kümmel Tria Mendes
* @desc contem os DAO para a tabela doc_processo
*/
class DocumentoProcesso extends DAO {
	const Tabela = "doc_processo";
	
	private static $campos = array("id","numero_pr","unOrgProc",
			"unOrgInt","assunto","tipoProc",
			"guardachuva","documento","obra",
			"anexos","referProc");
	
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
}
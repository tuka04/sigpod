<?php
/**
 * Obras
 * @version 0.1 (30/7/2013)
 * @package classes
 * @subpackage obras
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela obra_empreendimento
 * @see Acesso e querys aos bancos de dados
 */

class ObraEmpreendimento extends DAO{
	const Tabela = "obra_empreendimento";

	private static $campos = array("id","nome","nomeBusca",
			"unOrg","justificativa","descricao",
			"local","solicNome","solicDepto",
			"solicEmail","solicRamal","responsavelID",
			"ofirID");

	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	}
	
}
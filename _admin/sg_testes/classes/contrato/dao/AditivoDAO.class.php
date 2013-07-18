<?php
class AditivoDAO extends BD{
	
	const Tabela = "contrato_aditivo";
	/**
	 * array com os campos
	 * @var array
	 */
	static private $campos = array("doc_contratoID","sys_alertaID","alerta","usuariosID");
	/**
	 * @var BD
	 */
	public $bd;
	/**
	 * Constroi esse aditivo para o banco de dados;
	 * @param BD $bd
	 */
	public function AditivoDAO(BD $bd=null){
		if($bd!=null)
			$this->bd=$bd;
		else 
			$this->bd=new BD();
	}
	
	public function create(){
		//nada a fazer! A tabela ja esta criada.
	}
} 
?>
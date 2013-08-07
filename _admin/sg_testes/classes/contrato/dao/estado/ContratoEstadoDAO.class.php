<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 26/06/2013
 * @version 1.1.1.4
 * @desc DAO com o controle de alerta
 */

require_once 'classes/common/ArrayObj.class.php';
class ContratoEstadoDAO extends DAO{
	
	const Tabela = "contrato_estado";
	/**
	 * array com os campos
	 * @var array
	 */
	static private $campos = array("sysContratoEstadoID","docID","motivo","dias","data");
	/**
	 * id do contrato
	 * @var int
	 */
	private $sysContratoEstadoID;
	/**
	 * id do alerta do sistema (sys_alerta)
	 * @var int
	 */
	private $docID;
	/**
	 * se o esta com alerta
	 * @var boolean
	 */
	private $motivo;
	/**
	 * id do usuario q, possivelmente, removeu o alerta
	 * @var int
	 */
	private $data; 
	
	public function ContratoEstadoDAO(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();//remover após criar as tabelas
		requireSubModule("contrato_estado");
	}
	
	public function create(){
		//cria a tabela
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela."
				( 
				  sysContratoEstadoID int NOT NULL ,
	  			  docID int NOT NULL ,
	  			  motivo varchar(255) NULL default NULL ,
				  dias varchar(255) NULL default NULL ,
				  data int(11) NULL default NULL,
				  INDEX s_id (sysContratoEstadoID),
				  UNIQUE d_id (docID),
				  FOREIGN KEY (sysContratoEstadoID) REFERENCES sys_contrato_estado(id) ON DELETE NO ACTION ON UPDATE NO ACTION,
				  FOREIGN KEY (docID) REFERENCES doc(id) ON DELETE NO ACTION ON UPDATE NO ACTION
				) ENGINE=InnoDB;";
		$this->query($qr);//executa query acima
	}
	
	public function select($campo="",$valor="",$comp=" = "){
		if(empty($campo)||empty($valor))
			$w = "";
		elseif(!is_array($valor))
		$w = "WHERE ".$campo." ".$comp." '".$valor."'";
		elseif(is_array($valor))
		$w = "WHERE ".$campo." ".$comp." (".implode(",",$valor).") ";
		$join = " INNER JOIN ".SysContratoEstado::Tabela." ON ".SysContratoEstado::Tabela.".id = ".self::Tabela.".sysContratoEstadoID ";
		$qr = DAO::SELECT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, self::Tabela.".*,".SysContratoEstado::Tabela.".nome", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, self::Tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $join.$w, $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
}
?>
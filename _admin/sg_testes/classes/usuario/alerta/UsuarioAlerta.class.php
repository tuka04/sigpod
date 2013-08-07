<?php

class UsuarioAlerta extends DAO{
	
	const Tabela = "usuario_alerta";
	/**
	 * array com os campos
	 * @var array
	 */
	static private $campos = array("usuarioID");
	
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
		$this->create();
	}
	
	public function create(){
		$qr = "CREATE TABLE IF NOT EXISTS ".self::Tabela."
				(id int primary key auto_increment, usuarioID int not null , 
				INDEX u_id (usuarioID), 
				FOREIGN KEY (usuarioID) REFERENCES usuarios(id) ON DELETE NO ACTION ON UPDATE NO ACTION 
			)  ENGINE=InnoDB;";
		$this->query($qr);
	}
	
	public function select($campo="",$valor="",$comp=" = "){
		if(empty($campo)||empty($valor))
			$w = "";
		elseif(!is_array($valor))
			$w = "WHERE ".$campo." ".$comp." '".$valor."'";
		elseif(is_array($valor))
			$w = "WHERE ".$campo." ".$comp." (".implode(",",$valor).") ";
		$join = " INNER JOIN usuarios ON usuarios.id = ".self::Tabela.".usuarioID ";
		$qr = DAO::SELECT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, "usuarios.*, usuario_alerta.id as uaid", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, self::Tabela, $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $join.$w, $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
	
	public function selectUsuarios($campo="",$valor="",$comp=" = "){
		if(empty($campo)||empty($valor))
			$w = "";
		elseif(!is_array($valor))
		$w = "WHERE ".$campo." ".$comp." '".$valor."'";
		elseif(is_array($valor))
		$w = "WHERE ".$campo." ".$comp." (".implode(",",$valor).") ";
		$qr = DAO::SELECT;
		$qr = str_replace(DAO::TOKEN_CAMPOS, "*", $qr);
		$qr = str_replace(DAO::TOKEN_TABELA, "usuarios", $qr);
		$qr = str_replace(DAO::TOKEN_WHERE, $w, $qr);
		$qr = str_replace(DAO::TOKEN_ORDER, "", $qr);
		$qr = str_replace(DAO::TOKEN_LIMIT, "", $qr);
		return $this->query($qr);
	}
}

?>
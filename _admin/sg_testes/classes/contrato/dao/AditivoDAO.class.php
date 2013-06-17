<?php
class AditivoDAO extends BD{
	/**
	 * @var BD
	 */
	public $bd;
	/**
	 * Constroi esse aditivo para o banco de dados;
	 * @param BD $bd
	 */
	public function AditivoDAO(BD $bd){
		$this->bd=$bd;
	}
} 
?>
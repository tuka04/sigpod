<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @desc Contém métodos que verificam o vencimento do um contrato 
 * a partir da data de conclusão prevista
 * @since 26/06/2013
 * @version 1.1.1.4
 */ 
interface VencimentoIF {
	/**
	 * Construtor
	 * @param string $data
	 */
	public function Vencimento($id,$data,$valid=array());
	/**
	 * Retorna uma variavel desse objeto
	 * @param string $var
	 */
	public function getVar($var);
	/**
	 * Retorna se esse objeto esta vencido
	 * @return boolean
	 */
	public function estaVencido();
	/**
	 * Calcula se o objeto esta vencido, ou proximo a vencer, ou nenhuma opcao
	 */
	public function setVencido();
	/**
	 * Verifica se esta proximo do vencimento
	 * @return boolean
	 */
	public function estaProximoVencimento();
	
}
?>
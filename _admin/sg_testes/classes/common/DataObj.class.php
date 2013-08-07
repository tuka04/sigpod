<?php
/**
 * Vencimento
 * @version 0.1 (29/6/2013)
 * @package classes
 * @subpackage common
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula datas 
 */

class DataObj extends DateTime{
	/**
	 * @var string
	 */
	private $dateFormat = "d/m/Y";
	/**
	 * @var DateTimeZone
	 */
	private $timezone;
	/**
	 * @var DateTime
	 */
	private $data;
	/**
	 * @param string $data
	 */
	public function __construct($data){
		$this->timezone = new DateTimeZone("America/Sao_Paulo");
		$this->data = new DateTime($data, $this->timezone);
	}
	/**
	 * retorna ano da data
	 * @return date
	 */
	public function getAno(){
		return $this->data->format("Y");
	}
	
	/**
	 * retorna mes da data
	 * @return date
	 */
	public function getMes(){
		return $this->data->format("m");
	}
	
	/**
	 * retorna dia da data
	 * @return date
	 */
	public function getDia(){
		return $this->data->format("d");
	}
	/**
	* retorna dia da data
	* @return date
	*/
	public function getData(){
		return $this->data->format($this->dateFormat);
	}
}
?>
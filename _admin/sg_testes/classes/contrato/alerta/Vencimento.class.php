<?php
/**
 * Vencimento
 * @version 0.1 (29/6/2013)
 * @package classes
 * @subpackage contrato.alerta
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula o vencimento de um contrato
 */
require_once 'classes/contrato/interfaces/VencimentoIF.class.php';
class Vencimento implements VencimentoIF {
	/**
	 * Formato da data
	 * @var string
	 */
	private $dateFormat="d/m/Y";
	/**
	 * Data de conclusao prevista (ou outra a desejar)
	 * @var DateTime
	 */
	private $data;
	/**
	 * Prazo de validade, pode ser uma data ou numero de dias
	 * Se for um array, e um campo for separada por n:n2 entao
	 * os alertas vao de n ate n2
	 * @var ArrayObject
	 */
	private $validade;
	/**
	 * Booleano q indica se esse elemento esta vencido ou nao
	 * @var boolean
	 */
	private $vencido;
	/**
	 * Indica se esta proximo ao vencimento
	 * @var boolean
	 */
	private $proxVencimento;
	/**
	 * Um identificador qualquer do documento
	 * @var mixed
	 */
	private $id;
	/**
	 * Id da validade utilizado no calculo
	 * @var int
	 */
	private $idValidade;
	
	public function Vencimento($id,$data,$validade=array()){
		$this->id=$id;
		str_replace("-", "/", $data);
		$this->data = DateTime::createFromFormat($this->dateFormat, $data, new DateTimeZone("America/Sao_Paulo"));
		$this->vencido=false;
		$this->proxVencimento=false;
		$this->validade=$validade;
		if(empty($validade))
			$this->loadValidade();
		$this->setVencido();
	}
	/**
	 * Seta a validade a partir da tabela em SysAlerta
	 */
	private function loadValidade(){
		$sa = new SysAlerta();
		$val = $sa->select();
		$this->validade = new ArrayObject();
		foreach ($val as $v)
			$this->validade->append(array("id"=>$v["id"],"dias"=>$v["ini"]));
	}
	
	public function getVar($var){
		return (property_exists("Vencimento", $var))?$this->$var:null;
	}
	
	public function estaVencido(){
		return $this->vencido;
	}
	
	public function estaProximoVencimento(){
		return $this->proxVencimento;
	}
	
	public function setVencido(){
		$hj = new DateTime("today", new DateTimeZone("America/Sao_Paulo"));
		foreach ($this->validade->getArrayCopy() as $v){
			$a = explode(":",$v["dias"]);
			$len = count($a);
			if($len>2)
				die("Erro no vetor de validade. ".__FILE__." Classe:<b>".__CLASS__."</b> Linha: <b>".__LINE__."</b>");
			$data = DateTime::createFromFormat($this->dateFormat,$this->data->format($this->dateFormat),$this->data->getTimezone());
			if($len==1){
				$data->sub(new DateInterval("P".$a[$len-1]."D"));//subtrai um dia unico
				$interval = $hj->diff($data, false);//se essa subtracao for == hj entao: alerta
				if(($interval->days==0 && $interval->invert==0)){
					$this->proxVencimento = true;
				}
				elseif($interval->invert){
					if($interval->days > $a[$len-1]){
						$this->vencido=true;
						$this->idValidade=$v['id'];
					}
					else{
						$this->proxVencimento = true;
						$this->idValidade=$v['id'];
					}
				}
			}
			else{
				for($i=$a[$len-1];$i<=$a[0];$i++){
					$data->add(new DateInterval("P".($a[$len-1]+$i)."D"));
				}
			}
		}
	}
}
?>
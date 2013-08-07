<?php

class CommonMethods{

	/**
	 * @param date $data1
	 * @param date $data2
	 * @return multitype:NULL |multitype:number
	 */
	public static function montaData($data1,$data2){
		if(!$data1 && !$data2)
			return array(null,null);
		$datas = array(explode('/',$data1),explode('/',$data2));
		if(count($datas) == 2 && count($datas[0]) == 3 && count($datas[1]) == 3){//intervalo
			return array(mktime(0,0,1,$datas[0][1],$datas[0][0],$datas[0][2]),mktime(23,59,59,$datas[1][1],$datas[1][0],$datas[1][2]));
		} elseif(count($datas) == 2 && count($datas[0]) == 3 && count($datas[1]) < 3) {//apenas 1 data
			return array(mktime(0,0,1,$datas[0][1],$datas[0][0],$datas[0][2]),mktime(23,59,59,$datas[0][1],$datas[0][0],$datas[0][2]));
		} else {
			return array(null,null);
		}
	}
	/**
	 * @param timestamp $d1
	 * @param timestamp $d2
	 * @return number
	 */
	public static function getDiasEntreDatas($d1,$d2){
		if($d1==""||$d2=="")
			return 0;
		$segundos_diferenca = $d1 - $d2;
		//converto segundos em dias
		$dias_diferenca = $segundos_diferenca / (60 * 60 * 24);			
		//obtenho o valor absoluto dos dias (tiro o possível sinal negativo)
		$dias_diferenca = abs($dias_diferenca);
		//tiro os decimais aos dias de diferenca
		$dias_diferenca = floor($dias_diferenca);
		return  $dias_diferenca;
	}
	/**
	 * @param array $arr
	 * @param string $key
	 * @return multitype:
	 */
	public static function arrayToHash($arr,$key){
		$ret = new ArrayObject();
		foreach ($arr as $a)
			$ret->offsetSet($a[$key], $a);
		return $ret->getArrayCopy();
	}
	
	/**
	 * Retorna hash com merge dos campos, onde o campo eh a key
	 * @param array $obras
	 * @param string $campo
	 * @return multitype:|Ambigous <multitype:multitype: , unknown>
	 */
	public static function groupByCampo($arr,$campo){
		$r = array();
		if(!is_array($arr))
			return $r;
		foreach ($arr as $h){
			if(!isset($r[$h[$campo]]))
				$r[$h[$campo]]=array();
			$r[$h[$campo]][]=$h;
		}
		return $r;
	}
}
?>
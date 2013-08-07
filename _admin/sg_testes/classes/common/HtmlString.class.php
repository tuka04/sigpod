<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 23/07/2013
 * @version 1.1.1.5
 * @desc Classe que contem metodos comuns a tags e requisicoes HTTP
 */

class HtmlString {
	
	/**
	 * Função encapsuladora de htmlentities. Usada para padronizar o uso desta função no SiGPOD e evitar problemas futuros com encoding.
	 * @param string $string a string a ser htmlentities encodada
	 * @param $tipo flags para htmlenties (padrão ENT_QUOTES)
	 * @param string $cod codificação (padrão: ini_get('default_charset'))
	 * @param boolean $double se deverá fazer double encode (aka &aacute; -> &amp;aacute;) (padrão: false)
	 * @return string
	 */
	public static function encode($string, $tipo = ENT_QUOTES, $cod = '', $double = false){
		
		if ($string === null || $string === "") 
			return $string;
		$stringEnc = mb_detect_encoding($string);
		if ($cod == '') {
			if ($stringEnc == 'ASCII') 
				$cod = ini_get('default_charset');
			else 
				$cod = $stringEnc;
		}
		if ($stringEnc != $cod)
			$string = mb_convert_encoding($string, $cod);
		$ret = htmlentities($string, $tipo, $cod, $double);
		return $ret;
	}

	public static function encodeRequest(){
		foreach($_REQUEST as &$r)
			$r = self::encode(urldecode($r),ENT_QUOTES, null, false);
	}
	
	/**
	 * Função encapsuladora de html_entity_decode. Usada para padronizar o uso desta função no SiGPOD e evitar problemas futuros com encoding.
	 * @param string $string a string a ser decodada
	 * @param $flags flags para html_entity_decode (padrão: ENT_QUOTES)
	 * @param string $encoding codificação a ser usada (padrão: ini_get('default_charset'))
	 */
	public static function decode(&$string, $flags = ENT_QUOTES, $encoding = null) {
		if ($encoding == null || $encoding == "")
			$encoding = ini_get('default_charset');
		$string = html_entity_decode($string, $flags, $encoding);
		return $string;
	}
	/**
	 * Remove os acentos de uma string
	 * qndo estao no formato &alguma_coisa;
	 * @param string $str
	 * @return string
	 */
	public static function removeAcentos($str){
		$ret="";
		for($i=0;$i<strlen($str);$i++){
			if($str[$i]!="&"){
				$ret.=$str[$i];
			}
			else{
				$ret.=$str[$i+1];
				for($j=$i;$j<strlen($str) && $str[$j]!=";";$j++)
					$i=$j;
				$i++;
			}
		}
		return $ret;
	}
}
?>
<?php
	include_once '../modules.php';
	include_once '../includeAll.php';

	/*print "<pre>";
	var_dump(trataAditivo("TA002- prorrogação do PE em 17 dias - Publicado em 03/02/12"));
	print "</pre>";*/
	
	$file = fopen('contratos.csv', 'r');
	
	set_time_limit(600);
	
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	while (($linha = fgets($file)) !=  false) {
		$partes = explode(";", str_replace("\n", "", $linha));
	
		$escopo = true;
		
		/*if (stripos($partes[10], "escopo") === false) {
			$escopo = false;
		}
		
		print "<br><br>".$partes[11]." - " . var_dump($escopo);
		print "<br>";
		print "<pre>";
		var_dump(trataAditivo($partes[11], $escopo));
		print "</pre>";*/
		
		/*print "<br><br>".$partes[10]." - " . var_dump($escopo);
		print "<br>";
		print "<pre>";
		var_dump(trataAditivo($partes[10], $escopo, false));
		print "</pre>";*/
		
		print "<br><br>".$partes[15]." - ";
		print "<br>";
		print "<pre>";
		var_dump(trataResponsavel($partes[15], $bd));
		print "</pre>";
	}

	function trataAditivo($string, $escopo, $data = true) {
		if ($string === null || $string === "")
			return null;
		
		$string = str_replace(array("\n", "\r", "\t", "\"", "'"), array("", "", "", "", ""), $string);
		//$string = stringBusca($string);
		
		$string = mb_ereg_replace("^[ ]+", "", mb_strtolower('  '.$string,'utf-8')); 
		
		$chars_acentuados = 
		array("Ã","ã","Õ","õ","á","Á","é","É","í","Í","ó","Ó","ú","Ú","ç","Ç","à","À","è","È","ì","Ì","ò","Ò","ù","Ù","ä","Ä","ë","Ë","ï","Ï","ö","Ö","ü","Ü","Â","Ê","Î","Ô","Û","â","ê","î","ô","û","!","?","“","”","\"","\\","/","%");
		$chars_normais =
		array("a","a","o","o","a","a","e","e","i","i","o","o","u","u","c","c","a","a","e","e","i","i","o","o","u","u","a","a","e","e","i","i","o","o","u","u","A","E","I","O","U","a","e","i","o","u",".",".",".",".","." ,"." ,".",".");
		// retira os acentos
		$string = str_replace($chars_acentuados, $chars_normais, $string);
		
		var_dump($string);
		
		if ($data) {
			$partes = explode("-", $string);
			if (count($partes) <= 1)
				return false;
				
			foreach ($partes as $indice => $val) {
				$partes[$indice] = trim($val);
			}
			
			$parteRelevante = $partes[1];
			$q = $parteRelevante;
		}
		else {
			$q = $string;
		}
		
		var_dump($q);
		
		$tipo = '';
		$valor = 0;
		$campo = array();
		if ($data) {
			if (stripos($q, "prazo ") !== false) {
				if (!$escopo) 
					$campo[0] = 'prazoContr';
				else
					$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "prazo ") + strlen("prazo "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "prazos") !== false) {
				if (!$escopo) {
					$campo[0] = 'prazoContr';
					$campo[1] = 'prazoProjObra';
				}
				else {
					$campo[1] = 'prazoProjObra';
				}
				
				$substr = substr($q, stripos($q, "prazos ") + strlen("prazos "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "vigencia") !== false) {
				if (!$escopo) 
					$campo[0] = 'prazoContr';
				else
					$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "vigencia") + strlen("vigencia"), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, " pe ") !== false) {
				$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, " pe ") + strlen(" pe "), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			elseif (stripos($q, "execucao") !== false) {
				$campo[0] = 'prazoProjObra';
				
				$substr = substr($q, stripos($q, "execucao") + strlen("execucao"), strlen($q) - 1);
				$subpartes = explode(" ", $substr);
			}
			
			
			if (count($subpartes) > 0) {
				foreach ($subpartes as $indice => $v) {
					if ($v === null || $v === "" || $v === false || strlen($v) === 0) {
						continue;
					}
					
					if ($v + 0 !== 0) {
						$valor = $v + 0;
						if (isset($subpartes[$indice+1]) && stripos($subpartes[$indice+1], "dia") !== false) {
							$tipo = 'dias';
						}
						else {
							if (stripos($substr, "R$") !== false) 
								$tipo = 'dinheiro';
						}
						break;
					}
				}
			}
		}
		else {
			$q = trim($q);
			if (stripos($q, "alocacao") !== false) return null;
			$subpartes = explode(" ", $q);
			
			$campo = 'valorTotal';
			$tipo = 'dinheiro';
			
			$operador = '+';
			$valor = array();
			foreach ($subpartes as $indice => $v) {
				if ($v == '-') {
					$operador = '-';
				}
				else {
					$v = str_replace(".", "", $v);
					$v = str_replace(",", ".", $v);
					
					if ($v + 0 !== 0) {
						$v = $v+0;
						if ($operador == '-') {
							$valor[] = -$v;
						}
						else {
							$valor[] = $v;
						}
					}
				}
			}
			
		}
		
		
		return array("campo" => $campo, "valor" => $valor, "tipo" => $tipo);
		
	}

	
	function trataResponsavel($string, $bd) {		
		if ($string == null || $string == "") return null;
		$partes = explode("-", $string);
		
		if (count($partes) <= 1) return null;
		
		$email = $partes[1];
		
		$email = str_replace(",", ".", $email);
		$email = trim($email);
		
		var_dump($email);
		
		$sql = "SELECT * FROM usuarios WHERE email LIKE '".$email."'";
		$res = $bd->query($sql);
		
		if (count($res) > 0) 
			return $res[0];
		else
			return null;
	}

?>
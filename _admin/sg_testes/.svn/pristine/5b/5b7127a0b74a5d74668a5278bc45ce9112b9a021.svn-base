<?php
	include_once('../includeAll.php');
	include_once('../sgd_modules.php');
	
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	$file = fopen("arquivo antigo CINFRA.csv", "r");
	
	set_time_limit(180);
	
	$i = 0;
	
	$invalidos = fopen("antigos_invalidos.txt", "w");
	
	$unicamp = fopen("antigos_unicamp.csv", "w");
	$funcamp = fopen("antigos_funcamp.txt", "w");
	$sigpod = fopen("ja_presentes.txt", "w");
	
	// percorre o arquivo até chegar no fim
	$aspas = false;
	while (($linha = fgets($file)) !=  false) {
		$pr = "";
		$ano = "";
		if ($aspas == false) {
			print("<br>=======================================<br>");
			$campos = explode(';', $linha);
			
			if (($posicao = strpos($campos[3], '"')) !== false) {
				//print("aspas<br>");
				$aspas = true;
			}
			
			$pr = trim($campos[3], '"');
			if ($pr == null) continue;
			
			print("Linha $i: Processo: $pr<br>");

			if (!$aspas) $i++;
		}
		else {
			//$pr = trim($linha, '"');
			$pr = $linha;
			if (($posicao = strpos($linha, '"')) !== false) {
				//print("aspas<br>");
				$aspas = false;
				$pr[$posicao] = " ";
			}
			$pr = trim($pr);
			if ($pr == null) continue;
			print("Linha $i: Processo: $pr<br>");
			if (!$aspas) $i++;
		}
		
		$pr = str_replace("--", "-", $pr);
		
		$pr = str_ireplace(" - funcamp", "", $pr);
		$pr = str_ireplace("-funcamp", "", $pr);
		$pr = str_ireplace(" -funcamp", "", $pr);
		$pr = str_ireplace("- funcamp", "", $pr);
		$pr = str_ireplace("funcamp", "", $pr);
		$pr = trim($pr);
		//var_dump($pr);
		//print(strlen($pr) . "\n");
		if ($pr != "") {
			$pedacos = explode("-", $pr);
			if (count($pedacos) < 2) {
				print('<font color="red"><b>Erro: menos que 2 pedacos</b></font><br>');
				fwrite($invalidos, $pr . "\n");
				continue;
			}
			else {
				if (count($pedacos) == 4) {
					$num = 2;
					$ano = $pedacos[3];
				}
				else {
					$num = 1;
					if (count($pedacos) != 2) {
						if (strpos($pr, "/") !== false) {
							$num++;
							$ano = strstr($pedacos[$num], "/");
							$ano = ltrim($ano, "/");
						}
						if (strpos($pr, "\\") !== false) {
							$num++;
							$ano = strstr($pedacos[$num], "\\");
							$ano = ltrim($ano, "\\");
						}
						if ($ano == "") {
							$ano = $pedacos[$num+1];
						}
					}
					else {
						/*$ano = strstr($pedacos[1], "/");
						$ano = strstr($pedacos[1], "\\");
						$ano = ltrim($ano, "\\");
						$ano = ltrim($ano, "/");*/
						
						if (strpos($pr, "/") !== false) {
							$ano = strstr($pedacos[$num], "/");
							$ano = ltrim($ano, "/");
						}
						elseif (strpos($pr, "\\") !== false) {
							$ano = strstr($pedacos[$num], "\\");
							$ano = ltrim($ano, "\\");
						}
						else {
							$num = 0;
							$ano = $pedacos[1];
						}
					}
				}
				$central = ltrim($pedacos[$num], "0");
				
				if (strpos($central, "/") !== false) {
					$pedacosTemp = explode("/", $central);
					$central = $pedacosTemp[0];
				}
				if (strpos($central, "\\") !== false) {
					$pedacosTemp = explode("\\", $central);
					$central = $pedacosTemp[0];
				}
				$central = str_ireplace("Funcamp", "", $central);
				$central = str_ireplace("P", "", $central);
				$central = trim($central);
				$central = str_pad($central, 5, "0", STR_PAD_LEFT);
				
				$ano = trim($ano, "\0\n\t");
				$ano = str_ireplace("funcamp", "", $ano);
				//print("strlen ano: " .strlen($ano));
				if (strlen($ano) < 2) {
					print('<font color="blue"><b>ANO INVALIDO</b></font><br>');
					fwrite($invalidos, $pr . "\n");
					continue;
				}
				if ($ano < 100) {
					if ($ano > date("y")) $ano = "19" . $ano;
					else $ano = "20" . $ano;
				}
				else {
					if ($ano >= 100 && $ano <= 1000) {
						print('<font color="blue"><b>ANO INVALIDO</b></font><br>');
						fwrite($invalidos, $pr . "\n");
						continue;
					}
				}
				if ($ano > 2012) {
					print('<font color="blue"><b>ANO INVALIDO</b></font><br>');
					fwrite($invalidos, $pr . "\n");
					continue;
				}
				
				if ($central > 99999) {
					print('<font color="green"><b>NUMERO CENTRAL INVALIDO</b></font><br>');
					fwrite($invalidos, $pr . "\n");
					continue;
				}
				
				if (stripos($linha, "funcamp") !== false) {
					print('<font color="blue">PROCESSO FUNCAMP</font><br>');
					fwrite($funcamp, $central . ";" . $ano . "\n");
					print("Numero central: $central Ano: $ano<br>");
					continue;
				}
				else {
					$tipo = "P";
					//$digito = trim($pedacos[$num-1], "-P");
					//$digito = $pedacos[$num-1];
					
					$digito = substr($pr, 0, 2);
					$digito = trim($digito, "-P");
					$digito = str_pad($digito, 2, "0", STR_PAD_LEFT);
					if (stripos($pr, "E") !== false) {
						$tipo = "E";
					}
					if ($digito <= 0 || $digito > 34) {
						print('<font color="red">DIGITO INVALIDO</font><br>');
					}
				}
				print("Digito: $digito Tipo: $tipo Numero central: $central Ano: $ano<br>");

				$numeroComp = $digito. " " .$tipo. "-" .$central. "-" . $ano;
				$sql = "SELECT id FROM doc WHERE labelID = '1' AND numeroComp = '$numeroComp'";
				$res = $bd->query($sql);
				
				if (count($res) == 1) {
					print("Já consta no sistema!<br>");
					fwrite($sigpod, $digito. ";" .$tipo. ";" .$central. ";" .$ano. "\n");
					continue;
				}
				elseif (count($res) > 1) {
					print('<font color="red"><b>ERRO: MAIS DE UM PROCESSO ENCONTRADO</b></font><br>');
					continue;
				}
				elseif (count($res) == 0) {
					print("Não consta no sistema<br>");
					fwrite($unicamp, $digito. ";" .$tipo. ";" .$central. ";" .$ano. "\n");
					continue;
				}
			}
		}
		else {
			print('<font color="red">SEM PROCESSO</font><br>');
		}
	}
	
	fclose($file);
	fclose($invalidos);
	fclose($unicamp);
	fclose($funcamp);
	fclose($sigpod);
?>
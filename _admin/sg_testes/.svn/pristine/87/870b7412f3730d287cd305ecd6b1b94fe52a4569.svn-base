<?php

	include_once('../includeAll.php');
	include_once('../sgd_modules.php');
	include_once 'phpQuery-onefile.php';

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	$file = fopen("saida.csv", "r");
	//$saida = fopen("saida.csv", "w");
	
	set_time_limit(600);
	
	$i = 0;
	
	$telefone = '';
	$email = '';
	
	// percorre o arquivo até chegar no fim
	while (($linha = fgets($file)) !=  false) {
		$nome = '';
		$cnpj = '';
		$rua = '';
		$bairro = '';
		$cidade = '';
		$estado = '';
		$cep = '';
		$numero = '';
		$complemento = '.';
		
		$partes = explode(';', $linha);
		
		foreach($partes as $campo => $valor) {
			$partes[$campo] = trim($valor);
			$partes[$campo] = str_replace(array("\n"), array(""), $partes[$campo]);
			$partes[$campo] = htmlentities($partes[$campo]);
		}
		
		$nome = $partes[0];
		$cnpj = $partes[1];
		
		$cnpj = formataCNPJ($cnpj);
		
		$rua = $partes[2];
		$bairro = $partes[3];
		$cidade = $partes[4];
		$estado = $partes[5];
		$cep = $partes[6];
		$numero = $partes[7];
		if (isset($partes[8])) $complemento = $partes[8];
		
		$sql = "INSERT INTO empresa (nome, cnpj, endereco, complemento, cidade, estado, cep, telefone, email, servicos) VALUES (";
		$sql .= "'$nome', '$cnpj', '$rua, $numero, $bairro', '$complemento', '$cidade', '$estado', '$cep', '$telefone', '$email', '')";
		
		$bd->query($sql);
		
		print $sql ."<br />";
	}
	
	function validaCNPJ($cnpj) { 
	    if (strlen($cnpj) <> 18) return 0; 
	    $soma1 = ($cnpj[0] * 5) + 
	
	    ($cnpj[1] * 4) + 
	    ($cnpj[3] * 3) + 
	    ($cnpj[4] * 2) + 
	    ($cnpj[5] * 9) + 
	    ($cnpj[7] * 8) + 
	    ($cnpj[8] * 7) + 
	    ($cnpj[9] * 6) + 
	    ($cnpj[11] * 5) + 
	    ($cnpj[12] * 4) + 
	    ($cnpj[13] * 3) + 
	    ($cnpj[14] * 2); 
	    $resto = $soma1 % 11; 
	    $digito1 = $resto < 2 ? 0 : 11 - $resto; 
	    $soma2 = ($cnpj[0] * 6) + 
	
	    ($cnpj[1] * 5) + 
	    ($cnpj[3] * 4) + 
	    ($cnpj[4] * 3) + 
	    ($cnpj[5] * 2) + 
	    ($cnpj[7] * 9) + 
	    ($cnpj[8] * 8) + 
	    ($cnpj[9] * 7) + 
	    ($cnpj[11] * 6) + 
	    ($cnpj[12] * 5) + 
	    ($cnpj[13] * 4) + 
	    ($cnpj[14] * 3) + 
	    ($cnpj[16] * 2); 
	    $resto = $soma2 % 11; 
	    $digito2 = $resto < 2 ? 0 : 11 - $resto; 
	    return (($cnpj[16] == $digito1) && ($cnpj[17] == $digito2)); 
	} 

	function formataCNPJ($cnpj) {
		for($i=0, $j=0, $num=array(); $i<(strlen($cnpj)); $i++){
			if(is_numeric($cnpj[$i])){
				$num[$j]=$cnpj[$i];
				if($j == 1 || $j == 5){
					$num[$j+1] = '.';
					$j++;
				} elseif($j == 9) {
					$num[$j+1] = '/';
					$j++;
				} elseif($j == 14) {
					$num[$j+1] = '-';
					$j++;
				}
				$j++;
			}
		}
		$cnpj = implode('', $num);
		return $cnpj;
	}
	
?>
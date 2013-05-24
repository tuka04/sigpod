<?php

	include_once('../includeAll.php');
	include_once('../sgd_modules.php');
	include_once 'phpQuery-onefile.php';

	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	$file = fopen("empresas1.csv", "r");
	$saida = fopen("saida.csv", "w");
	
	set_time_limit(600);
	
	$i = 0;
	
	// percorre o arquivo até chegar no fim
	while (($linha = fgets($file)) !=  false) {
		print "<br />============== Linha $i =============<br />";
		
		$campos = explode(';', $linha);
		
		if (count($campos) < 4) {
			print "Linha fora do formato<br />";
			continue;
		}
		
		$nome = $campos[1];
		$end = $campos[2];
		$cnpj = $campos[3];
		
		$nome = str_replace(array("\n", "\"", "\'", ";"), array("", "", "", ""), $nome);
		$nome = trim($nome);
		
		$end = str_replace(array("\n", "\"", "\'", ";", ":", "."), array("", "", "", "", "", ""), $end);
		$end = trim($end);
		
		$cnpj = str_replace(array("\n", "\"", "\'", ";"), array("", "", "", ""), $cnpj);
		$cnpj = trim($cnpj);
		$cnpj = trim($cnpj, ".");
		
		print "Nome: <b>".$nome."</b><br />";
		print "End: <b>".$end."</b><br />";
		
		/*$virgulas = substr_count($end, ',');
		$hifens = substr_count($end, '-');
		
		$traco = false;
		
		if ($virgulas > $hifens) {
			$partes = explode(",", $end);
		}
		else {
			$partes = explode("-", $end);
			$traco = true;
		}
		
		foreach ($partes as $indice => $val) {
			$partes[$indice] = str_replace(array("\n", "\"", "\'", ";", ":"), array("", "", "", "", ""), $val);
			$partes[$indice] = trim($partes[$indice]);
			print "Parte $indice: $partes[$indice] <br />";
		}*/

		//$pattern = '[[:digit:]]{5}\-[[:digit:]]{3}';
		$pattern = "/[0-9]{5}-[0-9]{3}/";
		$matches;
		//$cep = preg_grep($pattern, array($end));
		
		preg_match($pattern, $end, $matches);
		
		//var_dump($matches);
		$cep = '<b><font color="red">CEP INVALIDO</font></b>';
		if (count($matches) > 0) {
			$cep = $matches[0];
		}
		
		print "CEP: $cep <br />";
		
		$html = simple_curl('http://m.correios.com.br/movel/buscaCepConfirma.do', array(
			'cepEntrada' => $cep,
			'tipoCep' => '',
			'cepTemp' => '',
			'metodo' => 'buscarCep'
		));
		
		phpQuery::newDocumentHTML($html, $charset = 'utf-8');
		
		$dados = array( 
			'logradouro'=> trim(pq('.caixacampobranco .resposta:contains("Logradouro: ") + .respostadestaque:eq(0)')->html()),
			'bairro'=> trim(pq('.caixacampobranco .resposta:contains("Bairro: ") + .respostadestaque:eq(0)')->html()),
			'cidade/uf'=> trim(pq('.caixacampobranco .resposta:contains("Localidade / UF: ") + .respostadestaque:eq(0)')->html()),
			'cep'=> trim(pq('.caixacampobranco .resposta:contains("CEP: ") + .respostadestaque:eq(0)')->html())
		);
		
		$dados['cidade/uf'] = explode('/',$dados['cidade/uf']);
		$dados['cidade'] = trim($dados['cidade/uf'][0]);
		$dados['uf'] = trim($dados['cidade/uf'][1]);
		unset($dados['cidade/uf']);
		
		//var_dump($dados);
		
		print "== Correio: ==<br />";
		foreach ($dados as $campo => $valor) {
			print "$campo: ".htmlentities(utf8_decode($valor))."<br />";
		}
		print "== Fim dados do Correio. ==<br >";
		
		$cnpj = formataCNPJ($cnpj);
		print "CNPJ: <b>".$cnpj."</b><br />";
		print validaCNPJ($cnpj) ."<br />";
		
		$nova_linha = '';
		$nova_linha = $nome . ';' . $cnpj. ";" . utf8_decode($dados['logradouro']) . ';' .utf8_decode($dados['bairro']) .";". utf8_decode($dados['cidade']). ";". utf8_decode($dados['uf']).";".utf8_decode($dados['cep'])."\n";
		
		fwrite($saida, $nova_linha);
		
		$i++;
	}


	fclose($saida);
	fclose($file);
	
	function simple_curl($url,$post=array(),$get=array()){
		$url = explode('?',$url,2);		
		if(count($url)===2){
			$temp_get = array();
			parse_str($url[1],$temp_get);
			$get = array_merge($get,$temp_get);
		}
		
		$ch = curl_init($url[0]."?".http_build_query($get));
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		return curl_exec ($ch);
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
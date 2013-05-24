<?php
	/*var_dump(
	 json_decode(html_entity_decode('[{&quot;nome&quot;:&quot;sala&quot;,&quot;caract&quot;:&quot;sss&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;N&atilde;o&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;N&atilde;o&quot;,&quot;area&quot;:&quot;21&quot;,&quot;obs&quot;:&quot;dhjahdkja ahd kasjhdas kjdhsahdasljhd&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Sem duplica&ccedil;&atilde;o :)&quot;,&quot;caract&quot;:&quot;sss&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;&quot;,&quot;estab&quot;:&quot;&quot;,&quot;gases&quot;:&quot;&quot;,&quot;area&quot;:&quot;22&quot;,&quot;obs&quot;:&quot;Sem mais observa&ccedil;&otilde;es&quot;,&quot;especificos&quot;:[{&quot;nome&quot;:&quot;gde_potencia&quot;,&quot;label&quot;:&quot;Equipamento de Grande Pot&ecirc;ncia&quot;,&quot;valor&quot;:&quot;condensador gigante&quot;,&quot;obs&quot;:&quot;&quot;},{&quot;nome&quot;:&quot;residuos&quot;,&quot;label&quot;:&quot;Gera&ccedil;&atilde;o de res&iacute;duos&quot;,&quot;valor&quot;:&quot;&quot;,&quot;obs&quot;:&quot;t&oacute;xico&quot;},{&quot;nome&quot;:&quot;lajes&quot;,&quot;label&quot;:&quot;Sobrecarga Diferenciada de Lajes&quot;,&quot;valor&quot;:&quot;&quot;,&quot;obs&quot;:&quot;&quot;}]},{&quot;nome&quot;:&quot;Sala nova&quot;,&quot;caract&quot;:&quot;id=2&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;Sim&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;Sim&quot;,&quot;area&quot;:&quot;23&quot;,&quot;obs&quot;:&quot;Obs gerais&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Mais uma sala de teste&quot;,&quot;caract&quot;:&quot;Din&acirc;mica&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;0&quot;,&quot;estab&quot;:&quot;0&quot;,&quot;gases&quot;:&quot;1&quot;,&quot;area&quot;:&quot;11&quot;,&quot;obs&quot;:&quot;&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Nome do local&quot;,&quot;caract&quot;:&quot;&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;N&atilde;o&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;N&atilde;o&quot;,&quot;area&quot;:&quot;456&quot;,&quot;obs&quot;:&quot;&quot;,&quot;especificos&quot;:[]}]',ENT_COMPAT,'UTF-8'))
	);*/


include_once '../includeAll.php';
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$file = file_get_contents("empresas_gdocs.csv");
$file = explode("\n", $file);

$l=2;
foreach ($file as $line) {
	//var_dump($line);
	$empr = explode(";", $line);
	
	if(count($empr) != 4){
		print("linha $l foi ignorada por estar em branco<br />");
		$l++;
		continue;
	}
	
	$nome = trim(htmlentities($empr[0],ENT_QUOTES,'utf-8', false));
	
	if($nome == ''){
		print("linha $l foi ignorada porque o NOME esta em branco<br />");
		$l++;
		continue;
	}
	
	for($i=0, $j=0, $num=array(); $i<(strlen($empr[1])); $i++){
		if(is_numeric($empr[1][$i])){
			$num[$j]=$empr[1][$i];
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
	$empr[1] = implode('', $num);
	
	//$sql = "SELECT cnpj FROM empresa WHERE (cnpj = {$empr[1]})";
	//$jaCadastrado = $bd->query($sql);
	
	if(isCnpjValid($empr[1]) || $empr[1] === ''){
		$cnpj = $empr[1];
	} else {
		$cnpj = '';
		print("linha $l ($nome) tem CNPJ ($empr[1]) invalido! A empresa ficara sem CNPJ no BD <br />"); $l++;
		continue;
	}
	
	$empr[2] = trim($empr[2]);
	
	$empr[3] = htmlentities(str_ireplace(array(" /","/","-"), ",", trim($empr[3])),ENT_QUOTES,'utf-8',false);
	
	$sql = "INSERT INTO empresa (nome, cnpj, email, servicos,endereco,complemento,cidade,estado,cep,telefone) VALUES ('{$nome}','{$cnpj}','{$empr[2]}','{$empr[3]}','','','','','','')";
	//print $sql.'<br>';
	if($bd->query($sql))
		print("");
	else
		print "ERRO ao adicionar empresa $nome <br />";
	$l++;
}


function isCnpjValid($cnpj){
	//Etapa 1: Cria um array com apenas os digitos num�ricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
	$j=0;
	for($i=0; $i<(strlen($cnpj)); $i++){
		if(is_numeric($cnpj[$i])){
			$num[$j]=$cnpj[$i];
			$j++;
		}
	}
	//Etapa 2: Conta os d�gitos, um Cnpj v�lido possui 14 d�gitos num�ricos.
	if(count($num)!=14)	{
		$isCnpjValid=false;
	}
	//Etapa 3: O n�mero 00000000000 embora n�o seja um cnpj real resultaria um cnpj v�lido ap�s o calculo dos d�gitos verificares e por isso precisa ser filtradas nesta etapa.
	if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 && $num[8]==0 && $num[9]==0 && $num[10]==0 && $num[11]==0){
		$isCnpjValid=false;
	}
	//Etapa 4: Calcula e compara o primeiro d�gito verificador.
	else {
		$j=5;
		for($i=0; $i<4; $i++){
			$multiplica[$i]=$num[$i]*$j;
			$j--;
		}
		$soma = array_sum($multiplica);
		$j=9;
		for($i=4; $i<12; $i++){
			$multiplica[$i]=$num[$i]*$j;
			$j--;
		}
		$soma = array_sum($multiplica);	
		$resto = $soma%11;			
		if($resto<2){
			$dg=0;
		}else{
			$dg=11-$resto;
		}
		if($dg!=$num[12]){
			$isCnpjValid=false;
		} 
	}
	//Etapa 5: Calcula e compara o segundo d�gito verificador.
	if(!isset($isCnpjValid)){
		$j=6;
		for($i=0; $i<5; $i++){
			$multiplica[$i]=$num[$i]*$j;
			$j--;
		}
		$soma = array_sum($multiplica);
		$j=9;
		for($i=5; $i<13; $i++){
			$multiplica[$i]=$num[$i]*$j;
			$j--;
		}
		$soma = array_sum($multiplica);	
		$resto = $soma%11;			
		if($resto<2){
			$dg=0;
		}else{
			$dg=11-$resto;
		}
		if($dg!=$num[13]){
			$isCnpjValid=false;
		}else{
			$isCnpjValid=true;
		}
	}
	//Trecho usado para depurar erros.
	/*
	if($isCnpjValid==true)	{
		echo "<p><font color=\"GREEN\">Cnpj � V�lido</font></p>";
	}
	if($isCnpjValid==false)	{
		echo "<p><font color=\"RED\">Cnpj Inv�lido</font></p>";
	}
	*/
	//Etapa 6: Retorna o Resultado em um valor booleano.
	return $isCnpjValid;			
}

?>
<?php
	/*var_dump(
	 json_decode(html_entity_decode('[{&quot;nome&quot;:&quot;sala&quot;,&quot;caract&quot;:&quot;sss&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;N&atilde;o&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;N&atilde;o&quot;,&quot;area&quot;:&quot;21&quot;,&quot;obs&quot;:&quot;dhjahdkja ahd kasjhdas kjdhsahdasljhd&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Sem duplica&ccedil;&atilde;o :)&quot;,&quot;caract&quot;:&quot;sss&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;&quot;,&quot;estab&quot;:&quot;&quot;,&quot;gases&quot;:&quot;&quot;,&quot;area&quot;:&quot;22&quot;,&quot;obs&quot;:&quot;Sem mais observa&ccedil;&otilde;es&quot;,&quot;especificos&quot;:[{&quot;nome&quot;:&quot;gde_potencia&quot;,&quot;label&quot;:&quot;Equipamento de Grande Pot&ecirc;ncia&quot;,&quot;valor&quot;:&quot;condensador gigante&quot;,&quot;obs&quot;:&quot;&quot;},{&quot;nome&quot;:&quot;residuos&quot;,&quot;label&quot;:&quot;Gera&ccedil;&atilde;o de res&iacute;duos&quot;,&quot;valor&quot;:&quot;&quot;,&quot;obs&quot;:&quot;t&oacute;xico&quot;},{&quot;nome&quot;:&quot;lajes&quot;,&quot;label&quot;:&quot;Sobrecarga Diferenciada de Lajes&quot;,&quot;valor&quot;:&quot;&quot;,&quot;obs&quot;:&quot;&quot;}]},{&quot;nome&quot;:&quot;Sala nova&quot;,&quot;caract&quot;:&quot;id=2&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;Sim&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;Sim&quot;,&quot;area&quot;:&quot;23&quot;,&quot;obs&quot;:&quot;Obs gerais&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Mais uma sala de teste&quot;,&quot;caract&quot;:&quot;Din&acirc;mica&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;0&quot;,&quot;estab&quot;:&quot;0&quot;,&quot;gases&quot;:&quot;1&quot;,&quot;area&quot;:&quot;11&quot;,&quot;obs&quot;:&quot;&quot;,&quot;especificos&quot;:[]},{&quot;nome&quot;:&quot;Nome do local&quot;,&quot;caract&quot;:&quot;&quot;,&quot;clima&quot;:&quot;&quot;,&quot;dados&quot;:&quot;N&atilde;o&quot;,&quot;estab&quot;:&quot;Sim&quot;,&quot;gases&quot;:&quot;N&atilde;o&quot;,&quot;area&quot;:&quot;456&quot;,&quot;obs&quot;:&quot;&quot;,&quot;especificos&quot;:[]}]',ENT_COMPAT,'UTF-8'))
	);*/


include_once '../includeAll.php';
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$empresas = $bd->query("SELECT * FROM empresa WHERE 1 ORDER BY cnpj");

$last_e = array();
foreach ($empresas as $e) {
	if($last_e['cnpj'] === $e['cnpj'] && $e['cnpj'] !== ''){
		foreach ($last_e as $e_attr => $e_value) {
			$last_e = seleciona_attr($last_e, $e_attr, $e_value, $e[$e_attr]);
		}
		print($last_e['nome'].' '.$e['nome']);
		$sql = "UPDATE empresa SET nome='".$last_e['nome']."', endereco='".$last_e['endereco']."', complemento='".$last_e['complemento']."', cidade='".$last_e['cidade']."', estado='".$last_e['estado']."', cep='".$last_e['cep']."', telefone='".$last_e['telefone']."', email='".$last_e['email']."', servicos='".$last_e['servicos']."' WHERE id=".$last_e['id'];
		//print $sql;
		if($bd->query($sql)){
			$sql = "DELETE FROM empresa WHERE id = ".$e['id'];
			if($bd->query($sql)) {
				print("CNPJ Duplicado encontrado nas posicoes ".$last_e['id'].' e '.$e['id'].' resolvido<br>');
				continue;
			} else {
				print("CNPJ Duplicado encontrado nas posicoes ".$last_e['id'].' e '.$e['id'].' impossivel deletar o cadastro id='.$e['id'].'<br>');
			}
		} else {
			print("CNPJ Duplicado encontrado nas posicoes ".$last_e['id'].' e '.$e['id'].' impossivel alterar o cadastro id='.$last_e['id'].'<br>');
		}
			
	}
	
	$last_e = $e;
}

function seleciona_attr($empresa, $attr, $val1, $val2){
	if($attr == 'id') return $empresa;
	
	if (!$val2 || count($var1) > count($var2)) {
		$empresa[$attr] = $val1;
	} else {
		$empresa[$attr] = $val2;
	}
	return $empresa;
}

?>
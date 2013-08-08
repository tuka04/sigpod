<?php
	/**
	 * @version 0.1 16/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc lida com a exibicao dos modulos do portal HTML
	 */
	//teste github
	include_once 'conf.inc.php';


	/**
	 * @desc monta o menu lateral do usuario
	 * @param string $template caminho do arquivo de template
	 * @param int $perm array de permissoes
	 * @param int $area 2310=index, 2=Empreeendimentos, 3=documentos, 5=OS, 7=pessoas, 11=empresas
	 * @param BD $bd conexao com o bd
	 */
	function showMenu($template,$perm,$area, BD $bd) {
		//carrega o arquivo com o codigo HTML basico da pagina
		$html = file_get_contents($template);
		$buffer = '';
		$data["itens_obra"] = '';
		
		//monta o 1o menu (de obras) se houver permissao
		if ($perm[11]) $data["itens_obra"] .= '<a href="sgo.php?acao=cadastrar">Cadastrar novo Empreeendimento</a><br />';
		if ($perm[12]) $data["itens_obra"] .= '<a href="sgo.php?acao=buscar">Buscar Empreendimento</a>';
		
			//$area /= 2;//areas que serao mostradas
		
		
		//seleciona todos os tipos de documento
		$docs = $bd->query("SELECT nome,nomeAbrv,cadAcaoID FROM label_doc WHERE cadAcaoID > 0");
		//seleciona cada tipo de doc, verifica se tem premissao pra cadastrar e entao cria o link
		foreach ($docs as $doc){
			if ($perm[$doc['cadAcaoID']]) $buffer .= '<a href="sgd.php?acao=cad&amp;tipoDoc='.$doc['nomeAbrv'].'"><img src="img/p.png" border="0" alt="" />'.$doc['nome'].'</a><br />';
		}
		//monta os links de cadastro de documentos em que ha permissao
		$data["itens_doc"] = '';
		if($buffer != ""){
			$data["itens_doc"] .= '<span id="cadDocLink" class="pLink">Cadastrar</span>
			<div class="subMenu2" id="cadDoc">'
			.$buffer.
			'</div>';
		}
		//seleciona os documentos que podem ser criados
		$docs = $bd->query("SELECT nome,nomeAbrv,novoAcaoID FROM label_doc WHERE novoAcaoID > 0");
		$buffer = "";
		//monta os links de criacao de documentos
		foreach ($docs as $doc){
			if ($perm[$doc['novoAcaoID']]) $buffer .= '<a href="sgd.php?acao=novo&amp;tipoDoc='.$doc['nomeAbrv'].'"><img src="img/p.png" border="0" alt="" />'.$doc['nome'].'</a><br />';
		}
		
		//se houver pelo menos um documento para novo, cria a secao de novo documento
		if($buffer != "") 
		$data["itens_doc"] .=
		'<br /><span id="novoDocLink" class="pLink">Criar Novo</span>
		<div class="subMenu2" id="novoDoc">'
			.$buffer.
		'</div>';
		//se tiver permissao, cria o link para buscar documento
		if($perm[1]) $data["itens_doc"] .= '<br /><a href="sgd.php?acao=buscar">Buscar Documento</a><br />';
		
		if($data["itens_doc"] == '') $area /= 3;
		
		$areaatual = showCodTela();
		
		// gera menu de pessoas
		$data['itens_pessoas'] = '';
		if (checkPermission(77) && count(getGerenciados($_SESSION['id'], $bd)) > 0)
			$data['itens_pessoas'] .= '<a href="sgp.php?acao=equipes">Suas Equipes</a><br />';
		if (checkPermission(78)) $data['itens_pessoas'] .= '<a href="sgp.php?acao=allTeams">Todas Equipes</a><br />';
		if (checkPermission(71)) $data['itens_pessoas'] .= '<a href="sgp.php?acao=ferias">Registro de F&eacute;rias/Licen&ccedil;a</a><br />';

		$data['ajuda'] = '<a href="#ajuda" onclick="mostrarAjuda(\''.substr($areaatual,6,6).'\')"><span id="labAj" class="menuHeader">Precisa de Ajuda?</span></a>';
		
		//gera menu de empresas
		$data['itens_empresas'] = '';
		if(checkPermission(94)) $data['itens_empresas'] .= '<a href="empresa.php?acao=cadEmpresaBig" >Cadastrar Empresa</a><br />';
		//if(checkPermission(96)) $data['itens_empresas'] .= '<a href="empresa.php?acao=cadFuncBig" >Cadastrar Funcion&aacute;rio de Empresa</a><br />';
		if(checkPermission(100)) $data['itens_empresas'] .= '<a href="empresa.php?acao=buscaEmpresa" >Buscar/Gerenciar Empresa</a>';
		
		
		$data["script"] = '<script type="text/javascript">showMenu('.$area.');</script>';
		
		//$data['chat'] = geraListaChat();
		
		//coloca os itens na posicao marcada no template
		$html = str_replace('{$itens_empresas}', $data['itens_empresas'], $html);
		$html = str_replace('{$ajuda}', $data['ajuda'], $html);
		$html = str_replace('{$itens_obra}', $data['itens_obra'], $html);
		$html = str_replace('{$itens_doc}', $data['itens_doc'], $html);
		$html = str_replace('{$itens_pessoas}', $data['itens_pessoas'], $html);
		//$html = str_replace('{$chat}', $data['chat'], $html);
		$html = str_replace('{$script}', $data['script'], $html);
		
		return $html;
	}
	
	/**
	 * Monta o caminho de 'diretorios'
	 * @param array $dir
	 * @param string $tipo
	 */
	function showNavBar($dir,$tipo='normal'){
		//inicio
		if($tipo == 'normal')
			$bar = '<a href="index.php">In&iacute;cio</a>';//se for janela normal, cria link
		elseif ($tipo == 'mini') {
			$bar = 'In&iacute;cio';//se for mini, apenas mostra o texto
		}
		//1o nivel
		switch ($_SERVER["PHP_SELF"]){//le qual o nome do arquivo sendo lido e decide qual secao o usuario esta
			case "/sgd.php" : $bar .= " :: Ger&ecirc;ncia de Documentos";
							  break;
			case "/sgo.php" : $bar .= " :: Ger&ecirc;ncia de Obras";
							  break;
			case "/os.php" : $bar .= " :: Ordem de Servi&ccedil;o Inform&aacute;tica";
							  break;
		}
		//2o nivel em diante le o array de dados
		//para cada posicao do array (name, url), cria um link para aquela secao
		foreach ($dir as $d){
			if ($d['url'] != ''){
				$bar .= ' :: <a href="'.$d['url'].'">'.$d['name'].'</a>';
			}else{
				$bar .= ' :: '.$d['name'];//se nao houver link, mostra apenas o texto
			}
		}
		return $bar;
	}
	
	/**
	 * Monta o codigo da tela
	 */
	function showCodTela($arqNome = '') {
		$numTela = ' Tela ';
		//le o nome do arquivo atual para decicir qual secao esta sendo visitada
		
		if($arqNome == '') $arqNome = $_SERVER['REQUEST_URI'];
		
		if(strpos($arqNome,"/login.php") !== false)
			$cod[0] = 'LOG';
		elseif(strpos($arqNome, "/index.php") !== false)
			$cod[0] = 'IND';
		elseif(strpos($arqNome, "/sgd.php") !== false)
			$cod[0] = 'SGD';
		elseif(strpos($arqNome, "/empresa.php") !== false)
			$cod[0] = 'EMP';
		elseif(strpos($arqNome, "/adm.php") !== false)
			$cod[0] = 'ADM';
		elseif(strpos($arqNome, "/sgo.php") !== false)
			$cod[0] = 'SGO';
		elseif(strpos($arqNome, "/os.php") !== false)
			$cod[0] = 'OSI';
		elseif(strpos($arqNome, "/report_bug.php") !== false)
			$cod[0] = 'BUG';
		elseif(strpos($arqNome, "/ajuda.php") !== false)
			$cod[0] = 'AJU';
		elseif(strpos($arqNome, "/sgp.php") !== false)
			$cod[0] = 'PSS';
		else
			$cod[0] = '000';
		
		
		//le as variaveis passada por URL para definir a subsecao a ser exibida
	
		$vars = explode("?",$arqNome);//separa URL dos parametros
		$vars = explode("&",$vars[count($vars)-1]);//separa cada parametro em uma posicao do array
		foreach ($vars as $v) {
			if(strpos($v,"=") === false) continue; //1a posicao nao eh variavel se nao houver variavel
			$v = explode("=",$v);//separa valor da chave
			$varsGET[$v[0]] = $v[1];//coloca valor na chave do array
		}
		
		if ($cod[0] == 'SGD') {//sgd.php
			if (isset($varsGET['acao']) && isset($varsGET['docID']) && $varsGET['acao'] == 'ver'){
				$cod[1] = 'VD';
				while(strlen($varsGET['docID']) < 5) $varsGET['docID'] = '0'.$varsGET['docID'];
				$cod[2] = strtoupper($varsGET['docID']);
			} elseif (isset($varsGET['acao']) && isset($varsGET['tipoDoc']) && $varsGET['acao'] == 'cad'){
				$cod[1] = 'CD';
				while(strlen($varsGET['tipoDoc']) < 5) $varsGET['tipoDoc'] = '0'.$varsGET['tipoDoc'];
				$cod[2] = strtoupper($varsGET['tipoDoc']);
			} elseif (isset($varsGET['acao']) && isset($varsGET['tipoDoc']) && $varsGET['acao'] == 'novo'){
				$cod[1] = 'NV';
				while(strlen($varsGET['tipoDoc']) < 5) $varsGET['tipoDoc'] = '0'.$varsGET['tipoDoc'];
				$cod[2] = strtoupper($varsGET['tipoDoc']);
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'salvar'){
				$cod[1] = 'SV';
				$cod[2] = '00000';			
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'busca_mini'){
				$cod[1] = 'BM';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'buscar'){
				$cod[1] = 'BU';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'anexar'){
				$cod[1] = 'AA';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'despachar'){
				$cod[1] = 'DP';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'novoDocVar'){
				$cod[1] = 'ND';
				$cod[2] = '00000';
			}
		} elseif ($cod[0] == 'EMP') {//empresa.php
			if (isset($varsGET['acao']) && $varsGET['acao'] == 'buscar'){
				$cod[1] = 'BU';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'cad'){
				$cod[1] = 'CD';
				$cod[2] = '00000';
			}
		} elseif ($cod[0] == 'ADM') {//adm.php
			$cod[1] = '??.?????';
		} elseif ($cod[0] == 'SGO') {//sgo.php
			$cod[1] = '??.?????';
		} elseif ($cod[0] == 'OSI') {//os.php
			$cod[1] = '??.?????';
		} elseif ($cod[0] == 'BUG') {//os.php
			if (isset($varsGET['acao']) && $varsGET['acao'] == 'enviar'){
				$cod[1] = 'EN';
				$cod[2] = '00000';
			} elseif (isset($varsGET['acao']) && $varsGET['acao'] == 'ver'){
				$cod[1] = 'VR';
				$cod[2] = '00000';
			} else {
				$cod[1] = 'CD';
				$cod[2] = '00000';
			}
		} else {
			$cod[1] = '00.00000';
			return $numTela.implode('.', $cod) . ' <a href="report_bug.php">Relatar erro</a>';
		}
		
		return $numTela.implode('.', $cod) . ' <a href="report_bug.php">Relatar erro</a>';
	}
	
	/**
	 * verifica se o usuario esta logado para mostrar uma determinada pagina
	 * @param int $id
	 */
	function checkLogin($id){
		//le qual pagina esta sendo acessada
		$varsGET = $_SERVER['PHP_SELF']."?";
		//le as variais passadas pela URL (_GET) e monta a URL completa
		foreach ($_GET as $key => $value) {
			$varsGET .= $key."=".$value."&";
		}
		//se nao estiver logado, volta para tela de login com as variaveis corretas
		if (!isset($_SESSION['username']) || (isset($_SESSION['username']) && $_SESSION['username'] == "") ||
			!isset($_SESSION['id']) || (isset($_SESSION['id']) && $_SESSION['id'] == "") ||
			!isset($_SESSION['grupo']) || (isset($_SESSION['grupo']) && $_SESSION['grupo'] == "") ||
			!isset($_SESSION['ativo']) || (isset($_SESSION['ativo']) && $_SESSION['ativo'] == "") ||
			!isset($_SESSION['perm'])) {
			showError(-1,"login.php?redir=".urlencode($varsGET));
		}
		if(!isset($_SESSION['logado']) || (isset($_SESSION['logado']) && !$_SESSION['logado'])) {
			showError(-1,"login.php?redir=".urlencode($varsGET));
		}
	}
	
	/**
	 * Verifica se o usuario tem permissao para realizar tal acao. Retorna para home com alert se nao tiver
	 * @param int $permID ID da acao
	 * @return bool $auth (true se ha permissao, false caso contrario)
	 */
	function checkPermission($permID) {
		checkLogin(0);
		//le no vetor de permissoes se o usuario logado tem permissao para realizar a acao
		//print($_SESSION['perm'][$permID]);exit();
		if(isset($_SESSION['perm'][$permID]) && $_SESSION['perm'][$permID] > 0) {
			return true;
		} elseif(!isset($_SESSION['perm'][$permID])) {
			$pessoa = new Pessoa();
			if (!isset($_SESSION['grupo']))
				checkLogin(0);
			$perm = $pessoa->getPermission($_SESSION['grupo']);
			if($perm && isset($perm[$permID])){
				$_SESSION['perm'] = $perm;
				return $perm[$permID];
			}
			global $bd;
			
			foreach ($bd->query("SELECT username FROM usuarios WHERE gid=2") as $user) {
				$bd->query("INSERT INTO chat (`from`,`to`,`message`,`sent`,`recd`) VALUES ('".$_SESSION['username']."', '".$user['username']."', 'ERRO ao ler a permissao {$permID} no BD', NOW(), 0)");
			}
			
			return false;
		} else {
			return false;
		}
	}
		
	
	/**
	 * Funcao que recebe o nome do campo e um array de valores e 
	 * retorna o nome do campo, label, codigo HTML para cadastro ou busca e o valor
	 * @param string $c
	 * @param mysql link $bd
	 * @param string $tipo
	 * @param array $valor 
	 * @return array $campo com [cod], [nome], [label], [valor]
	 */
	function montaCampo($c,$tipo = 'cad',$valor = null,$busca = false){
		global $conf;
		global $bd;
		//le os dados do campo
		$cp = getCampo($c);
		
		if(isset($cp[0]))
			$cp = $cp[0];//seleciona o primeiro campo retornado
		else
			return null;//retorna null se nao achar o campo
		
		//os campos de busca (no cadastro, os campos chave sao reproduzidos no form de cadastro com
		//um '_' na frente)
		if($busca){
			$c = '_'.$c;
		}
		
		$campo['nome']  = $cp['nome']; //nome eh o proprio nome passado por parametro (e o mesmo no BD)
		$campo['label'] = $cp['label']; //o label eh aquele lido do BD sem tratamento algum
		$campo['tipo'] = $cp['tipo'];
		$campo['cod']   = '';
		$campo['valor'] = '';
		$campo['parte'] = false;
		$campo['verAcao'] = $cp['verAcao'];
		$campo['editarAcao'] = $cp['editarAcao'];
		$campo['extra'] = '';
		
		if ($cp['tipo'] == 'input') {
			$campo['extra'] = $cp['extra'];
			// se campo text, valor eh o que foi digitado sem tratamento (valor do indice dado)
			if(isset($valor[$c])) $campo['valor'] = $valor[$c];
			
			//se for input com autocompletar de unidades, gera o HTML + javascript correspondente
			if (strpos($cp['extra'],"unOrg_autocompletar") !== false){
				if($tipo == 'edt')
					$campo['cod'] = '<input autocomplete="off" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.$campo['valor'].'" style="min-width:75%" />';
				else 
					$campo['cod'] = '<input autocomplete="off" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' style="min-width:450px"  />';
				$campo['cod'] .= '<script type="text/javascript">
									$(document).ready(function(){
										$("#'.$cp['nome'].'").autocomplete({
											source: function(request, response) { 
												$.get("unSearch.php", {
													q: request.term,
													show: "un"
												}, function(data) {
													//data = eval(data);
													try {
														data = eval(data);
													} catch(e) {
														if (e instanceof SyntaxError) {
															alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: "+ e.message);
														}
													}
													
													response(data);
												});
											},
											minLength: 2,
											autoFocus: true,
											select: function(){
												$("#'.$cp['nome'].'").focus();
											}
										});
									});
									
									
									
									$("#'.$cp['nome'].'").keyup(function(){
		
										v = $("#'.$cp['nome'].'").val();
										
										v = v.replace(/\./g,""); 
										
										var expReg  = /^[0-9]{2,12}$/i;
										
										if (expReg.test(v)){
											var i, vn="";
											for(i=0 ; i<v.length ; i++){
												if(i%2 == 0 && i != 0)
													vn += ".";
												vn += v[i];
											}				
											$("#'.$cp['nome'].'").val(vn);
										}
									});
								</script>';
			//se for campo input com ano autal, cria input com pre-valor caso cadastro e em branco caso busca
			} elseif (strpos($cp['extra'],"current_year") !== false){
				if($tipo == 'cad') $campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.date("Y").'" />';
				if($tipo == 'bus') $campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' />';
				if($tipo == 'edt') $campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.$campo['valor'].' />';
			} elseif (strpos($cp['extra'], "docResposta") !== false) {
				$campo['cod'] = '<input type="hidden" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' />{$docResposta}';
			} elseif (strpos($cp['extra'], "moeda") !== false && $tipo == 'edt') {
				$campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.number_format($campo['valor'], 2, ',', '.').'"/>';
			} elseif (stripos($cp['extra'], "moeda") !== false && $tipo == 'bus') {
				//var_dump("teste");
				//var_dump($valor[$cp['nome'].'_operador']);
				if (isset($valor[$cp['nome'].'_operador'])) {
					//$campo['operador'] = $valor[$cp['nome'].'_operador'];
					switch ($valor[$cp['nome']."_operador"]) {
						case 'eq':
							$campo['operador'] = "=";
							break;
						case 'lt':
							$campo['operador'] = "<";
							break;
						case 'leq':
							$campo['operador'] = "<=";
							break;
						case 'gt':
							$campo['operador'] = ">";
							break;
						case 'geq':
							$campo['operador'] = ">=";
							break;
						default:
							$campo['operador'] = "=";
							break;
					}
				}
				
				$campo['cod']  = '<select name="'.$cp['nome'].'_operador" id="'.$cp['nome'].'_operador">';
				$campo['cod'] .= '<option value="eq">=</option>';
				$campo['cod'] .= '<option value="lt">Menor que</option>';
				$campo['cod'] .= '<option value="leq">Menor ou igual que</option>';
				$campo['cod'] .= '<option value="gt">Maior que</option>';
				$campo['cod'] .= '<option value="geq">Maior ou igual que</option>';
				$campo['cod'] .= '</select>';
				
				$campo['cod'] .= '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' />';
			} else {
				//se for input simples, monta a tag
				if($tipo == 'edt')
					$campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.$campo['valor'].'"/>';
				else
					$campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' />';
			}
		} elseif ($cp['tipo'] == 'select') {
			//monta a estrutura basica de campo select (com 1a opcao --selecione--)
			$campo['cod'] = '<select name="'.$cp['nome'].'" id="'.$cp['nome'].'">';
			if($tipo != 'edt') $campo['cod'] .= '<option selected value="nenhum"> -- Selecione -- </option>';
			//separa todas as opcoes da selecao
			$attr = explode(",",$cp['attr']);
			//para cada selecao, monta o HTML correspondente
			foreach ($attr as $c) {
				$c = explode("=", $c);
				$c[0] = trim($c[0]);
				//se for separador, cria a opcao desabilitada
				if(strpos($c[0], '_separador_') !== false)
					$campo['cod'] .= '<option value="" disabled="" style="background-color: #404040; color:white">-&gt; '.$c[1].'</option>';
				else
					if($tipo == 'edt' && $valor[$campo['nome']] == $c[0]) { 
						//se for edicao, o valor do campo deve estar pre-selecionado
						$campo['cod'] .= '<option value="'.$c[0].'" selected="selected">'.$c[1].'</option>';
					} else {
						//senao cria campo normal
						$campo['cod'] .= '<option value="'.$c[0].'">'.$c[1].'</option>';
					}
				//valor eh o 'value' da opcao selecionada
				if (isset($valor[$campo['nome']]) && $c[0] == $valor[$campo['nome']])
					$campo['valor'] = $c[1];
			}
			$campo['cod'] .= '</select>';
						
		} elseif ($cp['tipo'] == 'yesno') {
			if($tipo == 'edt') {
				if($valor[$c] == 1)	$campo['cod'] = '<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" checked="checked" /> Sim&nbsp;&nbsp;<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="0" /> N&atilde;o';
				elseif($valor[$c] == 0) $campo['cod'] = '<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" /> Sim&nbsp;&nbsp;<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="0" checked="checked" /> N&atilde;o';
				else $campo['valor'] = $campo['cod'] = '<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" /> Sim&nbsp;&nbsp;<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="0" /> N&atilde;o';
			} elseif ($tipo == 'cad') {
				//se for tipo yes/no monta os 2 campos			
				$campo['cod'] = '<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" /> Sim&nbsp;&nbsp;<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="0" checked="checked" /> N&atilde;o';
			}
			else {
				$campo['cod'] = '<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" /> Sim&nbsp;&nbsp;<input type="radio" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="0" /> N&atilde;o';
			}
			//se o valor for 1, retorna sim, se 0, retorna nao, senao, nao informado
			if(isset($valor[$c])){
				if($valor[$c] == 1)	$campo['valor'] = 'sim';
				elseif($valor[$c] == 0) $campo['valor'] = 'n&atilde;o';
				else $campo['valor'] = 'n&atilde;o informado';
			}
			
		} elseif ($cp['tipo'] == 'checkbox') {
			//se for checkbox, monta o campo
			if($tipo == 'edt' && $valor[$c] == 1)
				$campo['cod'] = '<input type="checkbox" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" checked="checked" />';
			else 
				$campo['cod'] = '<input type="checkbox" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="1" />';
			if(isset($valor[$c])){
				//se valor for 1, retorna sim, se for 0, retorna nao, senao, retorna nao informado
				if($valor[$c] == 1)	$campo['valor'] = 'sim';
				elseif($valor[$c] == 0) $campo['valor'] = 'n&atilde;o';
				else $campo['valor'] = 'n&atilde;o informado';
			}
			
		} elseif ($cp['tipo'] == 'autoincrement') {
			//monta campo de autoincrement
			if($tipo == "cad") $campo['cod'] = '<input type="hidden" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="" />(Ser&aacute; gerado automaticamente.)';
			if($tipo == "bus") $campo['cod'] = '<input type="text" size="10" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="" />';
			if($tipo == "edt") $campo['cod'] = '';
			if(isset($valor[$c])) $campo['valor'] = $valor[$c];
			
		} elseif ($cp['tipo'] == 'textarea') {
			if ($cp['nome'] == "conteudo") {
				//var_dump(mb_detect_encoding($valor[$c], "utf-8,ascii,iso-8859-1"));
				if (strcasecmp(mb_detect_encoding($valor[$c], "utf-8,ascii,iso-8859-1"), "iso-8859-1") === 0) {
					$valor[$c] = mb_convert_encoding($valor[$c], "utf-8", "iso-8859-1");
				}
			}
			
			
			//monta o campo de texto
			if($tipo == "cad") $campo['cod'] = '<textarea name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].'></textarea>';
			if($tipo == "edt") $campo['cod'] = '<textarea name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].'>'.$valor[$c].'</textarea>';
			if($tipo == "bus") $campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" size="35" />';
			
			
			if(isset($valor[$c])) $campo['valor'] = SGDecode($valor[$c], ENT_QUOTES);
			
			// visualizar
			if ($cp['nome'] == "conteudo") {
				$script = '<script type="text/javascript">CKEDITOR.on("instanceReady", function(ck) { ck.editor.removeMenuItem("paste");	});</script>';
				
				if ($tipo == "cad") $campo['cod'] .= '<br /><center><a onclick="javascript:visualizarDoc(\'cad\')" class="link_preview prev_cad">Visualizar</a></center>'.$script;
				if ($tipo == "edt") $campo['cod'] .= '<br /><center><a onclick="javascript:visualizarDoc(\'edit\')" class="link_preview prev_edit">Visualizar</a></center>'.$script;
			}
			
		} elseif ($cp['tipo'] == 'userID') {
			//monta campo de usuario
			if (strpos($cp['extra'], 'current_user') !== false){
				//se for campo de usuario atual, apenas mostra o nome do usuario e cria campo oculto com o ID do usuario atual no cad e mostra input para busca por nome
				if($tipo == "cad") {
					//if ($_SESSION['area'] != "Administra&ccedil;&atilde;o") 
					if ($_SESSION['area'] != "Apoio" && $_SESSION['area'] != "Administra&ccedil;&atilde;o") {
						$campo['cod'] = '<input type="hidden" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="'.$_SESSION['id'].'" />'.$_SESSION['nomeCompl'];
					}
					else {
						$campo['cod'] = '<script type="text/javascript" src="scripts/emitente.js?r={$randNum}"></script>';
						$campo['cod'] .= '<input type="hidden" name="'.$cp['nome'].'" id="'.$cp['nome'].'" value="'.$_SESSION['id'].'" />';
						$campo['cod'] .= '<input type="hidden" name="emitente_campo" id="emitente_campo" value="'.$cp['nome'].'" />';
						$campo['cod'] .= '<select id="emitente_deptos" name="emitente_deptos">';
						$campo['cod'] .= '<option value="'.$_SESSION['id'].'" id="emitente_user" name="emitente_user" selected>'.$_SESSION['nomeCompl'].'</option>';
						$campo['cod'] .= '<option disabled style="background-color: #808080; color:white"> --> Departamentos </option>';
						$deptos = getDeptos();
						foreach ($deptos as $dep) {
							$campo['cod'] .= '<option id="'.$dep.'" value="'.$dep.'">'.$dep.'</option>';
						}
						$campo['cod'] .= '</select><br />';
						$campo['cod'] .= '<select id="emitente_pessoa" name="emitente_pessoa"></select>';
					}
				}
				if($tipo == "bus") {
					$campo['cod'] = '<input type="text" size="20" name="'.$cp['nome'].'" id="'.$cp['nome'].'" />';
					if(isset($valor[$c])) $campo['valor'] = $valor[$c];
				}
				if($tipo == "edt") $campo['cod'] = '';
			}
			if(strpos($cp['extra'], 'select') !== false){
				//campo de selecao de usuarios
				if (stripos($cp['extra'], 'allUsers') !== false) {
					$campo['cod'] = '<select name="'.$cp['nome'].'" id="'.$cp['nome'].'"><option selected="selected" value="0"> -- Selecione -- </option>';
					$attr = getAllUsersName();
					foreach ($attr as $c) {
						$campo['cod'] .= '<option value="'.$c['id'].'">'.$c['nomeCompl'].'</option>';
					}
				}
				else {
					$campo['cod'] = '<select name="'.$cp['nome'].'" id="'.$cp['nome'].'"><option selected value=""> -- Selecione -- </option>';
					$attr = explode(",",$cp['attr']);
					foreach ($attr as $c) {
						$c = explode("=", $c);
						$c[0] = trim($c[0],"\n\\\/<>");
						if($c[0] == '_separador_')
							//se o campo for separador, coloca opcao desabilitada
							$campo['cod'] .= '<option value="" disabled="" style="background-color: #404040; color:white">-&gt; '.$c[1].'</option>';
						else 
							$campo['cod'] .= '<option value="'.$c[0].'">'.$c[1].'</option>';
						if ($c[0] == $valor[$campo['nome']])
							$campo['valor'] = $c[1];//se select, valor eh o 'value' da opcao
					}
				}
				
				$campo['cod'] .= '</select>'; 
			}
			//tendo o ID do usuario, procura no BD o nome do usuario, que eh o valor do campo
			if (isset($valor[$campo['nome']]) && $valor[$campo['nome']] > 0){
				$res = getNamesFromUsers($valor[$campo['nome']]);

				if (count($res))
					$campo['valor'] = $res[0]['nomeCompl'];
				else
					$campo['valor'] = 'Usu&aacute;rio desconhecido.';
			}
			
		} elseif ($cp['tipo'] == 'documentos') {
			if ($tipo != 'bus') {
				//campos de documetos. cria div que mostrara os nomes de documentos e um campo oculto que guardas os IDs a serem colocados no campo do BD 
				$campo['cod'] = '<div id="'.$cp['nome'].'Nomes" class="cadDisp"></div><input type="hidden" name="'.$cp['nome'].'" id="'.$cp['nome'].'" />
					<a id="addDocLink" onclick="window.open(\'sgd.php?acao=busca_mini&amp;onclick=adicionarCampo&amp;target='.$cp['nome'].'\',\'addDoc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">Adicionar Documento </a>';
				if($tipo == "edt") $campo['cod'] = '';
				//retorna nome do documento
				$campo['valor'] = '';
				foreach(explode(",",$valor[$c]) as $id) {
					if($id) {
						$docAnex = new Documento($id);
						$docAnex->loadDados();
						$campo['valor'] .= showDocAnexo(array(array("id" => $docAnex->id, "nome" => $docAnex->dadosTipo['nome']." ".$docAnex->numeroComp)));
					}
				}
			}
			else {
				$campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" type="text" size="20" />';
				$campo['valor'] = '';
			}
			
		} elseif ($cp['tipo'] == 'composto') {
			//tipo composto de varios outros campos
			//algoritmo: le as partes, procura, recursivamente, o codigo de cada parte
			$partes = explode("+",$cp['attr']);
			$campo['cod'] = '<input type="hidden" name="'.$c.'" id="'.$c.'" value= "'.$cp['nome'].'" />';
			if((!isset($valor[$cp['nome']]) && $tipo != 'edt') || $valor[$cp['nome']] == $cp['nome'] ){
				//para cada parte, obtem o codigo da parte e concatena com os dados ja obtidos 
				foreach ($partes as $p) {
					$dados = montaCampo($p,$tipo,$valor,$busca);//busca nome, cod e valor da parte
					if($dados != null){//se a parte for campo
						$campo['nome'] .= ','.$dados['nome'];//concatena o nome da parte
						$campo['cod'] .= $dados['cod'];//concatena o codigo da parte
						$campo['valor'] .= $dados['valor'];//concatena o valor da parte
						/*if ($tipo == 'bus' && $dados['valor'] == "") {
							$campo['valor'] = "";
							break;
						}*/
					}else{//se a parte nao for campo (separador, por ex)
						$campo['cod'] .= str_replace('"','',$p);//concatena o codigo com a parte sem aspas
						$campo['valor'] .= str_replace('"','',$p);//concatena o valor com a parte sem aspas
					}
				}
			} else {
				$campo['valor'] .= $valor[$cp['nome']];
			}
			if (stripos($cp['extra'], 'mini_busca') !== false && $tipo != 'bus') {
				$campo['cod'] .= ' <a onclick="referenciarDoc(\''.$c.'\')">Buscar</a>';
				$campo['cod'] .= '<script type="text/javascript" src="scripts/referenciar.js?r={$randNum}"></script>';
			}
			if($tipo == "edt") $campo['cod'] = '<input type="text" size="40" name="'.$campo['nome'].'" id="'.$campo['nome'].'" value="'.$campo['valor'].'" />';

		} elseif($cp['tipo'] == 'anoSelect') {//tipo de anoSelect
			$anoAtual = date("Y");//determina o ano atual
			//se for busca, deicxa ano em branco por padrao (caso nao queira determinar o ano na busca)
			
			if($tipo == 'bus') {
				$campo['cod'] = '<input type="text" id="'.$campo['nome'].'" name="'.$campo['nome'].'" value="" size="3" />';
			}elseif ($tipo == 'edt'){
				$campo['cod'] = '<input type="text" id="'.$campo['nome'].'" name="'.$campo['nome'].'" value="'.$valor[$c].'" size="3" />';
			} else { 
				if(strpos($cp['extra'], "onlyCurrentYear") !== false) {
					$campo['cod'] = '<input type="hidden" id="'.$campo['nome'].'" name="'.$campo['nome'].'" value="'.$anoAtual.'" /> '.$anoAtual;
				} else {
					$campo['cod'] = '<input type="hidden" id="'.$campo['nome'].'" name="'.$campo['nome'].'" value="'.$anoAtual.'" /> ';
					$campo['cod'] .= '<input type="text" id="'.$campo['nome'].'2" name="'.$campo['nome'].'2" size="3" maxlength="4" />'.//cria o campo de texto para digitar manualmente caso o ano seja inferior ao apresentados na selecao
								'<select name="'.$campo['nome'].'1" id="'.$campo['nome'].'1">';
					//cria opcao para nao determinar o ano de procura
					$campo['cod'] .= '<option selected="selected" name="'.$anoAtual.'" value="'.$anoAtual.'">'.$anoAtual.'</option>';
					//completa a selecao com os ultimos 5 anos
					for ($i = 1; $i < 6; $i++) {
						$campo['cod'] .= '<option name="'.($anoAtual-$i).'" value="'.($anoAtual-$i).'">'.($anoAtual-$i).'</option>';
					}
					//cria a opcao 'outros' e o codigo JS para mudar lidar com os campos
					$campo['cod'] .= '<option id="'.$campo['nome'].'outroAno" name="'.$campo['nome'].'outroAno">Outro</option>
									</select>
									<script type="text/javascript">
										//campo 2 comeca oculto
										$("#'.$campo['nome'].'2").hide();
										//quando mudados a opcao selecionada
										$("#'.$campo['nome'].'1").change(function(){
											//copia a opcao selecionada para o campo oculto principal
											$("#'.$campo['nome'].'").val($("#'.$campo['nome'].'1").val());
											if($("#'.$campo['nome'].'outroAno").attr("selected")){
												//se selecionarmos outro, limpa o valor do campo principal
												$("#'.$campo['nome'].'").val("");
												//esconde o campo de selecao
												$("#'.$campo['nome'].'1").hide();
												//mostra o campo de texto pra digitacao
												$("#'.$campo['nome'].'2").show();
												//coloca o campo de texto no foco
												$("#'.$campo['nome'].'2").focus();
											}
										});
										
										//para cada caracter digitado, copia o novo valor do campo2 para o campo principal
										$("#'.$campo['nome'].'2").keyup(function(){
											$("#'.$campo['nome'].'").val($("#'.$campo['nome'].'2").val());
										});
									</script>';
					}
				}
			//valor do ano nao sofre tratamento
			if(isset($valor[$c])) $campo['valor'] = $valor[$c];
			else $campo['valor'] = date("Y");
		}
		elseif ($cp['tipo'] == 'data') {
			$campo['extra'] = $cp['extra'];
			if(isset($valor[$c]) && $valor[$c] > 0) $campo['valor'] = $valor[$c];
			else $campo['valor'] = 0;
			
			if($tipo == 'edt') {
				$data = '';
				if ($campo['valor'] > 0) {
					$data = date("d/m/Y", $campo['valor']);
				}
				$campo['cod'] = '<input type="text" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' value="'.$data.'" maxlength="10" />';
			}
			else {
				$campo['cod'] = '<input type="text" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' maxlength="10" />';
			}
			
			if (stripos($cp['extra'], "noDatePicker") === false) {
				$campo['cod'] .= '
				<script type="text/javascript">
					$(document).ready(function() {
						$("#'.$cp['nome'].'").datepicker({
							dateFormat: "dd/mm/yy",
							regional: "pt-BR",
							showOtherMonths: true,
							constrainInput: true,
							selectOtherMonths: true,
							constrainInput: true,
							appendText: "(dd/mm/aaaa)",
							onSelect: function() {
								trataData($(this));
								$(this).focus();
							}
						});
						
						$("#'.$cp['nome'].'").bind("keydown", function(e) {
							if (e.keyCode == 8 || e.keyCode == 46)
								return;
							
							var texto = $(this).val();
							if (texto.length == 2)
								$(this).val(texto + "/");
							else if (texto.length == 5)
								$(this).val(texto + "/");
						});
					});
				</script>';
			}
		}
		elseif ($cp['tipo'] == 'empresa') {
			if (isset($valor[$c]) && $valor[$c] > 0) {
				$empresa = new Empresa($bd);
				$empresa->load($valor[$c]);
				
				//$campo['valor'] = $valor[$c];
				$campo['valor'] = $empresa->get('nome');
			}
			else {
				$campo['valor'] = 'Nenhuma empresa selecionada.';
			}
			
			if ($tipo == 'cad') {
				$campo['cod'] = geraSelect($cp['nome'], Empresa::getEmpresas($bd), null, 0, 'wrapSelect');
				if (checkPermission(94)) {
					$campo['cod'] .= ' <a class="novaEmpresa">[Cadastrar Empresa]</a>';
				}
			}
			if ($tipo == 'edt') {
				$campo['cod'] = geraSelect($cp['nome'], Empresa::getEmpresas($bd), $valor[$c], 0);
			}
			if ($tipo == 'bus') {
				$campo['cod'] = '<input type="text" name="'.$cp['nome'].'" id="'.$cp['nome'].'" '.$cp['attr'].' />';
			}
			
			if ($tipo != 'bus') {
				$campo['cod'] .= ' '; //<a onclick="showEmpresaInterface()">[selecionar funcion&aacute;rios]</a>';
			}
		} elseif($cp['tipo'] == 'cont_rep') {
			$campo['valor'] = null;
			
			if($tipo != 'cad')
				$campo['cod'] = null;	
			else {
				$tiposDoc = array(
					'1' => array('nome' => 'ART'),
					'2' => array('nome' => 'As built'),
					'3' => array('nome' => 'Ata de reuni&atilde;o'),
					'4' => array('nome' => 'CD'),
					'5' => array('nome' => 'Certid&atilde;o CREA'),
					'6' => array('nome' => 'Composi&ccedil;&atilde;o de Pre&ccedil;os Unit&aacute;rios (CPU)'),
					'7' => array('nome' => 'Convoca&ccedil;&atilde;o de empresa / unidade'),
					'8' => array('nome' => 'Credenciamento de Colabor(es)'),
					'9' => array('nome' => 'Cronograma f&iacute;sico / f&iacute;sico-financeiro'),
					'10' => array('nome' => 'Croqui'),
					'11' => array('nome' => 'Curva ABC'),
					'12' => array('nome' => 'Di&aacute;rio de obra'),
					'13' => array('nome' => 'Email'),
					'14' => array('nome' => 'Garantia'),
					'15' => array('nome' => 'Manual'),
					'16' => array('nome' => 'Medi&ccedil;&atilde;o'),
					'17' => array('nome' => 'Mem&oacute;ria de c&aacute;lculo'),
					'18' => array('nome' => 'Memorial descritivo'),
					'19' => array('nome' => 'Nota fiscal'),
					'20' => array('nome' => 'Notifica&ccedil;&atilde;o'),
					'21' => array('nome' => 'Parecer t&eacute;cnico'),
					'22' => array('nome' => 'Projeto'),
					'23' => array('nome' => 'Proposta / Planilha / Or&ccedil;amento'),
					'24' => array('nome' => 'Protocolo PPCI'),
					'25' => array('nome' => 'Relat&oacute;rio de acompanhamento'),
					'26' => array('nome' => 'Relat&oacute;rio de atividades'),
					'27' => array('nome' => 'Relat&oacute;rio de intercorr&ecirc;ncia'),
					'28' => array('nome' => 'Relat&oacute;rio de pend&ecirc;ncias'),
					'29' => array('nome' => 'Relat&oacute;rio fotogr&aacute;fico'),
					'30' => array('nome' => 'Solicita&ccedil;&atilde;o de aditivo/supress&atilde;o de <br>prazo, valor ou reequil&iacute;brio'),
					'31' => array('nome' => 'Solicita&ccedil;&atilde;o de Atestado de Capacidade T&eacute;cnica'),
					'32' => array('nome' => 'Solicita&ccedil;&atilde;o de retirada de material'),
					'33' => array('nome' => 'Solicita&ccedil;&atilde;o de trabalho'),
					'34' => array('nome' => 'Subcontrato'),
					'35' => array('nome' => 'Termo de recebimento Provis&oacute;rio ou Definitivo'),
					'36' => array('nome' => 'Outros')
				);
				
				$campo['cod'] = '
				</td></tr></table>
				<table width=100%>
					<tr class="c">
						<td colspan="7"></td>
					</tr>
					<tr class="c">
						<td class="c"><b>n&deg;</b></td>
						<td class="c"></td>
						<td class="c"><b>Tipo de Documento Gen&eacute;rico</b></td>
						<td class="c"><b>N&uacute;mero do doc.</b></td>
						<td class="c"><b>Ano</b></td>
						<td class="c"><b>Assunto</b></td>
						<td class="c"><b>Anexos/Outros</b></td>
					</tr>
					{$tr_documentos}
				
				<script type="text/javascript" src="scripts/rep.js?r={$randNum}"></script>
				<div id="cadRepError"></div>
				';
				
				$tr_documentos = '';
				foreach ($tiposDoc as $id => $tipo) {
					$tr_documentos .= '<tr class="c doc_tr" id="doc'.$id.'_tr">
						<td class="c">'.$id.'.</td>
						<td class="c" style="align:center;"><input type="checkbox" id="doc'.$id.'_cb" class="tipoDoc" value="1" name="doc'.$id.'" /></td>
						<td class="c"><span id="doc'.$id.'_nome">'.$tipo['nome'].'</span></td>
						<td class="c"><input type="text" class="doc_numero" name="doc'.$id.'_numero"  id="doc'.$id.'_numero" size="10" style="display:none" /></td>
						<td class="c"><input type="text" class="doc_ano" name="doc'.$id.'_ano" id="doc'.$id.'_ano" size="4" maxlength="4" value="'.date('Y').'" style="display:none" /></td>
						<td class="c"><input type="text" class="doc_assunto" name="doc'.$id.'_assunto" id="doc'.$id.'_assunto" size="35" maxlength="140" style="display:none" /></td>
						<td class="c"><textarea class="doc_obs" name="doc'.$id.'_obs" id="doc'.$id.'_obs" cols="20" rows="4" style="display:none"></textarea></td>
					</tr>';
				}

				$campo['cod'] = str_ireplace('{$tr_documentos}', $tr_documentos, $campo['cod']);
			}
		} elseif ($cp['tipo'] == 'span') {
			if(isset($valor[$c])) $campo['valor'] = $valor[$c];
			
			if($tipo == "cad") $campo['cod'] = '<span id="'.$cp['nome'].'"></span>';
			if($tipo == "bus") $campo['cod'] = '';
			if($tipo == "edt") $campo['cod'] = '<input name="'.$cp['nome'].'" id="'.$cp['nome'].'" size="35" />';
		}
		else {
			//se for outra coisa, copia o codigo HTML em attr
			$campo['cod'] = $cp['attr'];
			//valor do campo indefinido nao sobre nenhum tratamento
			if(isset($valor[$c])) $campo['valor'] = $valor[$c];
		}
		//se for parte de um campo composto, marca com a flag
		if (strpos($cp['extra'],'parte') !== false) {
			$campo['parte'] = true;
		}
		//so for obrigatorio, marca com a classe obrigatorio
		if(strpos($cp['extra'], 'obrigatorio') !== false && $tipo == 'cad'){
			$max = 1;
			$campo['cod'] = preg_replace('/" /', '" class="obrigatorio" ', $campo['cod'], $max);
			$campo['cod'] .= '*';
		}
		
		return $campo;
	}
	
	/**
	 * Consulta todas as areas no BD
	 * @param Connection $bd
	 */
	function getDeptos(){
		//consulta todas as areas dos usuarios distintas
		$r = getAreasFromUsers();
		//coloca as areas em um array
		foreach ($r as $dep) {
			$deptos[] = $dep['area'];
		}
		//retorna o array
		return $deptos;
	}
	
	/**
	 * Gera codigo HTML para adicionar boxes de conteudo a pagina.
	 * Adicionalmente, gera JQuery para esconder os boxes cujo valor visible[c[i]]=false
	 * 
	 * (G)
	 * @param int $num
	 * @param array $visible
	 */
	function addContentBox($num,$visible) {
		if(count($visible) != $num+1)
			return null;
		
		$html = '';
		$js = '<script type="text/javascript">$(document).ready(function(){';
		for ($i = 0; $i < $num; $i++) {
			//cria o box de conteudo adicional
			$html .= '
			</div>
			<div id="c'.($i+2).'" class="boxCont">
			{$content'.($i+2).'}';
			
			if(!$visible[$i])//gera Jquery para esconder o campo se visible[i] eh falso
				$js .= '$("#c'.($i+1).'").hide();';
		}
		
		if(!$visible[$i])
				$js .= '$("#c'.($i+1).'").hide();';
		
		$js .= '}); </script>';
		return $html.$js;
	}
	
	/**
	 * Gera o PDF correspondente ao documento
	 * @param int $id id do documento a ser convertido.
	 * @param mysql link $bd conexao com o bd
	 */
	function geraPDF($id, $timestamp = false, $visualizar = false, Documento $view = null){
		if (!$visualizar) {
			$doc = new Documento($id);
			$doc->loadCampos();
		}
		else $doc = $view;
		
		require_once("classes/mpdf51/mpdf.php");
		//le os arquivos HTML para determinar o cabecalho, rodape e conteudo
		$header = file_get_contents("templates/doc_header.html");  
		$footer = file_get_contents("templates/doc_footer.html");
		$html = file_get_contents("templates/".$doc->dadosTipo['template']);

		//completa os campos de autor
		if ($timestamp) { // não é o primeiro pdf gerado para este documento
			$autor = $_SESSION;
		}
		else { // primeiro pdf, então o usuario pode escolher qual o emitente (se ele tiver permissao)
			if ($_SESSION['area'] != "Apoio" && $_SESSION['area'] != "Administra&ccedil;&atilde;o") {
				$autor = $_SESSION;
			}
			else {
				$autor = getUsers($doc->campos[$doc->dadosTipo['emitente']]);
				$autor = $autor[0];
				$autor['matricula'] = $autor['matr'];
			}
		}
		foreach ($autor as $ch => $dado) {
			$html = str_replace('{$Autor_'.$ch."}", $dado, $html);
		}
		
		if ($doc->dadosTipo['nomeAbrv'] == 'it') {
			$documento = '';
			$carimbo = '';
			
			
			if ($visualizar && isset($doc->campos['procIT']) && ($doc->campos['procIT'] != "0")) {
				$doc->anexado = 1;
				$doc->docPaiID = $doc->campos['procIT'];
			}
			
			if ($doc->anexado) {
				$docPai = new Documento($doc->docPaiID);
				$docPai->loadCampos();
				
				$documento = "ref. ".$docPai->dadosTipo['nome']." ".$docPai->numeroComp;
				
				$carimbo = "";
				if ($docPai->dadosTipo['nomeAbrv'] == "pr") {
					$carimbo = file_get_contents("templates/carimbo_proc.html");
					//if ($visualizar) $carimbo = mb_check_encoding($carimbo, 'UTF-8') ? $carimbo : utf8_encode($carimbo);
					$carimbo = str_replace('{$numeroComp}', $docPai->numeroComp, $carimbo);
				}
			}
			$html = str_replace('{$docNome}', $documento, $html);
			$html = str_replace('{$carimbo}', $carimbo, $html);
		}
		
		//tratamento de campos especiais (que nao apenas imprimir os dados do BD)
		foreach ($doc->campos as $ch => $dado) {
			$res = getCampo($ch);// print_r($doc->campos);
			$res = $res[0];
			
			// variaveis para 'quadrados x' de sap (em nome de unidade e em nome de pessoa)
			$img_un = "img/quadrado.jpg";
			$img_pessoa = "img/quadrado.jpg";
			
			if($res['tipo'] == 'userID'){
				//tratamento de usuario
				$resuser = getUsers($dado);
				foreach ($resuser[0] as $atr => $val) {
					//pra cada atributo (nome, sobrenome, matr, etc) coloca o valor correspondente
					$html = str_replace('{$'.$ch.'_'.$atr."}", $val, $html);
				}
			}
			elseif ($res['tipo'] == 'input' && $res['nome'] == "docResp") {
				$docResp = new Documento($dado);
				$docResp->loadCampos();
				$documento = $docResp->dadosTipo['nome']." ".$docResp->numeroComp;
				$html = str_replace('{$docNome}', $documento, $html);
				
				$carimbo = "";
				if ($docResp->dadosTipo['nomeAbrv'] == "pr") {
					$carimbo = file_get_contents("templates/carimbo_proc.html");
					//if ($visualizar) $carimbo = mb_check_encoding($carimbo, 'UTF-8') ? $carimbo : utf8_encode($carimbo);
					$carimbo = str_replace('{$numeroComp}', $docResp->numeroComp, $carimbo);
				}
				$html = str_replace('{$carimbo}', $carimbo, $html);
			}
			elseif($res['tipo'] == 'input' && strpos($res['extra'],"unOrg_autocompletar") !== false){
				//tratamento de unOrg
				//$unOrg['tudo'] = $dado;
				$un = explode("(", $dado);//corta o campo nos (
				$unOrg['sigla'] = rtrim($un[count($un)-1],")");//a sigla eh o que esta entre u ultimo ()
				$un = explode(' - ',$un[0],2);//separa no ' - ' 
				$unOrg['cod'] = $un[0];//o cogigo eh o que esta antes do hifen
				$un = explode(" / ", $un[1]);//separa o resto pelas barras
				$unOrg['nome'] = $un[count($un)-1];// o que esta no ultimo pedaco eh o nome da unidade
				if (strpos($dado, " - ") === false || strpos($dado, "(") === false || strpos($dado, ")") === false) {
					$unOrg['nome'] = $dado;
				}
				//coloca os dados nas posicoes corretas
				foreach ($unOrg as $atr => $val) {
					$html = str_replace('{$'.$ch.'_'.$atr."}", $val, $html);
				}
				// colocando imagem de quadrado para "em nome de unidade" em SAPs
				if ($dado != null) $img_un = "img/quadrado_x.jpg";
				$html = str_replace('{$org_quadrado}', $img_un, $html);
			}elseif ($res['tipo'] == 'documentos'){
				//tratamento de documentos (mostrar nomes, numeros, etc)
				$docID = explode(",", $dado); //print_r($dado);
				$docs[1]['nome'] = ''; $docs[2]['nome'] = ''; $docs['total']['nome'] = '';
				$docs['total']['tam'] = 0;
				$i = 0;
				foreach ($docID as $did) {
					//obtem os IDs dos documentos anexados
					if($did != ''){
						//carrega os dados do documento
						$doci = new Documento($did);
						$doci->loadCampos();
						if($doci->dadosTipo['nomeAbrv'] != 'dgen')
							$docs['total']['nome'] .= $doci->dadosTipo['nome'].' '.$doci->numeroComp.' ('.$doci->campos['assunto'].')<br />';
						else
							$docs['total']['nome'] .= $doci->numeroComp.' ('.$doci->campos['assunto'].')<br />';
						$docs['total']['tam']++;
						$docs[1]['nome'] .= $doci->dadosTipo['nome'].' '.$doci->numeroComp.'<br />';
						$docs[2]['nome'] .= $doci->campos['assunto'].'<br />';
					}
				}
				//completa a coluna total para que tenha pelo menos 6 linhas de altura
				while($docs['total']['tam'] < 6){
					$docs[1]['nome'] .= '<br />';
					$docs['total']['nome'] .= '<br />';
					$docs['total']['tam']++;
				}
				//coloca os documentos no lugar correto
				$html = str_replace('{$'.$ch."_1}", $docs[1]['nome'], $html);
				$html = str_replace('{$'.$ch."_2}", $docs[2]['nome'], $html);
				$html = str_replace('{$'.$ch."}", $docs['total']['nome'], $html);
			}else{
				$dado = montaCampo($ch, 'mostra', array($ch => $dado));
				$v = $dado['valor'];
				if ($res['nome'] == 'conteudo') {
					$v = SGDecode($v);
				}
				elseif ($res['nome'] == 'justificativa') {					
					$v = SGEncode($v, ENT_QUOTES, null, false);
					$v = str_replace("&lt;br /&gt;", "<br />", $v);
				}
				
				$html = str_replace('{$'.$ch."}", $v, $html);
				if ($res['nome'] == 'pessoaIntSAP') {
					if ($dado['valor'] != '') $img_pessoa = "img/quadrado_x.jpg";
					$html = str_replace('{$pessoa_quadrado}', $img_pessoa, $html);
				}
			}
		}
		
		//coloca despacho no documento
		$despacho = $doc->getHist();
		$desp = '';
		$i=count($despacho);
		while($desp == '' && $i >= 0){
			$desp = $despacho[$i]['despacho'];
			$i--;
		}
		
		$despacho = SGDecode($desp);
		$despacho = mb_check_encoding($desp, 'UTF-8') ? $desp : utf8_encode($desp);
		$html = str_replace('{$despacho}',$desp,$html);
		
		$data['dia1'] = date("j");
		$data['mes1'] = date("n");
		$mesExt = array("","janeiro","fevereiro","mar&ccedil;o","abril","maio","junho","julho","agosto","setembro","outubro","novembro","dezembro");
		$data['mes2'] = substr($mesExt[$data['mes1']],0,3);
		$data['mes3'] = $mesExt[$data['mes1']];
		$data['ano1'] = date("y");
		$data['ano2'] = date("Y");
		
		foreach ($data as $ch => $dado) {
			$html = str_replace('{$'.$ch."}", $dado, $html);
		}
		
		//para docs que contem 2 cabecalhos (rr)
		$html = str_replace('{$header}', $header, $html);
		
		// se é só para visualização, não gera pdf e retorna html
		if ($visualizar) 
			$html .= '<p style="border: 1px solid red; color: red; background-color: yellow; font-weight:bold; text-align:center">Este documento &eacute; apenas uma visualiza&ccedil;&atilde;o e pode n&atilde;o ter sido salvo no sistema! N&atilde;o se esque&ccedil;a de salv&aacute;-lo!</p>';
		
		//inicializa a variavel pdf com os tamanhos padrao
		if ($doc->dadosTipo['nomeAbrv'] == 'rr') {
			$pdf = new mPDF('c','A4',12,'Arial',30,30,10,10,12,5,'P');
		
			$pdf->allow_charset_conversion=true;
			$pdf->charset_in='UTF-8';
			
			//$pdf->shrink_tables_to_fit = 1;
		}
		else {
			$pdf = new mPDF('c','A4',12,'Arial',30,30,35,10,12,5,'P');
		
			$pdf->allow_charset_conversion=true;
			$pdf->charset_in='UTF-8';
			$pdf->SetHTMLHeader($header);
		}
		//seta os dados
		$pdf->SetHTMLFooter($footer); //Rodape eliminado
		$pdf->WriteHTML(($html));
		
		if($visualizar){
			//caso visualizar
			$fileName = 'user'.$_SESSION['id'].'_tempfile.pdf';
			
			if(!is_dir("files/temp_pdf")){
				mkdir("files/temp_pdf");
			}
			
			$pdf->Output('files/temp_pdf/'.$fileName,'F');
			
			return json_encode(array(array('success' => true)));
			
		} else {
			//seta o nome do arquivo
			if(!$timestamp)
				$fileName = '['.$doc->id.']_'.$doc->dadosTipo['nome'].'_'.$doc->numeroComp.'_(PDF_DOC_ORIGINAL).pdf';
			else
				$fileName = '['.$doc->id.']_'.$doc->dadosTipo['nome'].'_'.$doc->numeroComp.'_('.date("j-n-Y_H\hi\ms\s",$timestamp).').pdf';
			
			$fileName = strtolower($fileName);
			$fileName = str_replace(array('/','ç','á','ã','â','ê','é','í','ó','õ','ô','ú','&ccedil;','&aacute;','&atilde;','&acirc;','&ecirc;','&eacute;','&iacute;','&oacute;','&otilde;','&ocirc;','&uacute',' ','?','\'','"','!','@',"'"), array('-','c','a','a','a','e','e','i','o','o','o','u','c','a','a','a','e','e','i','o','o','o','u','_','','','','','',''), $fileName);
			
			$pdf->Output('files/'.$fileName,'F');
			
			//anexa ao documento
			$doc->anexo[] = $fileName;
			$doc->salvaAnexos();
			
			//retorna o nome do arquivo
			return $fileName;
		}
		
	}
	
	function geraCI($id){
		require_once("classes/mpdf51/mpdf.php");
		
		$html = file_get_contents("templates/modelo_ci.html");
		
		/*$CI = new Documento($id);
		$CI->loadCampos();
		print_r($CI);*/
		$doc = new Documento($id);
		$doc->loadCampos();
		$hist = $doc->getHist();
		
		$html = str_ireplace('{$ano2}', date("Y",$doc->data), $html);
		
		//TODO ler dados
		
		//TODO completar dados
	
	
		for ($i = 0; $i < 9; $i++) {
			if(!isset($hist[count($hist)-$i])){
				$data = '&nbsp;';
				$para = '&nbsp;';
				$desp = '&nbsp;';
			}elseif(strpos($hist[count($hist)-$i]['acao'],"Despachou para") !== false){
				$para = str_ireplace("Despachou para", "", $hist[count($hist)-$i]['acao']);
				$desp = "Despachou documento";
				$data = $hist[count($hist)-$i]['data'];
			} else {
				$desp = $hist[count($hist)-$i]['acao'];
				$para = '&nbsp;';
				$data = $hist[count($hist)-$i]['data'];
			}
			$html = str_ireplace('{$despacho'.$i.'}', $desp, $html);
			$html = str_ireplace('{$para'.$i.'}', $para, $html);
			$html = str_ireplace('{$data'.$i.'}', $data, $html);
		}
		
		$pdf = new mPDF('c','A4',12,'Arial',30,30,15,10,0,0,'P');
		$pdf->allow_charset_conversion=true;
		$pdf->charset_in='UTF-8';
		$pdf->WriteHTML(utf8_encode($html));
		$fileName = '['.$doc->id.']_CI_'.$doc->campos['numeroCI'].'_(PDF_DOC_ORIGINAL).pdf';
		
		$pdf->Output('files/'.$fileName,'I');
		//anexa ao documento
		$doc->anexo[] = $fileName;
		$doc->salvaAnexos();
	}
	
	// baseado em: http://www.drsolutions.com.br/exemplos/regesxp_mysql.pdf
	function stringBusca($str,$isFileName = false) {
		//$str = SGDecode($str);
		// converte para minusculas
		
		$str = mb_ereg_replace("^[ ]+", "", mb_strtolower('  '.$str,'utf-8')); 
		
		$chars_acentuados = 
		array("Ã","ã","Õ","õ","á","Á","é","É","í","Í","ó","Ó","ú","Ú","ç","Ç","à","À","è","È","ì","Ì","ò","Ò","ù","Ù","ä","Ä","ë","Ë","ï","Ï","ö","Ö","ü","Ü","Â","Ê","Î","Ô","Û","â","ê","î","ô","û","!","?",",","“","”","\"","\\","/","%");
		$chars_normais =
		array("a","a","o","o","a","a","e","e","i","i","o","o","u","u","c","c","a","a","e","e","i","i","o","o","u","u","a","a","e","e","i","i","o","o","u","u","A","E","I","O","U","a","e","i","o","u",".",".",".",".",".","." ,"." ,".",".");
		// retira os acentos
		$str = str_replace($chars_acentuados, $chars_normais, $str);
		
		if($isFileName){
			$str = str_replace(array(' ','!','@','#','$','%','&','{','}','(',')','+','-','/','~','"','\'',';','?','°','º','ª','*',':','=',',','<','>','|','\\'), '_', $str);
		}
		
		/*$chars_busca = array("a", "e", "i", "o", "u", "c");
		$env = array("[a]","[e]","[i]","[o]","[u]","[c]");
		$str = str_replace($chars_busca, $env, $str);
		$caracteresParaRegExp = array(
			"(a|ã|á|à|ä|â|&atilde;|&aacute;|&agrave;|&auml;|&acirc;|Ã|Á|À|Ä|Â|&Atilde;|&Aacute;|&Agrave;|&Auml;|&Acirc;)",
			"(e|é|è|ë|ê|&eacute;|&egrave;|&euml;|&ecirc;|É|È|Ë|Ê|&Eacute;|&Egrave;|&Euml;|&Ecirc;)",
			"(i|í|ì|ï|î|&iacute;|&igrave;|&iuml;|&icirc;|Í|Ì|Ï|Î|&Iacute;|&Igrave;|&Iuml;|&Icirc;)",
			"(o|õ|ó|ò|ö|ô|&otilde;|&oacute;|&ograve;|&ouml;|&ocirc;|Õ|Ó|Ò|Ö|Ô|&Otilde;|&Oacute;|&Ograve;|&Ouml;|&Ocirc;)",
			"(u|ú|ù|ü|û|&uacute;|&ugrave;|&uuml;|&ucirc;|Ú|Ù|Ü|Û|&Uacute;|&Ugrave;|&Uuml;|&Ucirc;)",
			"(c|ç|Ç|&ccedil;|&Ccedil;)"
		);
		
		$str = str_replace($env, $caracteresParaRegExp, $str);*/
		// troca espacos por *
		//$str = str_replace(" ",".*",$str);
		
		return $str;
	}
	
	function implodeRecursivo($separador, $array) {
		$ret = '';
		
		if (count($array) > 0 && is_array($array)) {
			foreach ($array as $item) {
				if (is_array($item)) {
					$ret .= implodeRecursivo($separador, $item) . $separador;
				}
				else {
					$ret .= $item . $separador;
				}
			}
		}
		else {
			return $array . $separador;
		}
		
		$ret = rtrim($ret, $separador);
		
		return $ret;
	}
	 
	function to_utf8($string) { 
		// From http://w3.org/International/questions/qa-forms-utf-8.html 
	    if (preg_match('%^(?: 
	      [\x09\x0A\x0D\x20-\x7E]            # ASCII 
	    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte 
	    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs 
	    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte 
	    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates 
	    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3 
	    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15 
	    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16 
	)*$%xs', $string)) { 
	        return $string; 
	    } else { 
	        return iconv('CP1252', 'UTF-8', $string); 
	    } 
	} 
	
	/**
	 * Função encapsuladora de htmlentities. Usada para padronizar o uso desta função no SiGPOD e evitar problemas futuros com encoding.
	 * @param string $string a string a ser htmlentities encodada
	 * @param $tipo flags para htmlenties (padrão ENT_QUOTES)
	 * @param string $cod codificação (padrão: ini_get('default_charset'))
	 * @param boolean $double se deverá fazer double encode (aka &aacute; -> &amp;aacute;) (padrão: false)
	 */
	function SGEncode($string, $tipo = ENT_QUOTES, $cod = '', $double = false) {
		if ($string === null || $string === "") return $string;
		$stringEnc = mb_detect_encoding($string);
		
		if ($cod == '') {
			if ($stringEnc == 'ASCII') {
				$cod = ini_get('default_charset');
			}
			else {
				$cod = $stringEnc;
			}
		}
		
		if ($stringEnc != $cod) {
			$string = mb_convert_encoding($string, $cod);
		}
		
		$ret = htmlentities($string, $tipo, $cod, $double);
		
		/*$arqTemp = fopen("dump_htmlentities.txt", "a");
		fwrite($arqTemp, "[".date("d H:i:s", time())."] ".$ret."\n");
		fclose($arqTemp);*/
		
		return $ret;
	}
	
	/**
	 * Função encapsuladora de html_entity_decode. Usada para padronizar o uso desta função no SiGPOD e evitar problemas futuros com encoding.
	 * @param string $string a string a ser decodada
	 * @param $flags flags para html_entity_decode (padrão: ENT_QUOTES)
	 * @param string $encoding codificação a ser usada (padrão: ini_get('default_charset')) 
	 */
	function SGDecode($string, $flags = null, $encoding = null) {
		if ($flags == null || $flags == '') {
			$flags = ENT_QUOTES;
		}
		
		if ($encoding == null || $encoding == "") {
			//$encoding = 'UTF-8';
			$encoding = ini_get('default_charset');
		}
		
		//var_dump($flags);
		
		return html_entity_decode($string, $flags, $encoding);
	}
?>
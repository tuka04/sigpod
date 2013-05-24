<?php
	/**
	 * @version 1.0 20/3/2012 
	 * @package geral
	 * @author Vitor Morelatti
	 * @desc módulos usados para o gerenciamento de pessoas
	 */

	include_once 'conf.inc.php';


	/**
 	* Mostra tela de férias de um usuário
 	* @param int $userID id do usuário
 	* @param BD $bd
 	* @return html
	 */
	function showFerias($userID, BD $bd) {
		if ($bd == null) return;
	
		// preenche com conteudo de férias já cadastradas
		$ferias = getFerias($userID, $bd);
		$conteudo = "";
		// percorre todas as férias para este usuário
		foreach ($ferias as $f) {
			// transforma as datas de Unix Timestamp para leitura de humanos
			$dataIni = '<span id="dataIni_val">' . date("d/m/Y", $f['dataIni']). '</span>';
			$dataFim = date("d/m/Y", $f['dataIni'] + (($f['duracao']-1) * 24 * 60 * 60));
			
			// pega informações do chefe/gerente imediato 
			$chefe = getUsers($f['gerenteID']);
			$chefe = $chefe[0]['nomeCompl'];
			
			// pega info de licença
			$lic = "--";
			if ($f['licenca'] != "") {
				$lic = getFeriasTipo($bd, $f['licenca']);
				$lic = $lic[0]['nome'];
			}	
			
			// monta as ações
			$acao = '';
			if ($f['id'] != "") {
				// verifica se o usuario pode ainda deletar suas ferias. No caso, as férias só poderão ser deletadas se elas ainda não tiverem acabado
				if (time() <= $f['dataIni'] + ($f['duracao'] * 24 * 60 * 60))
					$acao = '<a name="link'.$f['id'].'" id="link'.$f['id'].'" onclick="delFerias(\''.$f['id'].'\')"><span title="Apagar" class="ui-icon ui-icon-closethick"></span></a>';
			} 
			
			// monta a linha da tabela
			$conteudo .= '<tr class="c"><td class="c"><center>'.$dataIni.'</center></td><td class="c"><center>'.$dataFim.'</center></td><td class="c"><center>'.$f['duracao'].'</center></td><td class="c"><center>';
			$conteudo .= $chefe. '</td><td class="c"><center>'.$lic.'</center></td><td class="c"><center>'.$acao.'</center></td></tr>';
		}
		
		// se depois de percorrer todas as férias o conteudo continuar vazio, não há férias no Banco
		if ($conteudo == "")
			$conteudo = '<tr class="c"><td class="c" colspan="6"><center><b>Nenhuma f&eacute;rias lan&ccedil;ada no sistema ainda. :(</b></center></td></tr>';
		
		// pega as informações desse usuario
		$usuario = getUsers($userID);
		if (count($usuario) <= 0) {
			$gerente = "";
			$gerenteID = "";
			$header = "Usu&aacute;rio Inv&aacute;lido.";
			$usuario['nome'] = "";
		}
		else {
			$usuario = $usuario[0];
		
			// pega a interface de férias
			$html = getInterfaceFerias();
	
			$header = "F&eacute;rias de " .$usuario['nome'];
			
			// seta informações de gerente
			$gerente = $usuario['gerente'];
			// pega informações deste gerente no BD
			$sql = "SELECT * FROM usuarios WHERE username = '".$gerente."'";
			$res = $bd->query($sql);
			// pega ID e nome completo
			if (count($res) > 0) {
				$gerenteID = $res[0]['id'];
				$gerente = $res[0]['nomeCompl'];
			}
			else { 
				$gerenteID = "";
				$gerente = "";
			}
		}
		
		// monta select de tipos de férias
		$tipos = getFeriasTipo($bd);
		$selectTipo = '<select name="tipo" id="tipo" style="width: 10%">';
		$selectTipo .= '<option></option>';
		foreach($tipos as $t) {
			$selectTipo .= '<option value="'.$t['abrv'].'">'.$t['nome'].'</option>';
		}
		$selectTipo .= '</select>';
		
		// preenche HTML com o conteudo necessário
		$html = str_replace('{$header}', $header, $html);
		$html = str_replace('{$conteudo}', $conteudo, $html);
		$html = str_replace('{$gerenteNome}', $gerente, $html);
		$html = str_replace('{$gerenteID}', $gerenteID, $html);
		$html = str_replace('{$userID}', $userID, $html);
		$html = str_replace('{$usuario}', $usuario['nome'], $html);
		$html = str_replace('{$selectTipo}', $selectTipo, $html);
		
		return $html;
	}
	
	/**
	 * Salva férias no bd
	 * @param $dados
	 * @param $bd
	 * @return html
	 */
	function showSalvaFerias($dados, BD $bd) {
		// monta data
		$dataIni = 0;
		if (isset($dados['dataIni'])) {
			$partesData = explode("/", $dados['dataIni']);
			if (count($partesData) != 3) return 'Data inv&aacute;lida. Por favor, tente novamente com uma data em padrão dd/mm/AAAA.';
			$dia = $partesData[0];
			$mes = $partesData[1];
			$ano = $partesData[2];
			
			$dataIni = mktime(0, 0, 0, $mes, $dia, $ano);
			if (($dataIni + 24*60*60 * $dados['duracao']) <= time()) {
				return 'F&eacute;rias inv&aacute;lidas. Suas f&eacute;rias n&atilde;o podem acabar antes da data de hoje.';
			}
		}
		
		if (!isset($dados['userID']))
			return 'Dados insuficientes.';
		else {
			if ($dados['userID'] == "")
				return 'Dados insuficientes.';
		}
			
		$usuario = getUsers($dados['userID']);
		if (count($usuario) <= 0)
			return 'Usu&aacute;rio inv&aacute;lido.';
		$usuario = $usuario[0];
		
		// verificação de validade de intervalo
		if (checkInterval($dados['userID'], $dados['dataIni'], $bd) != false)
			return 'Esta data de in&iacute;cio &eacute; conflitante com outras f&eacute;rias suas. Por favor, mude a data inicial e tente novamente.';
		
		// verifica término
		if (checkInterval($dados['userID'], date("d/m/Y", ($dataIni + 86400 * $dados['duracao'])), $bd) != false)
			return 'Estas f&eacute;rias que voc&ecirc; est&aacute; tentando inserir possui intervalo conflitante com outras f&eacute;rias suas j&aacute; cadastradas. Por favor, mude a dura&ccedil;&atilde;o de suas f&eacute;rias e tente novamente.';
		
		// pega info do gerente
		if (!isset($dados['gerente']))
			return "Gerente com ID inv&aacute;lido. Contate seu administrador de sistema.";
		else {
			if ($dados['gerente'] == "") {
				//return "Gerente com ID inv&aacute;lido. Contate seu administrador de sistema.";
				$gerenteID = 0;
			}
		}
		
		if ($dados['gerente'] != "") { 
			$gerente = getUsers($dados['gerente']);
			if (count($gerente) <= 0) return "Gerente com ID inv&aacute;lido. Contate seu administrador de sistema.";
			$gerenteID = $gerente[0]['id'];
		}
		
		// tenta inserir dados no BD
		$sql = 'INSERT INTO pessoa_ferias (userID,dataIni,duracao,gerenteID,licenca) VALUES ("'.$dados['userID'].'","'.$dataIni.'","'.$dados['duracao'].'","'.$gerenteID.'","'.$dados['tipo'].'")';
		$res = $bd->query($sql);
		if ($res) {
			$sql = "SELECT id FROM pessoa_ferias WHERE userID = ".$dados['userID']." AND dataIni = ".$dataIni." ORDER BY id DESC";
			$res = $bd->query($sql);
			doLog($_SESSION['username'], "Registrou ferias (ID: ".$res[0]['id'].") para ".$usuario['username']." para dia ".$dados['dataIni']." com duracao de ".$dados['duracao']." dias.");
			return "F&eacute;rias inseridas com sucesso!";
		}
		return "Falha ao inserir f&eacute;rias no Banco de Dados.";
	}
	
	/**
	 * Mostra tela de Times
	 * @param $userID
	 * @param $bd
	 */
	function showTimes($userID, BD $bd, $todos = false) {
		// pega template
		$html = getInterfaceTime();
		$templateDiv = getDivTimes();
		
		// pega a informação deste usuario
		$user = getUsers($userID);
		$user = $user[0];
		
		// começa preenchimento do template
		$conteudo = "";
		$divCont = "";
		$div = $templateDiv['template'];
		if (!$todos) {
			$div = str_replace('{$nomeDiv}', "time_" .$user['username'], $div);
			$titulo = "Suas Equipes";
		}
		else{
			$titulo = "Todas Equipes";
			$div = str_replace('{$nomeDiv}', "time_todos", $div);
		}
		$todosDiv = "";
		
		// cria uma fila First In First Out
		$stack = array();
		if (!$todos) array_push($stack, $userID);
		else {
			$stack = getAllManagers($bd);
		}
		// pra cada elemento da fila, cria um item na lista para mostrar os subordinados
		while (($p = array_pop($stack)) != null) {			
			$header = '';
			$divCont = "";
			
			// pega lista de gerenciados desde usuario
			$gerenciados = getGerenciados($p, $bd);
			$user = getUsers($p);
			$user = $user[0];
			
			// se ele nao tem gerenciados, não há a necessidade de criar um div para ele
			if (count($gerenciados) <= 0) continue;
			
			$header = $templateDiv['header'];
			$header = str_replace('{$header}', $user['nomeCompl'], $header);
			$header = str_replace('{$id_header}', $user['username'], $header);
			$divCont = $templateDiv['conteudo'];
			
			// cria uma tabela de subordinados
			$subordinados = '<table class="c" style="width: 100%">';
			
			foreach($gerenciados as $g) {
				// pega a quantidade de docs pendentes com este subordinado
				$numDocs = getPendentDocs($g['id']);
				$numDocs = count($numDocs);
				
				if ($g['ativo'] == 0 && ($numDocs == 0 || $numDocs == null)) continue;
				
				// pega a próxima férias deste subordinado
				$ferias = getPendingVacation($g['id'], $bd);
				
				$subordinados .= '<tr class="light"><td style="width: 15%"><a href="javascript:void(0)" onclick="javascript:showUserProfile('.$g['id'].')">';
				$subordinados .= $g['nomeCompl'].'</a></td><td style="width: 15%"><a href="sgp.php?acao=docsTime&usuario='.$g['id'].'">Documentos</a> ('.$numDocs.' Pendentes)</td><td style="width: 15%"><a>Empreendimentos</a></td><td style="width: 15%"><a href="sgp.php?acao=ferias&usuario='.$g['id'].'">F&eacute;rias</a>';
				
				if (count($ferias) > 0) {
					$subordinados .= ' (In&iacute;cio: '.date("d/m/Y", $ferias[0]['dataIni']).' / Fim: '.date("d/m/Y", ($ferias[0]['dataIni']-1) + 24*60*60* $ferias[0]['duracao']).')';
				}
				$subordinados .= '</td></tr>';
								
				if (!$todos) array_unshift($stack, $g['id']); 
			}
			
			$subordinados .= '</table>';
			

			$divCont = str_replace('{$divContent}', $subordinados, $divCont);
			if ($divCont != "") {
				$todosDiv .= $header . $divCont;
			}
		}
		
		if ($todosDiv == "") $conteudo = "Voc&ecirc; n&atilde;o &eacute; gerente de ningu&eacute;m.";
		else $conteudo = str_replace('{$conteudo_times}', $todosDiv, $div);
		
		$html = str_replace('{$div_times}', $conteudo, $html);
		$html = str_replace('{$titulo}', $titulo, $html);
		$html .= '<script type="text/javascript">$(document).ready(function() { $(".times").accordion({ collapsible: true, autoHeight: false, nagivation: true });  $(\'.accordion .head\').click(function() { $(this).next().toggle();	return false; }).next().hide(); });</script>';
		
		return $html;
	}
	
	/**
	 * Mostra tela de documentos que estão apenas com o usuário [NÃO TERMINADO - NÃO ESTÁ SENDO USADO]
	 * @param int $userID id do usuário
	 * @param BD $bd
	 * @return html
	 */
	function showDocsTime($userID, BD $bd) {
		global $conf;
		if (!isset($userID) || !isset($bd))
			return "Par&acirc;metros insuficientes.";
		$sql = "SELECT * FROM doc WHERE ownerID = '".$userID."' ORDER BY id";
		$res = $bd->query($sql);
		
		$html = '<table id="docsPend" style="width: 100%">';
		$html .= '<tr class="c"><td class="c">Nº CPO</td><td class="c">Tipo/N&uacute;mero</td><td class="c">Emitente</td><td class="c">Assunto</td><td class="c">Empreendimento</td><td class="c">Despachar?</td></tr>';
		foreach ($res as $d) {
			$doc = new Documento($d['id']);
			$doc->loadCampos();
			
			$html .= '<tr class="c"><td class="c">'.$doc->id.'</td><td class="c"><a href="" onclick="window.open(\'sgd.php?acao=ver&docID='.$doc->id.'\',\'doc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">'.$doc->dadosTipo['nome'].' '.$doc->numeroComp.'</a></td><td class="c">'.$doc->emitente.'</td><td class="c">'.$doc->campos['assunto'].'</td><td class="c"></td><td class="c"></td></tr>';
		}
		
		$html .= '</table><br /><a href="javascript: history.go(-1)">Voltar</a>';
		
		return $html;
	}
	
	/**
	 * Deleta férias especificada
	 * @param int $id id da férias
	 * @param BD $bd
	 * @return array indicando sucesso ou falha
	 */
	function deletaFerias($id, BD $bd) {
		// se ID for nulo, retorna falso
		if ($id == null) {
			return array(array('success' => false));
		}
		
		// tenta deletar
		$sql = "DELETE FROM pessoa_ferias WHERE id = " .$id;
		$res = $bd->query($sql);
		if ($res) { // se deu tudo certo, retorna verdadeiro
			doLog($_SESSION['username'], "Deletou ferias de ID ".$id);
			return array(array('success' => true));
		}
		// se não, retorna falso
		return array(array('success' => false));
	}
	
	/**
	 * Verifica se há férias que cujo intervalo inclui a $data especificada para o usuário de $userID
	 * @param int $userID
	 * @param int $data [Formato dd/mm/aaaa]
	 * @param BD $bd
	 * @return array info de todas as férias que satisfazem esta condição
	 * @return false em caso de erro
	 */
	function checkInterval($userID, $data, BD $bd) {
		if (!isset($userID) || !isset($data) || !isset($bd)) return false;
		
		// tratamento de data
		$partesData = explode("/", $data);
		if (count($partesData) != 3) return false;
		$dia = $partesData[0];
		$mes = $partesData[1];
		$ano = $partesData[2];

		// transforma data em unix timestamp
		$data = mktime(0, 0, 0, $mes, $dia, $ano);
		
		// transforma 1 dia em segundos
		// 24 h * 60 min * 60 s
		$diaEmSegundos = 24 * 60 * 60;
		
		// faz consulta para retornar todas as férias deste usuario que dataIni <= $data <= dataFim
		$sql  = "SELECT * FROM pessoa_ferias WHERE userID = $userID AND ";
		$sql .= "dataIni <= $data AND (dataIni + ((duracao - 1) * $diaEmSegundos)) >= $data";
		
		$res = $bd->query($sql);
		if (count($res) > 0)
			return $res;
		
		return array();
	}
	
	/**
	 * Retorna se um usuário é gerente de outro
	 * @param $managerID
	 * @param $userID
	 * @return bool true caso afirmativo
	 * @return bool false caso contrario
	 */
	function isManager($managerID, $userID, BD $bd) {
		if (!isset($managerID) || !isset($userID))
			return false;
			
		$gerente = getUsers($managerID);
		$usuario = getUsers($userID);
		if (count($gerente) <= 0 || count($usuario) <= 0)
			return false;
			
		$sql = "SELECT * FROM usuarios WHERE id = $userID AND gerente = '".$gerente[0]['username']."'";
		$res = $bd->query($sql);
		if (count($res) > 0)
			return true;
		else
			return false;
	}
	
	function isIndirectManager($managerID, $userID, BD $bd) {
		if (!isset($managerID) || !isset($userID)) {
			return false;
		}
		
		$usuario = getUsers($userID);
		if (count($usuario) <= 0) {
			return false;
		}
		$usuario = $usuario[0];
		$verificados = array();
		
		$sql = "SELECT * FROM usuarios WHERE username = '".$usuario['gerente']."'";
		$res = $bd->query($sql);
		
		while ($usuario['gerente'] != "") {
			// verificação de loop infinito devido a ciclos na árvore de gerentes
			if (isset($verificados[$usuario['gerente']])) {
				return false;
			}
			
			$res = $res[0];
			if ($res['id'] == $managerID) {
				return true;
			}
			
			$verificados[$usuario['gerente']] = true;
			$usuario = $res;
			
			$sql = "SELECT * FROM usuarios WHERE username = '".$usuario['gerente']."'";
			$res = $bd->query($sql);
		}
		
		return false;
	}
	
	/**
	 * Função para verificar, no despacho, se o destinatário vai entrar ou está de férias
	 * @param $userID
	 * @param $bd
	 */
	function checkDesp($userID, BD $bd) {
		if (!isset($userID)) return array(array('success' => false));
		
		// calcula tempo de um dia em segundos
		$UmDia = 24 * 60 * 60;
		// calcula o tempo de uma semana em segundos
		$UmaSemana = 7 * $UmDia;

		
		$sql = "SELECT * FROM pessoa_ferias WHERE userID = ".$userID." AND (dataIni <= ".time()." AND (dataIni + ($UmDia * duracao) >= ".time().") OR ((dataIni - $UmaSemana) <= ".time()." AND ".time()." <= dataIni + ($UmDia * duracao)))";
		$res = $bd->query($sql);
		if (count($res) > 0) {
			return array(array('success' => true, 'dataIni' => date("d/m/Y", $res[0]['dataIni']), 'dataFim' => date("d/m/Y", $res[0]['dataIni'] + $res[0]['duracao'] * $UmDia)));
		}
		return array(array('success' => false));
	}
	
	/**
	 * Retorna os tipos de férias
	 * Se o parâmetro $tipo for especificado, retorna info de um tipo especificado
	 * @param BD $bd
	 * @param string $tipo abreviacao do tipo
	 * @return array [abrv][nome]
	 */
	function getFeriasTipo(BD $bd, $tipo = null) {
		// se não há tipo especificado
		if ($tipo == null) {
			$sql = "SELECT * FROM label_ferias_tipo ORDER BY nome";
		}
		else {
			// caso contrario, retorna dados do tipo especificado
			$sql = "SELECT * FROM label_ferias_tipo WHERE abrv = '$tipo' ORDER BY nome";
		}
		$res = $bd->query($sql);
		
		return $res;
	}
	
	/**
	 * Retorna todas as férias de um determinado usuário
	 * @param $userID
	 * @param $bd
	 * @return array
	 */
	function getFerias($userID, BD $bd) {
		$sql = "SELECT * FROM pessoa_ferias WHERE userID = $userID ORDER BY dataIni";
		$res = $bd->query($sql);
		return $res;
	}
	
	/**
	 * Retorna as férias que estão para começar de um usuário
	 * @param int $userID
	 * @param $bd 
	 */
	function getPendingVacation($userID, BD $bd) {
		// transforma 1 dia em segundos
		// 24 h * 60 min * 60 s
		$diaEmSegundos = 24 * 60 * 60;
		
		// realiza consulta
		$sql = "SELECT * FROM pessoa_ferias WHERE userID = $userID AND (dataIni >= ".time()." OR (dataIni + (duracao * $diaEmSegundos)) >= ".time().") ORDER BY dataIni";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna lista de ids de todos os gerentes
	 * @param $bd
	 */
	function getAllManagers($bd) {
		$sql = "SELECT gerente FROM usuarios GROUP BY gerente ORDER BY gerente DESC";
		$todosGerentes = $res = $bd->query($sql);
		
		$retorno = array();
		foreach($todosGerentes as $g) {
			if ($g['gerente'] == "") continue;
			$sql = "SELECT * FROM usuarios WHERE username = '" .$g['gerente']. "'";
			$res = $bd->query($sql);
			if (count($res) <= 0) continue;
			$retorno[] = $res[0]['id'];
		}
		//sort($retorno, SORT_NUMERIC);
		return $retorno;
	}
	
	function showUserProfile($userID){
		global $bd;
		$usuario = getUsers($userID);
		if(count($usuario) < 1){
			print json_encode(array(array("success" => false, "errorFeedback" => "usuario nao consta no BD")));
			exit();
		}
		$usuario[0]['gerente'] = getUserFromUsername($usuario[0]['gerente']);
		$usuario[0]['grupo'] = getGrupoName($usuario[0]['gid']);
		$usuario[0]['ultimoLogin'] = date("d/m/Y H:i",$usuario[0]['ultimoLogin']);
			
		//monta a string de ferias
		$ferias = getFerias($userID, $bd);
		$ferias_estado['emFerias'] = false;
		$ferias_estado['volta'] = 0;
		$ferias_estado['proxFerias'] = 0;
		foreach ($ferias as $f) {//percorre o array de ferias
			//tenta descobrir se o usuario esta em ferias
			if($f['dataIni'] < time() && $f['dataIni']+($f['duracao']-1)*24*60*60 > time()){
				$ferias_estado['emFerias'] = true;
				$ferias_estado['volta'] = ($f['dataIni']+($f['duracao']-1)*24*60*60);
			}
			//tenta descobrir quando sao as proximas ferias do usuario
			if(($f['dataIni'] > time() && $f['dataIni'] < $ferias_estado['proxFerias']) || ($f['dataIni'] > time() && $ferias_estado['proxFerias'] == 0))
				$ferias_estado['proxFerias'] = $f['dataIni'];
		}
		//monta a string para verificar se o ususario esta em ferias e quando sao as proximas ferias
		$usuario[0]['feriasEstado'] = '';
		if($ferias_estado['emFerias'])
			$usuario[0]['feriasEstado'] .= 'Em f&eacute;rias at&eacute; '.date("d/m/Y",$ferias_estado['volta']);
		if($ferias_estado['emFerias'] && $ferias_estado['proxFerias'])
			$usuario[0]['feriasEstado'] .= '<br />';
		if($ferias_estado['proxFerias'])
			$usuario[0]['feriasEstado'] .= 'Pr&oacute;ximas f&eacute;rias em ' .date("d/m/Y",$ferias_estado['proxFerias']);
		if(!$ferias_estado['emFerias'] && !$ferias_estado['proxFerias'])
			$usuario[0]['feriasEstado'] .= 'Nenhuma programada';
		
		//le o template do perfil do usuario
		$template = Pessoa::getUserProfileTemplate();
		
		//monta a tabela de ultimos despachos em documentos
		$docHist = '';
		foreach (Historico_Doc::get5Ultimos($userID) as $h) {//pra cada uma das ultimas 5 entradas
			$hist = new Historico_Doc($bd);//carrega a entrada de historico
			$hist->load($h['id']);
			$doc = new Documento($hist->get('docID'));
			$doc->loadTipoData();
			$docLink = '<a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->dadosTipo['nome'].' '.$doc->numeroComp.'</a>';
			
			$docHist .= str_replace(array('{$data}','{$obj_nome}','{$acao}'), array(date("d/m/Y H:i",$hist->get('data')),$docLink,SGEncode($hist->getLabelAcao())), $template['ultimos_desp_tr']);
		}
		if($docHist == '') $docHist = $template['no_hist_tr'];//mostra que nao tem historico
		
		//monta a tabela de ultimos despachos em empreendimentos
		$empreendHist = '';
		foreach (Historico_Empreend::get5Ultimos($userID) as $h) {//pra cada uma das ultimas 5 entradas
			$hist = new Historico_Empreend($bd);//carrega a entrada de historico
			$hist->load($h['id']);
			$empreend = new Empreendimento($bd);
			$empreend->load($hist->get('empreendID'));
			$empreendLink = '<a href="javascript:void(0)" onclick="'.Empreendimento::geraLink('verEmpreend', $empreend->get('id')).'">'.$empreend->get('nome').'</a>';
			$empreendText = $hist->printHTML();
			
			$empreendHist .= str_replace(array('{$data}','{$obj_nome}','{$acao}'), array($empreendText['plain_text']['date'],$empreendLink,$empreendText['plain_text']['text']), $template['ultimos_desp_tr']);
		}
		if($empreendHist == '') $empreendHist = $template['no_hist_tr'];//mostra que nao tem historico
		
		//monta a tabela de ultimos despachos em obras
		$obraHist = '';
		foreach (Historico_Obra::get5Ultimos($userID) as $h) {//pra cada uma das ultimas 5 entradas
			$hist = new Historico_Obra($bd);//carrega a entrada de historico
			$hist->load($h['id']);
			$obra = new Obra($bd);
			$obra->load($hist->get('obraID'));
			$obraLink = '<a href="javascript:void(0)" onclick="'.Obra::geraLink('verObra', $obra->get('id')).'">'.$obra->get('nome').'</a>';
			$obraText = $hist->printHTML();
			
			$obraHist .= str_replace(array('{$data}','{$obj_nome}','{$acao}'), array($obraText['plain_text']['date'],$obraLink,$obraText['plain_text']['text']), $template['ultimos_desp_tr']);
		}
		if($obraHist == '') $obraHist = $template['no_hist_tr'];//mostra que nao tem historico
		
		if (!isset($usuario[0]['descr'])) $usuario[0]['descr'] = '';
		if (!isset($usuario[0]['nomeCompl'])) $usuario[0]['nomeCompl'] = '';
		if (!isset($usuario[0]['area'])) $usuario[0]['area'] = '';
		if (!isset($usuario[0]['cargo'])) $usuario[0]['cargo'] = '';
		if (!isset($usuario[0]['ramal'])) $usuario[0]['ramal'] = '';
		if (!isset($usuario[0]['email'])) $usuario[0]['email'] = '';
		if (!isset($usuario[0]['ultimoLogin'])) $usuario[0]['ultimoLogin'] = '';
		if (!isset($usuario[0]['matr'])) $usuario[0]['matr'] = '';
		if (!isset($usuario[0]['feriasEstado'])) $usuario[0]['feriasEstado'] = '';
		if (!isset($usuario[0]['gerente'][0]['nomeCompl'])) $usuario[0]['gerente'][0]['nomeCompl'] = '';
		if (!isset($usuario[0]['grupo'][0]['nome'])) $usuario[0]['grupo'][0]['nome'] = '';
		
		//$profileHTML = str_replace(array('{$desc}','{$nomeCompl}','{$area}','{$cargo}','{$ramal}','{$email}','{$diretor_nome}','{$grupo_nome}','{$ultimo_login}','{$matricula}','{$ferias}'), array($usuario[0]['descr'],$usuario[0]['nomeCompl'],$usuario[0]['area'],$usuario[0]['cargo'],$usuario[0]['ramal'],$usuario[0]['email'],$usuario[0]['gerente'][0]['nomeCompl'],$usuario[0]['grupo'][0]['nome'],$usuario[0]['ultimoLogin'],$usuario[0]['matr'],$usuario[0]['feriasEstado']), $template['template']);
		
		$profileHTML = str_replace('{$desc}', $usuario[0]['descr'], $template['template']);
		$profileHTML = str_replace('{$nomeCompl}', $usuario[0]['nomeCompl'], $profileHTML);
		$profileHTML = str_replace('{$area}', $usuario[0]['area'], $profileHTML);
		$profileHTML = str_replace('{$cargo}', $usuario[0]['cargo'], $profileHTML);
		$profileHTML = str_replace('{$ramal}', $usuario[0]['ramal'], $profileHTML);
		$profileHTML = str_replace('{$email}', $usuario[0]['email'], $profileHTML);
		$profileHTML = str_replace('{$diretor_nome}', $usuario[0]['gerente'][0]['nomeCompl'], $profileHTML);
		$profileHTML = str_replace('{$grupo_nome}', $usuario[0]['grupo'][0]['nome'], $profileHTML);
		$profileHTML = str_replace('{$ultimo_login}', $usuario[0]['ultimoLogin'], $profileHTML);
		$profileHTML = str_replace('{$matricula}', $usuario[0]['matr'], $profileHTML);
		$profileHTML = str_replace('{$ferias}', $usuario[0]['feriasEstado'], $profileHTML);		
		
		$profileHTML = str_replace(array('{$ultimos_desp_doc}','{$ultimos_desp_empreend}','{$ultimos_desp_obra}'), array($docHist,$empreendHist,$obraHist), $profileHTML);
		
		return json_encode(array(array('success' => true, 'userName' => $usuario[0]['nome'], 'html' => rawurlencode($profileHTML))));
			
	}
?>
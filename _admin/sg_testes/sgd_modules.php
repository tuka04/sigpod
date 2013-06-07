<?php
	
	
	/**
	 * @version 1.0 25/5/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc contem os modulos que lidam com a impressao dos modulos na tela 
	 */

	/**
	 * @desc mostra os documentos pendentes para um determinado usuario
	 * @param int $userID
	 * @param connection $bd
	 */
	function showDocsPend($userID){
		global $conf;
		
		//le varial global contendo conexao com BD
		global $bd;
		
		$autosave = '';
		
		//seleciona todos os documentos que estao com determinado usuario
		if ($userID == $_SESSION['id']) {
			$res = getPendentDocs($userID, $_SESSION['area']);
			$msg = 'Seus Documentos Pendentes';
			$autosave = showAutoSavedDocs();
		}
		else {
			$usuario = $bd->query("SELECT nomeCompl FROM usuarios WHERE id = ".$userID);
			if (count($usuario) > 0) 
				$usuario = $usuario[0];
		
			$res = getPendentDocs($userID);
			$msg = 'Documentos Pendentes de '.$usuario['nomeCompl'];
		}
		
		// carrega todos os tipos de doc do bd e deixa em memória
		// a tabela é pequena, então guardar esta tabela em memória é vantajoso
		$docTypes = getAllDocTypes();
		$tipoDoc = array();
		foreach($docTypes as $dt) {
			// cria array que representará a tabela
			$tipoDoc[$dt['id']] = $dt;
		}
		
		// carrega todas as ações cadastradas no bd e deixa e memória
		// a tabela tem várias entradas, mas tem poucos campos, então ainda é vantajoso deixá-la em memória
		$todasAcoes = getTodasAcoes();
		
		//comeca a construcao da tabela de documentos pendentes
		$table = '<span class="header">'.$msg.'</span><br />';
		
		$table .= $autosave;
		
		$table .= '<script type="text/javascript" src="scripts/jquery.tools.min.js?r={$randNum}"></script>';
		$table .= '<script type="text/javascript" src="scripts/jquery.tablesorter.min.js?r={$randNum}"></script>';
		$table .= '<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>';
		$table .= '<script type="text/javascript" src="scripts/sgd_mini.js?r={$randNum}"></script>';
		
		//se nao houver nenhum documento, mostra mensagem indicando isso
		if (!count($res)) {
			$table .= '
			<thead></thead>
			<tbody>
				<tr><td colspan="5"><center><br /><b>N&atilde;o h&aacute; documentos pendentes.</b></center></td>
			</tr></tbody>';
		//se houver, cria a primeira linha da tabela
		} else {
			$table .= '
			<center><a onclick="$(\'#divDocFiltros\').toggle()">[Exibir/Ocultar Filtros]</a></center><div id="divDocFiltros" style="display: none;">
			<center>Exibir:'; 
			
			foreach($tipoDoc as $dt) {
				if($dt['nomeAbrv'] == 'sap' || $dt['nomeAbrv'] == 'rr' || $dt['nomeAbrv'] == 'resp' || $dt['nomeAbrv'] == 'contr')
					continue;
				$table .= ' 
				<input type="checkbox" id="check_'.$dt['nomeAbrv'].'" onclick="filtraDocPend(\''.$dt['nomeAbrv'].'\', false)" checked="checked"> 
				<a onclick="filtraDocPend(\''.$dt['nomeAbrv'].'\', true)">'.$dt['nome'].'</a>';
			}
			
			$table .= '<br />
			Somente recebidos nos &uacute;ltimos:
			<a id="filtraData1" name="2" class="pendDocsData" href="javascript:void(0);" onclick="javascript:filtraDocsPendData(1,2)"> 2 dias </a> |
			<a id="filtraData2" name="7" class="pendDocsData" href="javascript:void(0);" onclick="javascript:filtraDocsPendData(2,7)"> 7 dias </a> |
			<a id="filtraData3" name="30" class="pendDocsData" href="javascript:void(0);" onclick="javascript:filtraDocsPendData(3,30)"> 30 dias </a> |
			<a id="filtraData4" name="-1" class="pendDocsData" href="javascript:void(0);" onclick="javascript:filtraDocsPendData(4,-1)"> Todos </a>';
			
			$table .= '</center></div><br />';
			$table .= showMultiDocAction();
			$table .= '<br /><table width="100%" cellspacing="0" cellpadding="0" id="docsPend">';
			
			$table .= '
			<thead>
				<tr>
					<td class="c" width="1"></td>
					<th id="despHeader" class="c" style="display: none;">&Uacute;ltimo Despacho</th>
					<th class="c"><center>N&deg; CPO</center></td><th class="c">Tipo/N&uacute;mero</th>
					<th class="c"><center>Unidade/&Oacute;rg&atilde;o Interessado</center></th>
					<th class="c"><center>Assunto</center></th>
					<th class="c"><center>Empreendimento</center></th>
					<td class="c" width="150" style="vertical-align: middle; display: none;"><b>A&ccedil;&otilde;es</b></td>
				</tr>
			</thead>';
			$table .= '<tbody>';
		}
		
		//pra cada documento pendente encontrado, cria uma linha na tabela a ser mostrada
		$linha = 0;
		$despachos = array();
		
		foreach ($res as $r) {
			$linha++;
			//inicializa um novo documento generico
			$doc = new Documento($r['id']);
			//carrega os dados gerais do doc
			$doc->loadDados();
			// seta os dados do tipo do doc
			$doc->dadosTipo = $tipoDoc[$doc->labelID];
			
			// carrega campos específicos para os docs.
			// isto é feito para tentar minimizar o número de consultas ao banco
			$doc->campos['solicObra'] = 0;
			$doc->campos['assunto'] = "---";
			// carrega assunto e solicObra (quando o doc possui estes campos)
			if (stripos($doc->dadosTipo['campos'], "assunto") !== false) {
				$solicObra = '';
				// se for tipo ofe, adiciona solicObra na consulta
				if (stripos($doc->dadosTipo['nomeAbrv'], "ofe") !== false)
					$solicObra = ', solicObra';
				
				// realiza a consulta
				$sql = "SELECT assunto".$solicObra." FROM ".$doc->dadosTipo['tabBD']." WHERE id = ".$doc->tipoID;
				$assuntoRes = $bd->query($sql);
				
				// verificação de segurança. em tese, era pra sempre entrar aqui
				if (count($assuntoRes) > 0) {
					// seta o assunto
					$doc->campos['assunto'] = $assuntoRes[0]['assunto'];
					// seta solicObra, necessario
					if ($solicObra != '')
						$doc->campos['solicObra'] = $assuntoRes[0]['solicObra'];
				}
			} 
			
			// ainda carregando campos específicos... neste caso para sap e processo
			$doc->campos['guardachuva'] = 0;
			$doc->campos['tipoProc'] = "nenhum";
			// se for sap ou processo
			if (stripos($doc->dadosTipo['nomeAbrv'], "pr") !== false || stripos($doc->dadosTipo['nomeAbrv'], "sap") !== false) {
				// seta o campo de orgao/unidade interessado
				$unOrg = 'unOrgInt';
				// se for sap, o campo de interessado é diferente
				if (stripos($doc->dadosTipo['nomeAbrv'], "sap") !== false)
					$unOrg = 'unOrgIntSap';
				
				// realiza a consulta
				$sql = "SELECT guardachuva, tipoProc, $unOrg FROM ".$doc->dadosTipo['tabBD']." WHERE id = ".$doc->tipoID;
				$gcRes = $bd->query($sql);
				
				// verificação de segurança. em tese, era pra sempre entrar aqui
				if (count($gcRes) > 0) {
					// atribui os dados corretos
					$doc->campos['guardachuva'] = $gcRes[0]['guardachuva'];
					$doc->campos['tipoProc'] = $gcRes[0]['tipoProc'];
					$doc->emitente = $gcRes[0][$unOrg];
				}
			}
			
			//le as acoes possiveis para o tipo de documento para mostrar
			$acoes = explode(",",$doc->dadosTipo['acoes']);
			
			$checkBoxClass = '';
			
			if($doc->dadosTipo['atribObra'] == 1) {
				array_push($acoes, 5);
			}
			
			// se o doc tiver sido solicitado, só mostra ação de solicitar
			if ($doc->solicitante != '0') {
				$acoes = array(0 => 81);
				$checkBoxClass .= 'solic'; 
			}
			// se o doc tiver sido solicitado para desarquivamento, só mostrar ação de arquivar
			if ($doc->solicDesarquivamento != '0') {
				$acoes = array(0 => 70);
				$checkBoxClass .= ' solicDes';
			}
			
			if ($checkBoxClass != "") {
				$checkBoxClass = 'class="'.$checkBoxClass.'"';
			}
			
			// inicializa a data do último despacho para a data de criação do doc.
			// esse será o valor padrão caso (por algum motivo) não seja encontrado nenhum despacho para este doc
			$dataDesp = $doc->data;
			
			// consulta ao banco para mostrar texto com ultimo despacho
			if ($doc->ultimoHist != 0) {
				$hist = HistFactory::novoHist('doc', $bd);
				$hist->load($doc->ultimoHist);
				
				if (count($hist) > 0) {
					$dataDesp = $hist->get('data');
					$username = $hist->getName();
					
					// monta mensagem do despacho
					$despachos[] = 'Em ' . date("d/m/Y \&\a\g\\r\a\\v\\e\;\s H:i", $dataDesp). ', ' .SGDecode($username). ' ' .SGEncode($hist->getLabelAcao(), ENT_QUOTES);
					if ($hist->get('tipo') == 'solicArq') {
						$acoes = array(0 => 69);
					}
				}
				else {
					$despachos[] = "Sem despachos/Instru&ccedil;&otilde;es.";
				}
			}
			else {
				$despachos[] = "Sem despachos/Instru&ccedil;&otilde;es.";
			}
			
			// inicia uma linha
			//$table .= '<tr class="c" id="linhaDoc'.$linha.'" onmouseover="javascript:showDocPendAcoes('.$doc->id.')" onmouseout="javascript:hideDocPendAcoes('.$doc->id.')">';
			$table .= '<tr class="c" name="'.$doc->dadosTipo['nomeAbrv'].'" id="linhaDoc'.$linha.'">';
			$table .= '<td class="c" style="text-align: center;"><input type="checkbox" id="multiCheck'.$linha.'" name="multiCheck[]" value="'.$doc->id.'" '.$checkBoxClass.'></td>';
			$table .= '<td id="dataDesp'.$linha.'" name="'.$doc->dadosTipo['nomeAbrv'].'" class="c" style="display: none;">'.$dataDesp.'</td>';
			$table .= '<td class="c"><center><span id="id'.$linha.'">'.$doc->id.'</span></center></td>';
			$table .= "<td class=\"c\" title=\"".$despachos[$linha-1]."\" name=\"".$doc->id."\"><a id=\"".$linha."\" name=\"".$doc->dadosTipo['nomeAbrv']."\" onclick=\"window.open('sgd.php?acao=ver&docID=".$doc->id."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$doc->dadosTipo['nome']." ".$doc->numeroComp.'</a></td>';
			
			//preenche o emitente
			$emitente = explode(" - ",$doc->emitente);
			$emitenteF = $emitente[0];
			if(isset($emitente[1])) {
				$emitente = explode("/",$emitente[1]);
				$emitenteF = $emitente[count($emitente)-1];
			}
			if (verificaSigilo($doc) && !checkPermission(67))
				$table .= '<td class="c"><center></center></td>';
			else
				$table .= '<td class="c"><center>'.$emitenteF.'</center></td>';
			//preenche o assunto
			if (isset($doc->campos['assunto'])) {
				if (verificaSigilo($doc) && !checkPermission(67))
					$table .= '<td class="c"></td>';
				else
					$table .= '<td class="c"><center><span id="assunto'.$linha.'">'.$doc->campos['assunto'].'</span></center></td>';
			}
			else {
				$table .= '<td class="c"></td>';
			}
				
			// preenche empreendimento
			$obraList = $doc->getObras();
			if (count($obraList) > 0) {
				$sqlObra = "SELECT e.id, e.nome FROM obra_empreendimento AS e INNER JOIN obra_obra AS o ON e.id = o.empreendID WHERE ";
				foreach ($obraList as $o) {
					$sqlObra .= "o.id = ".$o['id']." OR ";
				}
				$sqlObra = rtrim($sqlObra, "OR ");
				$sqlObra .= ' GROUP BY e.id';
				$empreendList = $bd->query($sqlObra);
			}
			else {
				$empreendList = $doc->getEmpreend();
			}
			if ($empreendList != null) {
				$table .= '<td class="c">'; 
				foreach($empreendList as $empreend) {
					$table .= '<center><a onclick="window.open(\'sgo.php?acao=verEmpreend&empreendID='.$empreend['id'].'\',\'obra\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">'.$empreend['nome'].'</a></center> ';
				}
				$table .= '</td>';
			}
			//else $table .= '<td class="c" style="text-align:center;"><a href="javascript:void(0)" onclick="window.open(\'sgd.php?acao=atribObra&docID='.$doc->id.'&novaJanela=1\',\'doc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">[Atribuir empreendimento]</a></td>';
			else $table .= '<td class="c" style="text-align:center;"></td>';

			
			$table .= '<td class="c" style="display: none;">
						<div id="boxAcoes'.$doc->id.'" class="boxAcoes" style="display:none">
							A&ccedil;&otilde;es dispon&iacute;veis:
							<ul>';
			
			//preenche as acoes possiveis
			foreach ($acoes as $acao) {
				if ($acao){
					//$r = getAcao($acao);
					$r = $todasAcoes[$acao];
					if (!checkPermission($acao)) continue;
					if ($r['abrv'] == 'atribEmpreend') {
						if ($doc->dadosTipo['atribObra'] != 0) {
							continue;
						}
						if ($doc->dadosTipo['nomeAbrv'] == 'pr') { // se for processo, verifica o tipo e exibe opcoes
							// não é guardachuva e já tem atribuicao ? não exibe a opcao de atribuir
							if ($doc->campos['guardachuva'] != 1 && count($empreendList) > 0) continue; 
							if ($doc->campos['tipoProc'] == 'contrObr' || $doc->campos['tipoProc'] == 'plan' || $doc->campos['tipoProc'] == 'contrProj' || $doc->campos['tipoProc'] == 'acompTec' || $doc->campos['tipoProc'] == 'pagProj' || $doc->campos['tipoProc'] == 'outro') {
								$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=".$r['abrv']."&docID=".$doc->id."&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
							}
						}
						else { // nao é processo
							if (count($empreendList) > 0) continue; // já possui um empreendimento
							$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=".$r['abrv']."&docID=".$doc->id."&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
						}
					}
					elseif ($r['abrv'] == 'cadContr') {
						if (count($empreendList) <= 0) continue; // não possui empreendimento
						if ($doc->campos['tipoProc'] == 'contrObr' || $doc->campos['tipoProc'] == 'contrProj') {
							$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgo.php?acao=verEmpreend&empreendID=".$empreendList[0]['id']."&novoContrPr=".$doc->id."','obra','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
						}
					}
					elseif ($r['abrv'] == 'anexDoc') {
						if ($doc->anexado == 0) {
							$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=".$r['abrv']."&docID=".$doc->id."&onclick=anex&proc=true&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
						}
					}
					elseif ($r['abrv'] == 'resp') {
						$respAtiva = $doc->getRespAtiva();
						if ($respAtiva['idRespostaAtiva'] != false) {
							$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=ver&docID=".$respAtiva['idRespostaAtiva']."&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">Editar Doc. Inf.</a></li>";
						}
						else {
							$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=".$r['abrv']."&docID=".$doc->id."&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
						}
					}
					else {
						$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=".$r['abrv']."&docID=".$doc->id."&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$r['nome'].'</a></li>';
					}
				}
			}
			
			// clausula especial para RR
			if ($doc->labelID == 5) {
				$table .= "<li style=\"text-align:left; cursor: default;\"><a onclick=\"window.open('sgd.php?acao=anexDoc&docID=".$doc->id."&onclick=anex&novaJanela=1','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">Anexar a um Documento</a></li>";
				
			}
			
			//mostra linha para adicionar empreend
			if(isset($doc->campos['solicObra']) && $doc->campos['solicObra'] && checkPermission(11))
				$table .= '<li style="text-align:left; cursor: default;"><a href="sgo.php?acao=cadastrar&amp;docOrigemID='.$doc->id.'">Cadastrar Empreeendimento</a></li>';
			//fecha tags da linha
			$table .= '</div></td></tr>';
			
			
		} /* foreach */
		// gera tooltip
		if (count($despachos) > 0) {
			// gera div para dialogs
			$table .= '<div id="anex-confirm" title="Continuar anexa&ccedil;&atilde;o?"></div>';
			
			// javascript/jquery
			$table .= '<script type="text/javascript">$(document).ready(function() { ';
			
			// inicializa tooltip
			$table .= '
			$("td[title]").tooltip({
				 position: "center right",
				 effect: "slide",
				 onBeforeShow: function() {
				 	$(".tooltipAcoes").remove();
				 	this.getTrigger().closest("tr").addClass("active");
				 	$(".tooltip").append(\'<div class="tooltipAcoes"><br><br>\' +$("#boxAcoes"+this.getTrigger().attr("name")).html()+ \'</div>\');;
				 	$(".tooltip").draggable({ opacity: 0.5, containment: "body" });
				 },
				 onBeforeHide: function() {
				 	this.getTrigger().closest("tr").removeClass("active");
				 }
			 }).dynamic({ bottom: { direction: "down", bounce: true } });
			 ';
			
			// inicializa ordenador de tabela (tablesorter) - uk define o formato de data dd/mm/yyyy. caso não seja setado esse
			// parâmetro, o ordenador não funciona para datas nesse formato, pois ele espera formato de data americano
			$table .= '$("#docsPend").tablesorter({ sortList: [[1,1]], dateFormat: "uk" });
			';

			if (checkPermission(59)) {
				// inicializa drag & drop para anexos
				$table .= 'for (var i = 1; i <= '.$linha.'; i++) {
				';
				$table .= '
					$("#" + i).draggable({ revert: true, helper: "clone", start: function() { $(".tooltip").hide(); }, drag: function() { $(".tooltip").hide(); }, appendTo: "body" });
				';

				// percorre os tipos de documento
				foreach ($tipoDoc as $r) {
					$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = " .$r['id']. " AND aceitaAnexo = 1";
					$anexos = $bd->query($sql);
					$aceita = "";
					foreach($anexos as $a) {
						if ($aceita != "") $aceita .= ", ";
						$sql = "SELECT * FROM label_doc WHERE id = " .$a['tipoAnexoID'];
						$tipo = $bd->query($sql);
						if (count($tipo) > 0) $aceita .= "a[name=".$tipo[0]['nomeAbrv']."]";
					}
					if ($aceita != "") {
						$table .= '
						$("tr[name='.$r['nomeAbrv'].']").droppable({ hoverClass: "droppableHover", accept: "'.$aceita.'", activeClass: "droppable", tolerance: "pointer", drop: function(event, ui) { 
						';
						$table .= '
							var linha = $(this).attr("id").substring(8); var paiID = $("#id"+linha).html(); var linha_filho = $(ui.draggable).attr("id"); var filhoID = $("#id"+linha_filho).html();
						';
						$table .= '
							dragAnex(paiID, filhoID, linha, linha_filho, true); 
						}});
						';
						
					}
				}
			
				$table .= '
				}';
			}
			$table .= '});</script>';
		}

		
		//fecha as tags da tabela e retorna o codigo html da tabela
		$table .= '</tbody></table><br />';
		
		if (count($res) > 0) 
			$table .= showMultiDocAction(false);
		
		return $table;
	}
	
	function showMultiDocAction($top = true) {
		$html = '';
		
		$id = 'topSelectAction';
		if (!$top) $id = 'bottomSelectAction';
		
		if ($top)
			$html .= '<script type="text/javascript" src="scripts/sgd_multi.js"></script>';
		
		$despPerm = false;
		$solicPerm = false;
		$arqPerm = false;
		
		if (checkPermission(56))
			$despPerm = true;
		if (checkPermission(79))
			$solicPerm = true;
		if (checkPermission(69))
			$arqPerm = true;
			
		if (!$despPerm && !$solicPerm && !$arqPerm)
			return;
			
		$html .= '
		<center>Para todos os documentos selecionados, fazer: 
		<select id="'.$id.'">
			<option value="default" selected="selected">Selecione uma a&ccedil;&atilde;o...</option>';
		if ($despPerm)
			$html .= '
			<option value="despAll">Despachar</option>';
		
		if ($solicPerm)
			$html .= '
			<option value="solicArqAll">Solicitar Arquivamento</option>';
		
		if ($arqPerm)
		$html .= '
			<option value="arqAll">Arquivar</option>';
		
		$html .= '
		</select>
		</center>';
		
		if ($top) {
			$html .= '
			<div id="multiDespInterface" title="Despachar Documentos" style="display: none;">{$despInterface}</div>
			<div id="multiSolArqInterface" style="display: none;" title="Solicitar Arquivamento">{$solArqInterface}</div>
			<div id="multiArqInterface" style="display: none;">{$arqInterface}</div>
			';
			
			//$areas = getAreasFromUsers();
			/*$selectAreas = '<select id="para" name="para">';
			
			foreach ($areas as $a) {
				$selectAreas .= '<option value='.$a['area'].'>'.$a['area'].'</option>';
			}
			$selectAreas .= '</select>';
			
			$desp = '
			Despachar para:
			'.$selectAreas;*/
			
			$pseudoDoc = new Documento(0);
			
			$desp = showDesp('sf', getDeptos(), $pseudoDoc);
			
			$solArq = '';
			$arq = '';
			
			$html = str_replace('{$despInterface}', $desp, $html);
			$html = str_replace('{$solArqInterface}', $solArq, $html);
			$html = str_replace('{$arqInterface}', $arq, $html);
		}
		
		return $html;
	}
	
	/**
	 * @desc mostra os conteudos dos campos do documento (exceto emissor)
	 * @param Documento $doc documento passado por parametro
	 */
	function showDetalhes(Documento $doc){
		global $conf;
		global $bd;
		/**
		 * Solicitacao 002
		 */
		$colspan='2';
		//adiciona o titulo
		$html = '<script type="text/javascript">
		</script>
		<span class="headerLeft">Dados do Documento</span>';
		 
		//le os nomes dos campos desse tipo de documento
		$campos = explode(",", $doc->dadosTipo['campos']);
		//se o documento nao tiver campos, retorna mensagem
		if (!$campos[0])
			return $html."<br /><center><b>Não h&aacute; dados dispon&iacute;veis</b></center><br />";		
		//senao, comeca a montar a tabela
		$html .= '<table border="0" width="100%"><tr><td width=30%></td><td width=70%></td></tr>';
		$html .= '<tr class="c"><td class="c"><b>N&uacute;mero do Doc (CPO):</b> </td><td '.$colspan.' class="c"><span id="docID">'.$doc->id.'</span></td></tr>';
		
		$paiOwner = false;
		if ($doc->anexado == 1) {
			$docPai = new Documento($doc->docPaiID);
			$docPai->loadDados();
			if ($docPai->owner == $_SESSION['id'] || ($docPai->owner == -1 && $docPai->areaOwner == $_SESSION['area']))
				$paiOwner = true;
		}

		//mostra tabela com os dados deste tipo de documento
		foreach ($campos as $c) {
			if(strpos($doc->dadosTipo['emitente'],$c) === false){
				$c = montaCampo($c,'edt',$doc->campos);
				if ($c['nome'] == "docResp") {
					$docResp = new Documento($c['valor']);
					$docResp->loadCampos();
					$link = "<a onclick=\"window.open('sgd.php?acao=ver&docID=".$docResp->id."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$docResp->dadosTipo['nome']." ".$docResp->numeroComp.'</a>';
					$c['valor'] = $link;
				}
				if ($c['verAcao'] < 0 || ($c['verAcao'] > 0 && !checkPermission($c['verAcao'])))
					 continue;
				else {
					if (verificaSigilo($doc) && !checkPermission(67))
						break;
					else
						$html .= '<tr class="c"><td class="c"><b>'.$c['label'].':</b> </td><td '.$colspan.' class="c"><span id="'.$c['nome'].'_val">'.$c['valor'].'</span>';
				}
				//if((($doc->owner == $_SESSION['id'] || ($doc->owner == -1 && $doc->areaOwner == $_SESSION['area']) || verificaOwnerPai($doc)) && checkPermission(2) && $c['cod'] != '') || (checkPermission(3) && verificaOwnerPai($doc) && verificaDespachado($doc))){
				//var_dump($doc->verificaEditavel($c['nome']));
				if ($c['editarAcao'] < 0 || ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) || !$doc->verificaEditavel($c['nome'])) continue;
				$html .= '<form accept-charset="'.$conf['charset'].'" id="'.$c['nome'].'_form" action="javascript: editVal(\''.$c['nome'].'\')" method="post" style="display: inline">
				<span id="'.$c['nome'].'_edit" style="display:none;">
				'.$c['cod'].'
				</span>
				<input id="'.$c['nome'].'_link" class="buttonlink" type="submit" value="[editar]" />
				</form>';
				//}
				$html .= '</td></tr>';
			}
		}
		
		//mostra campos extras de documentos, obras e arquivos anexos
		if (isset($doc->campos["documento"]) && $doc->campos["documento"] != ''){
			if (!verificaSigilo($doc) || checkPermission(67)){
				$html .= '<tr class="c"><td class="c"><b>Documentos Anexos: </b></td><td '.$colspan.' class="c"> '.showDocAnexo($doc->getDocAnexoDet()).'</td></tr>';
			}
		}
		$colspan = 'colspan="2"';
		//se esse documento foi anexado a algum outro documento, mostra o documento pai
		if ($doc->anexado){
			$dp = new Documento($doc->docPaiID);
			$dp->loadTipoData();
			$html .= '<tr class="c"><td class="c"><b>Documento Pai:</b> </td><td '.$colspan.' class="c">'.showDocAnexo(array(array("id" => $dp->id, "nome" => $dp->dadosTipo['nome']." ".$dp->numeroComp))).'</td></tr>';
		}
		//se ha obra anexada, monta o campo pertinente
		//if (isset($doc->campos["obra"]) && $doc->campos['obra'] != 0)
		//	$html .= '<tr class="c"><td><b>Obra Ref:</b> </td><td>'.$doc->campos[$doc->campos["obra"]].'</td></tr>';
		
		//se ha template, podemos remontar o documento com as modificacoes
		if($doc->dadosTipo['template'] && ($paiOwner || ($doc->owner == $_SESSION['id'] || ($doc->owner == -1 && $doc->areaOwner == $_SESSION['area'])))) {
			$remontarDocLink = '<br /><a href="javascript:void(0)" onclick="javascript:remontarDoc()">[gerar documento novamente]</a>';
		} else {
			$remontarDocLink = '';
		}
		
		//mostra os arquivos anexos
		if (!verificaSigilo($doc) || checkPermission(67)){
			 $arqAnexo = substr(showArqAnexo($doc->anexo, $doc),0,-6);//removendo <br>
			 $html .= '<tr class="c"><td class="c"><b>Arquivos Anexos:</b> '.$remontarDocLink.' </td><td '.$colspan.' class="c" id="pdfAnexos" attr="unappendName">'.$arqAnexo.'</td></tr>';
		}
		$obraList = $doc->getObras(true);
		
		if (count($obraList) > 0) {
			$sqlObra = "SELECT e.id, e.nome FROM obra_empreendimento AS e INNER JOIN obra_obra AS o ON e.id = o.empreendID WHERE ";
			foreach ($obraList as $o) {
				$sqlObra .= "o.id = ".$o['id']." OR ";
			}
			$sqlObra = rtrim($sqlObra, "OR ");
			$empreendList = $bd->query($sqlObra);
		}
		else {
			$empreendList = $doc->getEmpreend();
		}
		
		if (count($empreendList) > 0) {
			$html .= '<tr class="c"><td class="c"><b>Associado ao(s) Empreendimento(s):</b> </td><td class="c">'; 

			$i = 0;
			foreach($empreendList as $empreend) {
				if ($i != 0) $html .= '<br />';
				$html .= '<a onclick="window.open(\'sgo.php?acao=verEmpreend&empreendID='.$empreend['id'].'\',\'obra\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">'.$empreend['nome'].'</a>';
				
				$i++;
			}
			
			$html .= '</td></tr>';
		}
			
		//retorna o cod html da tabela
		return $html."</table>";
	}
	
	/**
	 * @desc mostra os dados do emissor
	 * @param Documento $doc
	 */
	function showEmissor($doc){
		global $conf;
		//monta o cabecalho
		$html = '<span class="headerLeft">Dados do Emissor</span>';
		//inicializacao de variaveis
		$campo = array();
		$data = false;
		//se ha emitente para o documento
		if($doc->dadosTipo['emitente']) {
			//mostra os canpos relativos ao emitente
			$html .= '<table border="0" width="100%"><tr><td width=30%></td><td width=70%></td></tr>';
		} else {
			//senao mostra mensagem pertinente
			return $html."<br /><center><b>Não h&aacute; dados dispon&iacute;veis</b></center><br />";
		}
		//separa os nomes dos campos
		$campos = explode(",", $doc->dadosTipo['campos']);
		//para cada campo
		foreach ($campos as $c) {
			//pega os dados do campo
			$c = montaCampo($c, 'edt', $doc->campos);
			//verifica se o campo eh de emitente
			if (strpos($doc->dadosTipo['emitente'],$c['nome']) !== false){
				//se for, gera o codigo HTML
				$html .= '<tr class="c"><td class="c"><b>'.$c['label'].'</b>: </td><td class="c"><span id="'.$c['nome'].'_val">'.$c['valor'].'</span>';
				//if(($doc->owner == $_SESSION['id'] || $doc->criador == $_SESSION['id'] || ($doc->owner == -1 && $doc->areaOwner == $_SESSION['area'])) && checkPermission(2)  && $c['cod'] != ''){
				if ($c['editarAcao'] < 0 || ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) || !$doc->verificaEditavel($c['nome'])) continue;	
					$html .= '<form accept-charset="'.$conf['charset'].'" id="'.$c['nome'].'_form" action="javascript: editVal(\''.$c['nome'].'\')" method="post" style="display: inline">
					<span id="'.$c['nome'].'_edit" style="display:none;">
					'.$c['cod'].'
					</span>';
					if($c['tipo'] != 'userID')
						$html .= '<input id="'.$c['nome'].'_link" class="buttonlink" type="submit" value=" [editar]" />';
					$html .= '</form>';
				//}
				$html .= '</td></tr>';
				$data = true;
			}
		}
		//retorna codigo HTML
		return $html.'</table>';
	}
	
	/**
	 * Mostra o historico do documento
	 * @param Documento $doc
	 */
	/*function showHist($doc){
		//cria o cabecalho
		$html = '<span class="headerLeft">Hist&oacute;rico do Documento</span>';
		//le o hitorico do documento
		$res = $doc->getHist();
		//se nao houver entrada de historico, avisa nao ha historico
		if (count($res) == 0) {
			return $html."<center><b>Nenhum dado dispon&iacute;vel.</b></center><br />";
		//se nao, cria a tabela de historico
		}else{
			//tags de inicio da tabela
			$html .= '<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<tr><td width="100" class="cc"><b>data</b></td><td width="100" class="cc"><b>usu&aacute;rio</b></td><td class="cc"><b>a&ccedil;&atilde;o</b></td></tr>'; 
			//para cada entrada no historico
			foreach ($res as $r) {
				//print_r($r);
				//cria uma linha para este documento
				if($r['tipo'] == 'criacao') {
					$acao = 'Criou este documento.';
				} elseif ($r['tipo'] == 'obs') {
					if ($r['despacho'] == "") continue;
					$acao = 'Adicionou observa&ccedil;&atilde;o a este documento.';
				} elseif ($r['tipo'] == 'entrada') {
					$acao = 'Registrou entrada deste documento de '.$r['unidade'];
				} elseif ($r['tipo'] == 'saida') {
					$acao = 'Registou saida deste documento para '.$r['unidade'];
				} elseif ($r['tipo'] == 'despIntern') {
					$acao = 'Despachou este documento para '.$r['unidade'];
				} elseif ($r['tipo'] == 'arq') {
					$acao = 'Arquivou este documento.';
				} elseif ($r['tipo'] == 'desarq') {
					$acao = 'Desarquivou este documento.';
				} elseif ($r['tipo'] == 'solic') {
					$acao = 'Solicitou este documento.';
				} elseif ($r['tipo'] == 'solicArq') {
					$acao = 'Solicitou arquivamento deste documento.';
				} elseif ($r['tipo'] == 'solicDes') {
					$acao = 'Solicitou desarquivamento deste documento.';
				} elseif ($r['tipo'] == 'solicProt') {
					$acao = 'Solicitou o documento externamente.';
				} else {
					$acao = $r['acao'];
				}
				$nome_usuario = getUserFromUsername($r['username']);
				//cria uma linha para este documento
				$html .= '<tr class="c" style="cursor:pointer;" ';
				if($r['despacho']) $html .= 'onclick="showDesp('.$r['id'].')"';
				$html .= '><td class="cc">'.$r['data'].'</td><td class="cc" >'.$nome_usuario[0]['nome'].'</td><td class="c">'.SGDecode($acao).'</td></tr>';
				
				if($r['despacho']){
					$html .= '<tr id="desp'.$r['id'].'" class="c" style="display:none"><td class="c" colspan="3"><b>'.$r['label'].'</b>: '.SGDecode($r['despacho']).'</td></tr>';
				}
				/*if($r['despacho']){
					//se ha despacho, cria a linha de despacho
					$html .= '<tr class="c"><td class="cc" style="border: 0;">'.$r['data'].'</td><td class="cc" style="border: 0;">'.$r['username'].'</td><td class="c" style="border: 0;">'.$r['acao'].'</td></tr>';
					$html .= '<tr class="c"><td class="c" colspan="3"><b>Despacho: </b>'.$r['despacho'].'</td></tr>'; 
				} else {
					//senao, apenas cria a linha de acao
					$html .= '<tr class="c"><td class="cc">'.$r['data'].'</td><td class="cc">'.$r['username'].'</td><td class="c">'.$r['acao'].'</td></tr>';
				}*/
			/*}
			//fecha tag para tabela
			$html .= '</table>';
		}
		//retorna o codigo HTMl da tabela gerada
		return $html;
	}*/
	
	/**
	 * mostra as respostas para o documento passado por parametro
	 * @param Document $doc
	 * @param BD $bd
	 * @return html
	 */
	function showRespostas(Documento $doc, BD $bd) {
		global $conf;
		
		if ($doc == null) return ""; // nao há o que exibir
		if ($doc->dadosTipo['nomeAbrv'] == "resp") {
			$html = '<script type="text/javascript">';
			$html .= '$(document).ready(function() {';
			$html .= '$("#c3").hide();';
			$html .= '});</script>';
			return $html;
		}
		// cria o cabecalho
		$html = '<span class="headerLeft">&Uacute;timas Informa&ccedil;&otilde;es / Respostas</span>';
		//return;
		$resps = $doc->getResp();
		
		if (count($resps) == 0) { // se nao encontrou nenhuma resposta
			return $html."<center><b>Nenhuma resposta dispon&iacute;vel.</b></center><br />";
		}
		else {
			// comeca a montar a tabela
			$html .= '<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<tr><td width="100" class="cc"><b>data</b></td><td width="50" class="cc"><b>de:</b></td><td class="cc"><b>para:</b></td><td class="cc"><b>doc. inf.:</b></td><td class="cc"><b>despachado em:</b></td></tr>';
			
			foreach($resps as $r) {
				// comeca nova linha
				$html .= '<tr class="c">';
				// preenche data e username
				$html .= '<td class="cc">'.date("j/n/Y G:i",$r['data']).'</td><td class="cc">'.$r['username'].'</td>';
				if ($r['destinatario'] != "") {
					$destLabel = explode("- ", $r['destinatario']);
					if (count($destLabel) > 1) $html .= '<td class="cc">'.$destLabel[1].'</td>';
					else $html .= '<td class="cc">'.$r['destinatario'].'</td>';
				}
				else $html .= '<td class="cc" >--</td>';
				// preenche com link para resposta
				$docResp = new Documento($r['respID']);
				$docResp->loadCampos();
				$html .= "<td class=\"cc\"><a onclick=\"window.open('sgd.php?acao=ver&docID=".$docResp->id."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">nº ".$docResp->numeroComp.'</a></td>';
				// preenche com despacho
				
				$sql = "SELECT * FROM data_historico WHERE docID = " .$doc->id. " AND tipo = 'saida' AND data >= " .$r['data']. " ORDER BY data";
				$resDesp = $bd->query($sql);
				if (count($resDesp) > 0) $despachado = date("j/n/Y G:i",$resDesp[0]['data']);
				else $despachado = "Ainda na CPO.";
				
				$html .= '<td class="cc" style="text-align: left">'.$despachado.'</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		
		return $html;
	}
	
	/**
	 * @desc mostra os documentos anexos com os ids passados por parametro
	 * @param array $anexos
	 * @param connection $bd
	 */
	function showDocAnexo($anexos){
		global $conf;
		
		//inicializacao de variaveis
		$html = "";
		//se nao houver anexos, nao retorna nada.
		if($anexos == '')
			return '';
		//para cada anexode um documento
		/**
		 * Solicitacao 002
		 */
		 $anex = array();
		if(count($anexos)>0){
			$doc = new Documento($anexos[0]['id']);
			$doc->loadCampos();
			$doc->loadDados();
			$docPai = $doc->getDocPai();
			if($docPai)
				$anex = $docPai->getAnexos();//esse traz os anexos pelo historico
		}
		foreach ($anexos as $a) {
			//cria um link para visualizar esse doc anexo.
			/**
			 * Solicitacao 002
			 * mudamos $html .= "<a onclick=\"window.open('sgd.php?acao=ver&docID=".$a['id']."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">Documento ".$a['id'].": ".$a['nome']."</a><br />";
			 * pelo abaixo
			 */
			$unappendDialog = '<div style="display:none;" attr="unappendDialog"><p>Atenção ao desanexar o arquivo voltará ao usuário que criou</p></div>';
			$button = '<span id="button_'.$a['id'].'" class="" title="Remover anexo '.$a['id'].'""><img src="./img/delete.ico" width="14px" height="14px"/></span>';
			$display="none";
			foreach($anex as $v){	
				if($v->getId()==$a['id']){
					if($v->getOwner()==$_SESSION['id'])
						$display="block";
				}
			}
			$html .= "<div id='doc_".$a['id']."' class='unappendDocA' style='width:97%;float:left;'><a onclick=\"window.open('sgd.php?acao=ver&docID=".$a['id']."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">Documento ".$a['id'].": ".$a['nome']."</a></div><div id='anexo_".$a['id']."' class='unappendDoc' style='display:".$display.";text-align:right;width:3%;float:right;'>".$button.$unappendDialog."</div>";
		}
		//retorna o cod HTML a tabela gerada
		return $html;
	}
	
	/**
	 * @desc monta o menu lateral do visualizador de doc
	 * @param Documento $doc
	 * @param BD $bd
	 */
	function showAcoes(Documento $doc){
		global $conf;
		$doc->loadDados();
		//inicializacao de variaveis.
		$html = '<script type="text/javascript" src="scripts/menu_mini.js?r={$randNum}"></script>
		<a href="sgd.php?acao=ver&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Ver Detalhes</span></a><br />';
		
		$paiOwner = false;
		if ($doc->anexado == 1) {
			$docPai = new Documento($doc->docPaiID);
			$docPai->loadDados();
			if ($docPai->owner == $_SESSION['id'] || ($docPai->owner == -1 && $docPai->areaOwner == $_SESSION['area']))
				$paiOwner = true;
		}
		
		//se o usuario eh dono do documento e ele tem permissao para despachar
		if ((($doc->owner == $_SESSION['id'] || ($doc->owner == -1 && $doc->areaOwner == $_SESSION['area']))) && !$doc->anexado)  {
			//mostra o link para depsachar
			if ($doc->dadosTipo['nomeAbrv'] != "resp")
				$html .= '<a href="sgd.php?acao=desp&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Despachar</span></a><br />';
			//demais acoes
			$acoes = explode(",", $doc->dadosTipo['acoes']);
			//para cada acao
		
			if($doc->dadosTipo['atribObra'] == '1'){
				array_push($acoes, '5');
			}
			
			foreach ($acoes as $acao){
				//le os dados da acao do BD
				if($acao){
					$res = getAcao($acao);
					if($doc->dadosTipo['atribObra'] == '1' && $res[0]['abrv'] == 'atribEmpreend')
						continue;
					if (strpos($res[0]['nome'], "Despachar") !== false) continue; // para não aparecer 2 despachar no menu
					//verifica se tem permissao para faze-la
					if($_SESSION['perm'][$acao]) {
						if ($res[0]['abrv'] == "cadProcSap" && $doc->anexado != false) continue;
						elseif ($res[0]['id'] == 13) continue;
						elseif ($res[0]['abrv'] == "anexDoc" && $doc->anexado == 0) $html .= '<a href="sgd.php?acao='.$res[0]['abrv'].'&docID='.$doc->id.'&onclick=anex&proc=true&novaJanela=1"><span class="menuHeader">'.$res[0]['nome'].'</span></a><br />';
						elseif ($res[0]['abrv'] == "anexDoc" && $doc->anexado != 0) continue;
						elseif ($res[0]['abrv'] == "cadContr") {
							$empreendList = $doc->getEmpreend();
							if (count($empreendList) <= 0) continue;
							if ($doc->campos['tipoProc'] != 'contrObr' && $doc->campos['tipoProc'] != 'contrProj') continue;
							$html .= "
								<a onclick=\"window.open('sgo.php?acao=verEmpreend&empreendID=".$empreendList[0]['id']."&novoContrPr=".$doc->id."','obra','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\"><span class=\"menuHeader\">".$res[0]['nome'].'</span></a><br />';
						}
						else {
							//adiciona link para a acao no menu
							$html .= '<a href="sgd.php?acao='.$res[0]['abrv'].'&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">'.$res[0]['nome'].'</span></a><br />';
						}
					}
				}
			}
		
			if ($doc->dadosTipo['nomeAbrv'] == 'rr' && $doc->anexado == 0) $html .= '<a href="sgd.php?acao=anexDoc&docID='.$doc->id.'&onclick=anex&novaJanela=1"><span class="menuHeader">Anexar Documento</span></a><br />';
			
			// clausulas especiais para arquivamento
			if ($doc->arquivado == 0 && checkPermission(69))
				$html .= '<a href="sgd.php?acao=arquivar&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Arquivar</span></a><br />';
				
			if ($doc->areaOwner == 0 && checkPermission(79))
				$html .= '<a href="sgd.php?acao=solArq&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Solicitar Arquivamento</span></a><br />';
		}
		
		// caso o doc seja do tipo resposta e o usuário é dono dele, verifica se esta resposta está ativa 
		if ($doc->dadosTipo['nomeAbrv'] == "resp" && $paiOwner) {
			$docPai = new Documento($doc->docPaiID);
			$docPai->loadCampos();
			$respAtiva = $docPai->getRespAtiva();
			if ($respAtiva['idRespostaAtiva'] != false && $respAtiva['idRespostaAtiva'] == $doc->id) // se ele for a resposta ativa, dá a possibilidade ao usuario de anexar arquivos a ele.
				$html .= '<a href="sgd.php?acao=anexArq&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Anexar Arquivo</span></a><br />';
		}
		
		if($doc->dadosTipo['nomeAbrv'] == "memo" && $doc->verificaEditavel("assunto") && checkPermission(13))
			$html .= '<a href="sgd.php?acao=anexArq&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Anexar Arquivo</span></a><br />';
		
		// clausulas especiais para documentos que não estão na cpo ou com a pessoa
		if ($doc->owner == 0 && !$doc->anexado && $doc->dadosTipo['nomeAbrv'] != 'rr') {
			if ($doc->arquivado == 1) {
				if (checkPermission(70)) $html .= '<a href="sgd.php?acao=arquivar&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Desarquivar</span></a><br />';
				if (checkPermission(80)) $html .= '<a href="sgd.php?acao=solDesarq&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Solicitar Desarquivamento</span></a><br />';
			}
			elseif ($doc->arquivado == 0) {
				$html .= '<a href="sgd.php?acao=entrada&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Registrar Entrada deste Processo</span></a><br />';
				if ($doc->dadosTipo['nomeAbrv'] == 'pr')
					$html .= '<a href="sgd.php?acao=solDoc&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Requisitar este Processo</span></a><br />';
			}
		}
		else {
			if ($doc->arquivado == 1 && checkPermission(70))
				$html .= '<a href="sgd.php?acao=arquivar&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Desarquivar</span></a><br />';
			elseif ($doc->arquivado == 1 && checkPermission(80))
				$html .= '<a href="sgd.php?acao=solDesarq&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Solicitar Desarquivamento</span></a><br />';
		}
		
		if ($doc->dadosTipo['nomeAbrv'] == "contr") {
			$html .= '<a href="sgd.php?acao=geraREP&docID='.$doc->id.'&novaJanela=1"><span class="menuHeader">Gerar Registro de Entrada de Protocolo</span></a><br />';
		}

		//retorna o codigo HTML das acoes para o documento
		return $html;
	}
	
	/**
	 * mostra os campos para anexar arquivo
	 */
	function showAnexar($tipo = "f", $doc = null){
		global $conf;
		//inicializacao de variavele
		$html = '';
		//se nao for cadastro de documento, coloca campo oculto com o id pra prox pagina
		if($doc != null && $doc->id != 0) $html .= '
		<form accept-charset="'.$conf['charset'].'" id="fileUpForm" action="sgd.php?acao=anexArq&docID='.$doc->id.'&feedback" method="post" enctype="multipart/form-data">
		<input type="hidden" name="id" value="'.$doc->id.'" />';
		//inclui HTML do formulario para upload\
		$html .= '<div id="fileUpCell">
		<div id="arqs"></div>
		<input type="file" id="arq1" name="arq1" onclick="showInputFile(2)" />
		</div>';
		//se o tipo de exibicao for o formulario completo, coloca o botao enviar
		if($tipo == "f") $html .= '<input type="submit" value="Enviar" id="sendFiles" />
		</form>';
		//retorna HRML do form
		return $html;
	}
	
	/**
	 * Monta e mostra a seção de despacho para um documento passado por parametro
	 * @param Documento $doc
	 */
	function showDesp($tipo = "f", $deptos , Documento $doc = null, $resp = false){
		global $conf;
		//inf nao pode ser despachada logo de cara, entao coloca os campos ocultos
		if($resp)
			return '
			<!--<script type="text/javascript" src="scripts/jquery.autocomplete.js?r={$randNum}"></script>
			<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />-->
			<input type="hidden" name="para" id="para" value="">
			<input type="hidden" name="outro" id="outro">
			<input type="hidden" name="despExt" id="despExt">
			<input type="hidden" name="funcID">
			<input type="hidden" name="despacho" />';
		
		//doc ja esta anexado a aoutro e nao pode ser despachado
		if($doc != null && $doc->id != 0 && $doc->anexado)
			return '<b>Esse documento j&aacute; est&aacute; anexado a outro e n&atilde;o pode ser despachado.</b>';
		//montagem do form
		/*$html = '
		<script type="text/javascript" src="scripts/jquery.js"></script>*/
		$html = '<!--<script type="text/javascript" src="scripts/jquery.autocomplete.js?r={$randNum}"></script>
		<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />-->
		<script type="text/javascript" src="scripts/despacho.js?r={$randNum}">		
		</script>';
		//se for tipo formulario completo, cria as tags de form
		if($tipo == "f") $html.= '<span class="headerLeft">Despachar Documento</span>
		<form accept-charset="'.$conf['charset'].'" action="sgd.php?acao=despachar" method="post" id="despachoForm" enctype="multipart/form-data">';
		//cria select box para despacho
		$html .= '<b>Despachar para:</b> ';
		$html .= '<span id="camposDespacho">';
		// verifica se este doc foi requisitado por alguem
		if ($doc != null && $doc->solicitante != null && $doc->solicitante != "0") {
			// despacho automatico para o solicitante...
			// pega dados do solicitante, e cria campos invisíveis para passar esses dados
			$user = getUserFromUsername($doc->solicitante);
			$user = $user[0];
			$html .= $user['nomeCompl'] .'<br />';
			$html .= '<input type="hidden" name="para" id="para" value="'.$user['area'].'">';
			$html .= '<input type="hidden" name="outro" id="outro">';
			$html .= '<input type="hidden" name="despExt" id="despExt">';
			$html .= '<input type="hidden" name="funcID" value="'.$user['id'].'"><br />';
			// campo especial para controle do js
			$html .= '<input type="hidden" name="solicitante" id="solicitante" value="'.$user['nomeCompl'].'" disabled="disabled">';
		}
		else { // nao foi requisitado, então segue fluxo normal
			$html .= '<br />
			<select id="para" name="para">
			<option selected> --Selecione-- </option>';

			// verifica se o usuario tem permissao para despachar para fora
			if(checkPermission(62)) {
				$html .= '<option name="" disabled style="background-color: #808080; color:white">-> Solicitante</option>
				<option id="solic" value="solic">Solicitante</option>';
			}
			$html .= '<option name="" disabled style="background-color: #808080; color:white">-> CPO</option>';
			foreach ($deptos as $dep) {
				$html .= '<option id="'.$dep.'" value="'.$dep.'">'.$dep.'</option>';
			}
			// verifica se o usuario tem permissao para despachar para fora
			if(checkPermission(62)) {
				$html .= '<option name="" disabled style="background-color: #808080; color:white">-> Outra Unidade</option>
				<option id="ext" value="ext">Outra Unidade/&Oacute;rg&atilde;o</option>
				<option name="" disabled style="background-color: #808080; color:white">-> Outro</option>
				<option id="outr" value="outro">Outro</option>';
			}
			$html .= '</select><br />
			<select id="subp" name="funcID"></select>
			<input type="text" size=25 name="outro" id="outro" />
			<input type="text" size=100 id="despExt" name="despExt" autocomplete="off" /><br />';
		}
		$html .= '</span><div id="despAlerta" title="Despacho autom&aacute;tico"></div>';
		
		//cria input para levar o id par aa prox pagina
		if($doc != null && $doc->id != 0) $html .= '<input type="hidden" name="id" value="'.$doc->id.'" />';
		if($tipo == "f" || $tipo == "sf") $html .= '<b>Instruir: </b><textarea id="despacho" name="despacho" rows="4" style="width:98%;"></textarea><br />
												   ';// . showAnexar('nf');
		if($tipo == "f") $html.= '<input type="submit" value="Despachar" />
		</form>';
		
		//retorna o codigo html do form
		return $html;
	}
	
	function showEntradaForm($deptos, Documento $doc){
		global $conf;
		$html = '<span class="headerLeft">Despachar Documento</span>
		<form accept-charset="'.$conf['charset'].'" action="sgd.php?acao=despachar&entrada=1" method="post">';
		$html .= showDesp("sf",$deptos, $doc);
		$html .= '<span style="color: #BE1010; font-weight: bold;">Rela&ccedil;&atilde;o de Remessa de Entrada</span><br />';
		$html .= showReceb();
		$html .= '<br />
		<center><input type="submit" value="Enviar" /></center> 
		</form>';
		return $html;
	}
	/**
	 * Mostra formulario de Cadastramento/Criacao de documento
	 * @param string $acao
	 * @param string $tipo
	 * @param connection $bd
	 * @param int $sapID (opcional -> apenas para acao = cadProcSap)
	 */
	function showForm($acao,$tipo,$novaJanela,$bd,$sapID = null, $restaurar = false){
		global $conf;
		//define arquivo de template
		if($tipo == 'rep') {
			$template = "templates/template_cad_rep.php";
		} else {
			$template = "templates/template_".$acao.".php";
		}
		if ($acao == "novo_it") $acao = "novo";
		if ($acao == "cadContr") $acao = "cad";
		//if ($acao == "cadProcSap") $template = "templates/template_cad.php"; 
		//carrega arquivo de template
		$html = file_get_contents($template);
				
		//monta os campos de busca
		$dados = getDocTipo($tipo);
		
		//variaveis que vao guardar os campos gerais e de busca e emitente
		$cGeral = '';
		$cBusca = '';
		$cGeralNome = '';
		$cEmitente =  '';
		$conteudo = '';
		$cBuscaNomes = ''; 
		
		//(integracao OBRAS) gera campo para avisar a proxima pagina que ela deve exportar o docID para pagina pai
		if(($_GET['acao'] == 'novo_mini' || $_GET['acao'] == 'cad_mini') && isset($_GET['targetInput'])) {
			$cGeral .= '<input type="hidden" name="targetInput" id="targetInput" value="'.$_GET['targetInput'].'" />';
		}
		
		//gera campos para cadastro
		if (($acao == "cad") || ($acao == "cadProcSap")){
			//separa os campos
			$campos = explode(",", $dados[0]['campos']);
			
			//para cada campo do documento
			foreach ($campos as $c) {
				//monta o HTML do campo
				$c = montaCampo($c, 'cad',$dados);
				
				$c['nomeCampo'] = explode(",",$c['nome']);
				if(strpos($dados[0]['campoBusca'], $c['nomeCampo'][0]) === false){
					//nao eh campo de busca, cria o input na parte de campos
					if($cGeralNome) $cGeralNome .= ",";
					$cGeralNome .= $c['nome'];
					if ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) {
						$cGeral .= '<tr class="c" style="display: none;"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
					}
					else {
						$cGeral .= '<tr class="c"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
					}
					
				}else{
					//eh campo de busca, cria o input na area de busca
					$cBusca .= '<b>'.$c['label'].':</b> '.$c['cod'];
					$cBuscaNomes .= $c['nome'].',';
					//cria inputs ocultos no campo geral para passar as infos de busca para prox pagina
					foreach (explode(",",$c['nome']) as $campo) {
						$cGeral .= '<input type="hidden" id="_'.$campo.'" name="_'.$campo.'" value="" />';
					}
				}
			}
			//tira a virgula do final
			$cBuscaNomes = rtrim($cBuscaNomes,",");
			
			//adicao dos campos ocultos
			$cBusca .= '
			<input type="hidden" name="labelID" id="labelID" value="'.$dados[0]['id'].'" />
			<input type="hidden" name="tabBD" id="tabBD" value="'.$dados[0]['tabBD'].'" />';
			if ($acao != "cadProcSap") $cBusca .= '<input type="hidden" name="camposBusca" id="camposBusca" value="'.$cBuscaNomes.'" />';
			
			//adicao da tabela para alinhar os campos gerais
			$cGeral .= '<input type="hidden" name="tipoDocCad" id="tipoDocCad" value="'.$tipo.'" /> 
			<input type="hidden" name="camposBusca" id="camposBusca" value="'.$cBuscaNomes.'" />
			<input type="hidden" name="id" id="id" value="0" />';
			if ($acao != "cadProcSap") 
				$cGeral .= '<input type="hidden" name="action" id="action" value="'.$acao.'" />';
			else 
				$cGeral .= '<input type="hidden" name="action" id="action" value="cad" /><input type="hidden" name="sapID" id="sapID" value="'.$sapID.'" />'; 
			$cGeral .= '<input type="hidden" name="camposGerais" id="camposGerais" value="'.$cGeralNome.'" />';
			//coloca osinputs dentro da tabela
			$cGeral = '<table width="80%" border="0">'.$cGeral.'</table>';
		//cria os inpouts para novo documento
		}elseif ($acao == "novo" || $acao == "novo_it"){
			//carrega o template
			$html = file_get_contents($template);
			
			//separa os campos
			$campos = explode(",", $dados[0]['campos']);
			
			//separa os campos e cria os inputs
			foreach ($campos as $c) {
				$c = montaCampo($c, 'cad');
				//if(strpos($dados[0]['emitente'], $c['nome']) === false){
					//nao eh campo de emitente, cria o input na parte de campos
					if($c['nome'] == 'conteudo')
						$conteudo = '<tr class="c"><td colspan="2"><b>'.$c['label'].':</b><br />'.$c['cod'].'</td></tr>';
					elseif ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) {
						$cGeral .= '<tr class="c" style="display: none;"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
					}
					elseif (stripos($c['label'], "N&uacute;mero") !== false || stripos($c['label'], "Ano") !== false) {
						if ($tipo == "ofi" || $tipo == "memo" || $tipo == "resp" || $tipo == "it") {
							$cGeral .= '<tr class="c" style="display: none;"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
						}
						else {
							$cGeral .= '<tr class="c"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
						}
					}
					else 
						$cGeral .= '<tr class="c"><td style="width: 50%;"><b>'.$c['label'].':</b></td><td style="width: 50%;">'.$c['cod'].'</td></tr>';
						
				//}else{//eh campo emitente, cria o campo (se houver) na parte lateral
					//eh campo de busca, cria o input na area de busca
					//$cEmitente .= '<b>'.$c['label'].':</b> '.$c['cod'];
				//}
			}
			$cGeral .= $conteudo;
			//cria campos ocultos adicionais
			$cGeral = '
			<input type="hidden" name="tipoDocCad" id="tipoDocCad" value="'.$tipo.'" />
			<input type="hidden" name="id" id="id" value="0" />
			<input type="hidden" name="action" id="action" value="'.$acao.'" />
			<table width="100%" border=0>'.$cGeral.'</table>';
			if ($tipo == "it") {
				$cGeral = '<input type="hidden" name="empreendID" id="empreendID" value="{$empreendID}">' . $cGeral;
			}
		}
		//cria o campo para  historico
		$historico = '<div id="hist" class="cadDisp"></div>';
		//cria campo para anexar documentos
		/*$documentos = '
		<b>Documentos Anexos:</b><br />
		<div id="docsAnexosNomes" class="cadDisp"></div><input type="hidden" name="docsAnexos" id="docsAnexos" />
		<a id="addDocLink" href="#" onclick="window.open(\'sgd.php?acao=busca_mini&onclick=adicionar&target=docsAnexos\',\'addDoc\',\'width=750,height=550,scrollbars=yes,resizable=yes\')">';
		//coloca os campos no documento caso seja possivel adicionar documentos
		if($dados[0]['docAnexo']) $documentos .= 'Adicionar Documento';
		$documentos .= '</a><br /><br />';
		//ccria campos para adicionar obras
		$obra = '
		<b>Obra Ref:</b><br />
		<div id="obra" class="cadDisp"></div><input type="hidden" name="obrasAnexas" id="obrasAnexas" />
		<a id="addObraLink" href="#" onclick="window.open(\'\',\'addDoc\',\'width=750,height=550,scrollbars=yes,resizable=yes\')">';
		//coloca os campos no documento caso seja possivel adicionar obras
		if($dados[0]['obra']) $obra .= 'Adicionar Obra';
		$obra .= '</a><br /><br />';
		//cria campos para adicionar empresa
		$empresa = '
		<b>Empresa Ref:</b><br />
		<div id="empresa" class="cadDisp"></div><input type="hidden" name="emprAnexas" id="emprAnexas" />
		<a id="addEmpresaLink" href="#" onclick="window.open(\'empresa.php?acao=buscar&onclick=adicionar\',\'addEmpr\',\'width=750,height=550,scrollbars=yes,resizable=yes\')">';
		//coloca os campos dos documentos para adicionar empresa
		if($dados[0]['empresa']) $empresa .= 'Adicionar Empresa';
		$empresa .= '</a><br /><br />';*/
		//coloca codigo de recebimento
		$recebimento = showReceb();		
		//coloca os elementos no template nas posicoes corretas
		$html = str_replace('{$nova_janela}', $novaJanela, $html);
		$html = str_replace('{$campos_busca}', $cBusca, $html);
		$html = str_replace('{$campos}', $cGeral, $html);
		$html = str_replace('{$emitente}', $cEmitente, $html);
		//$html = str_replace('{$documentos}', $documentos, $html);
		//$html = str_replace('{$obra}', $obra, $html);
		//$html = str_replace('{$empresa}', $empresa, $html);
		$html = str_replace('{$anexarArq}', showAnexar('sf'), $html);
		$html = str_replace('{$historico}', $historico, $html);
		$html = str_replace('{$recebimento}', $recebimento, $html);
		if($tipo == 'resp') {
			$html = str_replace('input type="submit" value="Enviar"', 'input type="submit" value="Salvar Inf."', $html);
			$html = str_replace('{$despacho}', showDesp('sf',getDeptos(),null, true), $html);
		} else {
			if (checkPermission(101)) {
				$html = str_replace('{$despacho}', showDesp('sf',getDeptos(),null, false), $html);
			}
			else {
				//$html = str_replace('type="submit" value="Enviar"', 'type="submit" value="Salvar"', $html);
				$html .= '
				<script type="text/javascript">
					$(document).ready(function() {
						$("input[type=submit]").filter("[value=\'Enviar\']").each(function() {
							$(this).val("Salvar");
						});
					});
				</script>
				';
				
				
				$html = str_replace('{$despacho}', showDesp('sf',getDeptos(),null, true), $html);
			}
		}
		
		
		if ($tipo != 'it') {
			$html = '<script type="text/javascript" src="scripts/sgd_autosave.js?r={$randNum}"></script>' . $html;
			if ($restaurar) {
				if ($acao != 'cadProcSap')
					$html .= '<script type="text/javascript">$(document).ready(function() { restaurarDoc("'.$acao.'Form"); });</script>';
				else
					$html .= '<script type="text/javascript">$(document).ready(function() { restaurarDoc("cadForm"); });</script>';
			}
			else {
				if ($acao != 'cadProcSap') 
					$html .= '<script type="text/javascript">$(document).ready(function() { openForm("'.$acao.'Form"); });</script>';
				else
					$html .= '<script type="text/javascript">$(document).ready(function() { openForm("cadForm"); });</script>';
			}
		}
		else {
			//var_dump($restaurar);
			if ($restaurar)
				$html .= '<script type="text/javascript">$(document).ready(function() { restaurarDoc("formFase"); });</script>';
			else
				$html .= '<script type="text/javascript">$(document).ready(function() { openForm("formFase"); });</script>';
			
		}
		
		// codificacao do formulario
		$html = str_replace('{$charset}', $conf['charset'], $html);
		
		//retorna o cod HTML do formulario
		return $html;
	}
	/**
	 * Monta os campos de recebimento
	 */
	function showReceb() {
		//adiciona campo para numero da RR de entrada e unidade de origem
		// old auto-complete $("#unOrgReceb").autocomplete("unSearch.php",{minChars:2,matchSubset:1,matchContains:true,maxCacheLength:20,extraParams:{\'show\':\'un\'},selectFirst:true,onItemSelect: function(){$("#unOrgReceb").focus();}});
		$html = '<b>n&deg; Rela&ccedil;&atilde;o de Remessa:</b>
		<input type="text" id="rrNumReceb" name="rrNumReceb" size="3" maxlength="5" />/<input type="text" id="rrAnoReceb" name="rrAnoReceb" size="2" maxlength="4" value="'.date("Y").'" />
		<br />
		<b>Un/Org Expedidor:</b> <input type="text" id="unOrgReceb" name="unOrgReceb" size="60" />
		<script type="text/javascript">
		$(document).ready(function(){
			$("#unOrgReceb").autocomplete({
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
								alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
							}
						}
						
						response(data);
					});
				},
				minLength: 2,
				autoFocus: true,
				select: function(){
					$("#unOrgReceb").focus();
				}
			});
			
			
			$("#unOrgReceb").keyup(function(){
				var v = $("#unOrgReceb").val();
				v = v.replace(/\./g,""); 
					
				var expReg  = /^[0-9]{2,12}$/i;
					
				if (expReg.test(v)){
					var i, vn="";
					for(i=0 ; i<v.length ; i++){
						if(i%2 == 0 && i != 0)
							vn += ".";
						vn += v[i];
					}				
					$("#unOrgReceb").val(vn);
				}
			});	
		});</script>';
		//retorna HTML dos campos
		return $html;
	}
	
	/**
	 * Mostra links para visualizacao dos documentoa anexos
	 * @param array $anexos
	 * @param Documento $doc
	 */
	function showArqAnexo($anexos, Documento $doc){
		global $conf;
		
		//inicializacao de variaveis
		$html = '';
		
		$procura = '['.$doc->id.']_'.$doc->dadosTipo['nome'].'_'.$doc->numeroComp;
		$procura = strtolower($procura);
		$procura = str_replace(array('/','ç','á','ã','â','ê','é','í','ó','õ','ô','ú','&ccedil;','&aacute;','&atilde;','&acirc;','&ecirc;','&eacute;','&iacute;','&oacute;','&otilde;','&ocirc;','&uacute',' ','?','\'','"','!','@',"'","%"), array('-','c','a','a','a','e','e','i','o','o','o','u','c','a','a','a','e','e','i','o','o','o','u','_','','','','','','','_'), $procura);
		
		// last terá o link para o último pdf gerado
		$last = "";
		// hidden terá o div de histórico de pdfs gerados. ele estará escondido inicialmente.
		$hidden = "";
		
		//se houver anexos
		if (strlen($anexos[0]) > 0) {
			//para cada anexo
			foreach ($anexos as $a){
				if (stripos($a, $procura) !== false) {
					 // cria um link para o arquivo

					// se last for null, é o 1o item e não precisa adicionar nada ao hidden
					if ($last != "") {
						$hidden .= $last;
					}
					$last = "<a onclick=\"window.open('files/".mb_convert_encoding($a, 'utf-8', 'iso-8859-1')."','ArqAnexo','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".mb_convert_encoding($a, 'utf-8', 'iso-8859-1').'</a><br />';
				}
				else {
					$html .= "<a onclick=\"window.open('files/".mb_convert_encoding($a, 'utf-8', 'iso-8859-1')."','ArqAnexo','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".mb_convert_encoding($a, 'utf-8', 'iso-8859-1').'</a><br />';
				}
			}
			
			$html .= $last;
			if ($hidden != "") {
				$hidden = '<a onclick="showOldPDFs(\'pdfsAntigos\')">[mostrar pdfs alterados]</a><br /><div id="pdfsAntigos" style="display: none;">'.$hidden.'</div><br />';
				$html = $hidden . $html;
			}
		//se nao houver anexos
		}else{
			//produz mensagem avisando
			$html .= '<b>N&atilde;o h&aacute; arquivos anexos.</b>';
		}
		//retorna o codigo HTML dos anexos
		return $html;
	}
	
	/**
	 * Monta a formulario de busca simples
	 * @var string $onclick acao a ser realizada quando um item de resultado for clicado (ex: ver)
	 * @var mysql_link $bd
	 */
	function showBuscaForm($onclick){
		global $conf;
		//inicializacao dos scripts e inicializacao da tabela
		$html = '
		<script type="text/javascript" src="scripts/jquery.ui.datepicker-pt-BR.js"></script>
		<input type="hidden" id="onclick" value="'.$onclick.'" />
		<form accept-charset="'.$conf['charset'].'" id="buscaForm" action="" method="post">
		<table width="100%" border="0">
		<tr class="buscaFormTable"><td width="35%" colspan="3"><b>Efetuar busca nos seguintes tipos de documento:</b><br />
		';
		//le todos os tipos de documento
		$res = getAllDocTypes();
		//para cada tipo de documento separados em 3 colunas
		$col = array(0 => '', 1 => '', 2 => '');
		$i = 0;
		foreach ($res as $r){
			if($r['buscavel'] == 1){//cria um radio para selecionar esse tipo de documento
				$col[$i%3] .= '<input type="checkbox" class="tipoDoc" id="'.$r['nomeAbrv'].'" value="'.$r['nomeAbrv'].'" name="tipoDoc" /> <span id="nome_'.$r['nomeAbrv'].'">'.$r['nome']."</span><br />\n";
				$i++;
			}
		}
		//cria link para adicionar todos os documentos
		$col[0] .= '<a href="javascript:checkAll();">(des) marcar todos</a>';
		$html .= '
		<tr class="buscaFormTable"><td>'.$col[0].'</td><td>'.$col[1].'</td><td>'.$col[2].'</td></tr>
		<tr class="buscaFormTable"><td colspan="3"><br /><b>Com os seguintes campos:</b></td></tr>
		<tr class="buscaFormTable"><td colspan="3"><div id="camposBusca">Primeiro, selecione algum tipo de documento para efetuar a busca</div>
		<tr class="novaBuscaBtn" style="display:none"><td colspan="3"><center><input type="button" value="Nova Busca" onclick="novaBusca()" /></center></td></tr>
		<tr><td colspan="3"><b>Resultados:</b> <span id="numRes"></span></td></tr>
		<tr><td colspan="3">
			<div id="resBusca">
			
			</div>
		</td></tr>
		</form>
		</td></tr></table>';
		//retorna o cod HTML do formulario
		return $html;
	}
	
	/**
	 * Salva os dados do documento no BD
	 * @param array $dados
	 * @param mysql link $bd
	 * @param & $referDoc (usado apenas para não perder a referencia ao doc criado)
	 */
	function salvaDados($dados, $bd, &$referDocID = NULL) { 
		global $conf;
		
		//variavel debug deve ficar desativada em producao. Eh utilizada apenas para debugar a insercao de doc
		//quando =1, gera o relatorio completo da insercao do documento e mostra onde ocorreu um possivel erro
		$DEBUG = 0;
		//gera o cabecalho
		$html = '<span class="header">Relat&oacute;rio de Cadastro</span>';
		if($dados['id'] == 0){//verifica se eh novo documento (nao ha ID)
			//se for cadastro ou geracao de novo documento
			//inicializacao das variaveis
			$doc = new Documento($dados['id']);
			$doc->dadosTipo['nomeAbrv'] = $dados['tipoDocCad'];
			$doc->loadTipoData();
			// se for cadastro de um documento
			if($dados['action'] == 'cad'){
				//verifica se o documento ja esta cadastrado
				$query = "SELECT * FROM ".$doc->dadosTipo['tabBD']." WHERE ";
				//para cada campo de busca
				foreach (explode(",", $dados['camposBusca']) as $d) {
					//monta o campo com os dados enviados
					$campoDados = montaCampo($d, 'cad',$dados,true);
					//se esse campo nao faz parte de outro, concatena para realizar a consulta
					if(!$campoDados['parte']) $query .= $d."='".$campoDados['valor']."' AND ";
				}
				//adiciona AND para adicao dos dados
				$query = rtrim($query, "AND ");
				//consulta para ver se o documento ja esta cadastrado
				$r = $bd->query($query);
				//feedback
				if(count($r) && $doc->dadosTipo['nomeAbrv'] != 'dgen')
					return 'Documento j&aacute; cadastrado.<br /><a href="sgd.php?acao=cad&tipoDoc='.$doc->dadosTipo['nomeAbrv'].'">Cadastrar outro(a) '.$doc->dadosTipo['nome'].'</a> <br /> <a href=""></a>';
				
				//array para armazenar os campos do formulario
				//concatena todos os campos para trata-los
				$camposGerais = explode(",", $dados['camposGerais'].','.$dados['camposBusca']);
				//para cada campo geral
				foreach ($camposGerais as $cb) {
					//le os dados do campo
					$r = getCampo($cb);
					//copia variavel cb pois ela sera modificada
					$cbo = $cb;
					//verifica se o dado em questao era de busca (com '_' no  comeco)
					if(!isset($dados[$cb]) && isset($dados['_'.$cb])){
						//se tiver, modifica a variavel para fazer essa referencia 
						$cb = '_'.$cb;
					}
					//se nao achou o campo no BD (verif de seguranca)
					if(!isset($r[0])){
						//apaga as variaveis
						unset($dados[$cb]);
						unset($camposGerais[$cb]);
						//passa para o proximo campo
						continue;
					}
					//tratamento de campos para salvamento
					if(!isset($dados[$cb]) || $dados[$cb] == ''){
						//se o campo for checkbox e nao tiver variavel com esse nome
						if($r[0]['tipo'] == "checkbox")
							//eh porque ela nao foi checada
							$campos[$cbo] = 0;
						//se for campo autoincrement
						if($r[0]['tipo'] == "autoincrement"){
							//seleciona o attr da tabela
							$r2 = attrFromGenericTable($cb, $doc->dadosTipo['tabBD'], '1', $cb, 'DESC', '1');
							//incrementa o valor do attr e guarda no valor do campo
							$campos[$cbo] = $r2[0][$cb] + 1;
						}
					}
					//tratamento para o campo composto
					if($r[0]['tipo'] == 'composto'){
						$partes = explode("+",$r[0]['attr']);
						$campos[$cbo] = '';
						$referUnset = false;
						foreach ($partes as $p) {
							if (stripos($r[0]['nome'],"referProc") !== false) {
								if (isset($dados[$p]) && $dados[$p] == "" && strlen(str_replace('"','',$p)) > 1) {
									$c = montaCampo($r[0]['nome'],'bus',$dados);
									$referUnset = true;
									$campos[$cbo] = "";
								}
							}							
							if(!isset($dados[$p]) && isset($dados['_'.$p])) $p = '_'.$p;
							if (isset($dados[$p])){
								if (!$referUnset) $campos[$cbo] .= $dados[$p];
								unset($dados[$p]);
								unset($camposGerais[array_search(substr($p,1,strlen($p)),$camposGerais)]);
							} else {
								if (!$referUnset) $campos[$cbo] .= str_replace('"','',$p);
							}
						}
					//partes podem ser ignoradas pois sao tratadas na recursao
					} elseif($r[0]['tipo'] == 'parte'){
						continue;
					} 
					elseif ($r[0]['tipo'] == 'data') {
						$partesData = explode('/', $dados[$cb]);
						if (count($partesData) == 3) {
							$dia = $partesData[0];
							$mes = $partesData[1];
							$ano = $partesData[2];
							
							$data = mktime(0, 0, 0, $mes, $dia, $ano);
							$campos[$cbo] = $data;
						}
						else {
							$campos[$cbo] = $dados[$cb];
							if ($campos[$cbo] == null) $campos[$cbo] = 0;
						}
					//de resto, se o campo foi preenchido, atribui o dado a variavel
					} else {
						if(isset($dados[$cb]))
							$campos[$cbo] = $dados[$cb];
					}
					//tratamento de acentos e quebra de linha para HTML/HTML entities
					if(isset($dados[$cb])){
						//converte caracteres especiais/acentuados para HTML entities
						$campos[$cbo] =  str_replace("\n", "<br />", $campos[$cbo]);
					}
				}
			//tratamento de campos para geracao de novo documento
			}elseif($dados['action'] == 'novo'){
				// verificacao especial para resposta: se ja existir uma resposta ativa, nao deixar
				// cadastrar uma nova resposta
				if ($dados['tipoDocCad'] == "resp") {
					$respondendo = new Documento($dados['docResp']);
					//$respondendo->loadCampos();
					$respondendo->loadDados();
					$respAtiva = $respondendo->getRespAtiva();
					if ($respAtiva['podeCriarResp'] == false)
						return 'N&atilde;o foi poss&iacute;vel criar resposta: Este documento j&aacute; possu&iacute; resposta ativa.';
				}
				
				//nao ha cambos de busca na criacao de um novo documento
				//separa os campos do documento
				$camposForm = explode(",", $doc->dadosTipo['campos']);
				//para campo, trata de acordo com o tipo de campo
				foreach ($camposForm as $cb) {
					//le os dados do campo para
					$r = getCampo($cb);
					//se a variavel nao for passada, pode ser check box nao marcado
					if(!isset($dados[$cb]) || $dados[$cb] == ''){
						//le os dados do campo para 
						//$r = getCampo($cb);
						//se o campo for checkbox, entao ele nao foi checkado
						if($r[0]['tipo'] == "checkbox") {
							//coloca zero no valor do campo
							$dados[$cb] = 0;
						//se o campo for selecao de ano, considere o ano atual
						} elseif($r[0]['tipo'] == "anoSelect") {
							$dados[$cb] = date("Y");							
						//se o campo for autoincrement, deve-se verificar o ultimo valor para incrementa-lo
						} elseif($r[0]['tipo'] == "autoincrement"){
							//se campo reseta a cada ano
							if(strpos($r[0]['extra'], "current_year") !== false){
								//Seleciona o documento mais velho *deste ano* com o maior numero do attr autoincrement
								$r2 = $bd->query("SELECT t.".$cb." FROM ".$doc->dadosTipo['tabBD']." AS t LEFT JOIN doc AS d ON t.id=d.tipoID WHERE d.data>".mktime(0,0,0,1,1,date("Y"))." AND d.labelID=".$doc->dadosTipo['id']." ORDER BY d.data DESC LIMIT 1");
								// print($r2[0][$cb]."SELECT t.".$cb." FROM ".$doc->dadosTipo['tabBD']." AS t LEFT JOIN doc AS d ON t.id=d.tipoID WHERE d.data>".mktime(0,0,0,1,1,date("Y"))." AND d.labelID=".$doc->dadosTipo['id']." ORDER BY t.".$cb." DESC LIMIT 1");
								//se achar alguma entrada, incrementa o valor do ultimo doc
								if (isset($r2[0][$cb]) && $r2[0][$cb]){
									$dados[$cb] = (($r2[0][$cb]) + 1);//. '/' . date("Y");
								//senao, nenhum doc foi criado nesse ano, ainda. Cria o id 1/aaaa
								} else {
									$dados[$cb] = 1;//. '/' . date("Y");
								}
							//se o campo nao reseta a cada ano
							} else {
								//consulta o maior numero ja cadastrado
								$r2 = attrFromGenericTable($cb, $doc->dadosTipo['tabBD'], '1', $cb, 'DESC', '1');
								//e incrementa em uma unidade
								if (isset($r2[0])){
									$dados[$cb] = $r2[0][$cb] + 1;
								//se nao houver linhas, apenas cria a primeira
								} else {
									$dados[$cb] = 1;
								}
							}
						//trata campo de usuario atual
						}elseif(strpos($r[0]['extra'], "current_user") !== false){
							//coloca o id do usuario como valor
							$dados[$cb] = $_SESSION['id'];
						//trata campo composto
						}elseif($r[0]['tipo'] == 'composto'){
							//separa as partes do campo composto
							$partes = explode("+",$r[0]['attr']);
							//para cada parte do campo composto
							foreach ($partes as $p) {
								//verifica se o sub-campo tem algum valor
								if (isset($dados[$p])){
									//se tiver, concatena com o campo
									$dados[$cb] .= $dados[$p];
									unset($dados[$p]);
								//senao, eh uma string e nao nome de variavel
								} else {
									//entao, retira as aspas e concatena com o valor do campo
									$dados[$cb] .= str_replace('"','',$p);
								}
							}
						//se for parte de um campo, ignora pois ja foi tratado acima
						}elseif($r[0]['tipo'] == 'parte'){
							continue;
						}	
					}
					//trata o valor do campo convertendo acentos em entidades HTML
					$campos[$cb] = htmlspecialchars_decode($dados[$cb], ENT_QUOTES);
					//se for conteudo, converte as quebras de linha em cod HTML e aspas
					if($cb == "conteudo"){
						$campos[$cb] = str_replace(array("'","\n"),array("\'",""), $campos[$cb]);
					} else {
						if($r[0]['tipo'] == 'composto'){
							
							$partes = explode("+",$r[0]['attr']);
							$cbo = $cb;
							$campos[$cbo] = '';
							foreach ($partes as $p) {						
								if(!isset($dados[$p]) && isset($dados['_'.$p])) $p = '_'.$p;
								if (isset($dados[$p]) && $dados[$p] != ""){ // verifica se a parte nao tem valor nulo
									$campos[$cbo] .= $dados[$p];
								} else { // caso tenha valor nulo, o campo todo deverá ter valor nulo
									if (strlen(str_replace('"','',$p)) <= 1) {
										$campos[$cbo] .= str_replace('"','',$p);
									}
									else {
										$campos[$cbo] = "";
										break;
									}
								}
							}
						}
						$campos[$cb] = str_replace(array("\n","'"),array("<br />","\'"), $campos[$cb]);
					}
				}
			}
			//atribui a array temporaria ao documento
			$doc->campos = $campos;
			//campo de atrib obra da SAP nao deve ser salvo como um campo comum
			if ($dados['tipoDocCad'] == "sap")
				unset($doc->campos['obraSAP']);
			
			// clausula especial para SAP com referencia
			if ($doc->dadosTipo['nomeAbrv'] == 'sap' && $dados['action'] == 'novo' && isset($doc->campos['referProc'])) {
				$numPR = str_replace(" ", "", $doc->campos['referProc']);
				//$numPR = str_replace("-", "", $numPR, );
				$partes = explode("-", $numPR);
				if (count($partes) == 3) {
					$ano = $partes[2];
					$ano = substr($ano, 2);
					$numPR = $partes[0] . $partes[1] . "-" . $ano;
					$doc->campos['assunto'] .= " REF PROC " .$numPR;
				}
			}
			
			if($DEBUG) $html .= "Dados lidos com sucesso.<br />";	
			//adicao dos documentos anexos e feedback se debug setado
			if($doc->dadosTipo['docAnexo'] != 0){
				$doc->campos['documento'] = '';
			}
			/*elseif($doc->dadosTipo['docAnexo'] == -1) {
				$doc->campos['documento'] = '';
			}*/
			
			if ($doc->dadosTipo['docResp'] == 1) {
				$doc->campos['documento'] = '';
			}
			
			//adicao das obras anexas
			if($doc->dadosTipo['obra']){
				$doc->campos['obra'] = '';
			}
			//adicao das empresas anexas
			if($doc->dadosTipo['empresa']){
				$doc->campos['empresa'] = '';
			}
			
			if($doc->dadosTipo['nomeAbrv'] == "dgen" && $doc->campos['numero_dgen'] == "SIGPOD") {
				$doc->campos['numero_dgen'] = "";
			}
			
			//salvar campos no BD
			if ($doc->salvaCampos()){
				if($DEBUG) $html .= "Campos salvos com sucesso.<br />";
			} else { 
				if($DEBUG) $html .= "<b>Erro ao salvar campos. O documento n&atilde;o foi criado.</b><br />";
				return $html;
			}
			
			//salvar doc no BD
			if ($doc->salvaDoc(0)){
				if($DEBUG) $html .= "Documento criado com sucesso.<br />";
			} else { 
				if($DEBUG) $html .= "<b>Erro ao criar documento.</b><br />";
				return $html;
			}
			
			$doc->loadDados();
			//print_r($doc); exit();
			
			//atribuir SAP as obras se for contratacao
			if($dados['tipoDocCad'] == "sap" && strlen($campos['obraSAP']) > 0){
				$obraSAP = json_decode($campos['obraSAP']);
				foreach ($obraSAP as $o) {
					$obras[] = $o->obraID;
				}
				$doc->salvaObras($obras);
			}
			
			if($doc->dadosTipo['nomeAbrv'] == "dgen" && $doc->campos['numero_dgen'] == "") {
				$doc->updateCampo('numero_dgen', $doc->id);
				$doc->update('numeroComp', $doc->geraNumComp());
				//$doc->update('numeroComp', $doc->campos['tipoDocGen'].' '.$doc->campos['unOrg'].' '.$doc->id.'/'.$doc->campos['anoE']);
			}
			
			//logar historico de recebimento se os campos forem preenchidos
			if ($doc->dadosTipo['nomeAbrv'] != "contr" && $doc->dadosTipo['nomeAbrv'] != "rep") {
				if($dados['action'] == 'cad' && $dados['unOrgReceb'] && $dados['rrNumReceb'] && $dados['rrAnoReceb']){
					if ($doc->doLogHist($_SESSION['id'],'',"Via Rel. Remessa n&deg;".$dados['rrNumReceb']."/".$dados['rrAnoReceb'],$dados['unOrgReceb'],'entrada','','Recebido')) {
						if($DEBUG) $html .= "Hist&oacute;rico criado com sucesso.<br />";
					} else {
						if($DEBUG) $html .= '<b>Falha ao criar hist&oacute;rico de Recebimento</b><br />';
					}
				}
			}
						
			//logar historico
			if ($doc->doLogHist($_SESSION['id'],"","",'','criacao','','')){
				if($DEBUG) $html .= "Hist&oacute;rico criado com sucesso.<br />";
			}else{
				if($DEBUG) $html .= '<b>Falha ao criar hist&oacute;rico</b><br />';
			}
			
			//faz upload de arquivos, salva no documento e loga no historico
			$relArqHTML = "<br /><b>Arquivos</b><br />";			
			$relArq = $doc->doUploadFiles();
			$relArqHTML .= montaRelArq($relArq);
			$anexoSalvo = $doc->salvaAnexos();
			
			//se estiver no modo debug, mostra o estado de cada arquivo anexado
			if($DEBUG) 
				if ($anexoSalvo === true) {
					$html .= "<br />Arquivos anexados com sucesso.<br />";
				}elseif ($anexoSalvo === false){
					$html .= "<br /><b>Erro ao anexar arquivos.</B><br />";
				}elseif ($anexoSalvo === 0){
					$html .= "N&atilde;o h&aacute; arquivo anexado.<br /><br />";
				}
			
			//marca que os documentos filho foram anexados.
			if(!$doc->doFlagAnexado())
				if($DEBUG) $html .= "<b>Erro ao salvar dados nos documentos filhos</b><br />";
			
			if(!isset($dados['funcID'])) $dados['funcID'] = false;

			//CLAUSULA ESPECIAL PARA RR - caso seja RR, logar que os documentos foram enviados por ela
			if($doc->dadosTipo['nomeAbrv'] == 'rr'){
				//separar os documentos enviados pela RR gerada
				foreach (explode(",", $dados['docsDesp']) as $ddid) {
					//verif de seguranca para nao incluir documentos invalidos
					if($ddid > 0){
						//carrega os dados do documento 
						$docDesp = new Documento($ddid);
						$docDesp->loadDados();
						if($docDesp->owner != 0) showDespStatus($docDesp, array('para' => SGDecode($dados['para']) ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => $dados['despacho']),'hideFB');
						$docDesp->doLogHist($_SESSION['id'], '', 'Via <a href="javascript:void(0)" onclick="'.SGEncode(Documento::geraLinkDoc('ver', $doc->id),ENT_QUOTES,null,false).'"> Rel. Remessa CPO n&deg:'.$doc->campos['numeroRR'].'</a>',$doc->campos['unOrgDest'],'saida','','Despacho');
					}
				}
			}
			
			// CLAUSULA especial para criacao de processo a partir de SAP
			if (($dados['action'] == 'cad') && ($dados['tipoDocCad'] == 'pr') && (isset($dados['sapID']))) {
				// anexa a SAP ao novo processo criado
				anexarDoc($dados['sapID'], $doc->id);
				// carrega a sap
				$sap = new Documento($dados['sapID']);
				$sap->loadDados();
				// se a SAP estiver associada a um empreendimento, associa o processo ao empreendimento também
				if ((isset($sap->empreendID)) && ($sap->empreendID != 0)) {
					atribEmpreend($doc->id, $sap->empreendID, $bd, 0);
					includeModule("sgo");
					$empreendimento = new Empreendimento($bd);
					$empreendimento->load($sap->empreendID);
					if ((isset($empreendimento->ofir)) && ($empreendimento->ofir != 0)) { // verifica se existe oficio associado ao empreeendimento
						// anexa oficio ao processo criado
						anexarDoc($empreendimento->ofir, $doc->id);
						// desasocia oficio do empreendimento
						$empreendimento->ofir = 0;
						$empreendimento->save();
					}
				}
			}
			
			// Clausula especial para documento resposta
			if (($dados['action'] == 'novo') && ($dados['tipoDocCad'] == 'resp')) {
				// anexa resposta ao documento
				anexarDoc($doc->id, $dados['docResp']);
			}
			
			// Clausula especial para documento IT
			if (($dados['action'] == 'novo') && ($dados['tipoDocCad'] == 'it')) {
				if (isset($dados['procIT']) && $dados['procIT'] != "" && $dados['procIT'] > 0) {
					// anexa it ao documento
					anexarDoc($doc->id, $dados['procIT']);
				}
			}
			
			//grava despacho
			if ($dados['tipoDocCad'] != 'resp' && $dados['tipoDocCad'] != 'it' && $dados['tipoDocCad'] != 'contr' && $dados['tipoDocCad'] != 'rep') {
				// se o doc não for uma resposta, grava despacho
				$despStatus = showDespStatus($doc, array('para' => SGDecode($dados['para']) ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => $dados['despacho']),'hideFB');
			}
			elseif($dados['tipoDocCad'] == 'it') {
				if (isset($dados['procIT']) && $dados['procIT'] != "" && $dados['procIT'] > 0) {
					$docResp = new Documento($dados['procIT']);
					$docResp->loadDados();
					$despStatus = showDespStatus($docResp, array('para' => SGDecode($dados['para']) ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => $dados['despacho']),'hideFB');
				}
				else {
					$despStatus = showDespStatus($doc, array('para' => SGDecode($dados['para']) ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => $dados['despacho']),'hideFB');
				}
			}
			elseif($dados['tipoDocCad'] == 'contr') {
				$despStatus = '';
			}
			else {
				// se for uma resposta, faz o despacho do doc a qual ele responde
				if (isset($dados['docResp']) && $dados['docResp'] != null) {
					$docResp = new Documento($dados['docResp']);
					$docResp->loadDados();
					$despStatus = showDespStatus($docResp, array('para' => SGDecode($dados['para']) ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => $dados['despacho']),'hideFB');
				}
			}
			
			//gerar PDF @todo obras na SAP
			if($dados['action'] == 'novo'){
				$pdfFile = geraPDF($doc->id);
				if($pdfFile)
					if($DEBUG) $html .= '<b>Arquivo PDF gerado com sucesso. Clique para <a href="files/'.$pdfFile.'">visualizar/baixar o documento PDF </a>.</b>';
				else
					if($DEBUG) $html .= '<b>Erro ao gerar arquivo PDF.</b>';
			}
			
			//Reload dos campos para impressao
			$doc->loadDados();
			$doc->loadCampos($bd);
			//impressao dos dados
			//var_dump($referDocID);
			//if ($referDocID !== null) {
			$referDocID = $doc->id;
			//}
			$html .= 'Documento gerado com o N&uacute;mero CPO: <b><font color="red"><a href="javascript:void(0);" onclick="window.open('."'sgd.php?acao=ver&amp;docID=".$doc->id."','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes'".').focus()">'.$doc->id.'</a></font></b>
			<br />'.showDetalhes($doc).
			$relArqHTML.
			'<br /><b>Outras A&ccedil;&otilde;es:</b>';
			if(isset($_POST['targetInput'])) {
				$html .= '<br /><b><a href="javascript:void(0);" onclick="javascript:window.opener.newDocLink(\''.$doc->id.'\',\''.$doc->dadosTipo['nome'].' '.$doc->numeroComp.'\',\''.$_POST['targetInput'].'\',\'<br>\');self.close();">Adicionar documento ao formul&aacute;rio e fechar janela</a></b>';
			}
			$html .= '<br /><a href="sgd.php?acao='.$dados['action'].'&tipoDoc='.$doc->dadosTipo['nomeAbrv'].'"> Cadastrar novo(a) '.$doc->dadosTipo['nome'].'</a>'
			.$despStatus;
			
			//atalho para a pagina inicial
			$html .= '<br /> <a href="index.php">Voltar para p&aacute;gina inicial.</a>';
				
			//LOG dos usuarios
			doLog($_SESSION['username'],'Criou o documento '.$doc->id);
			
			if (!isset($dados['_keepAutoSave']) || ($dados['_keepAutoSave'] != 1 || $dados['_keepAutoSave'] != "1")) {
				descartaAutoSavedDoc($dados['tipoDocCad']);
			}
			
		}else{//se doc ja existe, faz o despacho
			$doc = new Documento($dados['id']);
			$doc->dadosTipo['nomeAbrv'] = $dados['tipoDocCad'];
			
			if(!isset($dados['funcID'])) $dados['funcID'] = false;
			
			//logar historico de recebimento
			if($dados['action'] == 'cad' && $dados['unOrgReceb'] && $dados['rrNumReceb'] && $dados['rrAnoReceb']){
				if ($doc->doLogHist($_SESSION['id'],'',"Via Rel. Remessa n&deg;".$dados['rrNumReceb']."/".$dados['rrAnoReceb'],$dados['unOrgReceb'],'entrada','','Recebido')) {
					if($DEBUG) $html .= "Hist&oacute;rico criado com sucesso.<br />";
				} else {
					if($DEBUG) $html .= '<b>Falha ao criar hist&oacute;rico de Recebimento</b><br />';
				}
			}
			//gravar despacho
			$html .= showDespStatus($doc,array('para' => $dados['para'] ,"outro" => $dados['outro'], 'funcID' => $dados['funcID'], 'despExt' => $dados['despExt'], 'despacho' => SGEncode($dados['despacho'], ENT_QUOTES, null, false)));
			$html .= '<br /> <a href="index.php">Voltar para p&aacute;gina inicial.</a>';
			
			if (isset($dados['_keepAutoSave']) && ($dados['_keepAutoSave'] != 1 || $dados['_keepAutoSave'] != "1")) {
				descartaAutoSavedDoc($dados['tipoDocCad']);
			}
		}
				
		return $html;
	}
	
	/**
	 * Monta o relatorio de upload dos arquivos (DEBUG)
	 * @param Array $files
	 */
	function montaRelArq($files){
		//inicializacao de variaveis
		$html = '';
		//para cada arquivo bem sucedido
		foreach ($files['success'] as $file) {
			//se o arquivo foi enviado corretamente. Gera a mensagem
			$html .= '<i>'.$file.'</i>: Arquivo foi anexado com sucesso.<br />';
			//loga a acao
			doLog($_SESSION['username'], "Anexou o arquivo $file.");
		}
		//para cada arquivo mau sucedido
		foreach ($files['failure'] as $file) {
			//se o arquivo obteve falha
			$html .= '<i>'.$file['name'].'</i>: Erro ao anexar arquivo (Erro '.$files['errorID'].').<br />';
			//loga a acao
			doLog($_SESSION['username'], "Obteve erro ao adicionar $file.");
		}
		//retorna o cod html da mensagem
		return $html;
	}
	
	/**
	 * Retorna string de feedback correspondente para dado return da funcao de salvamento. (DEBUG)
	 * @param Documento $doc
	 * @param string $dados
	 * @param string $mode
	 **/
	function showDespStatus(Documento $doc,$dados,$mode = 'showFB',$entrada = false) {
		global $conf;
		
		//inicializacao da variavel
		$html = "";

		$doc->loadDados();
		//print $entrada.'/'.$dados['rrNumReceb'].'/'.$dados['unOrgReceb'].'/'.$dados['rrAnoReceb'];exit();
		if ($entrada) {
			if(isset($dados['unOrgReceb']) && isset($dados['rrNumReceb']) && isset($dados['rrAnoReceb']) && $dados['unOrgReceb'] && $dados['rrNumReceb'] && $dados['rrAnoReceb']){
				$recebido = " via Rel. Remessa n&deg;".$dados['rrNumReceb']."/".$dados['rrAnoReceb'];
				$unOrgReceb = $dados['unOrgReceb'];
			} else {
				$recebido = 'unidade n&atilde;o especificada';	
				$unOrgReceb = '[unidade n&atilde;o especificada]';
			}
		
			if ($doc->doLogHist($_SESSION['id'], '', $recebido, $unOrgReceb, 'entrada', '', 'Recebido')) {
					if($mode == 'showFB') $html .= "Hist&oacute;rico criado com sucesso.<br />";
				} else {
					if($mode == 'showFB') $html .= '<b>Falha ao criar hist&oacute;rico de Recebimento</b><br />';
				}
			
			
			// se este doc tinha sido solicitado, ao entrar ele perde estas solicitacoes e vai automatico para solicitante
			$doc->update('solicitante', '0');
			$doc->update('solicitado', '0');
		}
		
		if($doc->dadosTipo['nomeAbrv'] != 'rep') {
			//realiza o despacho
			$desp = SGDecode($doc->doDespacha($_SESSION['id'],$dados));
			if (isset($dados['funcID'])) {
				$solic = getUserFromUsername($doc->solicitante);
				//$solic = $solic[0];
				if (count($solic) > 0 && $solic[0]['id'] == $dados['funcID']) {
					$doc->update('solicitante', '0');
					$doc->update('solicitado', '0');
					$doc->update('solicDesarquivamento', '0');
				}
			}
		}
		//se o modo de operacao eh diferente de hideFeedBack
		if($mode != "hideFB"){
			//se houve falha ao realizar o despacho
			if($desp === false){
				//gera feedback
				$html = "<b>Falha ao gravar despacho.</b><br />";
			//se nao foi digitado nenhum despacho.
			}elseif($desp === 0){
				//avisa que nao houve dispacho digitado
				$html = "<b>N&atilde;o h&aacute; despacho. Documento est&aacute; pendente para o usu&aacute;rio atual.</b><br />";
			//senao - sucesso ao salvar despacho
			}else{
				//gera msg de sucesso
				$html = 'Despacho para '.$desp.' gravado com sucesso.<br />';
			}
		}
		//se o despacho foi para fora, e nao foi uma RR
		if($dados['para'] == 'ext' && $dados['despExt'] && $doc->dadosTipo['nomeAbrv'] != 'rr')
			//gerar atalho para RR 
			$html .= '<br /><a onclick="window.open('."'sgd.php?acao=novoDocVar&amp;action=novo&amp;tipoDoc=rr&amp;anoE=".date("Y")."&amp;docsDesp=".$doc->id."&amp;unOrgDest=".urlencode($dados['despExt'])."&amp;ppara=ext&amp;despExt=".urlencode($dados['despExt'])."&amp;despacho=".urlencode($dados['despacho'])."','novaRR','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*".$conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes'".').focus()">Gerar Rela&ccedil;&atilde;o de Remessa</a>.<br />';
		//retorna o cod html
		return $html;
	}
	
	/**
	 * trata as variaveis passadas via $_GET para via $_POST (criacao de documento via URL)
	 * @param array $GET
	 */
	function trataGetVars($GET){
		//inicia o novo documento a ser criado
		$doc = new Documento(0);
		$doc->dadosTipo['nomeAbrv'] = $GET['tipoDoc'];
		$doc->loadTipoData();
		//para cada campo do documento
		foreach (explode(",",$doc->dadosTipo['campos']) as $campo) {
			//monta o campo a ser lido
			$nome = montaCampo($campo);
			//coloca o nome em um array
			$camposNomes[] = $nome['nome'];
		}
		//para cada campo do documento
		foreach ($camposNomes as $campo) {
			//verifica se ele foi passado para a pagina
			if (isset($GET[$campo])) {
				//se sim, coloca seu valor na variavel
				$dados[$campo] = urldecode($GET[$campo]);
			} else {
				//senao, deixa o valo da variavel vazio
				$dados[$campo] = '';
			}
		}
		//se a acao for de cadastro de novo documento, seta as variaveis pertinentes
		if($GET['action'] == 'cad'){
			//sinaliza que a acao deve ser de cadastro
			$dados['action'] = 'cad';
			//seta os campos gerais
			$dados['camposGerais'] = $doc->dadosTipo['campos'];
			//seta os campos de busca
			$dados['camposBusca'] = '';
		} elseif ($GET['action'] == 'novo'){
			//se for novo documento, apenas eh necessario setar a acao
			$dados['action'] = 'novo';
		}
		//indica qual o tipo de documento sera salvo
		$dados['tipoDocCad'] = $GET['tipoDoc'];
		//id=0 pois eh um novo documento
		$dados['id'] = 0;
		//cria os dados para despacho se houver. senao deixa os campos de despacho em branco
		if(isset($GET['para']))    $dados['para']     = urldecode($GET['ppara']);    else   $dados['para'] = '';
		if(isset($GET['despExt'])) $dados['despExt']  = urldecode($GET['despExt']);  else   $dados['despExt'] = '';
		if(isset($GET['outro']))   $dados['outro']    = urldecode($GET['outro']);    else   $dados['outro'] = '';
		if(isset($GET['despacho']))$dados['despacho'] = SGEncode($GET['despacho'], ENT_QUOTES, null, false); else   $dados['despacho'] = '';
		//cria a 'lista' de documentos anexos, se houver
		if (isset($GET['docsAnexos'])) {
			$dados['docsAnexos'] = $GET['docsAnexos'];
		} else {
			$dados['docsAnexos'] = '';
		}
		//cria a 'lista' de obras anexas se passada via URL
		if (isset($GET['obrasAnexas'])) {
			$dados['obrasAnexas'] = $GET['obrasAnexas'];
		} else {
			$dados['obrasAnexas'] = '';
		}
		//cria a 'lista' de empresas anexas, se passada via URL
		if (isset($GET['emprAnexas'])) {
			$dados['emprAnexas'] = $GET['emprAnexas'];
		} else {
			$dados['emprAnexas'] = '';
		}
		//retorna array com todas as variaveis necessarias para a acriacao do documento
		return $dados;
	}
	
	/**
	 * Salva nova atribuição de valor a um determinado documento
	 * @param int $docID
	 * @param string $campoName
	 * @param string $oldCampoVal
	 * @param string $newCampoVal
	 * 
	 */
	function editDoc($docID, $campoName, $newCampoVal) {
		$doc = new Documento($docID);
		$doc->loadCampos();		
		
		//TODO levantar solução generica para esse problema SGEncode no campo textarea
		if($campoName == "conteudo")
			$newCampoVal = SGDecode($newCampoVal,ENT_NOQUOTES);
		
		$campo = montaCampo($campoName,'mostra',$doc->campos,false);
		if ($campo['tipo'] == 'data') {
			if ($newCampoVal != "") {
				$dataArray = explode("/", $newCampoVal);
				if (count($dataArray) != 3)
					return $ret[] = array('success' => 'false');
				
				$newCampoVal = mktime(0, 0, 0, $dataArray[1], $dataArray[0], $dataArray[2]);
			}
			else {
				$newCampoVal = 0;
			}
		}

		$res = $doc->updateCampo($campoName, $newCampoVal);
		
		if ($res){
			//$campo = montaCampo($campoName,'mostra',$doc->campos,false);
			doLog($_SESSION['username'],'Alterou informa&ccedil;&otilde;es do documento'.$doc->id.'. Campo '.$campo['label'].' alterado de "'.$campo['valor'].'" para "'.$newCampoVal.'"');
			$doc->campos[$campoName] = $newCampoVal;
			if(strpos($doc->dadosTipo['numeroComp'], $campoName) !== false){
				$newNumeroComp = $doc->geraNumComp();
				$doc->update('numeroComp',$newNumeroComp);
			}
			if (strpos($doc->dadosTipo['emitente'], $campoName) !== false && stripos($doc->dadosTipo['emitente'], $campoName) == 0) {
				$doc->update('emitente', $newCampoVal);
			}
			
			if ($campoName == 'valorProj') {
				$doc->updateCampo('valorTotal', $newCampoVal);
				$ret[] = array('success' => 'true', 'extra' => 'valorTotal', 'extraVal' => $newCampoVal);
				
				return $ret;
			}
			
			if ($campoName == 'valorMaoObra') {
				$doc->updateCampo('valorTotal', $newCampoVal + $doc->campos['valorMaterial']);
				$ret[] = array('success' => 'true', 'extra' => 'valorTotal', 'extraVal' => $newCampoVal + $doc->campos['valorMaterial']);
				return $ret;
			}
			
			if ($campoName == 'valorMaterial') {
				$doc->updateCampo('valorTotal', $newCampoVal + $doc->campos['valorMaoObra']);
				$ret[] = array('success' => 'true', 'extra' => 'valorTotal', 'extraVal' => $newCampoVal + $doc->campos['valorMaterial']);
				return $ret;
			}
			
			if ($campoName == 'dataAssinatura') {
				if ($doc->campos['dataReuniao'] <= 0 && $doc->campos['prazoContr'] > 0) {
					$doc->updateCampo('vigenciaContr', $newCampoVal + (($doc->campos['prazoContr']-1) * 24 * 60 * 60));
					$ret[] = array('success' => 'true', 'extra' => 'vigenciaContr', 'extraVal' => date("d/m/Y", $newCampoVal + (($doc->campos['prazoContr']-1) * 24 * 60 * 60)));
					return $ret;
				}
			}
			
			if ($campoName == 'dataReuniao') {
				if ($doc->campos['prazoContr'] > 0) {
					if ($newCampoVal <= 0) {
						$newCampoVal = $doc->campos['dataAssinatura'];
					}
					$doc->updateCampo('vigenciaContr', $newCampoVal + (($doc->campos['prazoContr']-1) * 24 * 60 * 60));
					//$ret[] = array('success' => 'true', 'extra' => 'vigenciaContr', 'extraVal' => date("d/m/Y", $newCampoVal + (($doc->campos['prazoContr']-1) * 24 * 60 * 60)));
					//return $ret;
				}
				
				/* atualizacao data de inicio proj/obra */
				if ($newCampoVal > 0) {
					$data = Contrato::getProxDiaUtil($newCampoVal, true);
					$doc->updateCampo('inicioProjObra', $data);
					
					$doc->updateCampo('dataTermino', $data + (($doc->campos['prazoProjObra']-1) * 24 * 60 * 60));
					
					$ret[] = array('success' => 'true', 'extra' => 'reload');
					return $ret;
				}
				
			}
			
			if ($campoName == 'prazoContr') {
				$data = $doc->campos['dataAssinatura'];
				if ($doc->campos['dataReuniao'] > 0)
					$data = $doc->campos['dataReuniao'];
					
				if ($newCampoVal > 0) {
					$doc->updateCampo('vigenciaContr', $data + (($newCampoVal-1) * 24 * 60 * 60));
					$ret[] = array('success' => 'true', 'extra' => 'vigenciaContr', 'extraVal' => date("d/m/Y", $data + (($newCampoVal-1) * 24 * 60 * 60)));
					return $ret;
				}
				else {
					$doc->updateCampo('vigenciaContr', 0);
					$ret[] = array('success' => 'true', 'extra' => 'vigenciaContr', 'extraVal' => '');
					return $ret;
				}
			}
			
			if ($campoName == 'inicioProjObra') {
				$doc->updateCampo('dataTermino', $newCampoVal + (($doc->campos['prazoProjObra']-1) * 24 * 60 * 60));
				$ret[] = array('success' => 'true', 'extra' => 'dataTermino', 'extraVal' => date("d/m/Y", $newCampoVal + (($doc->campos['prazoProjObra']-1) * 24 * 60 * 60)));
				return $ret;
			}
			
			if ($campoName == 'prazoProjObra') {
				$doc->updateCampo('dataTermino', $doc->campos['inicioProjObra'] + (($newCampoVal-1) * 24 * 60 * 60));
				$ret[] = array('success' => 'true', 'extra' => 'dataTermino', 'extraVal' => date("d/m/Y", $doc->campos['inicioProjObra'] + (($newCampoVal-1) * 24 * 60 * 60)));
				return $ret;
			}
			
			$ret[] = array('success' => 'true');
		} else {
			$ret[] = array('success' => 'false');
		}
		return $ret;
	}
	
	/**
	 * Adiciona  HTML dos campos para anexar documento
	 * @param documento $doc
	 *
	 **/
	function addAnexarDoc($doc) {
		$alert = '';
		if($doc->anexado) {
			$alert = '<div id="alert" style="display: none; border: 1px solid red; text-align: center; margin: 5px; padding: 5px;">
			<span style="color: red">Aviso:</span> Este documento j&aacute est&aacute anexado. Caso opte por anex&aacute;-lo a outro, a liga&ccedil;&atilde;o anterior ser&aacute; perdida.
			</div>';
		}
		if ($doc->labelID == 5) $html = '<input id="addEste" type="radio" name="tipo" value="1" onclick="showAlert();" /> Anexar este documento a outro.<br />';
		else $html = '<input id="addEste" type="radio" name="tipo" value="1" onclick="showAlert();" /> Anexar este documento a um processo.<br />';
		if($doc->dadosTipo['docAnexo']) {
			$html .= '<input id="addOutr" type="radio" name="tipo" value="1" onclick="hideAlert();" /> Anexar outros documentos a este.';
		}
		$html .= $alert;
		return $html;
	}
	
	function anexarDoc($filhoID,$paiID) {
		global $bd;
		
		if ($filhoID == $paiID)
			return array(array('success' => 'false'));
			
		$doc = new Documento($paiID);
		$doc->loadCampos();
		
		$doc2 = new Documento($filhoID);
		$doc2->loadCampos();
		
		$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = ".$doc->dadosTipo['id']." AND tipoAnexoID = ".$doc2->dadosTipo['id']." AND aceitaAnexo = 1";
		$teste1 = $bd->query($sql);
		$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = ".$doc2->dadosTipo['id']." AND tipoAnexoID = ".$doc->dadosTipo['id']." AND aceitaAnexo = 1";
		$teste2 = $bd->query($sql);
		
		if (count($teste1) > 0 && count($teste2) <= 0) {
			// só o 1o doc aceita o 2o
			$pai = $doc;
			$filho = $doc2;
		}
		elseif (count($teste1) <= 0 && count($teste2) > 0) {
			// só o 2o aceita o 1o
			$pai = $doc2;
			$filho = $doc;
		}
		elseif (count($teste1) > 0 && count($teste2) > 0) {
			// os dois se aceitam, seleciona pela data de criacao
			if ($doc->data <= $doc2->data) {
				$pai = $doc;
				$filho = $doc2;
			}
			else {
				$pai = $doc2;
				$filho = $doc;
			}
		}
		else {
			// nenhum dos dois se aceitam
			return array(array('success' => 'false'));
		}
		
		if ($filho->anexado == 1) {
			return array(array('success' => 'false'));
		}
		
		while ($pai->anexado == 1) {
			$novoPai = new Documento($pai->docPaiID);
			$novoPai->loadCampos();
			$pai = $novoPai;
		}

		$sql = "SELECT * FROM label_doc_anexo WHERE tipoDocID = ".$pai->dadosTipo['id']." AND tipoAnexoID = ".$filho->dadosTipo['id']. " AND aceitaAnexo = 1";		
		$res = $pai->anexaDoc($filho->id);

		if($res) {
			// OBS: nao utilizar $filhoID e $paiID aqui, uma vez que quem eh pai e quem eh filho pode ter sido alterado pela
			// logica de anexacao do sistema
			$pai->doLogHist($_SESSION['id'], '', '', '', 'anexOutro', '', '', $filho->id);
			$filho->doLogHist($_SESSION['id'], '',	'', '', 'anexoEste', '', '', $pai->id);
			
			return array(array('success' => 'true', 'filhoID' => $filhoID));
		} else {
			return array(array('success' => 'false'));
		}
	}
	
	function showAtribuirAObra($docID){
		includeModule('sgo');
		global $bd;
		$template = showAtribuirObraTemplate();
		$doc = new Documento($docID);
		$doc->loadCampos();
		
		//atribuicao fo template
		$html = $template['template'];
		
		//le as obras associadas ao documento
		$obras = $doc->getObras();
		
		//mostra as obras associadas ou msg avisando que nao ha obras associadas
		if(count($obras) == 0) {
			$html = str_ireplace('{$obraAtual}', $template['sem_obra'], $html);
		} else {
			$obrasHTML = $template['com_obra'];
			foreach ($obras as $o) {
				$obrasHTML .= str_ireplace(array('{$obra_id}','{$obra_nome}'), array($o['id'],$o['nome']), $template['obra_link']);
			}
			
			$html = str_ireplace('{$obraAtual}', $obrasHTML, $html);
		}
		
		//sugestoes (breve)
		$html = str_ireplace('{$table_sugestoes}', '', $html);
		
		$html = str_ireplace('{$empreendMiniBusca}', showObraMiniBusca(), $html);
		return $html;
	}
	
	/**
	 * mostra tela de atribuicao de documento a um empreendimento
	 * @param int $docID o ID (número CPO) do documento
	 * @return html o código html da página
	 */
	function showAtribuirAEmpreend($docID){
		includeModule('sgo');
		global $bd;
		$template = showAtribuirObraTemplate();
		$doc = new Documento($docID);
		$doc->loadCampos();
		$guardachuva = 0;
		
		$atual = "";
		
		// define qual campo de unidade será utilizado baseado no tipo de documento
		switch ($doc->labelID) {
			case 1:  // processo
				$campoSugere = 'unOrgInt';
				break;
			case 2:  // oficio cpo
			case 4:  // sap
			case 8:  // resposta
			case 9:  // it
				$campoSugere = 'unOrgDest';
				break;
			case 3:  // oficio externo
			case 7:  // doc gen
			case 10: // contrato
				$campoSugere = 'unOrg';
				break;
			case 6:  // memo
				$campoSugere = 'destMEMO';
				break;
			default: 
				$campoSugere = 'unOrgInt';
				break;
		}
		
		if ($doc->dadosTipo['nomeAbrv'] == 'pr' && $doc->campos['guardachuva'] == 1) { // verifica se é processo e se ele é guarda-chuva
			$guardachuva = 1;
			// seleciona todos os empreendimentos aos quais este doc está associado
			$sql = "SELECT * FROM guardachuva_empreend WHERE docID = " .$doc->id;
			$res = $bd->query($sql);
			
			if (count($res) == 0) {
				// o guarda chuva nao está associado a nenhum empreendimento
				$atual = $template['sem_obra'];
			}
			else {
				$atual = $template['obra_header'];
			}
			
			// percorre os empreendimentos aos quais este doc está associado e cria lista
			foreach ($res as $r) {
				$empreendID = $r['empreendID'];
				$empreend = new Empreendimento($bd);
				$empreend->load($empreendID);
				$atual .= "<br />";
				$atual .= str_replace(array('{$obra_nome}', '{$obra_id}'), array($empreend->get('nome'), $empreend->get('id')), $template['obra_mini']);
			}
			
			$atual .= '</span>';
			// mostra sugestões pra esse documento anyway
			$sugestoes = $template['table_sugestoes'];
			$sugestoes = str_replace('{$tr_sugestoes}', showEmpreendSugest($doc->campos[$campoSugere], $bd), $sugestoes);
			
		}
		else { // não é processo ou não é guarda-chuva, segue fluxo antigo
			if($doc->empreendID){ // este doc esta associado a um empreendimento ?
				$empreend = new Empreendimento($bd);
				$empreend->load($doc->empreendID);
				$atual = str_replace(array('{$obra_nome}', '{$obra_id}'), array($empreend->get('nome'), $empreend->get('id')), $template['com_obra']);
				$sugestoes = '';
			} else { // ele nao está, então...
				if ($doc->docPaiID) { // verifica se ele possui um documento Pai. Se tiver, verifica se o pai dele está associado a um empreendimento
					$docPai = new Documento($doc->docPaiID);
					$docPai->loadCampos();
					if ($docPai->empreendID) { // o pai esta atribuido a um empreendimento ? se sim,
						$empreend = new Empreendimento($bd);
						$empreend->load($docPai->empreendID);
						$atual = str_replace(array('{$obra_nome}', '{$obra_id}'), array($empreend->get('nome'), $empreend->get('id')), $template['pai_obra']);
						$sugestoes = '';
					}
					else { // ele nao esta atribuido a um empreendimento
						$atual = $template['sem_obra'];
						$sugestoes = $template['table_sugestoes'];
						$sugestoes = str_replace('{$tr_sugestoes}', showEmpreendSugest($doc->campos[$campoSugere], $bd), $sugestoes);	
					}
				}
				else { // o doc nao tem pai e nem esta atribuido a um empreendimento, gera sugestoes
					$atual = $template['sem_obra'];
					$sugestoes = $template['table_sugestoes'];
					$sugestoes = str_replace('{$tr_sugestoes}', showEmpreendSugest($doc->campos[$campoSugere], $bd), $sugestoes);
				}
			}
		}
		
		$vars = array('{$obraAtual}','{$table_sugestoes}','{$empreendMiniBusca}','{$guardachuva}');
		$vals = array($atual        ,$sugestoes          ,showEmpreendMiniBusca('<br />Atribuir este documento ao empreendimento (Busca por Nome ou Unidade):'), $guardachuva);
		
		$html = str_replace($vars, $vals, $template['template']);
		
		return $html;
	}
	
	function atribObra($docID, $obraID, BD $bd, $remove = 0){
		$doc = new Documento($docID);
		$doc->loadDados();
		$doc->loadCampos();
		
		$obras = $doc->getObras();
		
		foreach ($obras as $k => $o) {
			$obras[$k] = $obras[$k]['id'];
		}
		
		foreach ($obras as $k => $o) {
			if($o == $obraID && $remove == 0)
				return array(array('success' => false, 'duplicado' => true));
			if ($o == $obraID) {
				$doc->removeObra($obras[$k]);
				return array(array('success' => true));
			}
		}
		
		$doc->salvaObras(array($obraID));
		return array(array('success' => true));
	}
	
	function atribEmpreend($docID, $obraID, BD $bd, $remover = 0) {
		$doc = new Documento($docID);
		$doc->loadDados();
		$doc->loadCampos();
		
		if ($doc->dadosTipo['nomeAbrv'] == 'pr' && $doc->campos['guardachuva'] == 1) { // verifica se é processo e se ele é guarda-chuva
			if ($remover == 0) { // caso seja uma atribuicao e não remoção,
				// verifica se essa associacao já existe
				$sql = "SELECT * FROM guardachuva_empreend WHERE docID = " .$docID. " AND empreendID = " .$obraID;
				$res = $bd->query($sql);
				if (count($res) != 0) return array(array('success' => false, 'duplicado' => true));
			
				$sql = "INSERT INTO guardachuva_empreend (docID, empreendID) VALUES (".$docID.", ".$obraID.")";
				$res = $bd->query($sql);
				if ($res) {
					doLog($_SESSION['username'], 'Atribuiu o doc de ID '.$docID.' ao empreendimento '.$obraID);
					//LOG NO HIST DO EMPREEND
					$bd->query("INSERT INTO empreend_historico (empreendID,userID,data,tipo,doc_targetID) VALUES ({$obraID},{$_SESSION['id']}, ".time().", 'atribDoc', {$docID})");
					return array(array('success' => true));
				}
				else {
					return array(array('success' => false));
				}
			}
			else { // remove a associacao escolhida
				$sql = "DELETE FROM guardachuva_empreend WHERE docID = " .$docID. " AND empreendID = " .$obraID;
				$res = $bd->query($sql);
				if ($res) {
					doLog($_SESSION['username'], 'Desatribuiu o doc de ID '.$docID.' do empreendimento '.$obraID);
					return array(array('success' => true));
				}
				else {
					return array(array('success' => false));
				}
			}
		}
		else { // nao é um processo guardachuva, segue fluxo antigo
			if ($remover == 1) $obraID = 0;
			$res = $doc->update('empreendID', $obraID);
		
			if ($res){
				doLog($_SESSION['username'], 'Atribuiu o doc de ID '.$docID.' ao empreendimento '.$obraID);
				//LOG no historico do DOC
				$bd->query("INSERT INTO empreend_historico (empreendID,userID,data,tipo,doc_targetID) VALUES ({$obraID},{$_SESSION['id']}, ".time().", 'atribDoc', {$docID})");
				return array(array('success' => true));
			} else {
				return array(array('success' => false));
			}
		}
	}
	
	/**
	 * Retorna lista de processos parecidos
	 * @param array $numPR [digito][tipo][central][ano]
	 * @param String $unOrg
	 * @param int $tipoDoc tipo de doc (por enquanto, essa funcao funciona apenas para processos)
	 * @param BD $bd
	 * @return array [id][numeroComp][emitente][assunto] array de ids de docs parecidos com este
	 */
	function getParecidos($numPR, $unOrg, $tipoDoc, BD $bd) {
		// seleciona todos os processos que possuem a mesma unidade que a passada por parametro
		
		if ($unOrg == "01.07.63.00.00.00 - COORDENADORIA DE PROJETOS E OBRAS (CPO)" || $unOrg == "01.14.16.00.00.00 - COORDENADORIA DE INFRAESTRUTURA (CINFRA)") {
			$sql = "SELECT d.id, d.numeroComp, d.emitente, p.assunto FROM doc AS d INNER JOIN doc_processo AS p ON d.tipoID = p.id WHERE (d.emitente LIKE '%cinfra%' OR d.emitente LIKE '%cpo%') AND d.labelID = $tipoDoc ORDER BY data ASC";
		}
		else {
			$sql = "SELECT d.id, d.numeroComp, d.emitente, p.assunto FROM doc AS d INNER JOIN doc_processo AS p ON d.tipoID = p.id WHERE d.emitente LIKE '%$unOrg%' AND d.labelID = $tipoDoc ORDER BY data ASC";
		}
		$res = $bd->query($sql);
		// retira zeros à esquerda do numero de cada parte do numero do processo
		foreach($numPR as $c => $v) {
			$numPR[$c] = ltrim($v, "0");
		}

		// inicializa as variáveis de retorno
		$retorno = array();
		$ret = "";
		foreach($res as $r) {
			$r = verificaCodificacao($r);

			// se $ret da iteração anterior for não nulo, acrescenta-o ao $retorno
			if ($ret !== "") {
				$retorno[] = $ret;
			}
			$ret = "";
			// divide o numero do processo em partes
			$temp = explode(" ", $r['numeroComp']);
			$partes = explode("-", $temp[1]);
			if (count($partes) != 3) { // se o numero do processo não tiver 3 partes separadas por hifens, o número está fora do padrão
				//$ret = $r;
				//$ret['assunto'] = "----ERRO PROCESSO----";
				continue;
			}

			// monta array do numero do processo sendo analisado
			$pr_aux = array('digito' => ltrim($temp[0], "0"), 'tipo' => ltrim($partes[0]), 'central' => ltrim($partes[1], "0"), 'ano' => ltrim($partes[2], "0"));

			$chars_dif = 0;
			
			// verifica se os números centrais são iguais
			if ($pr_aux['central'] == $numPR['central']) {
				$ret = $r;
				continue;
			}
			// verificação de segurança: verifica se os números centrais não ficaram nulos após a remoção de zeros à esquerda
			// isso só acontece quando o número central for todo igual à zero
			if (strlen($pr_aux['central']) <= 0) continue;
			if (strlen($numPR['central']) <= 0) continue;
			
			if (strlen($pr_aux['central']) != strlen($numPR['central'])) { // se o central digitado tiver tamanho menor do que o encontrado,
				// calcula a diferença de dígitos entre os dois números centrais
				$chars_dif = strlen($pr_aux['central']) - strlen($numPR['central']);
				if ($chars_dif < 0) $chars_dif = $chars_dif * (-1); 
				
				// verifica qual dos dois números possui menos dígitos
				$menor = strlen($numPR['central']);
				if (strlen($numPR['central']) > strlen($pr_aux['central'])) $menor = strlen($pr_aux['central']);
				
				// percorre os dois números centrais contando o número de dígitos diferente
				for ($i = 0; $i < $menor; $i++) {
					if ($numPR['central'][$i] != $pr_aux['central'][$i]) {
						$chars_dif++;
					}
				}

				// se no total houver menos de 1 dígito diferente, acrescenta esse processo ao retorno
				if ($chars_dif <= 1)  {
					$ret = $r;
					//var_dump($ret);
					continue; 
				}

				// calcula novamente a diferença de dígitos entre os dois números centrais
				$chars_dif = strlen($pr_aux['central']) - strlen($numPR['central']);
				if ($chars_dif < 0) $chars_dif = $chars_dif * (-1);
				
				// percorre os dois números centrais, desta vez de trás pra frente
				// isto é necessário, pois se o usuário 'comer' apenas o 1o dígito do número central, a etapa possivelmente acusaria que
				// a diferença entre os dois dígitos é maior do que 1, e portanto ignoraria os processos parecidos neste caso
				for ($i = 1; $i <= $menor; $i++) {
					if ($numPR['central'][strlen($numPR['central']) - $i] != $pr_aux['central'][strlen($pr_aux['central']) - $i]) {
						$chars_dif++;
					}
				}
				// se no total houver menos de 1 dígito diferente, acrescenta esse processo ao retorno
				if ($chars_dif <= 1)  {
					$ret = $r;
					continue; 
				}
				
			}
			elseif (strlen($pr_aux['central']) == strlen($numPR['central'])) { // se os dois números centrais possuem a mesma qtde de dígitos
				// verifica a qtde de chars diferentes existem em numero central
				for ($i = 0; $i < strlen($numPR['central']); $i++) {
					if ($numPR['central'][$i] != $pr_aux['central'][$i]) {
						$chars_dif++;
					}
				}
				if ($chars_dif <= 1) {
					$ret = $r;
					continue;
				}
			}
		}
		
		if ($ret !== "") {
			$retorno[] = $ret;
		}
		
		return $retorno;
	}
	
	/**
	 * Verifica se o documento é sigiloso ou não
	 * @param Documento $doc documento a ser verificado
	 * @return bool true se é sigiloso, false caso contrario
	 */
	function verificaSigilo(Documento $doc) {
		if (!isset($doc)) return false;

		global $bd;
		
		if (!isset($doc->campos['sigiloso']) && stripos($doc->dadosTipo['campos'], "sigiloso") !== false) {
			$sql = "SELECT sigiloso FROM ".$doc->dadosTipo['tabBD']." WHERE id = ".$doc->tipoID;
			$res = $bd->query($sql);
			
			if (count($res) > 0) {
				$doc->campos['sigiloso'] = $res[0]['sigiloso'];
			} 
		}
		
		if (isset($doc->campos['sigiloso']) && $doc->campos['sigiloso'] == 1) {
			return true;
		}
		if ($doc->anexado == true) {
			$docPai = new Documento($doc->docPaiID);
			return verificaSigilo($docPai);
		}
		return false;
	}
	
	/**
	 * Realiza arqivação/desarquivação do documento
	 * @param Documento $doc documento a ser arquivado/desarquivado
	 * @return true em caso de sucesso, false em caso de falha
	 */
	function doArquiva(Documento $doc) {
		if (!isset($doc)) return false;
		if ($doc->arquivado == 0) {
			if (!checkPermission(69)) {
				showError(12);
			}
			$arquivado = 1;
			$tipo = 'arq';
			$ownerID = 0;
			$ownerArea = "";
		}
		else {
			if (!checkPermission(70)) {
				showError(12);
			} 
			$arquivado = 0;
			$tipo = 'desarq';
			// verifica se as variaveis de sessao estao devidamente setadas...
			if (!isset($_SESSION['id']) || !isset($_SESSION['area'])) {
				return false;
			}
			if ($doc->solicDesarquivamento == '0') {
				$ownerID = $_SESSION['id'];
				$ownerArea = '';
			}
			else {
				$user = getUserFromUsername($doc->solicDesarquivamento);
				$user = $user[0];
				
				$ownerID = $user['id'];
				$ownerArea = '';
			}
		}
		
		$doc->arquivado = $arquivado;
		if ($doc->update('arquivado', $arquivado) == true) {
			$doc->doLogHist($_SESSION['id'], '', '', '', $tipo, '', '');
			if ($tipo == 'desarq' && $doc->solicDesarquivamento != '0') {
				$doc->doLogHist($_SESSION['id'], '', 'Despachado automaticamente atendendo pedido de desarquivamento.', $user['nomeCompl'], 'despIntern', '', 'Despacho');
				$doc->update('solicDesarquivamento', '0');
			}
			$doc->update('ownerID', $ownerID);
			$doc->update('OwnerArea', $ownerArea);
			return true;
		}
		else return false;
	}
	
	/**
	 * Mostra formulario para requisicao de processo
	 * @param $doc
	 */
	function showSolicDocForm($doc){
		global $conf;
		if (!isset($doc->dadosTipo['nomeAbrv']))
			$doc->loadTipoData();
		if ($doc->dadosTipo['nomeAbrv'] != 'pr')
			return 'Este documento n&atilde;o &eacute; um Processo e n&atilde;o pode ser solicitado.';
		if (stripos($_SESSION['area'], 'protocolo') === false && isset($doc->solicitante) && ($doc->solicitante != '0' && $doc->solicitante != ""))
			return 'Este documento j&aacute; foi solicitado por ' .$doc->solicitante. '.';
		if (!checkPermission(81))
			showError(12);	
		// se for o protocolo e tiver sido solicitado
		if (stripos($_SESSION['area'], 'protocolo') !== false && $doc->solicitado == 1) 
			return 'Este documento j&aacute; foi solicitado pelo Protocolo.';
		
		// se for o protocolo e não tiver sido solicitado, já cai no caso showSolicDoc
		if (stripos($_SESSION['area'], 'protocolo') !== false && $doc->solicitado == 0)
			return showSolicDoc($doc, $_SESSION['area'], "Solicitado no Teraterm.");
		
		return '<form accept-charset="'.$conf['charset'].'" action="sgd.php?acao=solDocConf&docID='.$doc->id.'&novaJanela=1" method="POST">
				Especifique o motivo da requisi&ccedil;&atilde;o:<br />'
				.geraTextarea('motivo_req', 50, 5, '').'<br />'
				.'<input type="submit" value="Enviar solicita&ccedil;&atilde;o">'
				.'</form>';
	}

	/**
	 * Mostra solicitação de doc
	 * @param $doc
	 * @return html
	 */
	function showSolicDoc(Documento $doc, $area = "", $motivo = "") {
		if (!isset($doc->dadosTipo['nomeAbrv']))
			$doc->loadTipoData();
		if ($doc->dadosTipo['nomeAbrv'] != 'pr')
			return 'Este documento n&atilde;o &eacute; um Processo e n&atilde;o pode ser solicitado.';
		
		if (stripos($area, 'protocolo') === false) { // se não partir do protocolo,...
			if (isset($doc->solicitante) && ($doc->solicitante != '0' && $doc->solicitante != "")) {
				return 'Este documento j&aacute; foi solicitado por ' .$doc->solicitante. '.';
			}
		
			if (!checkPermission(81)) {
				showError(12);
			}

			$doc->update('solicitante', $_SESSION['username']);
			$doc->doLogHist($_SESSION['id'], '', $motivo, '', 'solic', '', 'Motivo');
			return 'Solicita&ccedil;&atilde;o gravada com sucesso. O Protocolo lhe entregar&aacute; o documento assim que ele entrar na CPO.';
		}
		else { // solicitacao partiu de dentro do protocolo. Então,
			if ($doc->solicitado == 1) {
				return 'Este documento j&aacute; foi solicitado pelo Protocolo.';
			}
			
			$doc->update('solicitado', 1);
			$doc->doLogHist($_SESSION['id'], '', $motivo, '', 'solic', '', 'Motivo');
			$user = getUserFromUsername($doc->solicitante);
			$user = $user[0];
			return 'Solicita&ccedil;&atilde;o gravada com sucesso. Quando este documento entrar na CPO, ele ser&aacute; despachado automaticamente para '.$user['nomeCompl'].'.';
		}
	}
	
	/**
	 * mostra solicitação de arquivamento
	 * @param $doc
	 */
	function showSolicArq(Documento $doc) {		
		if (!checkPermission(79)) {
			showError(12);
		}
		
		$doc->update('ownerID', -1);
		$doc->update('OwnerArea', 'Protocolo');
		$doc->doLogHist($_SESSION['id'], '', '', '', 'solicArq', '', '');
		return 'Solicita&ccedil;&atilde;o gravada com sucesso. Encaminhe o documento para o Protocolo.';
	}
	
	/**
	 * mostra solicitação de desarquivamento
	 * @param $doc
	 */
	function showSolicDesarq(Documento $doc, $ajax = false) {
		if ($doc->solicDesarquivamento != "" && $doc->solicDesarquivamento != "0") {
			if ($ajax) return array(array('success' => false, 'feedback' => utf8_encode('Erro: Requisitado por ' .$doc->solicDesarquivamento)));
			return 'O desarquivamento deste documento j&aacute; foi solicitado por ' .$doc->solicDesarquivamento. '.';
		}		
		if (!checkPermission(80)) {
			if ($ajax) return array(array('success' => false, 'feedback' => utf8_encode('Erro de Permissao')));
			showError(12);
		}
		
		$doc->update('solicDesarquivamento', $_SESSION['username']);
		$doc->doLogHist($_SESSION['id'], '', '', '', 'solicDes', '', '');
		if ($ajax) return array(array('success' => true, 'feedback' => utf8_encode('Solicitado com sucesso!')));
		return 'Solicita&ccedil;&atilde;o gravada com sucesso.';
	}
	
	/**
	 * Mostra interface de documentos salvos automaticamente
	 * @param boolean $retornaConteudo variável que indica se o conteudo deve ser mostrado
	 * @return html
	 */
	function showAutoSavedDocs($retornaConteudo = true) {
		$doc = getAutoSavedDoc($retornaConteudo);
		
		if (!$doc['hasDocument']) {
			return '';
		}
		
		$html = '
		<div id="autoSavedDocInterface" style="border: 2px solid rgb(190, 16, 16); padding: 5px; ">
		<center><span style="padding: 5px; font-weight: bold; text-align: center; display: inline; width: 100%;">
		Rescunhos ainda n&atilde;o-salvos:</span><br /><span style="text-align: center; display: inline;">Use esse recurso somente em caso do documento n&atilde;o ter sido salvo anteriormente. Caso contr&aacute;rio, descarte o rascunho.</span>
		</center>
		<br />
		<table width="100%">
			<tr>
				<td width="10%">{$data}</td>
				<td width="15%">{$tipoDoc}</td>
				{$extra}
				<td width="10%">{$link_restaurar}</td>
				<td width="10%">{$link_descartar}</td>
			</tr>
		</table>
		</div>
		<br />
		';
		
		$data = 'Desconhecido';
		if (isset($doc['data'])) $data = $doc['data'];
		$tipoDoc = 'Desconhecido';
		if (isset($doc['tipoDocLabel'])) $tipoDoc = '<b>'.$doc['tipoDocLabel'].'</b>';
		$extra = '';
		$link_restaurar = '<b>[Restaurar]</b>';
		$link_descartar = '[Descartar]';
		
		if (isset($doc['conteudo'])) {
			$conteudo = $doc['conteudo'];
			//$conteudo = json_decode($conteudo);
			
			if ($conteudo !== null && $conteudo !== false) {
				foreach ($conteudo->text as $campo) {
					if ((!isset($campo->nome) || $campo->nome != "assunto") && (!isset($campo->id) || $campo->id != "assunto")) continue;
					
					//$extra .= '<td>Assunto: "' .urldecode(to_utf8($campo->valor)).'"</td>';
					$extra .= '<td>Assunto: "' .$campo->valor.'"</td>';
				}
				
			}
		}
		
		$urlVars = json_decode($doc['urlVars']);
		//$restaurar_url = 'sgd.php';
		$restaurar_url = '';
		$i = 0;
		$abrirNovaJanela = false;
		foreach ($urlVars as $campo => $valor) {
			if ($i == 0) $restaurar_url .= '?';
			else $restaurar_url .= '&';
			
			if ($campo == "novaJanela" && ($valor != 0 && $valor != "0" && $valor != false && $valor != "false")) {
				$abrirNovaJanela = true;
			}
			
			$restaurar_url .= $campo .'='. $valor;			
			$i++;
		}
		
		if ($i != 0) {
			$window = 'doc';
			
			if (isset($urlVars->acao) && ($urlVars->acao == 'cad' || $urlVars->acao == 'novo' || $urlVars->acao == 'resp')) {
				$restaurar_url = 'sgd.php' . $restaurar_url;
			}
			else {
				$restaurar_url = 'sgo.php' . $restaurar_url;
				$window = 'obra';
				$abrirNovaJanela = true;
			}
			
			$restaurar_url .= '&restaurar=true';
			if (!$abrirNovaJanela) {
				$link_restaurar = '<a href="'.$restaurar_url.'">'.$link_restaurar."</a>";
			}
			else {
				$link_restaurar = "<a onclick=\"window.open('".$restaurar_url."','".$window."','width='+screen.width*0.95+',height='+screen.height*0.9+',scrollbars=yes,resizable=yes').focus()\">".$link_restaurar;
				$link_restaurar .= "</a>";
			}
			
			//$link_restaurar .= '(use esse recurso somente em caso do documento n&atilde;o ter sido salvo anteriormente. Caso contr&aacute;rio, descarte o rascunho).';
		}
		else {
			$link_restaurar .= '[Erro na c&oacute;pia de Seguran&ccedil;a]';
		}
		
		$link_descartar = '<a onclick="javascript:descartarAutoSave()">'.$link_descartar.'</a>';
		
		$html = str_replace('{$data}', $data, $html);
		$html = str_replace('{$tipoDoc}', $tipoDoc, $html);
		$html = str_replace('{$extra}', $extra, $html);
		$html = str_replace('{$link_restaurar}', $link_restaurar, $html);
		$html = str_replace('{$link_descartar}', $link_descartar, $html);
		
		//$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Retorna o documento auto-salvado :P
	 * @param boolean $content indicador se a função deve retornar o conteudo/campos do documento
	 */
	function getAutoSavedDoc($content = false) {
		global $bd;
		// seleciona o doc salvo, se hovuer
		if (!$content)
			$sql = "SELECT userID, acao, doc, data, urlVars FROM doc_autosave WHERE userID = ".$_SESSION['id'];
		else
			$sql = "SELECT * FROM doc_autosave WHERE userID = ".$_SESSION['id'];
 
		$res = $bd->query($sql);
		
		// inicializa variáveis
		$conteudo = '';
		$hasDocument = false;
		$data = '';
		$acao = '';
		$tipoDoc = '';
		$tipoDocLabel = '';
		$urlVars = '';
		
		// se achou algum doc, trata variáveis
		if (count($res) > 0) {
			$res = $res[0];
			
			if ($content) {
				$res['content'] = str_replace(array("\n", "\t", "\""), array("", "", addslashes("\"")), $res['content']);
				$conteudo = json_decode(SGDecode($res['content']));
			}
			
			$hasDocument = true;
			$data = date("d/m/Y \&\a\g\\r\a\\v\\e\;\s G:i", $res['data']);
			$acao = $res['acao'];
			$tipoDoc = $res['doc'];
			$tipoDocLabel = getDocTipo($res['doc']);
			// monta label do tipo de doc
			if (count($tipoDocLabel) > 0) {
				$tipoDocLabel = $tipoDocLabel[0]['nome'];
			}
			else {
				$tipoDocLabel = '';
			}
			
			$urlVars = SGDecode($res['urlVars']);
		}
		
		// monta retorno
		if ($content) {
			$ret = array('hasDocument' => $hasDocument, 
						 'tipoDoc' => $tipoDoc, 
						 'tipoDocLabel' => $tipoDocLabel,
						 'acao' => $acao, 
						 'data' => $data, 
						 'conteudo' => $conteudo,
						 'urlVars' => $urlVars);
		}
		else {
			$ret = array('hasDocument' => $hasDocument, 
						 'tipoDoc' => $tipoDoc, 
						 'tipoDocLabel' => $tipoDocLabel,
						 'acao' => $acao, 
						 'data' => $data, 
						 'urlVars' => $urlVars);
		}
		
		
		//var_dump($ret);
		return $ret;
	}
	
	/**
	 * Descarta documento salvado automaticamente pelo SiGPOD
	 * @return boolean $success
	 */
	function descartaAutoSavedDoc($tipo) {
		global $bd;
		
		$sql = "SELECT doc FROM doc_autosave WHERE userID = ".$_SESSION['id'];
		$res = $bd->query($sql);
		
		if (count($res) > 0 && ($res[0]['doc'] == $tipo || $tipo == "any")) {
			$sql = "DELETE FROM doc_autosave WHERE userID = ".$_SESSION['id'];
			
			return $bd->query($sql);
		}
		
		return true;
	}
	
	function verificaCodificacao($vetor) {
		if (is_string($vetor) == true) {
			$vetor = mb_check_encoding($vetor, 'UTF-8') ? $vetor : utf8_encode($vetor);
			return $vetor;
		}
		if (is_array($vetor) == false) {
			return $vetor;
		}
		foreach($vetor as $campo => $valor) {
			$vetor[$campo] = verificaCodificacao($valor);
		}
		return $vetor;
	}
	
	
	function geraREP(Contrato $doc) {
		global $bd;
		require_once 'classes/mpdf51/mpdf.php';
		$template = 'templates/modelo_rep.html';
		$header = file_get_contents("templates/doc_header.html");
		$html = file_get_contents($template);
		
		$docPai = new Documento($doc->docPaiID);
		$docPai->loadCampos();
		
		$numProc = $docPai->campos['numero_pr'];
		//$data = date("d/m/Y", time());
		$empresa = new Empresa($bd);
		$empresa->load($doc->campos['empresaID']);
		
		$obras = $doc->getObras();
		$labelObras = '';
		if ($obras != null) {
			foreach ($obras as $o) {
				$labelObras .= $o['nome'].", ";
			}
		}
		$labelObras = rtrim($labelObras, ", ");
		
		$barcodeFile = 'files/['.$doc->id.']barcode.png';
		
		geraBarcode("".$doc->id, $barcodeFile);
		
		$html = str_replace('{$num_processo}', $numProc, $html);
		//$html = str_replace('{$data}', $data, $html);
		$html = str_replace('{$empresa_contr}', $empresa->get('nome'), $html);
		$html = str_replace('{$nome_obras}', $labelObras, $html);
		$html = str_replace('{$cod_barras}', '<img src="'.$barcodeFile.'" style="height: 30px">', $html);
		
		$html = $header . $html;
		
		//inicializa a variavel pdf com os tamanhos padrao
		$pdf = new mPDF('c','A4',10,'Arial',25,25,10,10,12,5,'P');
		
		$pdf->allow_charset_conversion=true;
		$pdf->charset_in='UTF-8';
		//seta os dados
		//$pdf->SetHTMLHeader($header);
		$pdf->WriteHTML($html);
		
		$pdf->Output('files/['.$doc->id.']relacao_entrada_protocolo.pdf','F');
	}
	
	function geraBarcode($string, $file = '') {
		require_once('classes/barcode/class/BCGFontFile.php');
		require_once('classes/barcode/class/BCGColor.php');
		require_once('classes/barcode/class/BCGDrawing.php');
		require_once('classes/barcode/class/BCGcode128.barcode.php');
		
		// Loading Font
		//$font = new BCGFontFile('./class/font/Arial.ttf', 18);
		$font = 0;
		
		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255);
		
		$drawException = null;
		try {
			//$code = new BCGcode39();
			$code = new BCGcode128();
			$code->setScale(2); // Resolution
			$code->setThickness(30); // Thickness
			$code->setForegroundColor($color_black); // Color of bars
			$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont($font); // Font (or 0)
			$code->parse($string); // Text
		} catch(Exception $exception) {
			$drawException = $exception;
		}
		
		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing($file, $color_white);
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		}
		
		if (!$file) {
			// Header that says it is an image (remove it if you save the barcode to a file)
			header('Content-Type: image/png');
			
			// Draw (or save) the image into PNG format.
			return $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		}
		else {
			$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		}
	}
?>
<?php
	/**
	 * @version 0.0 20/4/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina que lida com os modulos de gerenciamento de obras
	 */
	include_once('includeAll.php');
	includeModule('sgo');

	//verifica se usuario esta logado
	checkLogin(6);
	
	//inicialização de variaveis
	$html = new html($conf);
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	//configurações da pagina HTML
	$html->setTemplate($conf['template']);
	$html->header = "Ger&ecirc;ncia de Obras";
	$html->campos['codPag'] = showCodTela();
	$html->title .= "SGO";
	$html->user = $_SESSION["nomeCompl"];
	$html->path = showNavBar(array());
	
	$sgo = new SGO();
	//seleciona a acao a ser efetuada
	if(isset($_GET['acao'])) {
	
		if($_GET['acao'] == 'buscar') {//acao buscar obra
			$html = $sgo->montaBuscaObra($html, $conf);
						
		} elseif($_GET['acao'] == 'cadastrar') {//acao cadastrar obra
			$html = $sgo->montaCadEmpreend($html, $conf);
		
		} elseif($_GET['acao'] == 'salvarNova') {//acao salvar nova obra
			$html = $sgo->montaSalvaEmpreend($html, $conf, $_SESSION["perm"], null, $_POST, true);
			
		} elseif($_GET['acao'] == 'ver') {//acao ver detalhes da obra
			if (!isset($_GET['obraID'])) {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhuma obra selecionada"));
			} else {
				/*$html->content[4] = '
					</div>
					<div id="c4" class="boxCont">
					{$content4}';*/
				
				$mini = 0;
				if (isset($_GET['mini']) && $_GET['mini'] == 1) {
					$mini = 1;
				}
				$html = $sgo->montaVerObra($html, $conf, $mini);
			}
		
		}elseif($_GET['acao'] == 'verObra') {//acao ver detalhes da obra
			if (!isset($_GET['obraID'])) {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhuma obra selecionada"));
			} else {
				/*$html->content[4] = '
					</div>
					<div id="c4" class="boxCont">
					{$content4}';*/
				
				if (!isset($_GET['empreendID']) || $_GET['empreendID'] == "" || $_GET['empreendID'] == 0) {
					$sql = "SELECT empreendID FROM obra_obra WHERE id = ".$_GET['obraID'];
					$empreendID = $bd->query($sql);
					if (count($empreendID) <= 0) {
						verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhuma obra selecionada"));
					}
					$empreendID = $empreendID[0]['empreendID'];
				}
				$html = $sgo->montaVerEmpreend($html, $empreendID);
				$html->content[2] .= '
					<script type="text/javascript">
						$(document).ready(function() {
							$("#linkObraTab_'.$_GET['obraID'].'").click();
						});
					</script>
				';
			}
		
		} elseif($_GET['acao'] == 'verEmpreend') {//acao ver empreendimento
			if (/*!isset($_GET['obraID']) &&*/ !isset($_GET['empreendID']) || $_GET['empreendID'] == "") {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			} else {
				/*$html->content[4] = '
					</div>
					<div id="c4" class="boxCont">
					{$content4}';*/
				$restaurar = false;
				if (isset($_GET['restaurar']) && ($_GET['restaurar'] == true || $_GET['restaurar'] == 'true')) {
					$restaurar = true;
				}
				//var_dump($_GET['restaurar']);
				
				
				if (isset($_GET['novoContrPr']) && $_GET['novoContrPr'] != "") {					
					$html->content[2] = $sgo->montaVerContrato($html, $_GET['empreendID'], $bd, $restaurar);
					
					if (isset($_GET['restaurar']) && $_GET['restaurar']) {
						$html->showPage();
						exit();
					}
					
					$proc = new Documento($_GET['novoContrPr']);
					$proc->loadCampos();
					
					if ($proc->labelID == 1) {
						if (count($proc->getObras()) <= 0) {
							$html->content[2] .= '
							<script type="text/javascript">
								$(document).ready(function() {
									showInclObraForm('.$_GET['novoContrPr'].', \''.$proc->campos['tipoProc'].'\', '.$proc->campos['guardachuva'].', \''.str_replace(array("\n", "\r"), array("", ""), $proc->campos['unOrgProc']).'\');
								});
							</script>
							';
						}
						else {
							$html->content[2] .= '
							<script type="text/javascript">
								$(document).ready(function() {
									showOrigemRecForm('.$_GET['novoContrPr'].', \''.$proc->campos['tipoProc'].'\', undefined, '.$proc->campos['guardachuva'].', \''.str_replace(array("\n", "\r"), array("", ""), $proc->campos['unOrgProc']).'\');
								});
							</script>
							';
						}
					}
					$html->showPage();
					exit();
				}
				
				$html = $sgo->montaVerEmpreend($html,$_GET['empreendID']);
				if (isset($_GET['obraID']) && $_GET['obraID'] != "") {
					$html->content[2] .= '
						<script type="text/javascript">
							$(document).ready(function() {
								$("#linkObraTab_'.$_GET['obraID'].'").click();
							});
						</script>
					';
				}
			}
			
		} elseif($_GET['acao'] == 'editEmpreend') {//acao editar empreendimento
			if(!isset($_GET['empreendID'])){
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhum empreendimento selecionado"));
			} else {
				$html = $sgo->montaEditEmpreend($html, $_GET['empreendID'], $_SESSION['perm']);
			}

		} elseif($_GET['acao'] == 'editEquipe') {
			if(!isset($_GET['empreendID'])){
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhum empreendimento selecionado"));
			} else {
				$html = $sgo->montaEditEquipe($html, $_GET['empreendID'], $_SESSION['perm'], $_POST);
			}

		} elseif($_GET['acao'] == 'salvaEquipe') {
			if(!isset($_GET['empreendID'])){
				//verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhum empreendimento selecionado"));
				$ret = array(array('success' => false));
			} else {
				//$html = $sgo->montaSalvaEquipe($html, $_GET['empreendID'], $_SESSION['perm']);
				$ret = $sgo->montaSalvaEquipe($html, $_GET['empreendID'], $_SESSION['perm'], $_POST);
			}
			print json_encode($ret);
			exit();
			
		} elseif($_GET['acao'] == 'novaMsg') {
			if(!isset($_GET['empreendID'])){
				//$ret = array(array('success' => false));
				$html->content[1] = 'Nenhum empreendimento selecionado.';
			} else {
				$sgo->SalvaMsg($html, $_GET['empreendID'], $_SESSION['perm'], $_POST, $bd);
			}
			//print json_encode($ret);
			//exit();
		} elseif($_GET['acao'] == 'newObra') {//acao ver detalhes da obra
			if (!isset($_GET['empreendID'])) {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhum empreendimento selecionada"));
			} else {
				$html = $sgo->montaEditObra($html, 0, $_GET['empreendID'], $conf, $_SESSION["perm"]);
			}
		
		} elseif($_GET['acao'] == 'edit') {//acao ver detalhes da obra
			if (!isset($_GET['obraID'])) {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhuma obra selecionada"));
			} else {
				$html = $sgo->montaEditObra($html, $_GET['obraID'], $_GET['empreendID'], $conf, $_SESSION["perm"]);
			}
			
		} elseif($_GET['acao'] == 'cadHome') {//acao salvar nova obra
			$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2,$bd);
			$html->content[1] = showHomeObrasGmaps();

		} elseif($_GET['acao'] == 'salvar') {//acao salvar modif em obra
			if (!isset($_GET['obraID'])) {
				verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "nenhuma obra selecionada"));
			} else {
				$html = $sgo->montaObraSalva($html, $conf, $_SESSION["perm"], $_GET['obraID'], $_GET['empreendID'], $_POST);
			}
		
		} elseif($_GET['acao'] == 'salvarEmpreend') {//acao salvar mod em empreendimento
			if (!isset($_GET['empreendID'])) {
				$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhum empreendimento para editar"));
			} else {
				$html = $sgo->montaSalvaEmpreend($html, $conf, $_SESSION["perm"], $_GET['empreendID'], $_POST);
			}
			
		} elseif($_GET['acao'] == 'salvaRec') {//acao salvar recursos
			if(!isset($_GET['recID']) ||!isset($_GET['obraID']) || !isset($_GET['empreendID']) || !isset($_GET['rec_valor']) || !isset($_GET['rec_origem']) || !isset($_GET['rec_prazo']) || !isset($_GET['rec_justif']))
				$ret = array(array('success' => false, 'errorFeedback' => 'Faltam parametros'));
			else
				$ret[] = $sgo->salvaRecAJAX($_GET['recID'],$_GET['empreendID'], $_GET['obraID'], array('montante' => $_GET['rec_valor'], 'origem' => SGEncode(urldecode($_GET['rec_origem']),ENT_QUOTES, null, false), 'prazo' => $_GET['rec_prazo'], 'justificativa' => SGEncode(urldecode($_GET['rec_justif']),ENT_QUOTES, null, false)), $bd);
			
			print json_encode($ret);
			exit();

		} elseif ($_GET['acao'] == 'salvaEtapa') {//acao salvar mod em etapa
			//TODO precisa de revisao pos empreendimento ao usar
			if(!isset($_GET['obraID']) || !isset($_GET['tipoID']) || !isset($_GET['respID']) || !isset($_GET['procID']))
				$ret = array(array('success' => false));
			else
				$ret[] = $sgo->salvaEtapaAJAX($_GET['obraID'], array('tipoID' => $_GET['tipoID'], 'respID' => $_GET['respID'], 'procID' => $_GET['procID']));
				
			print json_encode($ret);
			exit();
		} elseif ($_GET['acao'] == 'verPlan') {
			if (!isset($_GET['empreendID'])) {
				print "ID do Empreendimento n&atilde;o especificado.";
				exit();
			}
			else {
				if (!isset($_GET['procIT'])) {
					$_GET['procIT'] = 0;
				}
				
				print $sgo->montaPlanejamento($_GET['empreendID'], $_GET['procIT']);
				exit();	
			}
		} elseif($_GET['acao'] == 'salvaFase') {
			$html->path = showNavBar(array(), 'mini'); //var_dump($_POST);exit();
			if(isset($_GET['obraID']) && isset($_GET['empreendID']) && isset($_POST['etapaID']) && isset($_POST['faseTipoID'])){
				$html = $sgo->montaSalvaFase($html, $_GET['empreendID'], $_GET['obraID'], $_POST['etapaID'], $_POST['faseTipoID'], $_POST);
			} else {			
				$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
			}
		}
		elseif ($_GET['acao'] == 'salvaResponsavel') {
			if (!isset($_GET['empreendID']) || !isset($_GET['obraID']) || !isset($_GET['tipoEtapa'])) {
				$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados insuficientes"));
			}
			else {
				//$html->content[1] = $sgo->montaSalvaResponsavelEtapa($html, $_GET['empreendID'], $_GET['obraID'], $_GET['tipoEtapa'], $_POST);
				$sgo->montaSalvaResponsavelEtapa($html, $_GET['empreendID'], $_GET['obraID'], $_GET['tipoEtapa'], $_POST);
			}
		} elseif($_GET['acao'] == 'getCaractEspecCampo'){
			if(!isset($_GET['campo']))
				print json_encode(array(array('success' => false, 'htmlCode' => '')));
			else {
				$fase = new Fase();
				$campos_especificos['lajes'] = array('nomeAbrv' => 'lajes', 'label' => 'Sobrecarga Diferenciada de Lajes', 'tipo' => '');
				$campos_especificos['residuos'] = array('nomeAbrv' => 'residuos', 'label' => 'Gera&ccedil;&atilde;o de res&iacute;duos', 'tipo' => '');
				$campos_especificos['anvisa'] = array('nomeAbrv' => 'anvisa', 'label' => '&Aacute;rea com restri&ccedil;&otilde;es da Anvisa', 'tipo' => '');
				$campos_especificos['gde_potencia'] = array('nomeAbrv' => 'gde_potencia', 'label' => 'Equipamento de Grande Pot&ecirc;ncia', 'tipo' => 'input');
				$campos_especificos['divisorias'] = array('nomeAbrv' => 'divisorias', 'label' => 'Divis&oacute;rias', 'tipo' => 'select');
				$campos_especificos['forro'] = array('nomeAbrv' => 'forro', 'label' => 'Forro', 'tipo' => 'select');
				$campos_especificos['isolamento_acustico'] = array('nomeAbrv' => 'isolamento_acustico', 'label' => 'Isolamento ac&uacute;stico', 'tipo' => '');
				$campos_especificos['gerador'] = array('nomeAbrv' => 'gerador', 'label' => 'Gerador', 'tipo' => '');
				
				if($campos_especificos[$_GET['campo']]['tipo'] == ''){
					print json_encode(array(array('success' => true, 'htmlCode' => '')));
				} else {
					print json_encode(array(array('success' => true, 'label:' => 'Valor' , 'htmlCode' => $fase->montaCampo($campos_especificos[$_GET['campo']]))));
				}
			}
			exit();
		}
		elseif ($_GET['acao'] == 'cadContr') {
			if (!isset($_POST['empreendID']) || $_POST['empreendID'] == "" || !isset($_POST['_numProcContr']) || $_POST['_numProcContr'] == "") {
				$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados insuficientes"));
			}
			else {
				$html = $sgo->salvaContrato($html, $_POST);
			}
		}
		elseif ($_GET['acao'] == 'getProcObras') {
			if (!isset($_GET['docID']) || $_GET['docID'] == "") {
				exit();
			}
			else {
				print json_encode(array($sgo->getObrasGuardaChuva($_GET['docID'])));
				exit();
			}
		}
		elseif ($_GET['acao'] == 'editContrObra') {
			if (!isset($_POST['docID']) || $_POST['docID'] == '') {
				print json_encode(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
				exit();
			}
			else {
				print json_encode(array($sgo->salvaEditContrObra($_POST['docID'], $_POST)));
				exit();
			}
		}
		elseif ($_GET['acao'] == 'editContrRec') {
			if (!isset($_POST['docID']) || $_POST['docID'] == '') {
				print json_encode(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
				exit();
			}
			else {
				print json_encode(array($sgo->salvaEditContrRec($_POST['docID'], $_POST)));
				exit();
			}
		} elseif ($_GET['acao'] == 'aditivar_contrato') {
			if ((!isset($_POST['contratoID']) || $_POST['contratoID'] == '') && (!isset($_POST['campo']) || $_POST['campo'] == '') && (!isset($_POST['valor']) || $_POST['valor'] == '') && (!isset($_POST['motivo']) || $_POST['motivo'] == '')) {
				print json_encode(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
				exit();
			} else {
				print json_encode(array($sgo->aditivaContrato($_POST['contratoID'], $_POST['campo'],str_replace(',', '.', str_replace('.', '', $_POST['valor'])), SGEncode(urldecode($_POST['motivo']),ENT_QUOTES, null, false))));
				exit();
			}
			
		} elseif ($_GET['acao'] == 'editar_aditivo') {
			if ((!isset($_POST['contratoID']) || $_POST['contratoID'] == '') && (!isset($_POST['campo']) || $_POST['campo'] == '') && (!isset($_POST['aditivoID']) || $_POST['aditivoID'] == '') && (!isset($_POST['valor']) || $_POST['valor'] == '') && (!isset($_POST['motivo']) || $_POST['motivo'] == '')) {
				print json_encode(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
				exit();
			} else {
				print json_encode(array($sgo->editarAditivoContrato($_POST['contratoID'], $_POST['aditivoID'], $_POST['campo'], str_replace(',', '.', str_replace('.', '', $_POST['valor'])), SGEncode(urldecode($_POST['motivo']),ENT_QUOTES, null, false))));
				exit();
			}
		}
		elseif ($_GET['acao'] == 'novaITsuplementar') {
			$html->path = showNavBar(array(), 'mini'); //var_dump($_POST);exit();
			if(isset($_POST['empreendID'])){
				$html = $sgo->montaSalvaITSuplementar($html, $_POST, $bd);
			} else {			
				$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes"));
			}
		}
		elseif ($_GET['acao'] == 'showFaseAjax') {
			//print ;
			if(isset($_GET['empreendID']) && isset($_GET['obraID']) && isset($_GET['etapaTipoID']) && isset($_GET['faseTipoID'])){
				print $sgo->montaVerFase($_GET['empreendID'], $_GET['obraID'], $_GET['etapaTipoID'], $_GET['faseTipoID'], $bd);
			}
			else {
				print "Erro: Dados insuficientes";
			}
			exit();
		}
		elseif ($_GET['acao'] == 'showRespEtapaAjax') {
			//print ;
			if(isset($_GET['empreendID']) && isset($_GET['obraID']) && isset($_GET['etapaTipoID'])){
				print $sgo->montaVerRespEtapa($_GET['empreendID'], $_GET['obraID'], $_GET['etapaTipoID'], $bd);
			}
			else {
				print "Erro: Dados insuficientes";
			}
			exit();
		}
		elseif ($_GET['acao'] == 'showItSuplementarAjax') {
			//print ;
			if(isset($_GET['empreendID'])){
				print $sgo->showItSuplementarForm($_GET['empreendID'], $bd);
			}
			else {
				print "Erro: Dados insuficientes";
			}
			exit();
		}
		elseif ($_GET['acao'] == 'verDocsPend') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerDocsPend($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verFinancas') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerFinancas($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verLivroObra') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerLivroObras($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verQuestionamentos') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerQuestionamentos($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verContratos') {
			if (isset($_GET['empreendID'])) {
				$restaurar = false;
				if (isset($_GET['restaurar']) && ($_GET['restaurar'] == true || $_GET['restaurar'] == 'true')) {
					$restaurar = true;
				}
				
				$html->content[2] = $sgo->montaVerContrato($html, $_GET['empreendID'], $bd, $restaurar);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verMedicoes') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerMedicoes($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verMensagens') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerMensagens($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif ($_GET['acao'] == 'verHistorico') {
			if (isset($_GET['empreendID'])) {
				$html->content[2] = $sgo->montaVerHistorico($html, $_GET['empreendID'], $bd);
			}
			else {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			}
		}
		elseif($_GET['acao'] == 'restaurarIT') {//acao ver empreendimento
			/*if (!isset($_GET['empreendID']) || $_GET['empreendID'] == "") {
				$html->content[2] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "Nenhuma obra ou empreendimento selecionados"));
			} else {*/
			$doc = getAutoSavedDoc(true);
			
			if ($doc['tipoDoc'] != 'it') {
				
			}
			else {
				$conteudo = $doc['conteudo'];
				$campos = $conteudo->text;
				
				$obraID = 0;
				$etapaTipoID = 0;
				$faseTipoID = 0;
				foreach($campos as $c) {
					//var_dump($c);
					if ($c->id == 'empreendID')
						$empreendID = $c->valor;
					elseif ($c->id == 'etapaTipoID')
						$etapaTipoID = $c->valor;
					elseif ($c->id == 'faseTipoID')
						$faseTipoID = $c->valor;
					elseif ($c->id == 'obraID')
						$obraID = $c->valor;
				}
				
				//var_dump($empreendID);
				//var_dump($campos);
				
				if ($obraID == 0) {
					$html = $sgo->montaVerEmpreend($html, $empreendID);
					//$html->content[2] .= '<script type="text/javascript" src="scripts/sgd_autosave.js?r={$randNum}"></script>';
					
					$comando = '';
					if ($etapaTipoID == 0 && $faseTipoID == 0) {
						$comando = 'showItSuplementar('.$empreendID.', true);';
					}
					else {
						$comando = 'carregaFase('.$empreendID.', '.$obraID.', '.$etapaTipoID.', '.$faseTipoID.', true);';
					}
					
					//var_dump($comando);
					
					$html->content[2] .= '
					<script type="text/javascript">
						$(document).ready(function() {
							$("#tabs").tabs("select", 1);
							
							'.$comando.'
						});
					
					</script>
					';
				}
			}
			
		}
		else {
			$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "P&aacute;gina inv&aacute;lida"));
		}
	} elseif($_GET['acao'] == 'obra_mini_busca') {
		
	} else {
		$html->content[1] = verObraFeedback(array("success" => false, "errorNo" => 1, "errorFeedback" => "P&aacute;gina inv&aacute;lida"));
	}
	
	$html->showPage();
?>
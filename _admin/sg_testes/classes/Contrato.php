<?php
class Contrato extends Documento {
	/**
	 * Define o tipo do processo Pai
	 * @var string
	 */
	private $tipoProc;
	
	
	/**
	 * Método construtor de contrato
	 * @param int $id docID
	 */
	function __construct($id) {
		parent::__construct($id);
		
		$tipoProc = '';
	}
	
	/*function loadDados() {
		parent::loadDados();
		
		if ($this->docPaiID == 0)
			return;
			
		$pai = new Documento($this->docPaiID);
		$pai->loadCampos();
		
		$this->tipoProc = $pai->campos['tipoProc'];
	}*/
	
	/**
	 * Método que anexa este contrato a um processo
	 * @param id $procID id do processo
	 */
	public function anexarContrato($procID) {
		$ret = anexarDoc($this->id, $procID);

		// se a anexação ocorreu com sucesso, atualiza campos
		if ($ret[0]['success']) {
			$this->anexado = 1;
			$this->docPaiID = $procID;
		}
		
		// retorna status da anexação
		return $ret;
	}
	
	/**
	 * Função que salva os recursos associados a este contrato
	 * @param array $recursos [id do recurso][valor]
	 */
	public function salvaRecursos($recursos) {
		global $bd;
		
		// se não há recursos para serem inseridos, retorna
		if (count($recursos) <= 0)
			return;
		
		// string de recursos inseridos, para ser salvo no log
		$listaRecursos = '';
		
		// percorre os recursos e monta sql correspondente
		$sql = "INSERT INTO obra_contrato_recurso (`contratoID`, `recursoID`, `valor`) VALUES ";
		foreach($recursos as $rec => $valor) {
			// trata valor
			if ($valor == "" || $valor < 0) {
				$valor = 0;
			} 
			
			// monta sql
			$sql .= '('.$this->id.', '.$rec.', '.$valor.'), ';
			
			// monta lista de recursos
			$listaRecursos .= '[id: '.$rec.' valor: R$'.$valor.'], ';
		}
		// remove espaços e vírgulas do fim do sql e da lista (string) de recursos
		$sql = trim($sql, ", ");
		$listaRecursos = trim($listaRecursos, ", ");
		
		// executa query
		$ret = $bd->query($sql);
		
		// se salvou no bd com sucesso, salva histórico
		if ($ret) {
			$this->doLogHist($_SESSION['id'], '', '', '', 'salvaRec', '', '');
			doLog($_SESSION['username'], 'Salvou/Adicionou origem de recursos ao contrato '.$this->id.': '.$listaRecursos);			
		} 
		
		// retorna status
		return $ret;
	}
	
	/**
	 * Salva edição dos recursos atribuidos a este contrato
	 * @param array $recursos [id recurso][valor]
	 */
	public function salvaEditRec($recursos) {
		// se não há recursos passados por parâmetro, dados insuficientes
		if (count($recursos) <= 0)
			return array("success" => false, "errorNo" => 3, "errorFeedback" => "Dados Insuficientes");
		
		// deleta vínculo com recursos e salva denovo
		if ($this->delRecursos()) {
			if ($this->salvaRecursos($recursos)) {
				return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
			}
		}
		
		// retorna status
		return array("success" => false, "errorNo" => 4, "errorFeedback" => "");
	}
	
	/**
	 * Apaga vínculos deste contrato com todas os recursos
	 */
	private function delRecursos() {
		global $bd;
		$sql = "DELETE FROM obra_contrato_recurso WHERE contratoID = ".$this->id;
		
		return $bd->query($sql);
	}

	/**
	 * Salva vínculos do contrato com os responsáveis por parte da empresa
	 * @param mixed $post
	 */
	public function salvaFunc($post) {
		global $bd;
		
		$filesFeedback = array();
		
		$i = 1;
		// percorre todos os funcionarios passados por post
		while(isset($post['empresaFuncID'.$i])) {
			$arquivoART = '';
			$ret = '';
			
			$crea = $post['empresaFuncID'.$i];
			$tipo = $post['tipoFunc'.$i];
			
			// se crea ou tipo forem vazios, não faz nada
			if ($crea == "" || $tipo == "") {
				$i++;
				continue;
			}
			
			// ignora arquivo de art se ele não for fornecido
			if ($_FILES['funcART'.$i] != "") {
				$ret = $this->uploadFile('funcART'.$i);
				$filesFeedback[] = $ret;
				$arquivoART = $ret['file']; 
			}
			
			// insere
			$sql = "INSERT INTO contrato_empresa_resp (docID, crea, ART, tipo) VALUES ";
			$sql .= "(".$this->id.", ".$crea.", '".$arquivoART."', '".$tipo."')";
			
			$bd->query($sql);
			
			doLog($_SESSION['username'], 'Criou vínculo do funcionário de Crea '.$crea.' com o contrato '.$this->id);
			
			$i++;
		}
	}
	
	/**
	 * Salva edição de funcionários
	 * @param mixed $post
	 */
	public function salvaEditFunc($post) {
		global $bd;
		
		// se a empresa passada for diferente da empresa atual, deleta o vínculo com empresa antiga
		if ($this->campos['empresaID'] != $post['empresaID']) {
			$sql = "DELETE FROM contrato_empresa_resp WHERE docID = ".$this->id;
			$bd->query($sql);
			
			doLog($_SESSION['username'], 'Editou vínculo de funcionários com o contrato '.$this->id);
		}
		// atualiza campo
		$this->updateCampo('empresaID', $post['empresaID']);
		$this->campos['empresaID'] = $post['empresaID'];
		
		$sql = '';
		$filesFeedback = array();
		
		$i = 0;
		// percorre funcionários passados por parâmetro e insere no bd
		while (isset($post['empresaFuncID'.$i])) {
			$arquivoART = '';
			$ret = '';
			
			$crea = $post['empresaFuncID'.$i];
			$tipo = $post['tipoFunc'.$i];
			
			// se crea ou tipo forem nulos, não faz nada
			if ($crea == '' || $tipo == '') {
				$i++;
				continue;
			}
			
			// ignora ART caso não tenha sido passada
			if ($_FILES['funcART'.$i] != "") {
				$ret = $this->uploadFile('funcART'.$i);
				$filesFeedback[] = $ret;
				$arquivoART = $ret['file']; 
			}
			
			// se o campo novoFuncX for = 0, é um novo funcionário.. portanto, insere
			if ($post['novoFunc'.$i] == 0) {
				$sql = "INSERT INTO contrato_empresa_resp (docID, crea, ART, tipo) VALUES ";
				$sql .= "(".$this->id.", ".$crea.", '".$arquivoART."', '".$tipo."')";
			}
			else {
				// caso contrário, é um funcionário com vínculo já existente, atualiza vínculo
				$sql = "UPDATE contrato_empresa_resp SET ";
				$sql .= " crea = ".$crea.", ";
				// se ART não foi passada, ignora
				if ($arquivoART != "")
					$sql .= "ART = '".$arquivoART."', ";
					
				$sql .= "tipo = '".$tipo."' WHERE docID = ".$this->id." AND crea = ".$post['novoFunc'.$i];
			}
			
			$bd->query($sql);
			
			$i++;
			
			doLog($_SESSION['username'], 'Adicionou vínculo do funcionário de CREA '.$crea.' ao contrato '.$this->id);
		}
		
		return array("success" => true);
	}
	
	/**
	 * Salva responsáveis pelo contrato CPO
	 */
	public function salvaRespCPO() {
		global $bd;
		
		$data = time();
		
		// array de usuários responsáveis já inseridos, para que não tente inserir 2x um mesmo usuário
		$inseridos = array();
		
		// seleciona Diretor da tabela de usuários
		$sql = "SELECT id FROM usuarios WHERE flagRespContr = 1 AND ativo = 1";
		$diretor = $bd->query($sql);
		
		// percorre diretores e insere (em tese, era pra ter 1 só...)
		foreach ($diretor as $d) {
			if ($d['id'] == 0)
				continue;
			
			// tipo = 1 -> diretor
			$sql = "INSERT INTO contrato_cpo_resp (docID, userID, tipo, data) VALUES ";
			$sql .= "(".$this->id.", ".$d['id'].", 1, ".$data.")";

			// seta que este usuário já está inserido
			$inseridos[$d['id']] = true;
			
			$bd->query($sql);
		}
		
		// seleciona coordenador e insere
		$sql = "SELECT id FROM usuarios WHERE flagRespContr = 2 AND ativo = 1";
		$coordenador = $bd->query($sql);
		
		// percorre lista de coordenadores (em tese, era pra ter 1 só)
		foreach ($coordenador as $c) {
			if (isset($inseridos[$c['id']]) || $c['id'] == 0)
				continue;
			
			// tipo = 2 -> coordenador
			$sql = "INSERT INTO contrato_cpo_resp (docID, userID, tipo, data) VALUES ";
			$sql .= "(".$this->id.", ".$c['id'].", 2, ".$data.")";

			// seta que o usuário já está inserido
			$inseridos[$c['id']] = true;
			
			$bd->query($sql);
		}
		
		// seleciona os responsáveis pelas obras setadas para este contrato
		$sql = "SELECT responsavelProjID 
				FROM obra_doc AS oc INNER JOIN obra_obra AS o ON o.id = oc.obraID
				WHERE oc.docID = ".$this->id;
		
		$respObras = $bd->query($sql);
		
		// se não retornar nada, seta array vazia para que não seja gerado warning no foreach
		if (count($respObras) <= 0)
			$respObras = array();
		
		// percorre responsáveis das obras e salva
		foreach ($respObras as $r) {
			if (isset($inseridos[$r['responsavelProjID']]) || $r['responsavelProjID'] == 0)
				continue;
				
			// tipo = 0 -> responsável por obra
			$sql = "INSERT INTO contrato_cpo_resp (docID, userID, tipo, data) VALUES ";
			$sql .= "(".$this->id.", ".$r['responsavelProjID'].", 0, ".$data.")";

			$inseridos[$r['responsavelProjID']] = true;
			
			$bd->query($sql);
		}
		
		// carrega processo pai para verificar a quais empreendimentos este contrato está vinculado
		// (obs: é necessário este passo por que o processo pode ser guarda-chuva)
		$proc = new Documento($this->docPaiID);
		$proc->loadCampos();
		
		// se não for guardachuva
		if ($proc->campos['guardachuva'] == 0) {
			// seleciona empreendimento
			$sql = "SELECT responsavelID FROM obra_empreendimento WHERE id = ".$proc->empreendID;
			$respEmpr = $bd->query($sql);
			
			// percorre lista de empreendimento (era pra ter 1 só)
			foreach($respEmpr as $e) {
				if (isset($inseridos[$e['responsavelID']]) || $e['responsavelID'] == 0)
					continue;
					
				// insere... tipo = 3 -> responsável pelo empreendimento
				$sql = "INSERT INTO contrato_cpo_resp (docID, userID, tipo, data) VALUES ";
				$sql .= "(".$this->id.", ".$e['responsavelID'].", 3, ".$data.")";
				
				// seta que já foi inserido
				$inseridos[$e['responsavelID']] = true;
				
				$bd->query($sql);
				
			}
		}
		else { // é guardachuva... :(
			// seleciona lista de empreendimentos a qual este processo está vinculado
			$sql = "SELECT empreendID FROM guardachuva_empreend WHERE docID = ".$proc->id;
			$empreends = $bd->query($sql);
			
			// se não retornar nada, seta array vazia para que não seja gerado warning no foreach
			if (count($empreends) <= 0)
				$empreends = array();
			
			// percorre lista
			foreach($empreends as $e) {
				$sql = "SELECT responsavelID FROM obra_empreendimento WHERE id = ".$e['empreendID'];
				$respEmpr = $bd->query($sql);
				
				// se não achou empreendimento ou o usuário já foi inserido, não faz nada
				if (count($respEmpr) == 0 || $respEmpr[0]['responsavelID'] == 0 || isset($inseridos[$respEmpr[0]['responsavelID']]))
					continue;
					
				$sql = "INSERT INTO contrato_cpo_resp (docID, userID, tipo, data) VALUES ";
				$sql .= "(".$this->id.", ".$respEmpr[0]['responsavelID'].", 3, ".$data.")";
				
				// seta inserido
				$inseridos[$respEmpr[0]['responsavelID']] = true;
				
				$bd->query($sql);
			}
			
			
		}
		
	}
	
	/**
	 * Desativa vínculo com funcionário
	 * @param int $crea
	 * @return array[success]
	 */
	public function desativaFunc($crea) {
		global $bd;
		
		// pega data de hoje, em timestamp
		$data = time();
		
		// monta sql para atualizar o funcionário
		$sql = "UPDATE contrato_empresa_resp SET ativo = 0, dataDesativado = ".$data." WHERE docID = ".$this->id." AND crea = ".$crea;
		
		// executa query
		$res = $bd->query($sql);
		
		// se ocorreu tudo bem,
		if ($res) {
			// salva no log do sistema
			doLog($_SESSION['username'], 'Desativou funcionário de Crea = '.$crea);
			return array("success" => true);
		}
		else {
			return array("success" => false);
		}
		
	}
	
	/**
	 * Mostra formulario de Cadastramento/Criacao de documento
	 * @param Empreendimento $empreend
	 * @param boolean $novaJanela
	 * @param connection $bd
	 * @param boolean $restaurar
	 */
	public static function showForm($empreend, $novaJanela, BD $bd, $restaurar = false){
		global $conf;
		$tipo = 'contr';
		
		// verificação de segurança: há usuário coordenador e diretor no bd
		// flag = 1 -> diretor, 2 -> coordenador
		$sql = "SELECT id FROM usuarios WHERE ativo = 1 AND (flagRespContr = 1 OR flagRespContr = 2)";
		$res = $bd->query($sql);
		
		if (count($res) < 2) {
			return "N&atilde;o existe Coordenador e/ou Diretor ativo(s) cadastrado(s) no Banco de Dados.<br />
			 		É necessário setar na &aacute;rea de Administra&ccedil;&atilde;o os respons&aacute;veis.<br />
			 		Por favor, contacte seu administrador.";
		}
		
		// define arquivo de template
		$template = "templates/template_cadcontr.php";
		// carrega arquivo de template
		$html = file_get_contents($template);

		// monta os campos de busca
		$dados = getDocTipo($tipo);
		
		// variaveis que vao guardar os campos gerais e de busca e emitente
		$cGeral = '';
		$cBusca = '';
		$cGeralNome = '';
		$cEmitente =  '';
		$conteudo = '';
		$cBuscaNomes = ''; 
		$cDir = '';
		
		// gera campos para cadastro
		// separa os campos
		$campos = explode(",", $dados[0]['campos']);
		
		// para cada campo do documento
		foreach ($campos as $c) {
			// monta o HTML do campo
			$c = montaCampo($c, 'cad', $dados, false);
			
			$c['nomeCampo'] = explode(",",$c['nome']);
			if(strpos($dados[0]['campoBusca'], $c['nomeCampo'][0]) === false){
				// nao eh campo de busca, cria o input na parte de campos
				if($cGeralNome) $cGeralNome .= ",";
				$cGeralNome .= $c['nome'];
				if ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) {
					$cGeral .= '<tr class="c" style="display: none;"><td style="width: 25%;"><b>'.$c['label'].':</b></td><td style="width: 25%;">'.$c['cod'].'</td></tr>';
				}
				else {
					//if ($c['nome'] != 'dataAssinatura' && $c['nome'] != 'prazoContr' && $c['nome'] != 'vigenciaContr' && $c['nome'] != 'inicioProjObra' && $c['nome'] != 'prazoProjObra' && $c['nome'] != 'dataTermino') {
					if (!Contrato::campoNaDireita($c['nome'])) {
						$cGeral .= '<tr class="c"><td style="width: 25%;"><b>'.$c['label'].':</b></td><td style="width: 25%;">'.$c['cod'].'</td></tr>';
					}
					else {
						$cDir .= '<tr class="c"><td style="width: 25%;"><b>'.$c['label'].':</b></td><td style="width: 25%;">'.$c['cod'].'</td></tr>';
					}
				}
				
			}else{
				// eh campo de busca, cria o input na area de busca
				$cBusca .= '<b>'.$c['label'].':</b> '.$c['cod'];
				$cBuscaNomes .= $c['nome'].',';
				// cria inputs ocultos no campo geral para passar as infos de busca para prox pagina
				foreach (explode(",",$c['nome']) as $campo) {
					$cGeral .= '<input type="hidden" id="_'.$campo.'" name="_'.$campo.'" value="" />';
				}
			}
		}
		// tira a virgula do final
		$cBuscaNomes = rtrim($cBuscaNomes,",");
			
		// adicao dos campos ocultos
		$cBusca .= '
		<input type="hidden" name="labelID" id="labelID" value="'.$dados[0]['id'].'" />
		<input type="hidden" name="tabBD" id="tabBD" value="'.$dados[0]['tabBD'].'" />';
			
		// adicao da tabela para alinhar os campos gerais
		$cGeral .= '<input type="hidden" name="tipoDocCad" id="tipoDocCad" value="'.$tipo.'" /> 
		<input type="hidden" name="camposBusca" id="camposBusca" value="'.$cBuscaNomes.'" />
		<input type="hidden" name="id" id="id" value="0" />';
		$cGeral .= '<input type="hidden" name="action" id="action" value="cad" />'; 
		$cGeral .= '<input type="hidden" name="camposGerais" id="camposGerais" value="'.$cGeralNome.'" />';
		$cGeral .= '<input type="hidden" name="empreendID" value="'.$empreend->get('id').'" />';
		$cGeral .= '<input type="hidden" name="inclObras" id="inclObras" />';
		$cGeral .= '<input type="hidden" name="inclRecursos" id="inclRecursos" />';
		$cGeral .= '<input type="hidden" name="tipoProc" id="tipoProc" />';
		
		// coloca os inputs dentro da tabela
		$cGeral = '<table width="80%" border="0">'.$cGeral.'</table>';
		$cDir = '<table width="80%" border="0">'.$cDir.'</table>';		
		
		// coloca os elementos no template nas posicoes corretas
		$html = str_replace('{$nova_janela}', $novaJanela, $html);
		$html = str_replace('{$campos_busca}', $cBusca, $html);
		$html = str_replace('{$campos}', $cGeral, $html);
		$html = str_replace('{$campos_dir}', $cDir, $html);
		$html = str_replace('{$emitente}', $cEmitente, $html);
		$html = str_replace('{$empresa_interface}', Empresa::showContrForm($bd), $html);
		//$html = str_replace('{$empresa}', Empresa::showContrForm($bd), $html);
		
		$html = '<script type="text/javascript" src="scripts/sgd_autosave.js?r={$randNum}"></script>' . $html;

		if ($restaurar) {
			$html .= '
			<script type="text/javascript">$(document).ready(function() {
				restaurarDoc("cadForm"); 
			});</script>';
		}
		/*else {
			$html .= '<script type="text/javascript">$(document).ready(function() { openForm("cadForm"); });</script>';
		}*/
		
		$html = str_replace('{$charset}', $conf['charset'], $html);
		
		// retorna o cod HTML do formulario
		return $html;
	}
	
	/**
	 * Retorna o html da lista de processos para
	 * cadastro de contratos
	 * @param Empreendimento $empreend
	 */
	public static function showCadEmprForm(Empreendimento $empreend) {
		global $conf;
		// seta template
		$template = showCadContrTemplate();
		$html = $template['template'];
		
		// seleciona todos os processos vinculados ao empreendimento $empreend
		$processos = $empreend->getProcsContr();
		
		//var_dump($processos);exit();
		
		if (count($processos) <= 0) return;
		
		// monta tabela de processos
		$tabela = '<tr><td><table width="100%">';
		$tabela .= '
		<tr class="c">
			<th class="c">Nº CPO</th>
			<th class="c">N&uacute;mero Processo</th>
			<th class="c">Assunto</th>
			<th class="c">Tipo</th>
			<th class="c">Guarda-chuva?</th>
			<td class="c"><b><center>A&ccedil;&atilde;o</center></b></td>
		</tr>
		';
		
		// percorre processos
		foreach($processos as $p) {
			// carrega o processo atual
			$doc = new Documento($p['id']);
			$doc->loadCampos();
			
			// seta tipo do processo
			$tipo = 'Contrata&ccedil;&atilde;o de Obra';
			//solicitacao 005: verifica se a key existe antes de acessa-la
			if(!array_key_exists("tipoProc", $doc->campos))
				$doc->campos['tipoProc'] = '';
			if ($doc->campos['tipoProc'] == 'contrProj')
				$tipo = 'Contrata&ccedil;&atilde;o de Projeto';
			
			// seta se é guardachuva
			$guardachuva = "N&atilde;o";
			//solicitacao 005: verifica se a key existe antes de acessa-la
			if(!array_key_exists("guardachuva", $doc->campos))
				$doc->campos['guardachuva'] = '';
			if ($doc->campos['guardachuva'] == 1) {
				$guardachuva = "Sim";
			}
				
			// cria link para processo
			$link = '<a onclick="window.open(\'sgd.php?acao=ver&docID='.$doc->id;
			$link .= '\',\'doc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*';
			$link .= $conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">';
			
			$onclick = '';
			//solicitacao 005: verifica se a key existe antes de acessa-la
			if(!array_key_exists("unOrgProc", $doc->campos))
				$doc->campos['unOrgProc'] = '';
			if (count($doc->getObras()) <= 0) {
				$onclick = 'onclick="showInclObraForm('.$p['id'].', \''.$doc->campos['tipoProc'].'\', ';
				$onclick .= $doc->campos['guardachuva'].', \''.str_replace(array("\n", "\r"), array("", ""), $doc->campos['unOrgProc']).'\')"';
			}
			else {
				$onclick = 'onclick="showOrigemRecForm('.$p['id'].', \''.$doc->campos['tipoProc'].'\', undefined, ';
				$onclick .= $doc->campos['guardachuva'].', \''.str_replace(array("\n", "\r"), array("", ""), $doc->campos['unOrgProc']).'\')"';
			}
			//solicitacao 005: verifica se a key existe antes de acessa-la
			if(!array_key_exists("assunto", $doc->campos))
				$doc->campos['assunto'] = '';
			// monta linha da tabela
			$tabela .= '
			<tr class="c">
				<td class="c">'.$p['id'].'</td>
				<td class="c">'. $link . $doc->numeroComp . '</a></td>
				<td class="c">'.$doc->campos['assunto'].'</td>
				<td class="c">'.$tipo.'</td>
				<td class="c">'.$guardachuva.'</td>
				<td class="c"><a '.$onclick.'>[Cadastrar Contrato]</a></td>
			</tr>';
		}
		
		$tabela .= '</table></td></tr>';
		
		// monta tudo :P
		$html = str_replace('{$tabela_processos}', $tabela, $html);
		// inclui div para dialog de inclusão de obras e de recursos
		$html = str_replace('{$div_obras}', Contrato::showIncludeObra($empreend), $html);
		$html = str_replace('{$div_recursos}', Contrato::showIncludeRecursos($empreend), $html);
		
		return $html;
	}

	/**
	 * Retorna div de inclusão de obras para formulário de cadastro
	 * @param Empreendimento $empreend
	 * @param $arrayObras array[id obra => true] -> obras já vinculadas a este contrato
	 */
	private static function showIncludeObra(Empreendimento $empreend, $arrayObras = null) {
		// seta template
		$html = '<div id="divInclObra" title="Incluir Obras" style="display: none;">
			<center><h3>O contrato est&aacute; vinculado &agrave;s obras:</h3></center>
			<table width="100%">
				{$linhas}
			</table>
		</div>
		<div id="divInclObraGuardaChuva" title="Incluir Obras" style="display: none;"></div>
		';
		
		// pega obras vinculadas ao empreendimento $empreend
		$obras = $empreend->get('obras');
		
		// se não achou nenhuma obra, seta $obras como array vazia para não gerar warning no foreach
		if (count($obras) <= 0) {
			$obras = array();
		}
		
		$linhas = '';
		// percorre as obras
		foreach ($obras as $o) {
			$checked = '';
			// seta o checkbox como checado, se a obra já estiver vinculada
			if (count($arrayObras) > 0 && $arrayObras[$o->get('id')] == true) {
				$checked = "checked=checked";
			}
			
			// monta html
			$linhas .= '<tr class="c">';
			$linhas .= '<td><input type="checkbox" name="inclObra[]" value="'.$o->get('id').'" '.$checked.'></td><td> '.$o->get('nome').'</td>';
			$linhas .= '</tr>';
		}
		
		if (count($obras) <= 0) {
			$linhas .= '<center><b>Nenhuma obra encontrada</b>.</center>';
		}
		
		$html = str_replace('{$linhas}', $linhas, $html);
		
		return $html;
	}
	
	/**
	 * Retorna div de inclusão de recursos e seus devidos valores
	 * para formulário de cadastro
	 * @param Empreendimento $empreend
	 */
	private static function showIncludeRecursos(Empreendimento $empreend) {
		$html = '<div id="contrOrigemRecursos" title="Origem dos Recuros" style="display: none;">
			<center><h3>Especifique a Origem dos Recursos: </h3></center>
			<table width="400">
				{$linhas}
			</table>
		</div>
		<div id="contrOrigemRecursosGuardaChuva" title="Origem dos Recursos" style="display: none;"></div>
		';
		
		// percorre os recursos do empreendimento $empreend
		$empreend->getRecursos();
		$recursos = $empreend->get('recursos');
		
		// se não encontrou nenhum recurso, seta $recursos como array vazia para não gerar warning no foreach
		if (count($recursos) <= 0)
			$recursos = array();
		
		$linhas = '';
		$coluna = 0;
		// percorre os recursos e monta linha
		foreach($recursos as $r) {
			$linhas .= '<tr class="c">';
			$linhas .= '<td style="width: 200px;"><input type="checkbox" name="inclRec[]" value="'.$r->get('id').'" /> '.$r->origem.'</td>';
			$linhas .= '<td style="width: 200px;"><input id="valRec_'.$r->get('id').'" class="valRec" /></td>';
			$linhas .= '</tr>';
			
			$coluna++;
		}
		
		if (count($recursos) <= 0) {
			$linhas .= '<center><b>Nenhum recurso cadastrado</b>.</center>';
		}
		
		// monta html
		$html = str_replace('{$linhas}', $linhas, $html);
		
		return $html;
	}
	
	/**
	 * Mostra detalhes da Empresa
	 */
	public function showEmpresaResumo() {
		global $bd;
		// carrega empresa vinculada
		$empresa = new Empresa($bd);
		$empresa->load($this->campos['empresaID']);
		
		// começa a montar html
		$html = '
		<span class="headerLeft">Empresa</span>
		<table width="100%">';
		
		// coloca empresa
		$html .= '
		<tr class="c">
			<td class="c" colspan="1"><b>Empresa</b>:</td>
			<td class="c" colspan="5">'.SGDecode($empresa->get('nome'));
		
		// verifica se o campo empresaID é editável... 
		//$incluiDivEdit = false;
		if ($this->verificaEditavel('empresaID')) {
			$c = 'empresaID';
			$c = montaCampo($c, 'edt', $this->campos);
			
			if (checkPermission(94)) $html .= ' <a onclick="editEmpresa('.$this->id.')">[editar]</a>';
			//$incluiDivEdit = true;
		}
		
		$html .= '</td>
                    <td class="c" colspan="1"><b>CNPJ</b>: </td>
			<td class="c" colspan="5">'.SGDecode($empresa->get('cnpj')).'
		</tr>';
		
		// pega os funcionários vinculados a este contrato
		$func = $empresa->getFuncionariosPorContrato($this->id);
		
		// se não achou nada, seta a lista de funcionários como uma array vazia
		// para que não gere warning no foreach
		if (count($func) <= 0)
			$func = array();
		
		// percorre lista de funcionários
		foreach ($func as $f) {
			$label = '';
			// seta label do tipo de responsabilidade
			if ($f['tipo'] == 'resp')
				$label = 'Respons&aacute;vel';
			elseif ($f['tipo'] == 'respTec')
				$label = 'Respons&aacute;vel T&eacute;cnico';
			else
				$label = 'Engenheiro Residente';
			
				
			// monta link para arquivo de ART
			$linkART = explode('/', $f['art']);
			$linkART = $linkART[count($linkART) - 1];
			$linkART = '<a href="'.$f['art'].'">'.$linkART.'</a>';

			// seta label de ativo/desativado
			$ativo = "Ativo";
			if ($f['ativo'] == 0) {
				$ativo = "Desativado";
			}
			
			// monta linha
			$html .= '
			<tr class="c">
				<td class="c"><b>'.$label.'</b>: </td>
				<td class="c">'.$f['nome'].'</td>
				<td class="c">'.$ativo.'</td>
				<td class="c"><b>ART</b>: </td>
				<td class="c">'.$linkART.'</td>
			</tr>
			';
			
		}
		
		// mostra link para edição de funcionários
		$html .= '
		<tr class="c">
			<td class="c" colspan="4">
				<a onclick="editEmpresa('.$this->id.')">[editar funcion&aacute;rios]</a>
			</td>
		</tr>
		';
		
		$html .= '</table>';
		
		//if ($incluiDivEdit == true) {
		// mostra div para dialog de edição de funcionários+empresa
		$html .= $this->showEditEmpresaForm($func);
		//}
		
		return $html;
	}
	
	/**
	 * monta div para edição vínculo de empresa/funcionários
	 * @param $funcCadastrados array de funcionários vinculados
	 */
	public function showEditEmpresaForm($funcCadastrados) {
		global $bd;
		global $conf;
		
		// carrega empresa
		$empresa = new Empresa($bd);
		$empresa->load($this->campos['empresaID']);
		
		// pega funcionários da empresa
		$funcionarios = $empresa->getFuncionarios();
		
		// começa a montar html
		$html = '
		<div id="contrEditEmpresa" title="Editar Empresa" style="display: none;">
		<form accept-charset="'.$conf['charset'].'" id="formEditEmpresa" action="sgd.php?acao=editContrFunc&docID='.$this->id.'" method="post" enctype="multipart/form-data">
		<input type="hidden" name="docID" value="'.$this->id.'">
		<table width="100%" id="tabelaEditEmpresa">';
		
		// se empresaID for editável, mostra select correspondente
		$selectEmpresa = '';
		if ($this->verificaEditavel('empresaID') && checkPermission(94)) {
			$selectEmpresa = geraSelect('empresaID', Empresa::getEmpresas($bd), $this->campos['empresaID']);
			$html .= '<tr class="c">';
			$html .= '<td class="c" colspan="2"><b>Empresa</b>:</td><td class="c" colspan="7">{$selectEmpresa}</td>';
			$html .= '</tr>';
		} // senão, mostra só nome da empresa
		else {
			$html .= '<tr class="c">';	
			$html .= '<td class="c" colspan="2"><b>Empresa</b>: </td>';
			$html .= '<td class="c" colspan="7">'.SGDecode($empresa->get('nome'));
			$html .= '<input type="hidden" name="empresaID" id="empresaID" value="'.$empresa->get('id').'">';
			$html .= '<input type="hidden" name="empresaNome" id="empresaNome" value="'.$empresa->get('nome').'">';
			$html .= '</tr>';
		}

		// monta array de label de tipo de responsabilidades
		$arrayTipo = array();
		$arrayTipo[] = array("value" => "resp", "label" => "Respons&aacute;vel");
		$arrayTipo[] = array("value" => "respTec", "label" => "Respons&aacute;vel T&eacute;cnico");
		$arrayTipo[] = array("value" => "eng", "label" => "Engenheiro Residente");
		
		$onclick = '';
		// percorre lista de funcionários já vinculados
		for ($i = 0; $i < count($funcCadastrados); $i++) {
			// se o vínculo está desativado, não dá a opção para editar funcionário
			if ($funcCadastrados[$i]['ativo'] == 0)
				continue;
				
			// pega crea e tipo
			$crea = $funcCadastrados[$i]['crea'];
			$tipo = $funcCadastrados[$i]['tipo'];
			
			// gera select de tipo de responsabilidade
			$select = geraSelect('tipoFunc'.$i, $arrayTipo, $tipo);
			// gera select de funcionários da empresa
			$funcSelect = geraSelect('empresaFuncID'.$i, $funcionarios, $crea, '', 'selectFunc');
			
			// monta html
			$html .= '<tr id="trFunc'.$i.'" class="c">';
			
			// verifica permissão para desativar funcionário
			if (checkPermission(97)) {
				$html .= '<td class="c"><a onclick="desativaFunc('.$this->id.', '.$crea.')">[desativar]</a>';
			}
			else {
				$html .= '<td class="c">';
			}
			
			// continua montando
			$html .= '<input type="hidden" name="novoFunc'.$i.'" value="'.$crea.'"></td>';
			$html .= '<td class="c"><b>Funcion&aacute;rio</b>: </td>';
			$html .= '<td class="c" id="tdFunc'.$i.'">'.$funcSelect.'</td>';
			$html .= '<td class="c"><b>CREA</b>: </td>';
			$html .= '<td class="c" id="tdCreaFunc'.$i.'">'.$crea.'</td>';
			$html .= '<td class="c"><b>Tipo</b>: </td>';
			$html .= '<td class="c" id="tdTipoFunc'.$i.'">'.$select.'</td>';
			$html .= '<td class="c"><b>ART</b>: </td>';
			$html .= '<td class="c" id="tdFuncART'.$i.'">';
			$html .= '<input type="file" name="funcART'.$i.'" id="funcART'.$i.'" '.$onclick.'></td>';
			$html .= '</tr>';
		}
		
		$select = geraSelect('tipoFunc'.$i, $arrayTipo);			
		$funcSelect = geraSelect('empresaFuncID'.$i, $funcionarios, '', '', 'selectFunc');
		
		$onclick = 'onclick="newFunc('.$i.')"';
		
		// monta linha para adição de novos funcionários
		$html .= '<tr id="trFunc'.$i.'" class="c">';
		$html .= '<td class="c"><input type="hidden" name="novoFunc'.$i.'" value="0"></td>';
		$html .= '<td class="c"><b>Funcion&aacute;rio</b>: </td>';
		$html .= '<td class="c" id="tdFunc'.$i.'">'.$funcSelect.'</td>';
		$html .= '<td class="c"><b>CREA</b>: </td>';
		$html .= '<td class="c" id="tdCreaFunc'.$i.'"></td>';
		$html .= '<td class="c"><b>Tipo</b>: </td>';
		$html .= '<td class="c" id="tdTipoFunc'.$i.'">'.$select.'</td>';
		$html .= '<td class="c"><b>ART</b>: </td>';
		$html .= '<td class="c" id="tdFuncART'.$i.'">';
		$html .= '<input type="file" name="funcART'.$i.'" id="funcART'.$i.'" '.$onclick.'></td>';
		$html .= '</tr>';
		
		// verifica permissão para cadastrar funcionários
		if (checkPermission(96)) {
			$html .= '
			<tr class="c">
				<td class="c" colspan="9"><center><a onclick="showCadFunc()">[Cadastrar Funcion&aacute;rio]</a></center></td>
			</tr>';
		}
		
		$html = str_replace('{$selectEmpresa}', $selectEmpresa, $html);
		
		$html .= '</table></div>';
		
		return $html;
	}
	
	
	/**
	 * Mostra resumo/detalhes do contrato
	 * @return html
	 */
	public function showResumo() {
		global $conf;
		global $bd;
		$doc = $this;
		includeModule('sgo');
		
		$tabelaEsq = '';
		$tabelaDir = '';
		
		// adiciona o titulo
		$html = '
		<script type="text/javascript" src="scripts/sgd_contrato.js?r={$randNum}"></script>
		<span class="headerLeft">Dados do Documento</span>';
		/**
		 * Solicitacao 003
		 * Inserindo jquery para as mascaras
		 */
		$html .= '<script type="text/javascript" src="scripts/plugins/mascara.jquery.js"></script>';
		// monta tabela exterior
		$html .= '
		<table border="0" width="100%">
			<tr>
				<td width="50%">{$tabelaEsq}</td>
				<td width="50%">{$tabelaDir}</td>
			</tr>
		</table>
		';
		
		// le os nomes dos campos desse tipo de documento
		$campos = explode(",", $doc->dadosTipo['campos']);
		
		// verificação de segurança: se o documento nao tiver campos, retorna mensagem
		if (!$campos[0]) {
			$html .= "<br /><center><b>Não h&aacute; dados dispon&iacute;veis</b></center><br />";
			return $html;
		}
		
		/*$empresa = new Empresa($bd);
		$empresa->load($doc->campos['empresaID']);*/
		
		// senao, comeca a montar as tabelas
		$tabelaEsq .= '<table border="0" width="100%"><tr><td width="30%"></td><td width="70%"></td></tr>';
		//solicitacao 005: insercao do estado do contrato
		requireSubModule(array("frontend","contrato_estado"));
		$ce = new ContratoEstado($this->id);
		$link = new HtmlTag("a", "","","[Editar]");
		$link->setAttr(array("href","onclick"),array("#","javascript:contratoEstadoDialogOpen(\"".ContratoEstado::DialogInsereID."\",\"".$doc->id."\");"));
		$link->setNext($ce->getAddDialog());
		$tabelaEsq .= '<tr class="c"><td class="c"><b>Estado do Contrato:</b> </td><td class="c"><span id="estado">'.$ce->getEstadoHtml()." ".$link->toString().'</span></td></tr>';
		
		//fim 005
		$tabelaEsq .= '<tr class="c"><td class="c"><b>N&uacute;mero do Doc (CPO):</b> </td><td class="c"><span id="docID">'.$doc->id.'</span></td></tr>';
		
		$tabelaDir .= '<table border="0" width="100%"><tr><td width="30%"></td><td width="70%"></td></tr>';
		
		// Mostra obras
		$obras = $this->getObras();
		$tabelaDir .= '<tr class="c"><td class="c"><b>Obras</b>: </td>';
		if (count($obras) <= 0) {
			$tabelaDir .= '<td class="c">Nenhuma Obra associada. ';
			
			// se o contrato for editável, dá a opção de editar as obras associadas
			if ($this->verificaEditavel(''))
				$tabelaDir .= '<a onclick="showEditObra('.$this->id.')">[editar]</a> ';
				
			$tabelaDir .= '</td>';
		}
		else {
			$tabelaDir .= '<td class="c">';
			foreach ($obras as $o) {
				$onclick = 'onclick="';
				$onclick .= 'window.open(\'sgo.php?acao=verObra&obraID='.$o['id'].'\',';
				$onclick .= '\'obra\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',';
				$onclick .= 'height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()';
				$onclick .= '"';
				$tabelaDir .= '<a '.$onclick.'>'.$o['nome'].'</a><br />';
			}
			
			// se o contrato for editável, dá a opção de editar as obras associadas
			if ($this->verificaEditavel(''))
				$tabelaDir .= '<a onclick="showEditObra('.$this->id.')">[editar]</a>';
				
			$tabelaDir .= '</td>';
		}
		$tabelaDir .= '</tr>';
		// fim obras
		
		// mostra recursos
		$recursos = $this->getOrigemRecursos();
		$tabelaEsq .= '<tr class="c"><td class="c"><b>Origem dos Recursos</b>: </td>';
		if (count($recursos) <= 0) {
			$tabelaEsq .= '<td class="c">Nenhuma Origem de Recurso associada. ';
			
			// se o contrato for editável, dá a opção de editar os recursos associados
			if ($this->verificaEditavel(''))
				$tabelaEsq .= '<a onclick="showEditRecurso('.$this->id.')">[editar]</a>';
			
			$tabelaEsq .= '</td>';
		}
		else {
			$tabelaEsq .= '<td class="c">';
			foreach ($recursos as $r) {
				$tabelaEsq .= $r['origem'] . ': R$ ' . $r['valor'] . '<br />';
			}
			
			// se o contrato for editável, dá a opção de editar os recursos associados
			if ($this->verificaEditavel(''))
				$tabelaEsq .= '<a onclick="showEditRecurso('.$this->id.')">[editar]</a>';
			$tabelaEsq .= '</td>';
		}
		$tabelaEsq .= '</tr>';
		// fim recursos
		
		// verificação de segurança: contrato deve sempre ter pai
		if ($doc->docPaiID == 0) {
			showError(11);
		}
		
		// carrega info de processo pai
		$paiOwner = false;
		$docPai = new Documento($doc->docPaiID);
		$docPai->loadCampos();
		if ($docPai->owner == $_SESSION['id'] || ($docPai->owner == -1 && $docPai->areaOwner == $_SESSION['area']))
			$paiOwner = true;
		/**
		 * Solicitacao 003
		 * objetos com ids e conteudo dos span que irao receber o evento (dialog) presente no javascript
		 */
		requireSubModule(array('aditivo','frontend'));//requisicao do sub_modulo aditivo
		$aditivos = new ArrayObject();
		//fim 003
		// monta tabelas para mostrar campos do contrato
		foreach ($campos as $c) {
			$aditivo_div = '';
			$tabela = 'tabelaEsq';
			if (Contrato::campoNaDireita($c)) {
				$tabela = 'tabelaDir';
			}
			if(strpos($doc->dadosTipo['emitente'],$c) === false){
				
				if ($c == 'empresaID') continue;
				$c = montaCampo($c,'edt',$doc->campos);

				if ($c['nome'] == "numProcContr") {
					$link = "<a onclick=\"window.open('sgd.php?acao=ver&docID=".$docPai->id;
					$link .= "','doc','width='+screen.width*".$conf["newWindowWidth"]."+',height='+screen.height*";
					$link .= $conf["newWindowHeight"]."+',scrollbars=yes,resizable=yes').focus()\">".$docPai->dadosTipo['nome'];
					$link .= " ".$docPai->numeroComp.'</a>';
					$c['valor'] = $link;
				}
				
				// verifica se usuário tem permissão para ver este campo
				if ($c['verAcao'] < 0 || ($c['verAcao'] > 0 && !checkPermission($c['verAcao'])))
					 continue;
				else {
					// verifica sigilo... contrato não tem campo de sigiloso, porém
					// não retirei porque pode ser que mudem de idéia...
					if (verificaSigilo($doc) && !checkPermission(67)) {
						break;
					}
					else {
						
						// monta o display do campo
						if ($c['tipo'] == 'data') {
							if ($c['valor'] > 0)
								$c['valor'] = date("d/m/Y", $c['valor']);
							else
								$c['valor'] = '';
						}
		
						$unity = '';
						if ($c['nome'] == "prazoContr" || $c['nome'] == "prazoProjObra") {
							$unity = 'dias';
						}
						//solicitacao 003: somando aditivos aos valores totais (finais)
						if($c["nome"]=="dataTermino"){
							$sumAd="";
							$total = 0;
							foreach ($aditivos as $ad){
								$unity=" dias";
								if($ad->getVar("tipo")!=AditivoTipo::Diario)
									continue;
								$totAd = $ad->getSum();
								if($totAd>0){
									$total+=$totAd;
// 									if($totAd==1)
// 										$unity=" dia";
// 									$sumAdTag = new HtmlTag("span", "", ""," (+) ".$totAd.$unity." Aditivo ".$ad->getVar("label"));//soma dos aditivos
// 									$sumAd .= "<br/>".$sumAdTag->toString();
								}
							}
							$unity=" dias";
// 							$sumAd .= "<hr>";
							$total = date('d/m/Y', strtotime("+".$total." days",strtotime(str_replace("/", "-", $c["valor"]))));
							$sumAdTag = new HtmlTag("span", "", ""," ".$total,new HtmlTagStyle("font-weight","bold"));//soma dos aditivos
							$sumAd .= $sumAdTag->toString();
						}
						else if($c["nome"]=="valorTotal"){
							$total=$c['valor'];
							$sumAd="";
							foreach ($aditivos as $ad){
								if($ad->getVar("tipo")!=AditivoTipo::Monetario)
									continue;
								$totAd = $ad->getSum();
								if($totAd>0){
									$total+=$totAd;
// 									$sumAdTag = new HtmlTag("span", "", ""," (+) R$ ".number_format($totAd, 2, ',', '.')." Aditivo ".$ad->getVar("label"));//soma dos aditivos
// 									$sumAd .= "<br/>".$sumAdTag->toString();
								}
							}
// 							$sumAd .= "<hr>";
							$sumAdTag = new HtmlTag("span", "", ""," R$ ".number_format($total, 2, ',', '.'),new HtmlTagStyle("font-weight","bold"));//soma dos aditivos
							$sumAd .= $sumAdTag->toString();
						}
						else
							$sumAd="";
						//fim 003
						//003.5 : mudancas no layout
						if($c["nome"]=="valorProj"||$c["nome"]=="valorMaoObra"||$c["nome"]=="valorMaterial"){
							$href="javascript:void(0)";
							$onclick='javascript:show_aditivar_campo("'.$c['nome'].'")';
							$aditivarDAO = new HtmlTag("a", "", "","[Aditivo]",null,new HtmlTagAttr(array("href","onclick"),array($href,$onclick)));
							$labelTB = new HtmlTable(rand(0, 100000), "", 1);
							$labelTB->appendLine("<b>".$c["label"]."</b>");
							$labelTB->appendLine($aditivarDAO->toString());
							$label = new HtmlTag("td", "", "c");
							$label->setVar("content", $labelTB->toString());
						}
						else if($c["nome"]=="prazoProjObra"||$c["nome"]=="prazoContr"){
							$href="javascript:void(0)";
							$onclick='javascript:show_aditivar_campo("'.$c['nome'].'")';
							$aditivarDAO = new HtmlTag("a", "", "","[Aditivo]",null,new HtmlTagAttr(array("href","onclick"),array($href,$onclick)));
							$labelTB = new HtmlTable(rand(0, 100000), "", 1);
							$labelTB->appendLine("<b>".$c["label"]."</b>");
							$labelTB->appendLine($aditivarDAO->toString());
							$label = new HtmlTag("td", "", "c");
							$label->setVar("content", $labelTB->toString());
						}
						else{
							$label = new HtmlTag("td", "", "c");
							$label->setVar("content", "<b>".$c["label"]."</b>");
						}
						//fim 003.5
						$$tabela .= '
						<tr class="c">'.$label->toString().'
							<td class="c" id="'.$c['nome'].'_value_tr">';
						if(isset($c['extra']) && strpos($c['extra'], 'moeda') !== false){
							//solicitacao 003 realiza a soma total
							if($c["nome"]=="valorTotal")
								$$tabela .= $sumAd;
							else 
								$$tabela .= 'R$ <span id="'.$c['nome'].'_val">'.number_format($c['valor'], 2, ',', '.').'</span>';
							//fim 003
						}
						else{
							//solicitacao 003
							if($c["nome"]=="dataTermino"){
								$unity="";
								$$tabela .= $sumAd;
							}
							else 
								$$tabela .= '<span id="'.$c['nome'].'_val">'.$c['valor'].' '.$unity.'</span>';
							//fim 003
						}
						//solicitacao 003
// 						$$tabela .= $sumAd;
						//fim 003
					}
					
				}
				
				//clausula para aditivo
				if(isset($c['extra']) && strpos($c['extra'], 'aditivo') !== false) {
					/**
					 * Solicitacao 003
					 */
					$ad = new Aditivo($this->bd,$this->id,$c["nome"],$c["label"]);//criando novo objeto aditivo
					if(strpos($c['extra'], 'moeda') !== false)
						$ad->setVar('tipo', AditivoTipo::Monetario);
					else
						$ad->setVar('tipo', AditivoTipo::Diario);
					$adByName = $ad->getAditivos();//aditivos pelo nome
					$valorTotal = doubleval($c["valor"]);//valor total do campo
					$adCount = count($adByName);//total de aditivos para essa campo
					//inicializa variaveis para calcular total dos aditivos
					$total_aditivos = 0;
					//abre tag onde os aditivos serao mostrados
					$aditivo_div .= '<div id="'.$c['nome'].'_aditivos_div">';
					//vamos marcar o ultimo aditivo, apenas para inserir o botao de [Aditivar]
					$lastAdId = -1;
					foreach ($adByName as $a){
						if($a["valor"]!=0)
							$lastAdId=$a["id"];
					}
					//para cada aditivo achado
					foreach ($adByName as $k=>$a) {
						//div pai
						$div = new HtmlTag("div","","adpai");
						$div->setStyle(array("float","width"), array("left","100%"));
						//div com valor
						$divValor = new HtmlTag("div", "", "");
						$divValor->setStyle(array("float","width"), array("left","70%"));
						//div para DataAccessObject
						$divDAO = new HtmlTag("div", "", "");
						$divDAO->setStyle(array("float","text-align","width"), array("right","right","25%"));
						//fazendo divValor ser filho de div pai
						$div->setChildren($divValor);
						//eh proxima da divValor
						$div->setChildren($divDAO);
						//guarda valor do aditivo
						$hidden = new HtmlTag("input", "", "");
						$hidden->setAttr(array("type","value","name"),array("hidden",$a["valor"],"aditivo_valor_".$a['id']));
						$hidden->setNext(new HtmlTag("input", "", "","",null,new HtmlTagAttr(array("type","value","name"),array("hidden",$a["motivo"],"aditivo_motivo_".$a['id']))));
						//nova taghtml
						$htmlTag = new HtmlTag("span", "aditivo_valor_".$a['id'], "");
						$htmlTag->setStyle(array("text-decoration","cursor"), array("none","pointer"));//estilo css
						$htmlTag->setAttr(array("show_admotivo_".$ad->getVar("label"),"attr"), array(true,"aditivo_dialog"));//attr para mostrar o motivo
						$htmlTag->setChildren($hidden);
						//dialog vai dentro da divValor
						$divValor->setVar("children", $htmlTag);
						//conteudo 
						$content="";
						//div que virara dialog
						$divMore = new HtmlTag("div", "aditivo_show_more_".$a["id"], "");
						$divMore->setStyle("display", "none");
						//input hidden para marcar o titulo do dialog
						$dialogTitle = new HtmlTag("input", "", "");
						$dialogTitle->setAttr(array("type","value","name"), array("hidden","Aditivo: ".$c["label"],"dialogTitle"));
						//tabela dentro do dialog
						$htmlTable = new HtmlTable("show_more_".$a["id"], "aditivo_show_more", 2);
						$htmlTable->setStyle(array("width","border"), array("90%","1px solid #000000"));
						$htmlTable->setLineStyle($htmlTable->getVar("style"));
						//criando os filhos da divMore
						$divMore->setChildren($dialogTitle);
						$divMore->setChildren($htmlTable);
						//div more eh filha da tag Html principal
						$htmlTag->setChildren($divMore);
						//inserir o valor no aditivo
						$ad->setVar("valor", $a["valor"]);
						//se o aditivo for referente ao campo sendo montado
						//formata o valor aditivado na forma 00,00%
						if($a["valor"]==0)
							continue;						
						$porcentagem = ($c["valor"]==0)?0.00:number_format($a['valor']/$c['valor']*100.00, 2, ',', '.');
						//se for tipo moeda, precisa colocar o R$ antes
						if(strpos($c['extra'], 'moeda') !== false){
							$valorAditivo=number_format($a['valor'], 2, ',', '.');
							$valorTotal += doubleval($a['valor']);
							$htmlTable->appendLine(array("Valor","R$ ".$valorAditivo));
							$htmlTable->appendLine(array("Porcentagem",$porcentagem."%"));
							$htmlTable->appendLine(array("Motivo",$a["motivo"]));
							$content = "(+) R$ ".$valorAditivo."(".$porcentagem."%)";
						}
						else{
							$valorTotal += intval($a['valor']);
							$valorAditivo = intval($a["valor"]);
							$htmlTable->appendLine(array("Dias",$valorAditivo));
							$htmlTable->appendLine(array("Porcentagem",$porcentagem."%"));
							$htmlTable->appendLine(array("Motivo",$a["motivo"]));
							$content = "(+) ".$valorAditivo.' '.($valorTotal>1?'dias':'dia')."(".$porcentagem."%)";
						}
						$href="javascript:void(0)";
						$onclick='javascript:show_editar_aditivo("'.$a['id'].'","'.$c['nome'].'")';
						$divDAO->setChildren(new HtmlTag("a", "", "","[Editar]",null,new HtmlTagAttr(array("href","onclick"),array($href,$onclick))));
						$htmlTable->setColumnStyle(new HtmlTagStyle(array("font-weight"),array("bold")),-1,0);
						$htmlTag->setVar("content", $content);
						if($a["id"]==$lastAdId)
							$div->setStyle("margin-bottom", "5px");
						$aditivo_div .= "".$div->toString();
						if($a["id"]==$lastAdId){
							if(strpos($c['extra'], 'moeda') !== false)
								$aditivo_div .= "<hr><b>Total: R$ ".number_format($valorTotal, 2, ',', '.')."</b>";
							else{
								$aditivo_div .= "<hr><b>Dias aditivados: ".$valorTotal.' '.($valorTotal>1?'dias':'dia')."</b>";
							}
						}
					}
					$aditivos->append($ad);
					$aditivo_div .= '</div>';
					// se houver aditivos, mostra o tota de aditivos
					if($total_aditivos){
						$aditivo_div .= '<br /> <b>Total de Aditivos</b>: ';
						//se campo for moeda, formata o R$ 00,00
						if(strpos($c['extra'], 'moeda') !== false)
							$aditivo_div .= 'R$ <span id="'.$c['nome'].'_total_aditivos">'.number_format($total_aditivos, 2, ',', '.').'</span>';
						else 
							$aditivo_div .= ' <span id="'.$c['nome'].'_total_aditivos">'.$total_aditivos.'</span>';
						//se nao for data, coloca porcentagem
						if($c['tipo'] != 'data')
							$aditivo_div .= ' (<span id="'.$c['nome'].'_total_aditivos_porcentagem">'.number_format($total_aditivos/$c['valor']*100, 2, ',', '.').'</span> %)';
					}
					$aditivo = true;
				}
				// verifica se o usuário tem permissão para editar o campo
				if ($c['editarAcao'] < 0 || ($c['editarAcao'] > 0 && !checkPermission($c['editarAcao'])) || !$doc->verificaEditavel($c['nome'])) {
					$$tabela .= $aditivo_div;
					$$tabela .= '</td></tr>';
					continue;
				}
				// caso tenha, monta formulário de edição
				$$tabela .= '<form accept-charset="'.$conf['charset'].'" id="'.$c['nome'].'_form" action="javascript: editContrVal(\''.$c['nome'].'\')" method="post" style="display: inline">
				<span id="'.$c['nome'].'_edit" style="display:none;">
				'.$c['cod'].'
				</span>
				<input id="'.$c['nome'].'_link" class="buttonlink" type="submit" value="[Editar]" /> 
				</form>';
				$$tabela .= $aditivo_div;
				$$tabela .= '</td></tr>';
			}//if
		}//foreach
		$tabelaEsq .= '</table>';
		$tabelaDir .= '</table>';
		if(isset($aditivo)) {
			//carrega o menu de motivos para atraso
			$motivos_aditivo = array(array('value'=> 'motivo1', 'label' => 'Erro de Calculo'), array('value'=> 'motivo2', 'label' => 'Erro Humano'), array('value'=> 'motivo3', 'label' => 'Motivo de Preguica Maior'), array('value'=> '_outro', 'label' => 'Outro Motivo'));
			//seta o HTML do dialog para inserir/editar aditivos
			/**
		     * Solicitacao 003: arrumando o dialog para separar os input em dias e valor_moeda
			 */
			$dialog_html = '<div id="aditivar_dialog" title="Aditivar campo" style="display:none">';//dialog jquery
			$div_c_desc = '<div class="aditivar_c_desc" style="float:left;">';//descricao do campo a esquerda
			$div_c_input = '<div class="aditivar_c_input" style="float:left;">';//input do campo
			$closeElem = new stdClass();//para fechar um elemento html
			$closeElem->div = '</div>';
			$c = new stdClass();
			$c->desc = new stdClass();
			$c->input = new stdClass();
			$c->desc->dia = "<div id='aditivar_desc_dia' attr='sw' style='display:none;'> Valor a ser adicionado (dias)</div>";
			$c->desc->moeda = "<div id='aditivar_desc_moeda' attr='sw' style='display:none;'>Valor a ser adicionado (R$)</div>";
			$c->desc->razao = '<div id="aditivar_desc_razao"  style="display:block;">Raz&atilde;o do aditivo: </div>';
			$c->input->dia = '<div id="aditivar_input_dia" attr="sw" style="display:none;"><input id="aditivar_valor" /></div>';
			$c->input->moeda = '<div id="aditivar_input_moeda" attr="sw" style="display:none;"><input type="text" id="aditivar_valor_moeda" /></div>';
			$c->input->razao = '<div id="aditivar_input_razao" style="display:block;">'.geraSelect('aditivar_razao', $motivos_aditivo, null, '', 'aditivo_razao_select').'<input id="aditivar_razao_outro" /></div>';
			$html .= $dialog_html.'<div style="float:left;">'.($div_c_desc.
					 ($c->desc->dia).($c->desc->moeda).$closeElem->div).
					 ($div_c_input.($c->input->dia).($c->input->moeda).$closeElem->div).$closeElem->div;
			$html .= '<div  style="float:left;">'.$div_c_desc.$c->desc->razao.$closeElem->div.$div_c_input.$c->input->razao.$closeElem->div.$closeElem->div;
			$html .= $closeElem->div;
			//fim 003
		}
		$html = str_replace('{$tabelaEsq}', $tabelaEsq, $html);
		$html = str_replace('{$tabelaDir}', $tabelaDir, $html);
		
		if ($this->verificaEditavel(''))
			$html .= $this->showEditObrasRec();
		//retorna o cod html da tabela
		return $html;
	}
	
	/**
	 * Retorna HTML para edição de inclusão de obras/recursos 
	 * @return html
	 */
	public function showEditObrasRec() {
		global $bd;
		
		// monta html
		$html = '
		<div id="divEditObras" title="Incluir/Editar Obras" style="display: none;">
			<center><h3>O contrato est&aacute; vinculado &agrave;s obras:</h3></center>
			<table width="100%">
				{$tabela_obras}
			</table>
		</div>
		<div id="divEditRec" title="Incluir/Editar Recursos" style="display: none;">
			<center><h3>Especifique a Origem dos Recursos: </h3></center>
			<table width="400">
				{$tabela_recursos}
			</table>
		</div>
		';
		
		// pega lista de empreendimentos vinculados a este contrato
		$empreendList = $this->getEmpreend();
		// seta variáveis das tabelas como vazias
		$tabela_obras = '';
		$tabela_recursos = '';
		
		// variáveis que guardarão número de obras e recursos encontrados
		$numeroObras = 0;
		$numeroRecursos = 0;
		
		// pega obras e recursos associados a este contrato
		$obrasAssociadas = $this->getObras();
		$recursosAssociados = $this->getOrigemRecursos();
		
		// percorre empreendimentos
		foreach ($empreendList as $e) {
			// carrega empreendimento atual
			$empr = new Empreendimento($bd);
			$empr->load($e['id'], true, true);
			
			// pega obras e recursos vinculados a este empreendimento
			$obras = $empr->get('obras');
			$recursos = $empr->get('recursos');
			
			// se achou pelo menos 1 obra
			if (count($obras) > 0) {
				// atualiza valor de número de obras encontradas
				$numeroObras = $numeroObras + count($obras);
				// começa a montar tabela
				$tabela_obras .= '<tr><td>Empreendimento <b>'.$empr->get('nome').'</b></td></tr>';
				
				// percorre lista de obras
				foreach ($obras as $o) {
					$checked = '';
					
					$obraID = $o->get('id');
					$achou = false;
					// percorre lista de obras associadas para ver se esta obra já está associada
					// caso ache, seta $achou como verdadeiro para marcar checkbox
					foreach($obrasAssociadas as $a) {
						if ($a['id'] == $obraID) {
							$achou = true;
							break;
						}
					}
					if ($achou) {
						$checked = 'checked="checked"';
					}
					
					// monta html
					$tabela_obras .= '
						<tr class="c">
							<td class="c">
								<input type="checkbox" name="inclObra[]" value="'.$obraID.'" '.$checked.'> '.$o->get('nome').'
							</td>
						</tr>';
				}
			} /* if obras */
			
			// se achou pelo menos 1 recurso
			if (count($recursos) > 0) {
				// atualiza valor de número de recursos encontrados
				$numeroRecursos = $numeroRecursos + count($recursos);
				$tabela_recursos .= '<tr><td colspan="2">Empreendimento <b>'.$empr->get('nome').'</b></td></tr>';
				
				// percorre recursos
				foreach ($recursos as $r) {
					$checked = '';
					$val = '';
					
					$recID = $r->get('id');
					// percorre recursos já associados para ver se este recurso já foi associado
					// caso encontre, seta checkbox como checado
					foreach ($recursosAssociados as $rA) {
						if ($rA['id'] == $recID) {
							$checked = 'checked="checked"';
							$val = 'value="'.$rA['valor'].'"';
						}
					}
					
					// monta html
					$tabela_recursos .= '
						<tr class="c">
							<td class="c">
								<input type="checkbox" name="inclRec[]" value="'.$recID.'" '.$checked.' /> '.$r->get('origem').' 
							</td>
							<td class="c">
								<input id="valRec_'.$r->get('id').'" '.$val.' class="valRec" /></td>
							</td>
						</tr>
					';
				}
			} /* if recursos */
			
			$tabela_obras .= '<tr><td><br /><!-- filler --></td></tr>';
			$tabela_recursos .= '<tr><td colspan="2"><br /><!-- filler --></td></tr>';
		}
		
		// se não achou obras
		if ($numeroObras <= 0) {
			$tabela_obras .= '
			<tr>
				<td><b>Nenhuma obra cadastrada para o(s) empreendimento(s) relacionado ao processo pai</b></td>
			</tr>';
		}
		
		// se não achou recursos
		if ($numeroRecursos <= 0) {
			$tabela_recursos .= '
			<tr>
				<td colspan="2"><b>Nenhum recurso cadastrado para o(s) empreendimento(s) relacionados ao processo pai</b></td>
			</tr>';
		}
		
		// monta html
		$html = str_replace('{$tabela_obras}', $tabela_obras, $html);
		$html = str_replace('{$tabela_recursos}', $tabela_recursos, $html);
		return $html;
	}
	
	/**
	 * Identifica se um campo deve ser mostrado na tabela da direita ou não no layout
	 * de resumo e cadastro
	 * @param string $campoNome O nome do campo em questão
	 * @return boolean
	 */
	public static function campoNaDireita($campoNome) {
		if ($campoNome == 'dataAssinatura' 	||
		    $campoNome == 'prazoContr'		||
		    $campoNome == 'vigenciaContr' 	||
		    $campoNome == 'inicioProjObra'	||
		    $campoNome == 'prazoProjObra' 	||
		    $campoNome == 'dataTermino'		||
		    $campoNome == 'dataReuniao')
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Retorna as origens de recursos associadas a este contrato
	 * @return array [id recurso][origem][valor]
	 */
	private function getOrigemRecursos() {
		$bd = $this->bd;
		
		$sql = "SELECT r.id, r.origem, c.valor 
				FROM obra_contrato_recurso AS c INNER JOIN obra_rec AS r ON c.recursoID = r.id
				WHERE c.contratoID = ".$this->id;
		
		return $bd->query($sql);
	}
	
	/**
	 * Método que verifica se os campos deste objeto são editáveis
	 * @see classes/Documento::verificaEditavel()
	 * @param string $campo nome do campo
	 * @return boolean
	 */
	function verificaEditavel($campo) {
		if (!isset($this->tipoProc) || $this->tipoProc == '') {
			//$this->loadDados();
			$this->setTipoProc();
		}
		
		if ($this->tipoProc == 'contrProj' && ($campo == 'valorMaoObra' || $campo == 'valorMaterial')) {
			return false;
		}
		if ($this->tipoProc == 'contrObr' && $campo == 'valorProj') {
			return false;
		}
		
		if ($campo == 'valorTotal') {
			return false;
		}
		
		if ($campo == 'vigenciaContr') {
			return false;
		}
		
		if ($campo == 'dataTermino') {
			return false;
		}
		
		if (checkPermission(2)) {
			if ($campo == 'dataAssinatura' || $campo == 'dataReuniao' || $campo == 'prazoContr' || $campo == 'inicioProjObra' || $campo == 'prazoProjObra')
				return true;
		}
		
		if ($campo == 'responsavelID' && stripos($_SESSION['cargo'], 'diretor') !== false) {
			return true;
		}
		
		if ($campo == 'dataReuniao' && stripos($_SESSION['cargo'], 'diretor') !== false) {
			return true;
		}
		
		return parent::verificaEditavel($campo);
		
	}
	
	/**
	 * Seta o tipo do Processo pai
	 */
	private function setTipoProc() {
		if ($this->docPaiID == 0)
			return;
			
		$pai = new Documento($this->docPaiID);
		$pai->loadCampos();
		
		$this->tipoProc = $pai->campos['tipoProc'];
	}
	
	function uploadFile($campoName) {	
		if($_FILES[$campoName] == ''){
			return array('success' => false);
		}
		
		if($_FILES[$campoName]['error'] > 0){
			return array('success' => false, "errorFeedback" => $_FILES[$campoName]['name'], "errorID" => $_FILES[$campoName]['error']);
		}
		
		if(!is_dir("files/contratos")){
			mkdir("files/contratos");
		}
		
		$fileName = "[".$this->id."]".$_FILES[$campoName]['name'];
		$fileName = stringBusca($fileName, true);
		
		$newName = $fileName;
		if (file_exists("files/contratos/".$newName)){
			//tratamento de nomes duplicados
			$j = 2;
			do  {//verifica se o nome do documento ja existe, se sim, adiciona (j) estilo windows para nao sobrescrever
		   		$oldName = explode(".", $fileName);
				
				if($oldName[count($oldName)-2])
					$oldName[count($oldName)-2] .= "(".$j.")";
				else
					$oldName[count($oldName)-1] .= "(".$j.")";
				
				$newName = implode(".", $oldName);
				$j++;
			} while (file_exists("files/contratos/".$newName));
		} else {
			$newName = $fileName;
		}
		
		if(move_uploaded_file($_FILES[$campoName]["tmp_name"], "files/contratos/".$newName) == false)
			return array('success' => false, "errorFeedback" => $_FILES[$campoName]['name'].' muito grande ou nome do arquivo inv&aacute;lido', "errorID" => 87);
		else {
			return array('file' => "files/contratos/".$newName , 'success' => true, "errorFeedback" => '', "errorID" => '');
		}   
		
	}
	
	function novoAditivo($campo, $valor, $motivo){
		if(!isset($this->campos[$campo]))
			return array('success' => false, "errorFeedback" => 'Campo nao existe', "errorID" => 88);
		
		$res = $this->bd->query("INSERT INTO contrato_aditivo (contratoID, campo, valor, motivo) VALUES ($this->id, '$campo', $valor, '$motivo')");
		
		
		
		if($res){
			$res = $this->bd->query("SELECT id FROM contrato_aditivo WHERE contratoID = $this->id AND campo = '$campo' AND valor = $valor AND motivo = '$motivo' ORDER BY id DESC LIMIT 1");
			
			if(count($res)) {
				$hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$consultaHist = $this->bd->query("SELECT id FROM data_historico WHERE docID = ".$this->id." AND tipo = 'aditivo' AND usuarioID = ".$_SESSION['id']." AND data >= ".$hoje);
				if (count($consultaHist) <= 0) {
					$this->doLogHist($_SESSION['id'], '', '', '', 'aditivo', '', '');
				}
				doLog($_SESSION['username'], 'Adicionou aditivo ao contrato '.$this->id.' de valor '.$valor.' no campo '.$campo.' e motivo '.$motivo);
				
				$novosVal = $this->getNovosValores($res[0]['id'], $campo);
				return array('success' => true, "errorFeedback" => '', "errorID" => 0, "aditivoID" => $res[0]['id'], 'novoValor' => $novosVal['novoValor'], 'novaPorcentagem' => $novosVal['novaPorcentagem'], 'novoTotal' => $novosVal['novoTotal'], 'novoTotalPorcentagem' => $novosVal['novoTotalPorcentagem']);
			} else
				return array('success' => false, "errorFeedback" => 'Erro ao ler aditivo do Banco de dados', "errorID" => 90);
		} else {
			return array('success' => false, "errorFeedback" => 'Erro ao escrever no Banco de dados', "errorID" => 89);
		}
		
	}
	
	function editarAditivo($aditivoID, $campo, $valor, $motivo){
		
		$res =  $this->bd->query("UPDATE contrato_aditivo SET valor = $valor, motivo = '$motivo' WHERE id = $aditivoID");
		
		$novosVal  = $this->getNovosValores($aditivoID, $campo);
		
		doLog($_SESSION['username'], 'Editou aditivo de ID: '.$aditivoID.' -> Campo: '.$campo.', Valor: '.$valor.' e Motivo: '.$motivo);
		
		if($res)
			return array('success' => true, "errorFeedback" => '', "errorID" => 0, 'novoValor' => $novosVal['novoValor'], 'novaPorcentagem' => $novosVal['novaPorcentagem'], 'novoTotal' => $novosVal['novoTotal'], 'novoTotalPorcentagem' => $novosVal['novoTotalPorcentagem']);
		else
			return array('success' => false, "errorFeedback" => 'Erro ao escrever no Banco de dados', "errorID" => 91); 
	}
	
	function getNovosValores($aditivoID,$campo){
		$novo['novoValor'] = 0;
		
		foreach ($this->getAditivos() as $aditivo) {
			if($campo == $aditivo['campo']) {
				$novo['novoValor'] += $aditivo['valor'];
				if($aditivoID == $aditivo['id']){
					$novo['novoTotal'] = $aditivo['valor'];
					if($this->campos[$campo] > 0) $novo['novaPorcentagem'] = number_format($aditivo['valor']/$this->campos[$campo]*100, 2, ',', '.');
					else $novo['novaPorcentagem'] = "0,00";
				}
			}
		}
		
		if($this->campos[$campo] > 0) $novo['novoTotalPorcentagem'] = number_format($novo['novoValor']/$this->campos[$campo]*100, 2, ',', '.');
		else  $novo['novoTotalPorcentagem'] = "0,00";
		
		return $novo;
	}
	/**
	 * Solicitacao 003
	 * Inserimos na query a busca pelo campo
	 * @param string $campo
	 * @return Ambigous <mixed, boolean, number, multitype:multitype: >
	 */
	function getAditivos($campo=""){
		if(!empty($campo))
			$qr = "SELECT id, campo, valor, motivo FROM contrato_aditivo WHERE contratoID = {$this->id} AND contrato_aditivo.campo LIKE '".$campo."'";
		else
			$qr = "SELECT id, campo, valor, motivo FROM contrato_aditivo WHERE contratoID = {$this->id}";
		return $this->bd->query($qr);
	}
	
	static function getProxDiaUtil($data, $timestamp = false) {
		global $bd;
		
		if (!$timestamp) {
			$campos = explode("/", $data);
			
			if (count($campos) != 3)
				return false;
			
			$dia = $campos[0];
			$mes = $campos[1];
			$ano = $campos[2];
			
			$inc = 1;
			$data = mktime(0, 0, 0, $mes, $dia+$inc, $ano);
			
			/*if (date('N', $data) != 6 && date('N', $data) != 7)
				return date("d/m/Y", $data);*/
			
			if (date('N', $data) == 6) {
				$inc = 3;
				$data = mktime(0, 0, 0, $mes, $dia+$inc, $ano);
			}
			elseif (date('N', $data) == 7) {
				$inc = 2;
				$data = mktime(0, 0, 0, $mes, $dia+$inc, $ano);
			}
			
			
			while ($inc < 32) {
				$sql = "SELECT * FROM feriados WHERE ano = ".$ano." AND data = ".$data;
				$res = $bd->query($sql);
				
				if (count($res) <= 0) break;
				
				$inc++;
				$data = mktime(0, 0, 0, $mes, $dia+$inc, $ano);
			}
			if ($inc >= 32) return null;
			
			if (date('N', $data) == 6 || date('N', $data) == 7) {
				$data = Contrato::getProxDiaUtil(date("d/m/Y", $data));
			}
			else {
				$data = date("d/m/Y", $data);
			}
			
			return $data;
		}
		else {
			$inc = 1;
			
			$data = $data + $inc*(24*60*60);
			
			/*if (date('N', $data) != 6 && date('N', $data) != 7)
				return $data;*/
			
			if (date('N', $data) == 6) {
				$inc = 3;
				$data = $data + ($inc-1)*(24*60*60);
			}
			elseif (date('N', $data) == 7) {
				$inc = 2;
				$data = $data + ($inc-1)*(24*60*60);
			}
			
			while ($inc < 32) {
				$sql = "SELECT * FROM feriados WHERE data = ".$data;
				$res = $bd->query($sql);
				
				if (count($res) <= 0) break;
				
				$inc++;
				$data = $data + $inc*(24*60*60);
			}
			if ($inc >= 32) return null;
			
			if (date('N', $data) == 6 || date('N', $data) == 7) {
				$data = Contrato::getProxDiaUtil($data, true);
			}
			
			return $data;
		}
	}
}
?>
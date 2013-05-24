<?php
class Fase {
	/**
	 * id desta fase
	 * @var int
	 */
	private $id;
	
	/**
	 * id da etapa a qual esta fase se refere
	 * @var int
	 */
	private $etapaID;
	
	/**
	 * id do responsável. default 0
	 * @var int
	 */
	public $responsavelID;
	
	/**
	 * Variável que indica se esta etapa foi habilitada/desabilitada;
	 * 1 -> habilitada, 0 -> desabilitada;
	 * default 1
	 * @var int
	 */
	public $enabled;
	
	/**
	 * Variável que indica se esta fase será realizada somente pela CPO;
	 * 1 -> só CPO; 0 -> fora da cpo;
	 * default: 0
	 * @var int
	 */
	public $somenteCPO;
	
	/**
	 * Variável que indica se esta etapa foi concluida;
	 * 1 -> concluida, 0 -> não concluida;
	 * default 0
	 * @var int
	 */
	public $concluido;
	
	/**
	 * id do tipo desta fase. chave estrangeira da tabela label_campo_fase
	 * @var int
	 */
	public $labelID;
	
	/**
	 * @var int
	 */
	public $tipoID;
	
	/**
	 * array de campos
	 * @var array(string)
	 */
	public $dadosTipo;
	
	/**
	 * array de valores dos campos
	 * @var array(campo => array)
	 */
	private $campos;
	
	/**
	 * indica se a fase carregada é uma fase com dados genéricas (padrão) ou se é carregada do bd
	 * @var boolean
	 */
	public $generic;
	
	/**
	 * @var BD
	 */
	private $bd;
	
	private $obsBD;
	
	
	/**
	 * Construtor de Fase. Carrega dados padrões
	 */
	function __construct() {
		global $bd;
		$this->id = 0;
		$this->dadosTipo = array();
		$this->campos = array();
		$this->generic = true;
	
		$this->responsavelID = 0;
		$this->enabled = 1;
		$this->concluido = 0;
		$this->labelID = 0;
		$this->tipoID = 0;
		
		$this->somenteCPO = 0;
	
		$this->bd = $bd;
		
		$this->obsBD = array();
	}
	
	/**
	 * Carrega a fase do tipo $tipoFaseID atribuida a etapa de id $etapaID
	 * @param int $etapaID id da etapa
	 * @param int $tipoFaseID id do tipo da fase
	 * @return array(sucesso, erroNo, feedback)
	 */
	function load($etapaID, $tipoFaseID) {
		$bd = $this->bd;
		
		$sql = "SELECT * FROM label_obra_fase WHERE id = ".$tipoFaseID;
		$dadosTipo = $bd->query($sql);
		
		if (count($dadosTipo) <= 0) {
			return array("success" => false, "errorNo" => 1, "errorFeedback" => "Tipo de Fase inv&aacute;lido.");
		}
		
		$this->labelID = $tipoFaseID;
		$this->dadosTipo = $dadosTipo[0];
		$this->loadCamposTipo();
		$this->etapaID = $etapaID;
		//$this->tipoID = $tipoFaseID;
		
		// seleciona todas as fases do tipo $tipoFaseID relacionadas a $etapaID
		$sql = "SELECT * FROM obra_fase WHERE etapaID = ".$etapaID." AND labelID = ".$this->labelID;
		$res = $bd->query($sql);

		if (count($res) <= 0) {
			// não achou nenhuma fase
			$this->tipoID = 0;
			return array("success" => true, "errorNo" => 0, "errorFeedback" => ":P");
		} else {
			// achou ([idealmente] apenas) uma fase
			$res = $res[0];
			
			$this->generic = false;
			$this->responsavelID = $res['responsavelID'];
			$this->concluido = $res['concluido'];
			$this->enabled = $res['enabled'];
			$this->etapaID = $res['etapaID'];
			$this->tipoID = $res['tipoID'];
			$this->id = $res['id'];
			if (isset($res['somenteCPO']) && $res['somenteCPO'] != "") {
				$this->somenteCPO = $res['somenteCPO'];
			}
			
			if ($this->tipoID != 0) {
				$sql = "SELECT * FROM ".$this->dadosTipo['tabBD']." WHERE id = ".$res['tipoID'];
				$res = $bd->query($sql);
				
				if (count($res) <= 0) {
					return array("success" => false, "errorNo" => 2, "errorFeedback" => "Erro ao carregar os dados dos campos desta fase.");
				}
				$res = $res[0];
				
				if(count($this->dadosTipo['campos'])) {
					foreach ($this->dadosTipo['campos'] as $v) {
						$sql = "SELECT * FROM obra_fase_obs WHERE campoID = {$v['id']} AND faseID = {$this->id}";
						$obs = $this->bd->query($sql);
						if(count($obs)) {
							$this->campos[$v['nomeAbrv']] = array("val" => $res[$v['nomeAbrv']], "obs" => $obs[0]['obs']);
							$this->obsBD[$v['id']] = array("isSet" => true, "campoNameAbrv" => $v['nomeAbrv']);
						} else {
							$this->campos[$v['nomeAbrv']] = array("val" => $res[$v['nomeAbrv']]);
							//if ($v['nomeAbrv'] == 'docID') var_dump($this->campos[$v['nomeAbrv']]);
							$this->obsBD[$v['id']] = array("isSet" => false, "campoNameAbrv" => $v['nomeAbrv']);
						}
					}
				}
			}
		}
		
	}
	
	/**
	 * Salva a Etapa no Banco de Dados
	 */
	function save() {
		$bd = $this->bd;
		
		if ($this->etapaID == 0) {
			print "Erro ao salvar Fase: ID da Etapa = 0. Contacte seu administrador.";
			exit();
		}
			
		if ($this->id != 0) {
			if ($this->tipoID > 0) { 
				$this->updateCampos();
				$this->update();
			} else {
				if (isset($this->dadosTipo['tabBD']) && $this->dadosTipo['tabBD'] != "") {
					$tipoID = $this->insertCampos();
					$this->tipoID = $tipoID;
				}
				$this->update();
			}
			
		} else {
			$tipoID = $this->tipoID;
			if (isset($this->dadosTipo['tabBD']) && $this->dadosTipo['tabBD'] != "") {
				if (!isset($this->tipoID) || $this->tipoID <= 0) {
					$tipoID = $this->insertCampos();
					$this->tipoID = $tipoID;
				}
				else { // tipoID está setado E tipoID > 0
					$this->updateCampos();
				}
			}
			
			global $conf;
			$sql =  "INSERT INTO obra_fase (`labelID`, `tipoID`, `etapaID`, `enabled`, `somenteCPO`, `responsavelID`, `concluido`) VALUES ";
			$sql .= "(".$this->dadosTipo['id'].",  $tipoID, ".$this->etapaID.", ".$this->enabled.",
			          ".$this->somenteCPO.", ".$this->responsavelID.", ".$this->concluido.")";
			$id = $bd->query($sql, $conf['DBTable'], true);

			$this->id = $id;
			doLog($_SESSION['username'], "Iniciou a fase {$id}");
			
			$tipoEtapa = $this->bd->query("SELECT obraID, empreendID FROM obra_etapa WHERE id = {$this->etapaID}");
			if(count($tipoEtapa) && $tipoEtapa[0]['obraID'] == 0){
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,fase_targetID) VALUES ({$tipoEtapa[0]['empreendID']}, ".time().", {$_SESSION['id']}, 'newFase', {$this->id})");
			}
		}
		
		foreach ($this->dadosTipo['campos'] as $campo) {
			foreach ($this->obsBD as $campoID => $obs) {
				if($obs['isSet'] == false && isset($this->campos[$obs['campoNameAbrv']]['val']) && $this->campos[$obs['campoNameAbrv']]['val'] != ""){
					$sql = "INSERT INTO obra_fase_obs (faseID, campoID, obs) VALUES ({$this->id}, {$campoID}, '".SGEncode($this->campos[$obs['campoNameAbrv']]['obs'],ENT_QUOTES,'',false)."')";
					$this->bd->query($sql);
				} elseif($obs['isSet'] == true && isset($this->campos[$obs['campoNameAbrv']]['val'])) {
					$sql = "UPDATE obra_fase_obs SET obs = '".SGEncode($this->campos[$obs['campoNameAbrv']]['obs'],ENT_QUOTES,'',false)."' WHERE faseID={$this->id} AND campoID={$campoID}";
					$this->bd->query($sql);
				}
			}
		}
		
		return array('success' => true, 'errorNo' => 1, 'errorFeedback' => 'Sem erros');
	}
	
	private function update() {
		$bd = $this->bd;
		
		$respAntigo = $bd->query("SELECT responsavelID FROM obra_fase WHERE id = {$this->id}");
		
		$sql  = "UPDATE obra_fase SET
				  enabled = ".$this->enabled.",
				  responsavelID = ".$this->responsavelID.",
		 		  concluido = ".$this->concluido;
		if ($this->tipoID != 0)	$sql .= ", tipoID = ".$this->tipoID;
		$sql .= " WHERE id = ".$this->id;
		$bd->query($sql);
		
		if($respAntigo[0]['responsavelID'] != $this->responsavelID){
			$tipoEtapa = $this->bd->query("SELECT obraID, empreendID FROM obra_etapa WHERE id = {$this->etapaID}");
			if(count($tipoEtapa) && $tipoEtapa[0]['obraID'] == 0){
				$this->bd->query("INSERT INTO empreend_historico (empreendID,data,userID,tipo,fase_targetID,user_targetID) VALUES ({$tipoEtapa[0]['empreendID']}, ".time().", {$_SESSION['id']}, 'atribRespFase', {$this->id}, {$this->responsavelID})");
			}
		}
		
		doLog($_SESSION['username'], "Editou fase {$this->id}");
	}
	
	private function updateCampos() {
		$bd = $this->bd;
		
		$sql = "UPDATE ".$this->dadosTipo['tabBD']." SET ";
		
		if (count($this->campos) > 0) {
			foreach ($this->campos as $index => $val) {
				if ($val['val'] == null) {
					$sql .= "`".$index."` = NULL";
				}
				else {
					$sql .= "".$index." = '".$val['val']."'";
				}
				$sql .= ', ';
			}
		}
		else {
			$campos = $this->dadosTipo['campos'];
			foreach ($campos as $c) {
				$sql .= "`".$c['nomeAbrv']."` = NULL";
				$sql .= ', ';
			}
		}
		$sql = trim($sql, ", ");
		$sql .= " ";
		
		$sql .= "WHERE id = ".$this->tipoID;
		
		$bd->query($sql);
	}
	
	private function insertCampos() {
		$bd = $this->bd;
		
		$sql = "INSERT INTO `".$this->dadosTipo['tabBD']."` (";
		
		if (count($this->campos) > 0) {			
			if (isset($this->campos['docID'])) {
				if ($this->campos['docID']['val'] == "" || $this->campos['docID']['val'] == 0) {
					return 0;
				}
			} 
			
			foreach ($this->campos as $index => $val) {
				$sql .= "`".$index."`";
				$sql .= ', ';
			}
			$sql = trim($sql, ", ");
			
			$sql .= ") VALUES (";
			foreach ($this->campos as $index => $val) {
				if ($val['val'] != null) 
					$sql .= "\"".$val['val']."\"";
				else
					$sql .= "NULL";
					
				$sql .= ', ';
			}
			$sql = trim($sql, ", ");
			$sql .= ")";
		}
		else {
			$campos = $this->dadosTipo['campos'];
			foreach ($campos as $c) {
				if ($c['nomeAbrv'] == 'docID') {
					return 0;
				}
				
				$sql .= "`".$c['nomeAbrv']."`";
				$sql .= ', ';
			}
			$sql = trim($sql, ", ");
			
			$sql .= ") VALUES (";
			foreach ($campos as $c) {
				$sql .= "NULL";
				$sql .= ', ';
			}
			$sql = trim($sql, ", ");
			$sql .= ")";
		}
		
		global $conf;
		return $bd->query($sql, $conf['DBTable'], true);
	}
	
	/**
	 * seta valor de um campo
	 * @param $campo
	 * @param $valor
	 * @param boolean sucesso
	 */
	function setCampo($campo, $valor, $obs, $empreendID = 0) { 
		if (!isset($campo['nomeAbrv']) && !isset($obs))
			return false;
		
		foreach ($this->dadosTipo['campos'] as $dadoCampo) {
			if($dadoCampo['nomeAbrv'] == $campo['nomeAbrv'] && $dadoCampo['tipo'] == "file") {
				
				if(($obs != NULL && $obs !== ''))	
					$this->campos[$campo['nomeAbrv']]['obs'] = $obs;
				
				if(strpos($campo['atribEspeciais'], 'multifile') !== false){
					$valor = json_decode(SGDecode($this->campos[$dadoCampo['nomeAbrv']]['val']), true);
					
					for ($i = 0 ; isset($_FILES[$dadoCampo['nomeAbrv'].$i]); $i++) {
						//var_dump($_FILES[$dadoCampo['nomeAbrv'].$i]);
						if($_FILES[$dadoCampo['nomeAbrv'].$i]['name'] !== '') {
							$res = $this->uploadFile($dadoCampo['nomeAbrv'].$i, $empreendID);
							
							if($res['success']) {
								$valor[] = $res['file'];
							} else {
								return $res;
							}
								
						}
						
					}
					
					$valor = json_encode($valor);
					$this->campos[$dadoCampo['nomeAbrv']]['val'] = $valor;
					
				} else {
					$res = $this->uploadFile($campo['nomeAbrv'], $empreendID);
					
					if($res['success']) {
						$valor = $res['file'];
						$this->campos[$campo['nomeAbrv']]['val'] = $valor;
						return true;
					} else {
						return $res;
					}
				}
			}
		}
		
		if(($obs != NULL && $obs !== '') || ($valor !== $this->campos[$campo['nomeAbrv']]['val'] && $obs === ''))	
			$this->campos[$campo['nomeAbrv']]['obs'] = SGEncode($obs,ENT_QUOTES,'',false);
		if($valor != NULL && $valor !== '')
			$this->campos[$campo['nomeAbrv']]['val'] = SGEncode($valor,ENT_QUOTES,'',false);
		
		return true;
	}
	
	
	/**
	 * Retorna valor de um campo
	 * @param string $campo nome do campo
	 * @return array valor do campo + comentario
	 */
	function getCampo($campo) {
		if (isset($this->campos[$campo]))
			return $this->campos[$campo];
		
		return null;
	}
	
	/**
	 * Mostra o resumo desta etapa
	 * @param int $procIT ID do processo a qual as its geradas devem ser associadas 
	 */
	public function showResumo($procIT) {
		$template = showFaseTemplate();
		$html = $template['template'];
		
		$campos = $this->dadosTipo['campos'];
		
		$hidden = '';
		$tabela = '';
		$estilo = '';
		foreach ($campos as $c) {
			$nome = $c['nome'];
			//$cod = 'Insira código aqui.';
			$cod = $this->montaCampo($c);
			$obs = $this->montaCampo(array("nomeAbrv" => $c['nomeAbrv']),true);
			
			if(strpos($c['atribEspeciais'], 'linha_inteira') !== false || $c['tipo'] == 'doc')
			 	$aux = str_replace('{$campo_nome}', $nome, $template['linha_inteira']);
			else
				$aux = str_replace('{$campo_nome}', $nome, $template['campo']);
				

			if ($c['tipo'] == 'doc') {				
				$hidden = '<input type="hidden" name="newDoc" id="newDoc_'.$this->dadosTipo['id'].'" value="1">';
				$hidden .= '<input type="hidden" name="procIT" value="'.$procIT.'">';
				
				$estilo = '';
				if ($cod != '') {
					$estilo = 'style="display: none;"';
				}
				
				$cod .= '<div id="novoForm'.$this->dadosTipo['etapaID'].$this->dadosTipo['id'].'" '.$estilo.'>';
				$cod .= showForm("novo_it", $c['atribEspeciais'], 1, $this->bd, null);
				$cod .= '</div>';
			}
				
			$aux = str_replace('{$campo_html}', $cod, $aux);
			$aux = str_replace('{$observacoes}', $obs, $aux);
			
			$tabela .= $aux;
		}
		
		if (!$this->checkEditPermission()) {
			$html .= '
			<script type="text/javascript">
				$(document).ready(function() {
					$("#submitFase'.$this->dadosTipo['id'].'").hide();
				});
			</script>';
		}
		
		$html = str_replace('{$estilo}', $estilo, $html);
		$html = str_replace('{$extra_hidden}', $hidden, $html);
		$html = str_replace('{$tipoFaseID}', $this->dadosTipo['id'], $html);
		$html = str_replace('{$fase_id}', $this->id, $html);
		$html = str_replace('{$campos}', $tabela, $html);
		$html = str_replace('{$nome_fase}', $this->dadosTipo['nome'], $html);
		$html = str_replace('{$fase_tipo_id}', $this->dadosTipo['id'], $html);
		
		return $html;
	}
	
	/**
	 * retorna array com informações de todas as fases relacionadas com o tipo de etapa $tipoEtapaID
	 * @param int $tipoEtapaId
	 * @return array
	 */
	public static function getFasesPorEtapa($tipoEtapaID) {
		global $bd;
		$sql = "SELECT * FROM label_obra_fase WHERE etapaID = ".$tipoEtapaID." ORDER BY ordem ASC";
		
		return $bd->query($sql);
	}
	
	/**
	 * Função auxiliar que carrega os dados da etapa armazenados na tabela desta fase
	 */
	private function loadCamposTipo() {
		$bd = $this->bd;
		$tipoFaseID = $this->dadosTipo['id'];
		
		$sql = "SELECT l.* FROM obra_fase_campo AS f INNER JOIN label_campo_fase AS l ON f.campoID = l.id WHERE f.faseID = ".$tipoFaseID;
		$res = $bd->query($sql);
		
		$this->dadosTipo['campos'] = $res;
	}

	/**
	 * Monta o campo para interface desta fase
	 * @param array $campo (id, nome, nomeAbrv, tipo, tamanho, atribEspeciais) [mesma estrutura da tabela label_campo_fase]
	 * @return string
	 */
	public function montaCampo($campo, $obs = false) {
		global $conf;
		$attr = array();
		//var_dump($campo['atribEspeciais']);
		if (isset($campo['tipo'])) $tipo = $campo['tipo'];
		$id = $campo['nomeAbrv'];
		
		$valorAtual = '';
		$inputEdicao = '';
		
		if($obs) {
			if ($this->checkEditPermission()) {
				if (isset($this->campos[$id]['obs'])) $valorAtual = $this->campos[$id]['obs'];
				$attr['value'] = $valorAtual;
				$inputEdicao = geraInput($id."_observacao", $attr);
			} else {
				if (isset($this->campos[$id]['obs'])) $valorAtual = $this->campos[$id]['obs'];
			}
			$id = $id."_observacao";
		} else {
			
			if($tipo === '') {
				return;
			} elseif (strcasecmp($tipo, 'FILE') === 0) {
				//$attr = array('type' => 'file');
				$attr['type'] = 'file';
				if(strpos($campo['atribEspeciais'],"multifile") !==  false) {
					$arquivos = array();
					if (isset($this->campos[$id]['val'])) $arquivos = json_decode(SGDecode($this->campos[$id]['val']), true);
					
					if(isset($this->campos[$id]['val']) && $this->campos[$id]['val'] !== '' && $this->campos[$id]['val'] !== null) {
						foreach ($arquivos as $arq) {
							$fileLabel = explode("/", $arq);
							$valorAtual .= '<a href="'.$arq.'" target="_blank">'.$fileLabel[count($fileLabel)-1].'</a><br />';
						}
					}
					$attr['class'] = "multifile";
					$attr['onclick'] = "javascript:addNewFile('$id',1)";
					if ($this->checkEditPermission()) {
						$inputEdicao = '<div id="'.$id.'_arqDiv">'.geraInput($id. '0', $attr).'</div>';
					}
				} else {
					if (isset($this->campos[$id]['val']) && $this->campos[$id]['val'] != null) {
						$fileLabel = explode("/", $this->campos[$id]['val']);
						$valorAtual = '<a href="'.$this->campos[$id]['val'].'" target="_blank">'.$fileLabel[count($fileLabel)-1].'</a>';
					}
					if ($this->checkEditPermission()) {
						$inputEdicao = geraInput($id, $attr);
					}
				}
				
				
				
			}
			elseif (strcasecmp($tipo, 'select') === 0) {
				$atribEspeciais = array();
				if (isset($campo['atribEspeciais']))
					$atribEspeciais = json_decode($campo['atribEspeciais'], true);
				//var_dump($atribEspeciais['itens']);
				
				$padrao = '';
				if (isset($atribEspeciais['padrao'])) $padrao = $atribEspeciais['padrao'];
				if (isset($this->campos[$id]['val']) && $this->campos[$id]['val'] != "") {
					$padrao = $this->campos[$id]['val'];
				}
				
				$itens = array();
				if (isset($atribEspeciais['itens'])) $itens = $atribEspeciais['itens'];
				if ($this->checkEditPermission()) {
					$inputEdicao = geraSelect($id, $itens, $padrao);
				}
			}
			elseif (strcasecmp($tipo, 'doc') === 0) {
				if (isset($this->campos[$id]['val']) && $this->campos[$id]['val'] != null && $this->campos[$id]['val'] != 0) {
					$doc = new Documento($this->campos[$id]['val']);
					$doc->loadCampos();
					$inputEdicao = '<a onclick="window.open(\'sgd.php?acao=ver&docID='.$doc->id.'\',\'doc\',\'width=\'+screen.width*';
					$inputEdicao .= $conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"];
					$inputEdicao .= '+\',scrollbars=yes,resizable=yes\').focus()">';
					$inputEdicao .= $doc->dadosTipo['nome']." ".$doc->numeroComp.'</a> ';
					if ($this->checkEditPermission()) {
						$inputEdicao .= '<a onclick="showItForm('.$this->dadosTipo['etapaID'].','.$this->dadosTipo['id'].')">[Nova IT]</a><br />';
					}
				}
			}
			elseif (strcasecmp($tipo, 'yesno') == 0) {
				$defaultYes = null;
				if (isset($this->campos[$id]['val']) && $this->campos[$id]['val'] != null) {
					if ($this->campos[$id]['val'] == 1) {
						$defaultYes = true;
						$valorAtual = 'Sim';
					}
					else {
						$defaultYes = false;
						$valorAtual = 'N&atilde;o';
					}
				}
				if ($this->checkEditPermission()) {
					$inputEdicao = geraSimNao($id, $defaultYes);
				} 
			} elseif(strcasecmp($tipo, 'tabela') === 0) {
				//tipo tabela - utilizado somente no levantamento de necessidades
				//definicao dos 'sub-atributos' do locais a serem inseridos
				$campos_gerais[] = array('nomeAbrv' => 'nome_local', 'label' => 'Nome do Local/Ambiente:', 'tipo' => 'input');
				$campos_gerais[] = array('nomeAbrv' => 'caract_local', 'label' => 'Caracter&iacute;stica', 'tipo' => 'input');
				$campos_gerais[] = array('nomeAbrv' => 'climatiz_local', 'label' => 'Climatiza&ccedil;&atilde;o', 'tipo' => 'select', 'atribEspeciais' => '{"tamanho":3,"padrao":"","itens":[{"value":"conforto","label":"Tipo Conforto"},{"value":"obrigatorio","label":"Obrigat&oacute;rio para Seguran&ccedil;a"},{"value":"outro","label":"Outro"}]}');
				$campos_gerais[] = array('nomeAbrv' => 'telef_local', 'label' => 'Dados/Telefonia', 'tipo' => 'yesno');
				$campos_gerais[] = array('nomeAbrv' => 'redeEstab_local', 'label' => 'Rede Estabilizada', 'tipo' => 'yesno');
				$campos_gerais[] = array('nomeAbrv' => 'gases_local', 'label' => 'Gases', 'tipo' => 'yesno');
				$campos_gerais[] = array('nomeAbrv' => 'area_local', 'label' => '&Aacute;rea (m<sup>2</sup>)', 'tipo' => 'input');
				$campos_gerais[] = array('nomeAbrv' => 'obs_local', 'label' => 'Observa&ccedil;&otilde;es Gerais', 'tipo' => 'input');
				//definicao das caracteristicas especificas dos locais a serem cadastrados
				$campos_especificos[] = array('nomeAbrv' => 'lajes', 'label' => 'Sobrecarga Diferenciada de Lajes', 'tipo' => '');
				$campos_especificos[] = array('nomeAbrv' => 'residuos', 'label' => 'Gera&ccedil;&atilde;o de res&iacute;duos', 'tipo' => '');
				$campos_especificos[] = array('nomeAbrv' => 'anvisa', 'label' => '&Aacute;rea com restri&ccedil;&otilde;es da Anvisa', 'tipo' => '');
				$campos_especificos[] = array('nomeAbrv' => 'gde_potencia', 'label' => 'Equipamento de Grande Pot&ecirc;ncia', 'tipo' => 'input');
				$campos_especificos[] = array('nomeAbrv' => 'divisorias', 'label' => 'Divis&oacute;rias', 'tipo' => 'select', 'atribEspeciais' => '{"tamanho":3,"padrao":"","itens":[{"value":"concreto","label":"Concreto"},{"value":"metal","label":"Estrutura Met&aacute;lica"},{"value":"outro","label":"Outro"}]}');
				$campos_especificos[] = array('nomeAbrv' => 'forro', 'label' => 'Forro', 'tipo' => 'select');
				$campos_especificos[] = array('nomeAbrv' => 'isolamento_acustico', 'label' => 'Isolamento ac&uacute;stico', 'tipo' => '');
				$campos_especificos[] = array('nomeAbrv' => 'gerador', 'label' => 'Gerador', 'tipo' => '');
				
				//carrega a interface do campo tabela
				$template = showCampoTabela($id);
				
				//seleciona o template do campo tabela
				$html = $template['template'];
				//le os locais para geracao da tabela de locais
				$locais = array();
				if (isset($this->campos[$id]['val'])) {
					$locais = json_decode(utf8_encode(SGDecode($this->campos[$id]['val'],ENT_QUOTES)));
				}
				//var para tratamento dos campos yesNo
				$bool = array('0'=> "N&atilde;o", '1' => "Sim");
				//para cada local lido, gera uma linha na tabela
				$i = 0;
				$local_tr = '';
				if($locais)
					foreach ($locais as $l) {
						$l->dados = $bool[$l->dados];
						$l->estab = $bool[$l->estab];
						$l->gases = $bool[$l->gases];
						$especificos = '';
						
						foreach ($l->especificos as $ce) {
							$especificos .= $ce->label.": ".$ce->valor->label." (Obs: ".$ce->obs.") <br />";
						}
						
						$local_tr .= str_ireplace(
									array('{$id_local}','{$local_nome}','{$local_caract}','{$local_climatiz}','{$local_dados}','{$local_estab}','{$local_gases}','{$local_area}','{$local_obsGerais}','{$local_caractEspec}'),
									array($i,            $l->nome       , $l->caract      , $l->clima->label , $l->dados      , $l->estab     , $l->gases       , $l->area      , $l->obs           , $especificos),
									$template['local_tr']);
					
						$i++;
					}
				
				$html = str_ireplace('{$local_tr}', $local_tr, $html);
				//completa campos gerais
				$campos_tr = '';
				foreach ($campos_gerais as $campo) {
					$campos_tr .= str_ireplace(array('{$nomeCampo}', '{$htmlCampo}'), array($campo['label'],$this->montaCampo($campo)), $template['campos_tr']);
				}
				
				$caract_especif = '';
				//completa os campos especificos
				foreach ($campos_especificos as $c) {
					$caract_especif .= str_ireplace(array('{$caract_espec_nome}','{$caract_espec_label}','{$caract_espec_input}'), array($c['nomeAbrv'],$c['label'],$this->montaCampo($c)), $template['caract_especif_tr']);
				}
				$html = str_ireplace('{$caract_especif_tr}', $caract_especif, $html);
				
				$localL = '';
				if (isset($this->campos[$id]['val'])) $localL = $this->campos[$id]['val'];
				$html = str_ireplace(array('{campos}','{$local_json}'), array($campos_tr, $localL), $html);
				return $html;
			//campo textarea
			} elseif(strcasecmp($tipo, 'textarea') == 0) {
				//completa com o valor atual do campo
				$valorAtual = '';
				if (isset($this->campos[$id]['val'])) $valorAtual = $this->campos[$id]['val'];
				
				//le os atributos especiais
				$atribEspeciais = json_decode($campo['atribEspeciais']);
				if(is_array($atribEspeciais->attr)){
					foreach ($atribEspeciais->attr as $atrib) {
						foreach (get_object_vars($atrib) as $atr => $val) {
							$attr[$atr] = $val;
						}
					}
				}
				$valorTextArea = '';
				if (isset($this->campos[$id]['val'])) $valorTextArea = $this->campos[$id]['val'];
				$inputEdicao = geraTextarea($id, $atribEspeciais->cols, $atribEspeciais->rows, $valorTextArea, $attr);
				
			} else {
				if (isset($this->campos[$id]['val']) && $this->campos[$id]['val'] != null) {
					$attr['value'] = $this->campos[$id]['val'];
				}
				$attr['size'] = '50';
				if (isset($this->campos[$id]['val'])) $attr['value'] = $this->campos[$id]['val'];
				if ($this->checkEditPermission()) {
					$inputEdicao = geraInput($id, $attr);
				}
			}
		}
		if(!$valorAtual)
			return $inputEdicao;
		
		$html = '<div id="atual_'.$id.'">' .
					$valorAtual;
		if($this->checkEditPermission()){
			$html .= ' <a id="'.$id.'_edit_link" href="javascript:void(0);" onclick="javascript:showEditLink(\''.$id.'\',\''.$campo['atribEspeciais'].'\')">[Editar]</a>';
		}
		$html .= '</div>';
		if($this->checkEditPermission())
			$html .= '<div id="edit_'.$id.'" style="display:none">
				'.$inputEdicao.'
			</div>';
		
		return $html;
	}
	
	function get($attr){
		if(isset($this->$attr))
			return $this->$attr;
		else
			return null;
	}
	
	function checkEditPermission() {
		require_once 'sgp_modules.php';
		global $bd;
		checkLogin(0);
		
		if ($_SESSION['id'] == $this->responsavelID) {
			return true;
		}
		
		if (isIndirectManager($_SESSION['id'], $this->responsavelID, $bd)) {
			return true;
		}
		
		if ($_SESSION['grupo'] == 2) {
			return true;
		}
		
		return false;
	}
	
	
	function uploadFile($campoName, $empreendID){
		
		if($_FILES[$campoName] == ''){
			return array('success' => false);
		}
		
		if($_FILES[$campoName]['error'] > 0){
			return array('success' => false, "errorFeedback" => $_FILES["arq".$i]['name'], "errorID" => $_FILES["arq".$i]['error']);
		}
		
		if(!is_dir("files/empreend_".$empreendID)){
			mkdir("files/empreend_".$empreendID);
		}
		
		$fileName = "[Fase_".$this->id."]".$_FILES[$campoName]['name'];
		$fileName = stringBusca($fileName,true);
		
		$newName = strtolower($fileName);
		if (file_exists("files/empreend_".$empreendID."/".$newName)){
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
			} while (file_exists("files/empreend_".$empreendID."/".$newName));
		} else {
			$newName = $fileName;
		}
		
		if(move_uploaded_file($_FILES[$campoName]["tmp_name"], "files/empreend_".$empreendID."/".$newName) == false)
			return array('success' => false, "errorFeedback" => $_FILES[$campoName]['name'].' muito grande ou nome do arquivo inv&aacute;lido', "errorID" => 87);
		else {
			return array('file' => "files/empreend_".$empreendID."/".$newName , 'success' => true, "errorFeedback" => '', "errorID" => '');
		}   
		
		//var_dump($_FILES);exit();
	}
}
?>
<?php
class Historico_Doc extends historico {
	/**
	 * ID do documento em questão
	 * @var int
	 */
	protected $docID;
	
	/**
	 * Destinatário do despacho (Corresponde ao campo 'unidade' na data_historico)
	 * @var string
	 */
	protected $unidade;
	
	/**
	 * em desuso
	 * @var unknown_type
	 */
	protected $volumes;
	
	/**
	 * Corresponde ao conteudo do despacho
	 * @var string
	 */
	protected $despacho;
	
	/**
	 * O label do despacho. Aparece antes do $despacho em negrito no html
	 * @var string
	 */
	protected $label;
	
	/**
	 * Ação do Despacho
	 * @var string
	 */
	protected $acao;
	
	protected $doc_targetID;
	
	/**
	 * Construtor
	 * @param BD $bd
	 */
	function __construct($bd) {
		$this->docID = 0;
		$this->unidade = '';
		$this->volumes = null;
		$this->despacho = '';
		$this->label = '';
		$this->acao = '';
		$this->doc_targetID = 0;
		
		parent::__construct($bd);
	}
	
	/**
	 * Carrega o histórico em sí
	 * @param int $id id do histórico
	 * @return boolean $success
	 */
	public function load($id) {
		$bd = $this->get('bd');
		
		$sql = "SELECT * FROM data_historico WHERE id = ".$id;
		$res = $bd->query($sql);
		
		// verificação de segurança
		if (count($res) > 0) {
			$res = $res[0];
			
			// seta os valores
			$this->set('id', $id);
			$this->set('data', $res['data']);
			$this->set('userID', $res['usuarioID']);
			$this->set('tipo', $res['tipo']);
			$this->set('docID', $res['docID']);
			$this->set('acao', $res['acao']);
			$this->set('unidade', $res['unidade']);
			$this->set('label', $res['label']);
			$this->set('volumes', $res['volumes']);
			$this->set('despacho', $res['despacho']);
			$this->set('doc_targetID', $res['doc_targetID']);
			
			// retorna sucesso
			return true;
		}
		
		// retorna falha
		return false;
	}
	
	
	/**
	 * Salva o histórico
	 * @return boolean success
	 */
	public function save() {
		// só salva se o id deste histórico é zero (ou seja, ainda não foi inserido no banco de dados)
		if ($this->get('id') == 0) {
			$bd = $this->get('bd');
			
			// cria sql
			$sql = "INSERT INTO data_historico (data, tipo, docID, usuarioID, acao, unidade, label, despacho, volumes, doc_targetID) VALUES (";
			$sql .= time() . ", ";
			$sql .= "'" . $this->get('tipo') . "', ";
			$sql .= $this->get('docID') . ", ";
			$sql .= $this->get('userID') . ", ";
			$sql .= "'" . $this->get('acao') . "', ";
			$sql .= "'" . $this->get('unidade') . "', ";
			$sql .= "'" . $this->get('label') . "', ";
			$sql .= "'" . $this->get('despacho') . "', ";
			$sql .= "'" . $this->get('volumes') . "', ";
			$sql .= $this->get('doc_targetID') . ")";
			// insere e retorna id
			return $bd->query($sql, null, true);
		}
		
		// se chegou aqui, retorna falso
		return false;
	}
	
	
	/**
	 * Imprime o HTML deste histórico
	 * @return html
	 */
	public function printHTML() {
		$html = '';
		
		// pega conteudo do despacho
		$despacho = $this->get('despacho');
		
		// seta valor do onclick para esta linha
		$onclick = '';
		if ($despacho != null) {
			// se despacho for diferente de null, insere onclick
			$onclick = 'onclick="showDesp('.$this->get('id').')"';
		}
		
		// carrega dados do usuário que 'realizou' esta entrada de histórico
		$usuario = $this->getUsuario($this->get('userID'));
		
		// pega o label da ação realizada
		$acao = $this->getLabelAcao();
		
		// monta linha
		$html .= '
		<tr class="c" style="cursor: pointer;" '.$onclick.'>
			<td class="cc">'.date("d/m/Y H:i", $this->get('data')).'</td>
			<td class="cc"><a href="javascript:void(0)" onclick="showUserProfile('.$this->get('userID').')">'.SGDecode($usuario['nome']).'</a></td>
			<td class="c">'.$acao.'</td>
		</tr>
		';
		
		// se o despacho não for vazio, insere linha abaixo (escodida) para que
		// ela seja mostrada quando o usuário clicar na linha original do despacho
		if ($despacho != null) {
			$html .= '
			<tr id="desp'.$this->get('id').'" class="c" style="display: none;">
				<td class="c" colspan="3"><b>'.SGDecode($this->get('label')).'</b>: '.SGDecode($despacho).'</td>
			</tr>
			';
		}
		
		// retorna o html gerado
		return $html;
	}
	
	/**
	 * Imprime o html formatado para ajax
	 * @return JSON
	 */
	public function printHTMLAjax() {
		
	}
	
	/**
	 * Retorna todos os ids dos históricos vinculados ao doc de id $docID
	 * @param int $docID
	 * @param BD $bd
	 * @return array [id]
	 */
	public static function getAllHistID($docID, BD $bd) {
		//$bd = $this->get('bd');
		
		$sql = "SELECT id FROM data_historico WHERE docID = ".$docID." ORDER BY data DESC, id DESC";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna as 5 ultimas entradas de historico de um determinado Usuario
	 * @param int $userID
	 */
	public static function get5Ultimos($userID){
		global $bd;
		
		$sql = "SELECT id FROM data_historico WHERE usuarioID = {$userID} ORDER BY data DESC LIMIT 5";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna array contento username, nome, sobrenome e nome completo do usuário passado por parâmetro
	 * @return array [username][nome][sobrenome][nomeCompl]
	 */
	private function getUsuario($id) {
		$defaultReturn = array(
							"username" => 'Desconhecido',
							"nome" => 'Desconhecido',
							"sobrenome" => 'Desconhecido',
							"nomeCompl" => 'Desconhecido');
		
		if ($id == null || $id <= 0) {
			return $defaultReturn;
		}
		
		$bd = $this->get('bd');
		
		$sql = "SELECT username, nome, sobrenome, nomeCompl FROM usuarios WHERE id = ".$id;
		$res = $bd->query($sql);
		
		if (count($res) != 1) {
			return $defaultReturn;
		}
		else {
			return $res[0];
		}
	}
	
	/**
	 * Retorna o label da ação deste histórico, dependendo do $tipo deste histórico
	 * @return string $acao
	 */
	function getLabelAcao() {
		$acao = '';
		
		if($this->get('tipo') == 'criacao') {
			$acao = 'Criou este documento.';
		} elseif ($this->get('tipo') == 'obs') {
			if ($this->get('despacho') == "") return $acao;
			$acao = 'Adicionou observa&ccedil;&atilde;o a este documento.';
		} elseif ($this->get('tipo') == 'entrada') {
			$acao = 'Registrou entrada deste documento de '.SGDecode($this->get('unidade'));
		} elseif ($this->get('tipo') == 'saida') {
			$acao = 'Registou saida deste documento para '.SGDecode($this->get('unidade'));
		} elseif ($this->get('tipo') == 'despIntern') {
			$acao = 'Despachou este documento para '.SGDecode($this->get('unidade'));
		} elseif ($this->get('tipo') == 'arq') {
			$acao = 'Arquivou este documento.';
		} elseif ($this->get('tipo') == 'desarq') {
			$acao = 'Desarquivou este documento.';
		} elseif ($this->get('tipo') == 'solic') {
			$acao = 'Solicitou este documento.';
		} elseif ($this->get('tipo') == 'solicArq') {
			$acao = 'Solicitou arquivamento deste documento.';
		} elseif ($this->get('tipo') == 'solicDes') {
			$acao = 'Solicitou desarquivamento deste documento.';
		} elseif ($this->get('tipo') == 'solicProt') {
			$acao = 'Solicitou o documento externamente.';
		} elseif ($this->get('tipo') == 'salvaObras') {
			$acao = 'Salvou v&iacute;nculo de obras para este documento.';
		} elseif ($this->get('tipo') == 'salvaRec') {
			$acao = 'Salvou origens e/ou valores de recursos v&iacute;nculados a este contrato.';
		} elseif ($this->get('tipo') == 'aditivo') {
			$acao = 'Inseriu novo aditivo a este contrato.';
		} elseif ($this->get('tipo') == 'anexoEste') {
			$histOkay = $this->testaDocTargetID(true);
			
			if ($histOkay) {
				$doc = new Documento($this->get('doc_targetID'));
				//print_r($doc);
				$doc->loadTipoData();
				$acao = 'Anexou este documento ao <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a>';
			}
			else {
				if ($this->get('acao') != "") {
					$acao = $this->get('acao');
				}
				else {
					$esteDoc = new Documento($this->get('docID'));
					$esteDoc->loadDados();
					if ($esteDoc->docPaiID != 0) {
						$doc = new Documento($esteDoc->docPaiID);
						$doc->loadTipoData();
						$acao = 'Anexou este documento ao <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a>';
					}
					else {
						$acao = 'Anexou este documento.';
					}
				}
			}
		} elseif ($this->get('tipo') == 'anexOutro') {
			$histOkay = $this->testaDocTargetID(true);
			
			if ($histOkay) {
				$doc = new Documento($this->get('doc_targetID'));
				//var_dump($this->get('doc_targetID'));
				$doc->loadTipoData();
				$acao = 'Anexou o documento <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a> a este.';
			}
			else {
				if ($this->get('acao') != "") {
					$acao = $this->get('acao');
				}
				else {
					$acao = 'Anexou outro documento a este';
				}
			}
		}
		elseif ($this->get('tipo') == 'desanexE') {
			/**
			 * Solicitacao 002
			 * Incluindo Remocao do anexo ao historico
			 */
			$histOkay = $this->testaDocTargetID(true);
				
			if ($histOkay) {
				$doc = new Documento($this->get('doc_targetID'));
				//print_r($doc);
				$doc->loadTipoData();
				$acao = 'Removeu este anexo do documento <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a>';
			}
			else {
				if ($this->get('acao') != "") {
					$acao = $this->get('acao');
				}
				else {
					$esteDoc = new Documento($this->get('docID'));
					$esteDoc->loadDados();
					if ($esteDoc->docPaiID != 0) {
						$doc = new Documento($esteDoc->docPaiID);
						$doc->loadTipoData();
						$acao = 'Removeu este anexo do documento <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a>';
					}
					else {
						$acao = 'Removeu este anexo de um documento.';
					}
				}
			}
		} elseif ($this->get('tipo') == 'desanexO') {
			/**
			 * Solicitacao 002
			 * Incluindo Remocao do anexo ao historico
			 */
			$histOkay = $this->testaDocTargetID(true);
				
			if ($histOkay) {
				$doc = new Documento($this->get('doc_targetID'));
				//var_dump($this->get('doc_targetID'));
				$doc->loadTipoData();
				$acao = 'Removeu o anexo <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', $doc->id).'">'.$doc->id.' ('.$doc->dadosTipo['nome'].' '.$doc->numeroComp.')</a> deste.';
			}
			else {
				if ($this->get('acao') != "") {
					$acao = $this->get('acao');
				}
				else {
					$acao = 'Removeu anexo deste';
				}
			}
		}
		else {
			$acao = $this->get('acao');
		}
		
		return $acao;
	}
	
	private function getArrayAcoes() {
		$array = array();
		
		$array['criacao'] = 'Criou este documento.';
		$array['obs'] = 'Adicionou observa&ccedil;&atilde;o a este documento.';
		$array['entrada'] = 'Registrou entrada deste documento de {$unidade}';
		$array['saida'] = 'Registou saida deste documento para {$unidade}';
		$array['despIntern'] = 'Despachou este documento para {$unidade}';
		$array['arq'] = 'Arquivou este documento.';
		$array['desarq'] = 'Desarquivou este documento.';
		$array['solic'] = 'Solicitou este documento.';
		$array['solicArq'] = 'Solicitou arquivamento deste documento.';
		$array['solicDes'] = 'Solicitou desarquivamento deste documento.';
		$array['solicProt'] = 'Solicitou o documento externamente.';
		$array['salvaObras'] = 'Salvou v&iacute;nculo de obras para este contrato.';
		$array['salvaRec'] = 'Salvou origens e/ou valores de recursos vinculados a este contrato.';
		$array['aditivo'] = 'Inseriu novo aditivo a este contrato.';
		$array['anexoEste'] = 'Anexou este documento ao {$doc_id} ({$doc_tipo_nome} {$doc_numero})';
		$array['anexOutro'] = 'Anexou o documento <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', '{$doc_id}').'">{$doc_id} ({$doc_tipo_nome} {$doc_numero})</a> a este.';
		/**
		* Solicitacao 002	
		*/
		$array['desanexE'] = 'Removeu este anexo do documento {$doc_id} ({$doc_tipo_nome} {$doc_numero})';
		$array['desanexO'] = 'Removeu o anexo <a href="javascript:void(0)" onclick="'.Documento::geraLinkDoc('ver', '{$doc_id}').'">{$doc_id} ({$doc_tipo_nome} {$doc_numero})</a> deste.';
		/**
		 * fim
		 */
		
		return $array;
	}
	
	private function testaDocTargetID($chatError = false) {
		if ($this->get('doc_targetID') == 0) {
			if ($chatError) {
				$bd = $this->get('bd');
				$sql  = "INSERT INTO chat (chat.from, chat.to, message, sent, recd) VALUES ";
				$users = "SELECT username FROM usuarios WHERE gid = 2";
				$users = $bd->query($users);
				
				//var_dump($users);
				if (count($users) > 0) {
					$i = 0;
					foreach($users as $u) {
						if ($i != 0) $sql .= ', ';
						$sql .= "('SiGPOD', '".$u['username']."', '".rawurlencode('Erro no hist&oacute;rico do documento '.$this->docID.'. ('.$this->get('tipo').').')."', NOW(), 0)";
						
						$i++;
					}
					//var_dump($sql);
					
					//$bd->query($sql);
				}
			}
			return false;
		}
		
		return true;
	}
	
	/**
	 * Solicitacao 002
	 * ParserHist: Faz o parser o histórico dado uma $str. Essa $str é
	 * comparada ao tipo e retorna o historico do tipo, (user + dados)
	 * @param String $str : tipo
	 * @param int $id : id do historico
	 * @return stdClass $obj
	 */
	public function parserHist($str,$id){
		$this->load($id);
		if(strcmp($str, $this->get("tipo"))!=0)
			return null;
		$obj = new stdClass();
		$obj->owner = $this->get("userID");
		$obj->docID = $this->get("docID");
		$obj->targetID = $this->get("doc_targetID");
		return $obj;
	}
	/*public function getData() {
		return $this->data;
	}*/
	
	/*
	 * public static function getAllFromID($id);
	 *
	 * @return [id][label]
	 * public static function getAllTypes();
	 */
}
?>
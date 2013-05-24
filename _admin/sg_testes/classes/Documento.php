<?php
/**
 * @version 0.6 21/3/2011 
 * @package geral
 * @author Mario Akita
 * @desc contem os atributos dos documentos e os metodos para trabalho com documentos 
 */

class Documento {
	/**
	 * id do documento
	 * @var int
	 */
	public $id;
	
	/**
	 * indica se o doc ja foi anexado a algum doc. Um doc anexado nao pode ser despachado e nem ter doc anexados a ele
	 * @var boolean
	 */
	public $anexado;
	public $docPaiID;

	/**
	 * data de criacao do documento em unix timestamp
	 * @var int
	 */
	public $data;
	
	/**
	 * id do usuario que criou o documento
	 * @var int
	 */
	public $criador;
	
	/**
	 * id do usuario que possui o documento no momento (que tem pendente)
	 * @var int
	 */
	public $owner;
	
	/**
	 * nome da area em que o doc se encontra se foi despachado para area
	 * @var string
	 */
	public $areaOwner;
	
	/**
	 * id do tipo de documento no BD
	 * @var int
	 */
	public $labelID;
	
	/**
	 * id do documento dentro da tabela
	 * @var int
	 */
	public $tipoID;
	
	/**
	 * array com os nomes de arquivos anexos
	 * @var array
	 */
	public $anexo;
	
	/**
	 * array com os nomes e valores dos campos
	 * @var array
	 */
	public $campos;
	
	/**
	 * dados do tipo
	 * @var array
	 */
	public $dadosTipo;
	
	/**
	 * Nome do emitente (tabela pendentes)
	 * @var string
	 */
	public $emitente;
	
	/**
	 * Numero completo do documento
	 * @var string
	 */
	public $numeroComp;
	
	/**
	 * ID da obra a qual esse doc esta assoc
	 * @var int
	 */
	public $obraID;
	
	/**
	 * ID do empreendimento ao qual esse doc esta assoc.
	 * @var int
	 */
	public $empreendID;
	
	/**
	 * indica se este documento está no arquivo
	 * 0 = não, 1 = sim
	 */
	public $arquivado;
	
	/**
	 * indica quem solicitou este documento
	 * @var string
	 */
	public $solicitante;
	
	/**
	 * indica se o documento foi solicitado no teraterm (ou qq que seja o nome do artefato arqueológico)
	 */
	public $solicitado;
	
	/**
	 * indica se o documento foi solicitado para desarquivamento
	 * @var int
	 * 0 = não, 1 = sim
	 */
	public $solicDesarquivamento;
	
	/**
	 * identifica o id da ultima entrada do historico
	 * @var int;
	 */
	public $ultimoHist;
	
	/**
	 * Instancia de conexao ao BD para uso interno
	 * @var BD
	 */
	public $bd;
	
	public $isSigiloso;
	
	/**
	 * construtor da classe. atribui apenas ID do documento
	 * @param int $id
	 */
	function __construct($id){
		global $bd;
		$this->bd = $bd;
		$this->id = $id;
	}
	
	/**
	 * carrega dados do documento comuns a todos os documentos (DOC)
	 */
	function loadDados($dados = null) {
		if(!$this->bd){
			global $bd;
			$this->bd = $bd;
		}
		
		if (!$dados) {
			$res = $this->bd->query("SELECT * FROM doc WHERE id = ".$this->id);
			if(count($res) != 1) showError(5);
			else $res = $res[0];
		}
		else {
			$res = $dados;
			//var_dump(true);
		}
		
		//var_dump($res);
		$this->data = $res['data'];
		$this->criador = $res['criadorID'];
		$this->areaOwner = SGDecode(SGDecode($res['OwnerArea']));
		$this->owner = $res['ownerID'];
		if($res['anexado']) $this->anexado = true;
		else $this->anexado = false;
		$anexo = explode(",",$res['anexos']);
		$this->docPaiID = $res['docPaiID'];
		if($anexo[0] != '')
			$this->anexo = $anexo; 
		$this->labelID = $res['labelID'];
		$this->tipoID = $res['tipoID'];
		$this->emitente = $res['emitente'];
		$this->numeroComp = $res['numeroComp'];
		$this->obraID = $res['obraID'];
		$this->empreendID = $res['empreendID'];
		$this->arquivado = $res['arquivado'];
		$this->solicitante = $res['solicitante'];
		$this->solicitado = $res['solicitado'];
		$this->solicDesarquivamento = $res['solicDesarquivamento'];
		$this->ultimoHist = $res['ultimoHist'];
	}

	/**
	 * carrega os dados relativo ao tipo de documento (LABEL_DOC)
	 */
	function loadTipoData(){
		if(!$this->bd){
			global $bd;
			$this->bd = $bd;
		}
		
		if($this->labelID != null){
			$res = $this->bd->query("SELECT * FROM label_doc WHERE id = ".$this->labelID);
		}elseif(isset($this->dadosTipo['nomeAbrv'])){
			$res = $this->bd->query("SELECT * FROM label_doc WHERE nomeAbrv = '".$this->dadosTipo['nomeAbrv']."'");
			
		}else{
			$this->loadDados();
			$res = $this->bd->query("SELECT * FROM label_doc WHERE id = ".$this->labelID);
		}
		
		if (!count($res)){
			showError(5);
			exit();
		}
		
		$this->dadosTipo = $res[0];
	}
	
	/**
	 * carrega os dados relativo aos campos do documento (DOC_TIPO)
	 */
	function loadCampos(){
		if(!$this->bd){
			global $bd;
			$this->bd = $bd;
		}
		
		if ($this->dadosTipo == null)
			$this->loadTipoData();
		
		$res = $this->bd->query("SELECT * FROM ".$this->dadosTipo['tabBD']." WHERE id = ".$this->tipoID);
		
		if (!count($res)){
			print("ERRO ao carregar campos do documento {$this->id}");
			//showError(5);
			exit();
		}
				
		foreach ($res[0] as $name => $valor) {
			$tipo = $this->bd->query("SELECT tipo,attr FROM label_campo WHERE nome = '$name'");
			
			if(isset($tipo[0]) && $tipo[0]['tipo'] ==  'composto'){
				$partes = explode("+", $tipo[0]['attr']);
				$valorC = $res[0][$name];
				for ($i = 0; $i < count($partes); $i++) {
					if(substr($partes[$i], 0, 1) == '"'){//se comeca com " entao eh separador
						if($i == 0) continue;
						$quebra = explode(substr($partes[$i], 1, -1), $valorC);
						$res[0][$partes[$i-1]] = $quebra[0];
						if (isset($quebra[1])) $valorC = $quebra[1];
					}
				}
				if (substr($partes[count($partes)-1],0,1) != '"')
					$res[0][$partes[count($partes)-1]] = $valorC;
			}
		}
		$this->campos = $res[0];
	}
	
	/**
	 * Le os dados de cada documento anexo e o retorna em forma de array
	 * @return array com par [id],[nome] do documento anexo ou null
	 */
	function getDocAnexoDet(){
		$data = '';
		if (isset($this->campos['documento'])) {
			$ids = explode(",", $this->campos['documento']);
		
			foreach ($ids as $id){
				if ($id){
					$doc = new Documento($id);
					$doc->loadTipoData();
					// nao mostra docs resposta
					if (stripos($doc->dadosTipo['nomeAbrv'], "resp") !== false) continue;
					$data[] = array("id" => $doc->id, "nome" => $doc->dadosTipo['nome']." ".$doc->numeroComp);
				}
			}
			return $data;
		}else{
			return null;
		}
	}
	
	/**
	 * Le os dados do historico do documento e o retorna em forma de array
	 * @return array na forma [id][data][username][userID][action]
	 */
	function getHist($UNIXTimestamp = false) {
		$res = $this->bd->query("SELECT dh.id,dh.data,dh.despacho, u.username, u.id as userID, dh.acao, dh.tipo, dh.volumes, dh.unidade, dh.label FROM data_historico AS dh
		LEFT JOIN usuarios AS u ON dh.usuarioID = u.id WHERE dh.docID =".$this->id." ORDER BY dh.data DESC, dh.id DESC");
		
		if(!$UNIXTimestamp){
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]['data'] = date("j/n/Y G:i",$res[$i]['data']);
			}
		}
		
		return $res;
	}
	
	/**
	 * Retorna as ultimas respostas do doc
	 * @return array [data][username][destinatario][respID][despacho]
	 */
	function getResp() {
		/*$sql = "SELECT d.data, u.nome AS username, h.unidade AS destinatario, d.id AS respID, h.despacho FROM
		doc AS d INNER JOIN usuarios AS u ON d.criadorID = u.id,
		(SELECT * FROM data_historico WHERE (tipo = 'despIntern' OR tipo = 'saida' OR tipo = 'obs') ORDER BY data DESC) AS h 
		WHERE d.docPaiID =" .$this->id. " AND d.labelID = 8 AND h.docID = " .$this->id. " AND h.data LIKE SUBSTR(d.data, 1, CHAR_LENGTH(d.data))
		ORDER BY d.data DESC";*/
		$sql = "SELECT d.data, u.username AS username, r.unOrgDest AS destinatario, d.id AS respID FROM
		(doc AS d INNER JOIN usuarios AS u ON d.criadorID = u.id) INNER JOIN doc_resp AS r ON d.tipoID = r.id 
		WHERE d.docPaiID =" .$this->id. " AND d.labelID = 8
		ORDER BY d.data DESC";
		$res = $this->bd->query($sql);
		//print $sql;
		
		return $res;
	}
	
	/**
	 * Retorna a resposta ativa deste documento, se houver
	 * @return array [id] da resposta ativa
	 * @return true caso o documento nao possa ter uma resposta
	 */
	function getRespAtiva() {
		$ret = array('idRespostaAtiva' => false, 'podeCriarResp' => false);
		
		//seleciona a ultima resposta criada
		$sql = "SELECT id,data FROM doc WHERE labelID = 8 AND docPaiID = " .$this->id. " ORDER BY data DESC LIMIT 1";
		$ultResp = $this->bd->query($sql);
		
		//verifica se esta resposta esta ativa (ou seja, foi criada antes da ultima saida)
		//consulta data da ultima saida
		$sql = "SELECT hist.data FROM data_historico AS hist INNER JOIN doc AS dc ON hist.docID = dc.id 
		WHERE dc.id = ".$this->id." AND hist.tipo = 'saida'
		ORDER BY hist.data DESC LIMIT 1";
		$ultSaida = $this->bd->query($sql);
		
		// o documento não possui respostas
		if (count($ultResp) <= 0) {
			if ($this->owner == $_SESSION['id'] || ($this->owner == -1 && $this->areaOwner == $_SESSION['area']))
				$ret['podeCriarResp'] = true;
				
			return $ret;
		}
		// se chegou aqui, o doc possui respostas, mas não possui histórico de saída...
		if (count($ultSaida) <= 0) {
			if ($this->owner != 0) {
				$ret['podeCriarResp'] = false;
			}
			$ret['idRespostaAtiva'] = $ultResp[0]['id'];
			
			return $ret;
		}
		
		//se ultimo despacho de saida foi depois da criacao da resp, entao ele nao esta ativo
		if($ultSaida[0]['data'] < $ultResp[0]['data'])
			$ret['idRespostaAtiva'] = $ultResp[0]['id'];
		
		//se nao ha resposta ativa e ele esta na CPO, pode ser criada nova resposta
		if($ret['idRespostaAtiva'] === false && $this->owner != 0)
			$ret['podeCriarResp'] = true;
			
		return $ret;
		
	}
	
	/**
	 * Faz upload dos arquivos enviados via form
	 * @return string Relatorio do upload
	 */
	function doUploadFiles(){
		$success = array();
		$failure = array();
		
		for ($i = 1; isset($_FILES["arq".$i]); $i++) {
			
			if ($_FILES["arq".$i]['name'] == '')
				continue;
			
			if($_FILES["arq".$i]['error'] > 0 && $this->id == 0){
				$failure[] = array("name" => $_FILES["arq".$i]['name'], "errorID" => $_FILES["arq".$i]['error']);
				continue;
			}
			
			$fileName = "[".$this->id."]".$_FILES["arq".$i]['name'];
			$fileName = stringBusca($fileName,true);
		
			if (file_exists("files/" . $fileName)){
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
		    	} while (file_exists("files/".$newName));
		    	
			if(move_uploaded_file($_FILES["arq".$i]["tmp_name"], "files/" . $newName) == false)
		    		$failure[] = array("name" => $_FILES["arq".$i]['name'], "errorID" => "Arquivo muito grande ou nome do arquivo inv&aacute;lido");
		    	else {
			    	$success[] = $newName;
			    	$this->anexo[] = $newName;
			    	$this->doLogHist($_SESSION['id'], "Adicionou o arquivo $newName ao documento",'','','','','');
		    	}    	
		    } else {
		    	
		      if(move_uploaded_file($_FILES["arq".$i]["tmp_name"], "files/" . $fileName) ==  false){
		      	$failure[] = array("name" => $_FILES["arq".$i]['name'], "errorID" => "Arquivo muito grande ou nome do arquivo inv&aacute;lido");
		      } else {
			      $success[] = $fileName;
			      $this->anexo[] = $fileName;
			      $this->doLogHist($_SESSION['id'], "Adicionou o arquivo $fileName ao documento",'','','','','');
		      }
			}
		}
		$files['success'] = $success;
		$files['failure'] = $failure;
		return $files;
	}
	
	function salvaCampos() {
		global $conf;
		if ($this->id == 0) {//adicao de novo registro no BD
			$campos = $this->campos;
			//cria campo de documento vazio
			if($this->dadosTipo['docAnexo']){
				$campos['documento'] = '0';
			}
			// cria campo de documento vazio se puder receber resposta
			if ($this->dadosTipo['docResp']) {
				$campos['documento'] = '0';
			}
			
			//cria campo de obra vazio
			if($this->dadosTipo['obra']){
				$campos['obra'] = '0';
			}
			//cria campo de empresa
			if($this->dadosTipo['empresa']){
				$campos['empresa'] = '0';
			}
			
			$sql = "INSERT INTO ".$this->dadosTipo['tabBD'];
			$colunas = '';
			$valores = '';
			foreach ($campos as $nome => $valor) {
				if($colunas)
					$colunas .= ",";
					
				$colunas .= $nome;
					
				if($valores)
					$valores .= ",";
					
				$valores .= "'".$valor."'";
			}
			
			$sql .= " (".$colunas.") VALUES (".$valores.")";
			
			$tipoID = $this->bd->query($sql, $conf['tabBD'], true);
			
			$this->tipoID = $tipoID;
			
			return true;
			
		} else {//atualizacao de registro no BD
			$sql = "UPDATE ".$this->dadosTipo['tabBD']." SET ";
			
			foreach ($this->campos as $nome => $valor) {
				$sql .= $nome." = '".$valor."' , ";
			}
			$sql = rtrim($str,", ");
			$sql .= " WHERE id = ".$this->id;
			
			return $this->bd->query($sql);
		}
		//echo str_ireplace("'", '', SGEncode ($sql,ENT_QUOTES));
		//return $this->bd->query($sql);
		//return 1;
	}
	
	function salvaDoc($ownerID){
		global $conf;
		
		/*$q = "SELECT id FROM ".$this->dadosTipo['tabBD']." WHERE ";
		
		$campoBusca = explode("," , $this->dadosTipo['campoBusca']);
		foreach ($campoBusca as $cp) {
			$q .= $cp."='".$this->campos[$cp]."' AND " ;
		}
		$q = rtrim($q," AND "); 
		
		$id = $this->bd->query($q);
		$id = $id[0]["id"];*/
		$id = $this->tipoID;
		
		/**/
		$numComp = $this->geraNumComp();
		/**/
		
		$empreendimentoID = 0;
		if (isset($this->empreendID)) $empreendimentoID = $this->empreendID;
		
		$campoEmitente = explode(',', $this->dadosTipo['emitente']);
		foreach ($campoEmitente as $cp) {
			if (isset($this->campos[$cp])) {
				$tipo = $this->bd->query("SELECT tipo,extra FROM label_campo WHERE nome = '".$cp."'");
				if ($tipo[0]['tipo'] == 'userID' || strpos($tipo[0]['extra'],'current_user') !== false){
					$emitente = $_SESSION['nome']." ".$_SESSION['sobrenome'];
					$this->campos[$cp] = $_SESSION['id'];
				} elseif ($tipo[0]['tipo'] == 'userID'){
					$nome = $this->bd->query("SELECT nome,sobrenome FROM usuarios WHERE id=".$this->campos[$cp]);
					$emitente = $nome[0]['nome']." ".$nome[0]['sobrenome'];
				} else {
					$emitente = $this->campos[$cp];
				}
				break;
			}
		}
		
		if ($this->id == 0){//adicao de novo registro no BD
			$sql = "INSERT INTO doc (data,criadorID,ownerID,labelID,tipoID,emitente,numeroComp,anexos,empreendID)
					VALUES  (".time().",".$_SESSION['id'].",".$ownerID.",".
					$this->dadosTipo['id'].",".$id.",'".$emitente."','".$numComp."','',".$empreendimentoID.")";
					
			$idDoc = '';
			if (($idDoc = $this->bd->query($sql, $conf['DBTable'], true)) === false){
				return false;
			} else {
				//$idDoc = $this->bd->query("SELECT id FROM doc WHERE labelID = ".$this->dadosTipo['id']." AND tipoID = ".$id);
				//$this->id = $idDoc[0]['id'];
				$this->id = $idDoc;
				$this->numeroComp = $numComp;
				$this->emitente = $emitente;
			}
		} else {
			//troca de arquivo salvaAnexos(), troca de dono doDespacha()
		}
		return true;
	}
	
	function geraNumComp() {
		$numComp = '';
		$campoComp = explode("+", $this->dadosTipo['numeroComp']);
		foreach ($campoComp as $cp) {
			if(isset($this->campos[$cp])){
				$cpDados = $this->bd->query("SELECT extra FROM label_campo WHERE nome = '".$cp."'");
				if(strpos($cpDados[0]['extra'],"unOrg_autocompletar") !== false){//tratamento para unOrg
					$c = explode("(",$this->campos[$cp]);
					$c = rtrim($c[count($c)-1],")");
					$numComp .= $c;
				}else{
					$numComp .= $this->campos[$cp];
				}
			}else{
				$numComp .= $cp;
			}
		}
		$numComp = rtrim($numComp," ");
		return $numComp;
	}
	
	
	
	/**
	 * Atualiza os nomes dos arquivos anexos no BD
	 */
	function salvaAnexos(){
		if(count($this->anexo) < 1)
			return 0;
		$anexo = implode(",", $this->anexo);
		return $this->bd->query("UPDATE doc SET anexos='".$anexo."' WHERE id = ".$this->id); 
	}
	
	/**
	 * Realiza o despacho de documentos
	 * @param int $userID ID do usuario atual
	 * @param int $dados [funcID] [despExt] [outro] campos para decisao de despacho
	 * @param string $despacho Conteudo do despacho
	 */
	function doDespacha($userID,$dados) { //print_r($dados);exit();
		$vol = ''; //$vol = $dados['volumes'];
		$ownerID = 0;
		$ownerArea = '';
		$para = '';
		$tipo = 'despIntern';
		
		if ($dados['funcID'] && $dados['funcID'] != '_todos'){
			$ownerID = $dados['funcID'];//doc despachado para funcionario
			$para = $this->bd->query("SELECT nomeCompl FROM usuarios WHERE id = ".$ownerID);
			$para = $para[0]['nomeCompl'];
		} elseif (($dados['funcID'] == '_todos' && $dados['para'] != 'solic') && (SGEncode($dados['para'], ENT_QUOTES, null, false) != "--Selecione--" && SGEncode($dados['para'], ENT_QUOTES, null, false) != "ext"))	{
			$ownerID = -1;
			$para = SGEncode($dados['para'], ENT_QUOTES, null, false);
			$ownerArea = $para;
		} elseif ($dados['para'] == 'ext' || $dados['despExt']) {
			$para = $dados['despExt'];//despacho para outra unOrg
			$tipo = 'saida';
		} elseif ($dados['outro']) {
			$para = SGEncode($dados['outro'], ENT_QUOTES, null, false);//despacho para outros
			$tipo = 'saida';
		} elseif ($dados['para'] ==  'solic') {
			$para = " o solicitante"; // despacho para solicitante
			$tipo = 'saida';
		} elseif ($dados['para'] == 'cpo_arq') {
			$para = " o Arquivo";
		} else {
			$ownerID = $_SESSION['id'];//doc pendente para usuario atual caso nao tenha despachado para lugar nenhum
		}
		
		$r = $this->bd->query("UPDATE doc SET ownerID = $ownerID, ownerArea ='$ownerArea' WHERE id = ".$this->id);
		
		if($r && $ownerID != $_SESSION['id']){
			if(!$this->doLogHist($userID, '', $dados['despacho'], $para, $tipo, $vol, 'Despacho'))
				return false;
			return $para;
		} elseif ($r && $ownerID == $_SESSION['id']) {
			if (!isset($dados['despacho']) || $dados['despacho'] == null)
				return "si mesmo";
			if(!$this->doLogHist($userID, '',$dados['despacho'],'','obs','','Observa&ccedil;&atilde;o'))
				return false;
			return "si mesmo";
		} elseif(!$r) {
			return false;//erro ao atualizar BD
		} else {
			return $ownerID;
		}
	}
	
	/**
	 * Grava historico do documento
	 * @param int $id id do usuario logado
	 * @param string $acao
	 */
	function doLogHist($userID, $acao, $despacho, $unidade, $tipo, $volumes, $label,$doc_targetID = 0){
		$hist = HistFactory::novoHist('doc', $this->bd);
		$hist->set('docID', $this->id);
		$hist->set('acao', $acao);
		$hist->set('userID', $userID);
		$hist->set('despacho', $despacho);
		$hist->set('unidade', $unidade);
		$hist->set('tipo', $tipo);
		$hist->set('volumes', $volumes);
		$hist->set('label', $label);
		$hist->set('doc_targetID', $doc_targetID);
		
		$ret = $hist->save();
		
		if ($ret) {			
			$this->ultimoHist = $ret;
			$this->update('ultimoHist', $ret);
		}
		
		return $ret;
	}
	
	/**
	 * Grava anexado = 1 nos campos dos docs anexados a este documento e o id do pai
	 */
	function doFlagAnexado(){
		if(isset($this->campos['documento']) && $this->campos['documento'] != ''){
			$docsAnexados = explode(",", $this->campos['documento']);
			foreach ($docsAnexados as $doc) {
				if ($doc){
					$r = $this->bd->query("UPDATE doc SET anexado = 1, docPaiID = ".$this->id.", ownerID = 0 WHERE id = $doc");
					if (!$r)
						return false;
					$docA = new Documento($doc);
					$docA->bd = $this->bd;
					$docA->loadDados();
					$docA->doLogHist($_SESSION['id'], "Anexou este documento ao documento ".$this->id." (".$this->dadosTipo['nome']." ".$this->numeroComp.")",'','','','','');
					doLog($_SESSION['username'],"Anexou documento ".$docA->id." (".$docA->dadosTipo['nome']." ".$docA->numeroComp.") ao documento ".$this->id." (".$this->dadosTipo['nome']." ".$this->numeroComp.")",$this->bd);
				}
			}
			return true;			
		}
		return true;
	}
	
	/**
	 * Atualiza o valor de um determinado campo.
	 * @param string $campo
	 * @param string $newVal
	 */
	function updateCampo($campo, $newVal) {
		$sql = "UPDATE ".$this->dadosTipo['tabBD']." SET $campo = '$newVal' WHERE id=".$this->tipoID;
		$ret = $this->bd->query($sql);
		
		$this->campos[$campo] = $newVal;
		
		return $ret;
	}
	
	/**
	 * Anexa um documento a este documento
	 * @param int $id
	 */
	function anexaDoc($id){		
		$sql1 = "UPDATE doc SET anexado = 1, docPaiID = ".$this->id.", ownerID = 0 WHERE id = $id";
		if($this->bd->query($sql1)){
			if($this->campos['documento'] == '0') {
				$this->updateCampo('documento', $id);
			} else {
				$this->updateCampo('documento', $this->campos['documento'].",$id");
			}
			return true;
		} else {
			return false;
		}
	}
	
	function update($nomeVar,$newVal){
		$sql = "UPDATE doc SET ".$nomeVar."='".$newVal."' WHERE id=".$this->id;
		$this->$nomeVar = $newVal;
		return $this->bd->query($sql);
	}
	
	/**
	 * Retorna as informacoes do empreendimento ao qual este documento está asscociado
	 * @return array contendo id e nome do empreendimento
	 */
	function getEmpreend($distinct = false) {
		$retorno = array();
		
		if ($this->dadosTipo['nomeAbrv'] == 'pr' && $this->campos['guardachuva'] == 1) { // verifica se este doc é processo guardachuva
			$sql = "SELECT * FROM guardachuva_empreend WHERE docID = " .$this->id;
			if($distinct) $sql. ' GROUP BY empreendID';
			$res = $this->bd->query($sql);
			if (count($res) <= 0) return null;
			
			foreach($res as $r) {
				$sql = "SELECT id, nome FROM obra_empreendimento WHERE id ='" .$r['empreendID']. "'";
				$empreend = $this->bd->query($sql);
				$retorno[] = $empreend[0];
			}
			return $retorno;
		}
		
		// não é processo guardachuva...					
		if (isset($this->docPaiID) && $this->docPaiID > 0) {
			$docPai = new Documento($this->docPaiID);
			$docPai->loadCampos();
					
			$retPai = $docPai->getEmpreend();
			
			/*$sql = "SELECT e.id, e.nome FROM obra_empreendimento AS e INNER JOIN";
			$sql .= " (SELECT id, empreendID FROM doc WHERE id =".$this->docPaiID. ") AS res ON res.empreendID = e.id";
			$res = $this->bd->query($sql);
				
			if (count($res) > 0) {
				$retorno[] = $res[0]; 
				return $retorno;
			}*/
			if (count($retPai) > 0) {
				return $retPai;
			}
		}
		else {
			$sql = "SELECT id, nome FROM obra_empreendimento WHERE id ='" .$this->empreendID. "'";
			$res = $this->bd->query($sql);
			if (count($res) > 0) {
				$retorno[] = $res[0];
				return $retorno;
			}
		}
			
		return null;	
	}
		
	
	
	/**
	 * Verifica permissão de edição deste documento
	 * @param string $campo id do campo
	 * @return bool true -> é editavel, false cc.
	 */
	function verificaEditavel($campo){
		// se o doc foi despachado pra fora, ele não poderá mais ser editado. Nunca.
		if ($this->verificaDespachado()) return false;
		//else print(" nao foi despachado<br>");
		// se o usuário não for owner do doc, ele não pode editar.
		if (!$this->verificaOwnerPai()) return false;
		//else print("sou owner do doc<br>");
		// chegou aqui, o doc não foi despachado pra fora e o usuário é owner dele.
		// então, verifica se ele tem a permissão 2 para editar qq coisa
		if (checkPermission(2)) return true;
		//else print("nao tem perm 2<br>");
		// se ele não tem, verifica a permissão 3. se não tem, não pode editar
		if (!checkPermission(3)) return false;
		//else print("tem perm 2<br>");
		// ele tem permissão 3, então verifica se ele tem permissão para editar o campo em sí
		$sql = "SELECT editarAcao FROM label_campo WHERE nome = '" .$campo. "'";
		$res = $this->bd->query($sql);
		
		// verificação de segurança: campo inválido
		if (count($res) <= 0) return false;
		
		$res = $res[0];
		
		// se o usuário só tem a permissão 3 e o campo não tem uma permissão setada, ele não pode editar
		if ($res['editarAcao'] == 0) return false;
		//else print("campo nao editavel<br>");
		// se o campo tem permissão setada e o usuário tem esssa permissão, ele pode editar
		if (checkPermission($res['editarAcao'])) return true;
		//else print("nao tem perm para editar esse campo especifico<br>");
		// senão, não pode
		return false;
		
	}
	
	/**
	 * Verifica se o usuario eh dono do documento pai.
	 * Resolve a edicao de anexos de anexos.
	 * @param Documento $doc
	 */
	function verificaOwnerPai() {
		$docsVerificados = array();
		$docAtual = $this;
		
		while ($docAtual->docPaiID > 0){
			if(array_search($docAtual->id, $docsVerificados) !== false)
				return false;
				
			array_push($docsVerificados, $docAtual->id);
			
			$docPai = new Documento($docAtual->docPaiID);
			$docPai->loadDados();
			$docAtual = $docPai;
		}
		//print_r($doc);
		if ($docAtual->owner == $_SESSION['id'] || ($docAtual->owner == -1 && $docAtual->areaOwner == $_SESSION['area']))
			return true;
		return false;
	}
	
	/**
	 * Verifica se um documento foi despachado depois de criado
	 * ou se o pai foi despachado depois que o 1o foi anexado
	 * @param Documento $doc
	 * @return bool true se já foi despachado pra fora, falso cc
	 */
	function verificaDespachado() {
		$doc = $this;
		$data_despacho_pai = 0;
		$docsVerifs = array();
		
		//deve verificar se ja foi despachado
		foreach ($doc->getHist() as $h) {
			if($h['tipo'] == 'saida')
				return true;
		}
		
		//tem pai		
		while($doc->docPaiID !=0 && array_search($doc->id, $docsVerifs) === false) {
			//deve verificar se o pai esta anexado
			// a partir do momento que um doc filho foi anexado, se o doc pai foi despachado, nao podem mais ser editados
			foreach ($doc->getHist(true) as $h) {
				if($h['tipo'] == 'anexoEste') {
					$data_anexacao_filho = $h['data'];
					break;
				}
			}
			//carrega o documento pai
			$docPai = new Documento($doc->docPaiID);
			$docPai->loadDados();
			$historico_pai = $docPai->getHist(true);
			//para cada registro de historico do pai
			foreach ($historico_pai as $h) {
				if($h['tipo'] == 'saida') {
					$data_despacho_pai = $h['data'];
					break;
				}
			}
			//se o filho foi anexado antes do pai ser despachado, ja foi despachado
			if($data_despacho_pai > $data_anexacao_filho)
				return true;
			//verifica iterativamente o pai do pai
			$doc = $docPai;
		}
		return false;
	}
	
	/**
	 * Retorna HTML do histórico
	 * @return html
	 */
	public function showHist() {
		// carrega template da tabela de histórico
		$html = historico::getTemplate();
		
		// instancia um objeto de histórico, através da classe HistFactory
		$hist = HistFactory::novoHist('doc', $this->bd);
		
		// pega todos os históricos associados a este documento
		$listaHistorico = HistFactory::getHistID('doc', $this->id, $this->bd);
		
		// inicializa variável que guardará as linhas da tabela de histórico
		$linhas = '';
		
		// se encontrou algum histórico,
		if (count($listaHistorico) > 0) {
			// percorre os históricos
			foreach ($listaHistorico as $histID) {
				// carrega o histórico atual
				$hist->load($histID['id']);
				// concatena html do histórico com as linhas
				$linhas .= $hist->printHTML();
			}
		}
		
		// terminou de montar as linhas do histórico
		// coloca as linhas dentro do template
		$html = str_replace('{$linhas_historico}', $linhas, $html);
		
		// retorna html
		return $html;
	}
	
	static public function geraLinkDoc($acao, $docID){
		return "window.open('sgd.php?acao={$acao}&docID={$docID}','doc','width='+screen.width*0.95+',height='+screen.height*0.9+',scrollbars=yes,resizable=yes').focus()";
	}
	
	/**
	 * Salva as obras relacionadas a este documento
	 * @param array $obras [id obra]
	 * @return boolean
	 */
	public function salvaObras($obras) {
		global $bd;
		
		// se não há obras para serem inseridas, não faz nada
		if (count($obras) <= 0)
			return;
			
		// começa a montar consulta para saber as obras inseridas
		$selectNomes = "SELECT nome FROM obra_obra WHERE ";
		
		// começa a montar sql de insert
		$sql = "INSERT INTO obra_doc (`docID`, `obraID`) VALUES ";
		// percorre as obras e monta sql correspondente
		foreach ($obras as $o) {
			// query de insert
			$sql .= '('.$this->id.', '.$o.'), ';
			
			// query de consulta de nome
			$selectNomes .= 'id = '.$o.' OR ';
		}
		// remove espaço e vírgula do fim das consultas
		$sql = trim($sql, ", ");
		$selectNomes = trim($selectNomes, " OR ");
		
		// realiza a inserção
		$ret = $bd->query($sql);
		
		// se inseriu com sucesso,
		if ($ret) {
			// consulta nomes de obras e gera string de nomes de obras inseridas
			$nomes = $bd->query($selectNomes);
			$nomes = implodeRecursivo(', ', $nomes);
			
			// salva log
			$this->doLogHist($_SESSION['id'], '', '', '', 'salvaObras', '', '');
			doLog($_SESSION['username'], 'Salvou/Adicionou v&iacute;nculo de obras do doc '.$this->id.': '.$nomes);			
		} 
		
		// retorna o resultado da query
		return $ret;
	}
	
	/**
	 * Salva a edição de obras
	 * @param array $obras [id obras]
	 * @return array [success][errorNo][errorFeedback]
	 */
	public function salvaEditObras($obras) {
		// se não há obras alteradas,
		if (count($obras) <= 0)
			return array("success" => false, "errorNo" => 1, "errorFeedback" => "Dados Insuficientes");
		
		// deleta o vínculo com as obras e salva as novas
		if ($this->delObras()) {
			if ($this->salvaObras($obras)) {
				return array("success" => true, "errorNo" => 0, "errorFeedback" => "");
			}
		}
		
		// retorna
		return array("success" => false, "errorNo" => 2, "errorFeedback" => "");
	}
	
	/**
	 * Remove atribuição de uma obra em um doc
	 */
	public function removeObra($obraID){
		$this->bd->query("DELETE FROM obra_doc WHERE docID=".$this->id." AND obraID=".$obraID);
		return array(array("success" => true));
	}
	
	/**
	 * Apaga vínculos deste contrato com todas as obras
	 * @return boolean success
	 */
	private function delObras() {
		global $bd;
		$sql = "DELETE FROM obra_doc WHERE docID = ".$this->id;
		
		return $bd->query($sql);
	}
	
	/**
	 * Verifica se esse documento é atribuível a obras
	 * @return boolean success
	 */
	public function isAttrObra() {
		if ($this->dadosTipo['atribObra'] == 1)
			return true;
			
		return false;
	}
	
	/**
	 * Retorna as obras associadas a este documento
	 * @return array [id][nome]
	 */
	public function getObras($distinct = false) {
		$bd = $this->bd;
		
		$sql = "SELECT o.id, o.nome 
				FROM obra_doc AS c INNER JOIN obra_obra AS o ON c.obraID = o.id 
				WHERE docID = ".$this->id;
		if($distinct) $sql .= " GROUP BY o.empreendID";
		return $bd->query($sql);
	}
	
	/**
	 * Retorna apenas os IDs das obras associadas a este documento
	 * @return array
	 */
	public function getObrasId() {
		global $bd;
		
		$obras = $this->getObras();
		
		$ret = array();
		if (count($obras) > 0) {
			foreach($obras as $o) {
				$ret[] = $o['id'];
			}
		}
		
		return $ret;
	}
}

?>
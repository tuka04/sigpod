<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 23/07/2013
 * @version 1.1.1.5
 * @desc Classe que irá substituir as funcoes do arquivo sgd_busca.php (tbm para sge, sgo...)
 */
require_once "BD/DAO/DAO.class.php";
class Busca extends DAO{
	/**
	 * tipo busca
	 * @var string
	 */
	private $tipo;
	/**
	 * Dados de campos especificos
	 * @var ArrayObj
	 */
	private $campos;
	
	public function __construct($tipo){
		parent::__construct("", array());
		$this->loadRequire();
		if(!isset($_REQUEST["tipoDoc"]))
			$_REQUEST["tipoDoc"]="";
		$this->tipo = $tipo;
	}
	
	public function run(){
		if($this->tipo=="busca")
			return $this->tipoBusca();
	}
	
	private function loadRequire(){
		require_once 'classes/system/label/LabelCampo.class.php';
		require_once 'classes/system/label/LabelDoc.class.php';
		require_once 'classes/documento/Documentos.class.php';
		require_once 'classes/documento/processo/DocumentoProcesso.class.php';
		require_once 'classes/obras/Obras.class.php';
		require_once 'classes/obras/ObraEmpreendimento.class.php';
		require_once 'classes/historico/Historicos.class.php';
	}
	
	private function tipoBusca(){
		//tratamento de acentos, etc para a var $_REQUEST
		HtmlString::encodeRequest();
		//carrega campos especificos
		$this->loadCampos();
		//quais tipos de documento procurar?
		$tpdocs = $this->getDocTipo();
		//buscara no historico sse haver consulta em despacho
		$historico=($this->getConsultaDespacho()->count()>0)?true:false;
		$res = $this->query($this->getQuery($tpdocs, $historico,false,new LimitQuery("0","100")));
		$total = $this->query($this->getQuery($tpdocs, $historico,true,null));
		$this->parseResult($res,$total,$tpdocs);
	}
	/**
	 * Traz do LabelDoc o tipo de documento desejado
	 * @return Ambigous <mixed, boolean, number, multitype:multitype: >
	 */
	private function getDocTipo(){
		$tp = new LabelDoc();
		if(!empty($_REQUEST["tipoDoc"])){
			$aux = new ArrayObj(explode(',',$_REQUEST['tipoDoc']));
			$aux->filterEmpty();//remove brancos
			$r = $tp->select("nomeAbrv",$aux->getArrayCopy());
		}
		else 
			$r = $tp->select();
		return new ArrayObj($r);
	}
	/**
	 * Carrega a busca por campo especifico
	 */
	private function loadCampos(){
		$this->campos = new ArrayObj();
		$campos = explode("|", $_REQUEST['valoresBusca']);
		$nomes = new ArrayObj();
		$valores = new ArrayObj();
		foreach ($campos as $c) {
			if($c != '') {
				$dados = explode("=", $c);
				$nomes->append($dados[0]);
				$valores->append($dados[1]);
			}
		}
		$this->campos->offsetSet("nome", $nomes);
		$this->campos->offsetSet("valor", $valores);
		
		if (!isset($_REQUEST['dataCriacao1'])) 
			$_REQUEST['dataCriacao1'] = null;
		if (!isset($_REQUEST['dataCriacao2']))
			$_REQUEST['dataCriacao2'] = null;
		if (!isset($_REQUEST['arquivado'])) 
			$_REQUEST['arquivado'] = "";
	}
	/**
	 * Monta consulta para despacho no historico
	 * @return ArrayObj
	 */
	private function getConsultaDespacho(){
		$resDesp = new ArrayObj();
		$despachos = array('dataDespacho1', 'unDespacho', 'dataReceb1', 'unReceb', 'contDesp');
		foreach ($despachos as $idx) {
			if(isset($_REQUEST[$idx]) && $_REQUEST[$idx]){
				if($idx == 'dataReceb1' || $idx == 'dataReceb2' ) {
					$resDesp->offsetSet("dataReceb", CommonMethods::montaData($_REQUEST["dataReceb1"], $_REQUEST["dataReceb2"]));
				} 
				elseif($idx == 'dataDespacho1' || $idx == 'dataDespacho2') {
					$resDesp->offsetSet("dataDespacho", CommonMethods::montaData($_REQUEST["dataDespacho1"], $_REQUEST["dataDespacho2"]));
				} 
				else{
					$resDesp->offsetSet($idx, $_REQUEST[$idx]);
				}
			}
		}
		return $resDesp;
	}
	/**
	 * Retorna a string de restricao de despacho
	 * @return string
	 */
	private function getRestricaoConsultaDespacho(){
		if($this->getConsultaDespacho()->count()==0)
			return "";
		$desp = $this->getConsultaDespacho()->getArrayCopy();
		$sql_desp = '';
		$sql_receb = '';
		$sql_cont = '';
		$sql = "";
		if((isset($desp['dataDespacho']) && count($desp['dataDespacho']) == 2) || (isset($desp['unDespacho']) && $desp['unDespacho'])) {
			$sql_desp = "((h.tipo = 'saida' OR h.tipo = 'despIntern') AND ";
			if(isset($desp['dataDespacho']) && count($desp['dataDespacho']) == 2)
				$sql_desp .= "h.data < ".$desp['dataDespacho'][1]." AND h.data > ".$desp['dataDespacho'][0]." AND ";
			if(isset($desp['unDespacho']) && $desp['unDespacho'])
				$sql_desp .= "h.unidade LIKE '%".str_replace(' ', '%', $desp['unDespacho'])."%'";
			$sql_desp = rtrim($sql_desp, " AND "). ")";
		}
		if((isset($desp['dataReceb']) && count($desp['dataReceb']) == 2) || (isset($desp['unReceb']) && $desp['unReceb'])) {
			$sql_receb = "(h.tipo = 'entrada' AND ";
			if(isset($desp['dataReceb']) && count($desp['dataReceb']) == 2)
				$sql_receb .= "h.data < ".$desp['dataReceb'][1]." AND h.data > ".$desp['dataReceb'][0]." AND ";
			if(isset($desp['unReceb']) && $desp['unReceb'])
				$sql_receb .= "h.unidade LIKE '%".str_replace(' ', '%', $desp['unReceb'])."%'";
			$sql_receb = rtrim($sql_receb, " AND "). ")";
		}
		if(isset($desp['contDesp']) && $desp['contDesp']) {
			$sql_cont = "( h.despacho LIKE '%".str_replace(' ', '%', $desp['contDesp'])."%') ";
		}
		if($sql_cont || $sql_desp || $sql_receb){
			$sql .= " AND (";
			if($sql_cont)
				$sql .= $sql_cont." OR ";
			if($sql_desp)
				$sql .= $sql_desp." OR ";
			if($sql_receb)
				$sql .= $sql_receb;
			$sql = rtrim($sql," OR ").")";
		}
		return $sql;
	}
	/**
	 * Seta a restricao de busca generica
	 */
	private function getBuscaGenerica($contGen,$restrTipo,$tipos){
		if($contGen) {
			$rPalavra = '';
			$lc = new LabelCampo();
			foreach (explode(' ', $contGen) as $palavra) {
				$rTipo = '';
				foreach ($tipos as $i=>$rt) {
					$rCampos = '';
					foreach (explode(',',$rt['campos']) as $campo) {
						$campo = $lc->montaCampos($campo,'bus');
						if($campo['tipo'] == 'input' || $campo['tipo'] == 'textarea') {
							//$rCampos .= " ".$rt['nomeAbrv'].".".$campo['nome']." LIKE '%$palavra%' OR ";
							//$rCampos .= " ".$rt['nomeAbrv'].".".$campo['nome']." REGEXP \"".stringBusca($palavra)."\" OR ";
							$palavra = HtmlString::decode($palavra);
// 							$rCampos .= " html_decode(".$rt['nomeAbrv'].".".$campo['nome'].") LIKE '%" . stringBusca($palavra) . "%' OR ";
							$rCampos .= " html_decode(t".$i.".".$campo['nome'].") LIKE '%" . stringBusca($palavra) . "%' OR ";
						}
					}
					$rCampos = rtrim($rCampos,' OR');
					//$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tab'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR d.numeroComp LIKE '%$palavra%') AND d.labelID = ".$rt['id'].") OR ";
					//$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tab'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR d.numeroComp REGEXP \"".stringBusca($palavra)."\") AND d.labelID = ".$rt['id'].") OR ";
					HtmlString::decode($palavra);
// 					$rTipo .= 'd.id IN (SELECT d.id FROM doc AS d INNER JOIN '.$rt['tabBD'].' AS '.$rt['nomeAbrv'].' ON d.tipoID='.$rt['nomeAbrv'].".id WHERE ($rCampos OR html_decode(d.numeroComp) LIKE '%" . stringBusca($palavra) . "%') AND d.labelID = ".$rt['id'].") OR ";
					$rTipo .= " (".$rCampos.") OR";
				}
				$rTipo = rtrim($rTipo,' OR');
				$rPalavra .= '(' . $rTipo . ') AND ';
			}
			$rPalavra = rtrim($rPalavra, ' AND ');
// 			$sql = "d.id IN (SELECT d.id FROM doc AS d WHERE $rPalavra )";
			$sql = "($rPalavra )";
			return $sql;
		} else {
			return null;
		}
	}
	/**
	 * @param ArrayObj $tpdocs
	 * @return ArrayObj
	 */
	private function getRestricoes(ArrayObj $tpdocs){
		$restricao = new ArrayObj();
		if(isset($_REQUEST["numCPO"])&&$_REQUEST["numCPO"])
			$restricao->append("d.id = ".$_REQUEST["numCPO"]);
		if(isset($_REQUEST["numDoc"])&&$_REQUEST["numDoc"])	
			$restricao->append("d.numeroComp LIKE '%".$_REQUEST["numDoc"]."%'");
		$criacao = CommonMethods::montaData($_REQUEST["dataCriacao1"],$_REQUEST["dataCriacao2"]);
		if(isset($criacao[0]))
			$restricao->append("d.data > ".$criacao[0]);
		if(isset($criacao[1]))
			$restricao->append("d.data < ".$criacao[1]);
		//permissao para buscar em arquivos?
		if(!checkPermission(68))
			$_REQUEST["arquivado"]=0;
		if(intval($_REQUEST["arquivado"]) == 1)
			$restricao->append("d.arquivado = 1");
		elseif($_REQUEST["arquivado"] == 0)
			$restricao->append("d.arquivado = 0");
		$str = '(';
		foreach ($tpdocs as $t) {
			if (count($tpdocs) == 1 && $t == 5) {
				$str .= "d.labelID = 1 OR d.labelID = 2 OR d.labelID = 3 OR d.labelID = 4 OR d.labelID = 5 OR d.labelID = 6 OR d.labelID = 7";
			}
			else {
				$str .= "d.labelID = ".$t['id']." OR ";
			}
		}
		$str = rtrim($str, " OR").')';
		$restricao->offsetSet("tipo", $str);
		if(isset($_REQUEST['anex']) && $_REQUEST['anex'])
			$restricao->append("(d.ownerID = '" .$_SESSION['id']. "' OR (d.ownerID = '-1' AND html_decode(d.OwnerArea) = '".$_SESSION['area']."'))");
		//despacho
		$restricao->offsetSet("despacho", $this->getRestricaoConsultaDespacho());
		$restricao->offsetSet("generica", $this->getBuscaGenerica($_REQUEST["contGen"], $str, $tpdocs));
		$restricao->offsetSet("restrTipo", $this->getRestricaoPorTipo());
		return $restricao;
	}
	
	private function getRestricaoPorTipo(){
		$nome = $this->campos->offsetGet("nome");
		$valor = $this->campos->offsetGet("valor");
		$restrTipos = new ArrayObj();
		foreach ($nome as $k=>$n)
			$restrTipos->offsetSet($n, $valor[$k]);
		$lc = new LabelCampo();
		$sql = "";
		$idsUsers = new ArrayObj();
		foreach ($restrTipos->getArrayCopy() as $k=>$n){
			if(stripos($k, "_operador") !== false) 
				continue;
			if($k=="obraSAP")
				continue;
			$v = $lc->montaCampos($k,'bus',$nome);
			if($v==null)
				continue;
			if($v->offsetGet("tipo")=="composto"){
				$comp = $v->offsetGet("valor");
				foreach (explode(",",$v->offsetGet("nome")) as $n2){
					if(isset($nome[$n2])){
						if($nome[$n2] == ""){
							$comp="";
							break;
						}
					}
					else {
						$comp="";
						break;
					}
				}
				//campo obraSAP nao existe
				if(!empty($comp)&&$comp!="" && $k!="obraSAP")
					$sql .= " t.".$k." LIKE '%".$comp."%' AND ";
			} 
			elseif($v->offsetGet("parte")) { //tratamento de campos partes
				continue;
			}
			elseif($v->offsetGet("tipo") == 'userID') {//tratamento especial para campos de nome de usuario - deve procurar pelo nome e nao pelo ID
				$idsUsers->append($n);
				$campoUserID = $k;
			}
			elseif($v->offsetGet("tipo") == 'select') {
				if ($n != 'nenhum') 
					$sql .= " AND t.".$k." = '".$n."' AND ";
			} 
			elseif($v->offsetGet("tipo") == 'empresa') {
				if ($n != null) {
					$e = new Empresas();
					$e->setVar("campos", array("id"));
					$empresas = $e->select("html_decode(nome)", '%'.$n.'%', ' LIKE ');
					foreach($empresas as $e) 
						$sql .= " t.".$k." = " .$e['id']. " OR ";
					$sql = rtrim($sql, "OR ").' AND ';
				}
			} elseif($v->offsetGet("tipo") == 'input' && strpos($v->offsetGet("extra"),"unOrg_autocompletar") !== false && (strpos($n, "CPO") !== false || strpos($n, "cpo") !== false) || strpos($n, "01.07.63.00") !== false) {
				$sql .= " (t.".$k." LIKE '%01.14.16.00%' OR t.".$k." LIKE '%".$n."%') AND ";
			} elseif(stripos($v->offsetGet("extra"), "moeda") !== false && $v->offsetExists("operador") && $v->offsetGet("valor") != "") {
				$sql .= " t.".$k." ".$v->offsetGet("operador")." ".$v->offsetGet("valor").' AND ';
			} elseif($v->offsetGet("valor")!="" && $k!="obraSAP") { //montagem da condicao
				$sql .= " t.".$k." LIKE '%".$n."%' AND ";
			}
		}
		if($idsUsers->count()>0){
			//procura os usuarios que tem a string buscada no nome
			$u = new Usuario();
			$w=" WHERE ";
			foreach ($idsUsers->getArrayCopy() as $n){
				if($n==0 && $idsUsers->count()==1)
					$w = "";
				else 
					$w .= " nome LIKE '%".$n."%' OR username LIKE '%".$n."%' OR";
			}
			if($w!=""){
				$qr = "SELECT DISTINCT id FROM ".Usuario::Tabela.rtrim($w,"OR");
				$res = $u->query($qr);
				if(count($res)==0)
					die("Erro ao carregar usuarios. ".__FILE__." ".__FUNCTION__." ".__LINE__);
				//para cada usuario encontrado, faz uma restricao pelo Userid dele
				$sql .= " (";
				foreach ($res as $user)
					$sql .= ' t.'.$campoUserID." = ".$user['id'].' OR';
				//trata e adiciona a restricao
				$sql = rtrim($sql,' OR').') AND ';
			}
		}
		return rtrim($sql,"AND ");
	}
	
	private function getQuery(ArrayObj $tpdocs,$historico,$total=false,LimitQuery $l=null){
		$join="";
		if($historico)
			$join = " RIGHT JOIN data_historico AS h ON doc.id = h.docID ";
		$campos = "";
		foreach ($tpdocs->getArrayCopy() as $i=>$t){
			foreach (explode(",",$t["campos"]) as $c){
				if($c=="obraSAP")//removendo campo obraSAP
					continue;
				if($tpdocs->count()==1)
					$campos .= "t.".$c." as '".$t["tabBD"].".".$c."', ";
				else 
					$campos .= "t".$i.".".$c." as '".$t["tabBD"].".".$c."', ";
			}
			if($tpdocs->count()==1)
				$join .= " LEFT JOIN ".$t["tabBD"]." as t ON t.id = d.tipoID ";
			else 
				$join .= " LEFT JOIN ".$t["tabBD"]." as t".$i." ON t".$i.".id = d.tipoID ";
		}
		//join com empreend
		$join .= " LEFT JOIN obra_empreendimento as oe ON oe.id = d.empreendID ";
		$campos .= " oe.id as eid, oe.nome as enome ";
		if($total)
			$qr = "SELECT COUNT(d.id) as total FROM doc as d";
		else if($campos!="")
			$qr = "SELECT d.*, ".$campos." FROM doc as d";
		$restr = $this->getRestricoes($tpdocs);
		$qr .= $join;
		$w="";
		foreach ($restr->getArrayCopy() as $r){
			if(!empty($r))
				$w .= "(".$r.") AND ";
		}
		if(!empty($w))
			$qr = rtrim($qr." WHERE ".$w,"AND ");
		$qr .= " ORDER BY d.id DESC ";
		if($l!=null)
			$qr .= $l->toString();
		return $qr;
	}
	/**
	 * @param string $docs
	 * @param hash $tipoDoc
	 */
	private function getNomeDocsAnexos($docs,$tipoDoc){
		if (isset($docs)){
			$ids = explode(",", $docs);
			$d = new Documentos();
			if(count($ids)==0)
				return null;
			$res = CommonMethods::arrayToHash($d->select("id",$ids," IN "),"id");
			$data = new ArrayObj();
			foreach ($res as $id=>$r)
				$data->append(array("id"=>$id,"nome"=>$tipoDoc[$r["labelID"]]["nome"]." ".$r["numeroComp"]));
			return $data->getArrayCopy();
		}
		return null;
	}

	public function parseResult($res,$total,ArrayObj $tpdocs){
		$hash_data = $data = new ArrayObj();//resposta
		$total = $total[count($total)-1]['total'];
// 		$ld = new LabelDoc();
		$tipoDoc = CommonMethods::arrayToHash($tpdocs->getArrayCopy(), "id");
		//extrair ids
		$res_ids=new ArrayObj();
		foreach ($res as $r)
			$res_ids->append($r["id"]);
		//selecionar as obras pelos ids
		$o = new Obras();
		$join = new ArrayObj();
		$j = new JoinSQL("obra_doc", "obraID", Obras::Tabela.".id");
		$join->append($j);
		$join->append(new JoinSQL("obra_empreendimento", "id", Obras::Tabela.".empreendID"));
		$w = new WhereClause($j->getTable().".docID", $res_ids->getArrayCopy());
		$o->setVar("campos", array($j->getTable().".docID", Obras::Tabela.".id", Obras::Tabela.".nome","obra_empreendimento.id as eid","obra_empreendimento.nome as enome"));
		$obras = $o->selectJoin($join,$w);
		$obras = new ArrayObj(CommonMethods::groupByCampo($obras, "docID"));
		//load os historicos
		$h = new Historicos();
		$h->setVar("campos", array(Historicos::Tabela.".id",
						  		   Historicos::Tabela.".data",
								   Historicos::Tabela.".docID",
				   				   Historicos::Tabela.".despacho",
				                   Usuario::Tabela.".username", 
								   Usuario::Tabela.".id as 'userID'", 
						           Historicos::Tabela.".acao", 
								   Historicos::Tabela.".tipo", 
								   Historicos::Tabela.".volumes",
								   Historicos::Tabela.".unidade",
								   Historicos::Tabela.".label"));
		$join = new ArrayObj();
		$join->append(new JoinSQL("usuarios", "id", Historicos::Tabela.".usuarioID"," LEFT "));
		$w = new WhereClause(Historicos::Tabela.".docID", $res_ids->getArrayCopy());
		$hres = $h->selectJoin($join,$w,new OrderedBy("data","DESC"));
		foreach ($hres as &$histo)
			$histo['data'] = date("j/n/Y G:i",$histo['data']);
		$hres = Historicos::groupHistoByCampo($hres, "docID");
		$hash_histo = new ArrayObj($hres);//hash do historico
		$documento = new Documentos();
		$u = new Usuario();
		$users = $u->select();//seleciona todos os users
		$hash_uid = new ArrayObj(CommonMethods::arrayToHash($users,"id"));//hash por id dos users
		$hash_username = new ArrayObj(CommonMethods::arrayToHash($users,"username"));
		//conversao para JSON
		$empresas = $contratos = new ArrayObj();
		foreach ($res as $r) {
			$d = new ArrayObj();
			//total de docs
			$d->offsetSet("total", $total);
			//ignora o ID (autoincrement na tabela de tipo) para ID (tabela doc) da CPO
			$d->offsetSet("id", $r["id"]);
			//tipo de doc
			$dadosTipo = $tipoDoc[$r["labelID"]]; 
			$d->offsetSet("tipo", $dadosTipo);
			//adiciona o nome do documento
			$d->offsetSet("nome", $dadosTipo["nome"]." ".$r["numeroComp"]);
			//adiciona o ID do documento pai e flag de anexado
			$d->offsetSet("anexavel",$r["anexos"]!=""?true:false);
			$d->offsetSet("anexado",$r["anexos"]!=""?true:false);
			$d->offsetSet("docPaiID", $r["docPaiID"]);
		
			// clausula especial para contrato
			// se selecionou apenas 1 tipo e o tipo do doc atual é contrato (ou seja, selecionou apenas tipo contrato)

			if ($tpdocs->count() == 1 && $dadosTipo['nomeAbrv'] == 'contr'){
				//TODO CONTRATO DEVE ESTAR ASSOCIADO A UM PROCESSO
				if($r["docPaiID"]!=0){
					$this->auxLoadCamposContrato($r, $d, $dadosTipo["tabBD"]);
					$d->offsetSet("numeroCompl", $r["numeroComp"]);
					$d->offsetSet("empresaID", $r[$dadosTipo["tabBD"].".empresaID"]);
					$contratos->offsetSet($r["id"], $r);
					$empresas->append($r[$dadosTipo["tabBD"].".empresaID"]);
				}
			}
			//monta sigilo
			$sigilo = true;
			if(isset($r["sigilo"]) && $r["sigilo"] && !checkPermission(67)){
				$sigilo = false;
				$d->offsetSet("emitente","");
			}
			else{
				$d->offsetSet("emitente",$r["emitente"]);
				if(isset($r[$dadosTipo["tabBD"].".unOrgInt"]) && $dadosTipo['nomeAbrv'] == "pr")
					$d->offsetSet("emitente",$r[$dadosTipo["tabBD"].".unOrgInt"]);
				elseif (isset($r[$dadosTipo["tabBD"].'.unOrgIntSAP']) && $dadosTipo['nomeAbrv'] == "sap")
					$d->offsetSet("emitente",$r[$dadosTipo["tabBD"].".unOrgIntSAP"]);
			}
			//arquivado?
			if (isset($r["arquivado"]))
				$d->offsetSet("arquivado", $r["arquivado"]);
			else 
				$d->offsetSet("arquivado", 0);

			// adiciona tipo do processo (se o doc for processo)
			if ($dadosTipo['nomeAbrv'] == 'pr') {
				$d->offsetSet("tipoProc", $r[$dadosTipo["tabBD"].".tipoProc"]);
				$d->offsetSet("guardachuva", $r[$dadosTipo["tabBD"].".guardachuva"]);
			}
			if ($dadosTipo['nomeAbrv'] == 'sap')
				$d->offsetSet("guardachuva", "");
			//carregamento dos dados dos documentos anexos
			if(!($sigilo))
				$d->offsetSet("docs", array());
			else if(isset($r[$dadosTipo["tabBD"].".documento"]))
				$d->offsetSet("docs", $this->getNomeDocsAnexos($r[$dadosTipo["tabBD"].".documento"], $tipoDoc));
			//carregamento dos dados dos arquivos anexo
			if ($r['anexos'] == null || !($sigilo))
				$d->offsetSet("docs", array());
			else
				$d->offsetSet("docs", $r["anexos"]);
			
			$d->offsetSet("obra", array(array("id" => "", "nome" => "")));
			if(isset($hres[$r["id"]]))
				$d->offsetSet("hist", $hres[$r["id"]]);
			else 
				$d->offsetSet("hist", array());
			//carega o dono atual do documento
			$d->offsetSet("ownerID", $r["ownerID"]);
			$d->offsetSet("ownerName",$documento->getOwner(new ArrayObj($r),$hash_uid,$hash_histo));
			//carrega se o documento eh despachavel (ownerID != usuario atual)
			
			if ($r['ownerID'] == $_SESSION['id'] || $r['ownerID'] == 0)
				$d->offsetSet("despachavel", 1);
			else
				$d->offsetSet("despachavel", 0);
			//se nao houver empresa, coloca array vazio
			if(!isset($r['empresa']))
				$d->offsetSet("empresa", array());
			//se hao houver assunto, coloca hifen
			if(!isset($r[$dadosTipo["tabBD"].'.assunto']) || !($sigilo))
				$d->offsetSet("assunto", '-');
			else{
				$d->offsetSet("assunto", $r[$dadosTipo["tabBD"].'.assunto']);
			}
			//obras
			$empreendList = array();
			if(isset($r["enome"]) && $r["eid"] && $r["eid"]!="" && $r["enome"]!=""){
				$empreendList[] = array(0 => $r["eid"], 1 => $r["enome"]);
			}
			else {
				$pais = $documento->getDocPai($r);
				$emp = array();
				if($pais->count()>1){
					if($pais->offsetExists("empreendID")){
						$emp[]=$pais->offsetGet("empreendID");
					}
					else{
						foreach ($pais->getArrayCopy() as $p){
							if($p["empreendID"])
								$emp[]=$p["empreendID"];
						}
					}
					if(count($emp)){
						$e = new ObraEmpreendimento();
						$e->setVar("campos", array("id","nome"));
						$re = $e->select("id",$emp);
						if(count($re)>0){
							$lista = array(0 => $re[0]['id'], 1 => $re[0]['nome']);
							$empreendList[] = $lista;
						}
					}
				}	
			}
			if(count($empreendList)==0){
				if($obras->offsetExists($r["id"])){
					$oaux=$obras->offsetGet($r["id"]);
					if(count($oaux)>0){
						foreach ($oaux as $o){
							$lista = array(0 => $o['eid'], 1 => $o['enome']);
							$empreendList[] = $lista;
						}
					}
				}
			}
			$d->offsetSet("empreendList", $empreendList);
			
			if ($r["solicitante"] != "0") {
				$d->offsetSet("solicitante", $r["solicitante"]);
				$solic = $hash_username->offsetGet($r["solicitante"]);
				$d->offsetSet("solicID", $solic['id']);
				$d->offsetSet("solicNome", $solic['nomeCompl']);
				$d->offsetSet("solicArea", $solic['area']);
			}
	
			// verificacao de codificacao utf8
// 			$d = verificaCodificacao($d);
			$hash_data->offsetSet($r["id"], $d->getArrayCopy());
		}
		if($contratos->count()>0){
			$this->buildContrato($contratos,$empresas,$hash_data);
		}
		$data = new ArrayObj();
		foreach ($hash_data->getArrayCopy() as $v)
			$data->append($v);
		echo $data->toJson();
		exit();
	}
	
	private function auxLoadCamposContrato($r, ArrayObj &$d, $tb){
		if (isset($r[$tb.'.dataAssinatura'])) {
			if ($r[$tb.'.dataAssinatura'] != 0) {
				$d->offsetSet('dataAssinatura', date("d/m/Y", $r[$tb.'.dataAssinatura']));
			}
			else {
				$d->offsetSet('dataAssinatura', "---");
			}
		}
		if (isset($r[$tb.'.prazoContr'])) {
			if ($r[$tb.'.prazoContr'] != 0) {
				$d->offsetSet('prazoContr',date("d/m/Y", $r[$tb.'.prazoContr']));
			}
			else {
				$d->offsetSet('prazoContr', "---");
			}
		}
		if (isset($r[$tb.'.vigenciaContr'])) {
			if ($r[$tb.'.vigenciaContr'] != 0) {
				$d->offsetSet('vigenciaContr',date("d/m/Y", $r[$tb.'.vigenciaContr']));
			}
			else {
				$d->offsetSet('vigenciaContr', "---");
			}
		}
		if (isset($r[$tb.'.inicioProjObra'])) {
			if ($r[$tb.'.inicioProjObra'] != 0) {
				$d->offsetSet('inicioProjObra',date("d/m/Y", $r[$tb.'.inicioProjObra']));
			}
			else {
				$d->offsetSet('inicioProjObra', "---");
			}
		}
		if (isset($r[$tb.'.prazoProjObra'])) {
			if ($r[$tb.'.prazoProjObra'] != 0) {
				$d->offsetSet('prazoProjObra',date("d/m/Y", $r[$tb.'.prazoProjObra']));
			}
			else {
				$d->offsetSet('prazoProjObra', "---");
			}
		}
		if (isset($r[$tb.'.dataTermino'])) {
			if ($r[$tb.'.dataTermino'] != 0) {
				$contr = new Contrato($d->offsetGet("id"));
				requireSubModule(array("aditivo","frontend"));
				$at = new Aditivo($contr->bd, $contr->id);
				$a = new Aditivo($contr->bd, $contr->id, "prazoProjObra");
				$ad = $a->getAditivos();
		
				foreach ($ad as $v)
					$at->setVar("valor", $v["valor"]);
				$a = new Aditivo($contr->bd, $contr->id, "prazoContr");
				$ad = $a->getAditivos();
				foreach ($ad as $v)
					$at->setVar("valor", $v["valor"]);
				$dias = $at->getSum();
				$d->offsetSet("dataTermino", date("d-m-Y", $r[$tb.'.dataTermino']));
				//somando os aditivos à data
				$d->offsetSet("dataTermino", date("d-m-Y", strtotime("+".$dias." days",strtotime($d->offsetGet("dataTermino")))));
			}
			else {
				$d->offsetSet('dataTermino', "---");
			}
		}
	}
	
	private function buildContrato(ArrayObj $hc,ArrayObj $empr, ArrayObj &$hash_data){
		//extrair os docPaiID dos contratos
		$docID=$paiID=new ArrayObj();
		foreach ($hc->getArrayCopy() as $c){
			$paiID->append($c["docPaiID"]);
			$docID->append($c["id"]);
		}
		//carrega os docs pais, ou seja, os docs aos quais os contratos estao associados
		$d = new Documentos();
		$j = new JoinSQL("doc_processo", "id", "tipoID");
		$d->setVar("campos", array(Documentos::Tabela.".id as id",Documentos::Tabela.".numeroComp as numeroComp",DocumentoProcesso::Tabela.".tipoProc as tipoProc"));
		$w = new WhereClause(Documentos::Tabela.".id",$paiID->getArrayCopy());
		$docPais = new ArrayObj(CommonMethods::arrayToHash($d->selectJoin(new ArrayObj(array($j)),$w),"id"));
		//carregar obras
		$j = new JoinSQL("obra_doc", "obraID", "id", "INNER");
		$w = new WhereClause($j->getTable().".docID", $docID->getArrayCopy());
		$o = new Obras();
		$o->setVar("campos", array(Obras::Tabela.".id as id",Obras::Tabela.".nome as nome",$j->getTable().".docID as docID"));
		$obras = new ArrayObj(CommonMethods::groupByCampo($o->selectJoin(new ArrayObj(array($j)),$w),"docID"));
		//carregar empresas
		$e = new Empresas();
		$e->setVar("campos", array("id","nome"));
		$empr = new ArrayObj(CommonMethods::arrayToHash($e->select("id",$empr->getArrayCopy()),"id"));
		foreach ($hc->getArrayCopy() as $c){
			if(!$hash_data->offsetExists($c["id"]))
				continue;
			$contr = $hash_data->offsetGet($c["id"]);
			$pai = $docPais->offsetGet($c["docPaiID"]);
			$contr['data_contrato'] = array();
			//atribui obras
			if($obras->offsetExists($c["id"]))
				$contr['data_contrato']['obras'] = $obras->offsetGet($c["id"]);
			else
				$contr['data_contrato']['obras'] = array();
			$contr['data_contrato']['proc'] = array();
			$contr['data_contrato']['empresa'] = 'Desconhecida';
			$contr['data_contrato']['proc']['id'] = $pai["id"];
			$contr['data_contrato']['proc']['numeroCompl'] = $pai["numeroComp"];
			$contr['data_contrato']['proc']['tipoProc'] = $pai["tipoProc"];
			if($empr->offsetExists($contr["empresaID"])){
				$aux =  $empr->offsetGet($contr["empresaID"]);
				$contr['data_contrato']['empresa'] = $aux["nome"];
			}
			$hash_data->offsetSet($c["id"], $contr);
		}
	}
}

?>
<?php
/**
 * LabelCampo
 * @version 0.1 (29/7/2013)
 * @package classes
 * @subpackage system.label
 * @author Leandro Kümmel Tria Mendes
 * @desc classe q manipula acoes em bd do tabela label_campo
 * @see Acesso e querys aos bancos de dados
 */
class LabelCampo extends DAO{
	
	const Tabela = "label_campo";
	
	private static $campos = array("id","nome","label",
									"tipo","attr","extra",
									"verAcao","editarAcao","tooltip");
	private $tipo;
	
	public function __construct($tipo='cad'){
		parent::__construct(self::Tabela, self::$campos);
		$this->loadModules();
		$this->tipo=$tipo;
	} 
	
	private function loadModules(){
		requireSubModule("frontend");
		require_once 'classes/usuario/Usuario.class.php';
		require_once 'classes/documento/Documentos.class.php';
		require_once 'classes/documento/tipo/DocumentosTipo.class.php';
		require_once 'classes/empresa/Empresas.class.php';
	}
	/**
	 * @param string $nomes
	 * @param string $valor
	 * @param string $busca
	 * @return NULL|ArrayObj
	 */
	public function montaCampos($nomes,$valor = null,$busca = false){
		//pega os campos pelo nome do banco de dados
		$cp = new ArrayObj($this->select("nome",$nomes));
		if($cp->count()==0)//nao ha campos, entao nda feito.
			return null;
		//caso busca prepare os nomes com _
		if($busca){
			if(is_array($nomes))
				foreach ($nomes as &$n)
					$n = '_'.$n;
		}
		//retorno
		$ret = new ArrayObj();
		foreach ($cp->getArrayCopy() as $c){
			$campo = new ArrayObj();
			$campo->offsetSet("nome", $c["nome"]);
			$campo->offsetSet("label", $c["label"]);
			$campo->offsetSet("tipo", $c["tipo"]);
			$campo->offsetSet("cod", "");
			$campo->offsetSet("valor", "");
			$campo->offsetSet("parte", false);
			$campo->offsetSet("verAcao", $c["verAcao"]);
			$campo->offsetSet("editarAcao", $c["editarAcao"]);
			$campo->offsetSet("extra", "");
			if ($c['tipo'] == 'input') {
				$campo->offsetSet("extra", $c["extra"]);
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxInputType($campo, $valor);
			} elseif ($c['tipo'] == 'select') {
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeSelect($campo, $valor);
			} elseif ($c['tipo'] == 'yesno') {
				$this->auxTypeRadio($campo, $c, $valor);
			} elseif ($c['tipo'] == 'checkbox') {
				$this->auxTypeCheckbox($campo, $c, $valor);	
			} elseif ($c['tipo'] == 'autoincrement') {
				$this->auxTypeAutoincrement($campo, $c, $valor);			
			} elseif ($c['tipo'] == 'textarea') {
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeTextarea($campo, $c, $valor);
			} elseif ($c['tipo'] == 'userID') {
				$campo->offsetSet("extra", $c["extra"]);
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeUserID($campo, $c, $valor);
			} elseif ($c['tipo'] == 'documentos') {
				$this->auxTypeDocumentos($campo, $c, $valor);
			} elseif ($c['tipo'] == 'composto') {
				$campo->offsetSet("extra", $c["extra"]);
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeComposto($campo, $c, $valor, $busca);
			} elseif($c['tipo'] == 'anoSelect') {//tipo de anoSelect
				$campo->offsetSet("extra", $c["extra"]);
				$this->auxTypeAnoSelect($campo, $c, $valor);
			}
			elseif ($c['tipo'] == 'data') {
				$campo->offsetSet("extra", $c["extra"]);
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeData($campo, $c, $valor);
			}
			elseif ($c['tipo'] == 'empresa') {
				$campo->offsetSet("attr", $c["attr"]);
				$this->auxTypeEmpresa($campo, $c, $valor);
			} elseif($c['tipo'] == 'cont_rep') {
				$this->auxTypeContRep($campo, $c, $valor);
			} elseif ($c['tipo'] == 'span') {
				$this->auxTypeSpan($campo, $c, $valor);
			}
			else {
				//se for outra coisa, copia o codigo HTML em attr
				$campo->offsetSet("cod", $c["attr"]);
				//valor do campo indefinido nao sobre nenhum tratamento
				if(isset($valor[$c])){
					if(!is_array($valor))
						$campo->offsetSet("valor", $valor);
					else 
						$campo->offsetSet("valor", $valor[$c]);
				}
			}
			//se for parte de um campo composto, marca com a flag
			if (strpos($c['extra'],'parte') !== false)
				$campo->offsetSet("parte", true);
			//so for obrigatorio, marca com a classe obrigatorio
			if(strpos($c['extra'], 'obrigatorio') !== false && $this->tipo == 'cad'){
				$cod = preg_replace('/" /', '" class="obrigatorio" ', $campo->offsetGet("cod"), 1);
				$campo->offsetSet("cod",$cod."*");
			}
		}				
		return $campo;
	}
	
	private function getCampoHtmlTag(ArrayObj $campo, $c, $valor){
		if($campo->offsetGet("tipo")=="input"){
			$campo->offsetSet("extra", $c["extra"]);
			if(isset($valor[$c]))
				$campo->offsetSet("valor", $valor[$c]);
		}
	}
	/**
	 * @param ArrayObj &$campo
	 */
	private function auxInputType(ArrayObj &$campo, $valor){
		//se for input com autocompletar de unidades, gera o HTML + javascript correspondente
		if (strpos($campo->offsetGet("extra"),"unOrg_autocompletar") !== false){
			if($this->tipo == 'edt')
				$tag = new HtmlTag("input", 
								   $campo->offsetGet("nome"),
						           "",
						           "",
								   new HtmlTagStyle("min-width","75%"),
								   new HtmlTagAttr(array("name","value","autocomplete"),array($campo->offsetGet("nome"),$campo->offsetGet("valor"),"off")),
								   $campo->offsetGet("attr"));
			else
				$tag = new HtmlTag("input",
						$campo->offsetGet("nome"),
						"",
						"",
						new HtmlTagStyle("min-width","450px"),
						new HtmlTagAttr(array("name","autocomplete"),array($campo->offsetGet("nome"),"off")),
						$campo->offsetGet("attr"));
			$cod = $tag->toString().JS::generateJSDocReady(JS::generateJqueryDialog($campo->offsetGet("nome")));
			$campo->offsetSet("cod", $cod);
		} elseif (strpos($campo->offsetGet("extra"),"current_year") !== false){
			if($this->tipo == 'cad')
				$tag = new HtmlTag("input",
						$campo->offsetGet("nome"),
						"",
						"",
						null,
						new HtmlTagAttr(array("name","value"),array($campo->offsetGet("nome"),date("Y"))),
						$campo->offsetGet("attr"));
			if($this->tipo == 'bus')
				$tag = new HtmlTag("input",
						$campo->offsetGet("nome"),
						"",
						"",
						null,
						new HtmlTagAttr(array("name"),array($campo->offsetGet("nome"))),
						$campo->offsetGet("attr"));
			if($this->tipo == 'edt')
				$tag = new HtmlTag("input",
						$campo->offsetGet("nome"),
						"",
						"",
						null,
						new HtmlTagAttr(array("name","value"),array($campo->offsetGet("nome"),$campo->offsetGet("valor"))),
						$campo->offsetGet("attr"));
			$campo->offsetSet("cod", $tag->toString());
		} elseif (strpos($campo->offsetGet("extra"), "docResposta") !== false) {
			$tag = new HtmlTag("input",
					$campo->offsetGet("nome"),
					"",
					"",
					null,
					new HtmlTagAttr(array("type","name"),array("hidden",$campo->offsetGet("nome"))),
					$campo->offsetGet("attr"));
			$campo->offsetSet("cod", $tag->toString().'{$docResposta}');
		} elseif (strpos($campo->offsetGet("extra"), "moeda") !== false && $this->tipo == 'edt') {
			HtmlTag("input",
			$campo->offsetGet("nome"),
			"",
			"",
			null,
			new HtmlTagAttr(array("name","value"),array($campo->offsetGet("nome"),number_format($campo->offsetGet("valor"), 2, ',', '.'))),
			$campo->offsetGet("attr"));
			$campo->offsetSet("cod", $tag->toString());
		} elseif (stripos($campo->offsetGet("extra"), "moeda") !== false && $this->tipo == 'bus'){
			if (isset($valor[$campo->offsetGet("nome").'_operador'])) {
				//pega operador
				$op=$this->getCampoOperador($valor[$campo->offsetGet("nome").'_operador']);
				$campo->offsetGet("operador",$op);
			}
			$tag = new HtmlTag("select", $campo->offsetGet("nome")."_operador","","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")."_operador"));
			$tag->setChildren(new HtmlTag("option", "","","=",null,new HtmlTagAttr("value","eq")));
			$tag->setChildren(new HtmlTag("option", "","","Menor que",null,new HtmlTagAttr("value","lt")));
			$tag->setChildren(new HtmlTag("option", "","","Menor ou igual que",null,new HtmlTagAttr("value","leq")));
			$tag->setChildren(new HtmlTag("option", "","","Maior que",null,new HtmlTagAttr("value","gt")));
			$tag->setChildren(new HtmlTag("option", "","","Maior ou igual que",null,new HtmlTagAttr("value","geq")));
			$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")),$campo->offsetGet("attr")));
			$campo->offsetSet("cod", $tag->toString());
		} else {
			if($this->tipo == 'edt')
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("name","value"),array($campo->offsetGet("nome"),$campo->offsetGet("valor"))),$campo->offsetGet("attr"));
			else
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")),$campo->offsetGet("attr"));
			$campo->offsetSet("cod", $tag->toString());
		}
	}
	
	private function auxTypeSelect(ArrayObj &$campo, $valor){
		$tag = new HtmlTag("select", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")));
		if($this->tipo != 'edt')
			$tag->setChildren(new HtmlTag("option", "",""," -- Selecione -- ",null,new HtmlTagAttr("value","nenhum"),"selected")); 
		//separa todas as opcoes da selecao
		$attr = explode(",",$campo->offsetGet("attr"));
		//para cada selecao, monta o HTML correspondente
		foreach ($attr as $c) {
			$c = explode("=", $c);
			$c[0] = trim($c[0]);
			//se for separador, cria a opcao desabilitada
			if(strpos($c[0], '_separador_') !== false)
				$tag->setChildren(new HtmlTag("option", "","","-&gt; ".$c[1],new HtmlTagStyle(array("background-color","color"),array("#404040","white")),new HtmlTagAttr(array("value","disabled"),array("nenhum","")),"selected"));
			else
			if($this->tipo == 'edt' && $valor[$campo->offsetGet('nome')] == $c[0]) {
				//se for edicao, o valor do campo deve estar pre-selecionado
				$tag->setChildren(new HtmlTag("option", "","",$c[1],null,new HtmlTagAttr("value",$c[0]),"selected"));
			} else {
				//senao cria campo normal
				$tag->setChildren(new HtmlTag("option", "","",$c[1],null,new HtmlTagAttr("value",$c[0])));
			}
			//valor eh o 'value' da opcao selecionada
			if (isset($valor[$campo->offsetGet('nome')]) && $c[0] == $valor[$campo->offsetGet('nome')])
				$campo->offsetSet("valor", $c[1]);
		}
		$campo->offsetSet("cod", $tag->toString());
	}
	
	private function auxTypeRadio(ArrayObj &$campo, $c, $valor){
		if($this->tipo == 'edt') {
			if($valor[$c] == 1)	{//sim checked
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"1")),"checked");
				$tag->setNext(new HtmlTag("span", "","","Sim&nbsp;&nbsp;"));
				$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"0"))));
				$tag->setNext(new HtmlTag("span", "","","N&atilde;o"));
			}
			elseif($valor[$c] == 0){//nao checked
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"1")));
				$tag->setNext(new HtmlTag("span", "","","Sim&nbsp;&nbsp;"));
				$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"0")),"checked"));
				$tag->setNext(new HtmlTag("span", "","","N&atilde;o"));
			}
			else {//ninguem checked
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"1")));
				$tag->setNext(new HtmlTag("span", "","","Sim&nbsp;&nbsp;"));
				$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"0"))));
				$tag->setNext(new HtmlTag("span", "","","N&atilde;o"));
			}
		} 
		else{
			//se for tipo yes/no monta os 2 campos
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"1")));
			$tag->setNext(new HtmlTag("span", "","","Sim&nbsp;&nbsp;"));
			$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("radio",$campo->offsetGet("nome"),"0")),"checked"));
			$tag->setNext(new HtmlTag("span", "","","N&atilde;o"));
		}
		$campo->offsetSet("cod", $tag->toString());
		//se o valor for 1, retorna sim, se 0, retorna nao, senao, nao informado
		if(isset($valor[$c])){
			if(is_array($valor)&&array_key_exists($c,$valor)){
				if($valor[$c] == 1)	
					$campo->offsetSet("valor", "sim");
				elseif($valor[$c] == 0)
					$campo->offsetSet("valor", "n&atilde;o");
				else
					$campo->offsetSet("valor", "n&atilde;o informado");
			}
			else 
				$campo->offsetSet("valor", "n&atilde;o informado");
		}
	}
	
	private function auxTypeCheckbox(ArrayObj &$campo, $c, $valor){
		//se for checkbox, monta o campo
		if($this->tipo == 'edt' && $valor[$c] == 1)
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("checkbox",$campo->offsetGet("nome"),"1"),"checked"));
		else
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("checkbox",$campo->offsetGet("nome"),"1")));
		$campo->offsetSet("cod", $tag->toString());
		//se o valor for 1, retorna sim, se 0, retorna nao, senao, nao informado
		if(isset($valor[$c])){
			if($valor[$c] == 1)	
				$campo->offsetSet("valor", "sim");
			elseif($valor[$c] == 0)
				$campo->offsetSet("valor", "n&atilde;o");
			else 
				$campo->offsetSet("valor", "n&atilde;o informado");
		}
	}
	
	private function auxTypeAutoincrement(ArrayObj &$campo, $c, $valor){
		//monta campo de autoincrement
		if($this->tipo == "cad")
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","(Ser&aacute; gerado automaticamente.)",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),"")));
		if($this->tipo == "bus")
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value","size"),array("text",$campo->offsetGet("nome"),"","10")));
		if($this->tipo == "edt")
			$tag = new HtmlTag("", "");
		$campo->offsetSet("cod", $tag->toString());
		if(isset($valor[$c]) && array_key_exists($valor, $c)){
			$campo->offsetSet("valor", $valor[$c]);
		}
	}
	
	private function auxTypeTextarea(ArrayObj &$campo, $c, $valor){
		if ($campo->offsetGet("nome") == "conteudo") 
			if(isset($valor[$c]) && array_key_exists($valor, $c))
				if (strcasecmp(mb_detect_encoding($valor[$c], "utf-8,ascii,iso-8859-1"), "iso-8859-1") === 0) 
					$valor[$c] = mb_convert_encoding($valor[$c], "utf-8", "iso-8859-1");
	
		//monta o campo de texto
		if($this->tipo == "cad") 
			$tag = new HtmlTag("textarea", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")),$campo->offsetGet("attr"));
		if($this->tipo == "edt") 
			$tag = new HtmlTag("textarea", $campo->offsetGet("nome"),"",$valor[$c],null,new HtmlTagAttr("name",$campo->offsetGet("nome")),$campo->offsetGet("attr"));
		if($this->tipo == "bus") 
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","size","name"),array("text","35",$campo->offsetGet("name"))));
		
		if(isset($valor[$c])){
			if(!is_array($valor))
				$campo->offsetSet("valor", HtmlString::decode($valor));
			else 
				$campo->offsetSet("valor", HtmlString::decode($valor[$c]));
		}
		// visualizar
		if ($campo->offsetGet('nome') == "conteudo") {
			$tag->setNext(new HtmlTag("br", ""));
			$center = new HtmlTag("center", "");
			if ($this->tipo == "cad") 
				$link = new HtmlTag("a", "","link_preview prev_cad","Visualizar",null,new HtmlTagAttr(array("href","onclick"),array("#","javascript:visualizarDoc(\'cad\')")));
			if ($this->tipo == "edt") 
				$link = new HtmlTag("a", "","link_preview prev_edit","Visualizar",null,new HtmlTagAttr(array("href","onclick"),array("#","javascript:visualizarDoc(\'edit\')")));
			$center->setChildren($link);
			$center->setVar("content", JS::getCKEditor());
			$tag->setNext($center);
		}
		$campo->offsetSet("cod", $tag->toString());
	}
	
	private function auxTypeUserID(ArrayObj &$campo, $c, $valor){
		//monta campo de usuario
		if (strpos($campo->offsetGet("extra"), 'current_user') !== false){
			//se for campo de usuario atual, apenas mostra o nome do usuario e cria campo oculto com o ID do usuario atual no cad e mostra input para busca por nome
			if($this->tipo == "cad") {
				if ($_SESSION['area'] != "Apoio" && $_SESSION['area'] != "Administra&ccedil;&atilde;o") {
					$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),$_SESSION["id"])));
					$tag->setNext(new HtmlTag("span", "","",$_SESSION["nomeCompl"]));
				}
				else {
					$tag = new HtmlTag("script", "","","",null,new HtmlTagAttr(array("type","src"),array("text/javascript",'scripts/emitente.js?r={$randNum}')));
					$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),$_SESSION["id"]))));
					$tag->setNext(new HtmlTag("input", 'emitente_campo',"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden","emitente_campo",$campo->offsetGet("nome")))));
					$select = new HtmlTag("select","emitente_deptos","","",null,new HtmlTagAttr("name","emitente_deptos"));
					$select->setChildren(new HtmlTag("option", "emitente_user","",$_SESSION['nomeCompl'],null,new HtmlTagAttr(array("name","value"),array("emitente_user",$_SESSION['id'])),"selected"));
					$select->setChildren(new HtmlTag("option", "emitente_user",""," --> Departamentos ",new HtmlTagStyle(array("background-color","color"),array("#808080","white")),new HtmlTagAttr(array("name","value"),array("emitente_user",$_SESSION['id'])),"disabled"));
					$u = new Usuario();
					$select->setChildren($u->getAreasHtmlTagOption());
					$tag->setNext($select);
					$tag->setNext(new HtmlTag("br", ""));
					$tag->setNext(new HtmlTag("select","emitente_pessoa","","",null,new HtmlTagAttr("name","emitente_pessoa")));
				}
			}
			if($this->tipo == "bus") {
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","size"),array("text","20")));
				if(isset($valor[$c]))
					$campo->offsetSet("valor", $valor[$c]); 
			}
			if($this->tipo == "edt")
				$tag = new HtmlTag("", ""); 
			$campo->offsetSet("cod", $tag->toString());
		}
		if(strpos($campo->offsetGet("extra"), 'select') !== false){
			//campo de selecao de usuarios
			$tag =  new HtmlTag("select",$campo->offsetGet("nome"),"","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")));
			$tag->setChildren(new HtmlTag("option", "",""," -- Selecione -- ",null,new HtmlTagAttr(array("value","selected"),array("0","true"))));
			if (stripos($campo->offsetGet("extra"), 'allUsers') !== false) {
				$u = new Usuario();
				$tag->setChildren($u->getNomesCompletosHtmlTagOption()); 
			}
			else {
				$attr = explode(",",$campo->offsetGet('attr'));
				foreach ($attr as $c) {
					$c = explode("=", $c);
					$c[0] = trim($c[0],"\n\\\/<>");
					if($c[0] == '_separador_')//se o campo for separador, coloca opcao desabilitada
						$tag->setChildren(new HtmlTag("option", "","",'-&gt; '.$c[1],new HtmlTagStyle(array("background-color","color"),array("#404040","white")),new HtmlTagAttr("disabled","")));
					else
						$tag->setChildren(new HtmlTag("option", "","",$c[1],null,new HtmlTagAttr("value",$c[0])));
					if ($c[0] == $valor[$campo->offsetGet('nome')])
						$campo->offsetSet('valor', $c[1]);//se select, valor eh o 'value' da opcao
				}
			}
			$campo->offsetSet("cod", $tag->toString());
		}
		//tendo o ID do usuario, procura no BD o nome do usuario, que eh o valor do campo
		if (isset($valor[$campo->offsetGet('nome')]) && $valor[$campo->offsetGet('nome')] > 0){
			$u = new Usuario();
			$res = $u->select("id",$valor[$campo->offsetGet('nome')]);
			if (count($res))
				$campo->offsetSet('valor', $res[0]['nomeCompl']);
			else
				$campo->offsetSet('valor', 'Usu&aacute;rio desconhecido.');
		}
	}
	
	private function auxTypeDocumentos(ArrayObj &$campo, $c, $valor){
		if($this->tipo != 'bus') {
			//campos de documetos. cria div que mostrara os nomes de documentos e um campo oculto que guardas os IDs a serem colocados no campo do BD
			$div = new HtmlTag("div", $campo->offsetGet("nome")."Nomes","cadDisp");
			$div->setNext(new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),""))));
			global $conf;
			$div->setNext(new HtmlTag("a", "addDocLink","","Adicionar Documento",null,
									  new HtmlTagAttr(array("href","onclick"),array("#",'window.open(\'sgd.php?acao=busca_mini&amp;onclick=adicionarCampo&amp;target='.$campo->offsetGet("nome").'\',\'addDoc\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()'))));
			if($this->tipo == "edt")
				$campo->offsetSet("cod", "");
			else 
				$campo->offsetSet("cod", $div->toString());
			//retorna nome do documento
			$campo->offsetSet("valor", "");
			if(!empty($valor[$c]) && isset($valor[$c]) && is_array($valor)){
				$docAnex = new Documentos();
				$camposInner = new ArrayObj(LabelDoc::$campos);
				$camposInner->putIniMark(LabelDoc::Tabela.".");
				$camposInner->mirrorValues(" as ");
				$docAnex->setVar("campos", array_merge(array("doc.*"),$camposInner->getArrayCopy()));
				$join = new JoinSQL("label_doc", "id", "labelID","INNER");
				$docs = $docAnex->selectJoin(new ArrayObj(array($join)),new WhereClause("id", explode(",",$valor[$c])));
				$str = "";
				foreach($docs as $anex) 
					$str .= showDocAnexo(array(array("id" => $anex["id"], "nome" => $anex["label_doc.nome"]." ".$anex["numeroComp"])));
				$campo->offsetSet("valor", $str);			
			}
		}
		else {
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","size"),array("text",$campo->offsetGet("nome"),"20")));
			$campo->offsetSet("valor", "");
			$campo->offsetSet("cod", $tag->toString());
		}
	}
	
	private function auxTypeComposto(ArrayObj &$campo, $c, $valor, $busca){
		//tipo composto de varios outros campos
		//algoritmo: le as partes, procura, recursivamente, o codigo de cada parte
		$partes = explode("+",$campo->offsetGet("attr"));
		$tag = new HtmlTag("input", $c,"","",null,new HtmlTagAttr(array("type","value","name"),array("hidden",$campo->offsetGet("nome"),$c)));
		$nome = $campo->offsetGet("nome");
		$cod= $campo->offsetGet("cod");
		$val= $campo->offsetGet("valor");
		if((!isset($valor[$campo->offsetGet("nome")]) && $this->tipo != 'edt') || $valor[$campo->offsetGet("nome")] == $campo->offsetGet("nome")){
			//para cada parte, obtem o codigo da parte e concatena com os dados ja obtidos
			
			foreach ($partes as $p) {
				$dados = $this->montaCampos($p,$this->tipo,$valor,$busca);
				if($dados != null){//se a parte for campo
					$nome .= ','.$dados['nome'];//concatena o nome da parte
					$cod .= $dados['cod'];//concatena o codigo da parte
					$val .= $dados['valor'];//concatena o valor da parte
				}
				else{//se a parte nao for campo (separador, por ex)
					$cod .= str_replace('"','',$p);//concatena o codigo com a parte sem aspas
					$val .= str_replace('"','',$p);//concatena o valor com a parte sem aspas
				}
			}
		} 
		else 
			$val .= $valor[$campo->offsetGet("nome")];
		$campo->offsetSet("valor", $val);
		$campo->offsetSet("nome", $nome);
		if (stripos($campo->offsetGet('extra'), 'mini_busca') !== false && $this->tipo != 'bus') {
			$tag->setNext(new HtmlTag("a", "","","Buscar",null,new HtmlTagAttr(array("href","onclick"),array('#','referenciarDoc(\''.$c.'\')'))));
			$tag->setNext(new HtmlTag("script", "","","",null,new HtmlTagAttr(array("type","src"),array("text/javascript",'scripts/referenciar.js?r={$randNum}'))));
		}
		if($this->tipo == "edt"){
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value","size"),array("text",$campo->offsetGet("nome"),$campo->offsetGet("valor"),"40")));
		}
		$campo->offsetSet("cod", $tag->toString().$cod);
	}
	
	private function auxTypeAnoSelect(ArrayObj &$campo, $c, $valor){
		$data = new DataObj("today");
		//se for busca, deicxa ano em branco por padrao (caso nao queira determinar o ano na busca)
		if($this->tipo == 'bus') {
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value","size"),array("text",$campo->offsetGet("nome"),"","3")));
		}elseif ($this->tipo == 'edt'){
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value","size"),array("text",$campo->offsetGet("nome"),$valor[$c],"3")));
		} else {
			if(strpos($campo->offsetGet('extra'), "onlyCurrentYear") !== false) {
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),$data->getAno())));
				$tag->setNext(new HtmlTag("span", "","",$data->getAno()));
			}
			else {
				$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value"),array("hidden",$campo->offsetGet("nome"),$data->getAno())));
				$tag->setNext(new HtmlTag("input", $campo->offsetGet("nome")."2","","",null,new HtmlTagAttr(array("type","name","value","size","maxlength"),array("text",$campo->offsetGet("nome")."2","","3","4"))));
				$select = new HtmlTag("select", $campo->offsetGet("nome")."1","","",null,new HtmlTagAttr("name",$campo->offsetGet("nome")."1"));
				$select->setChildren(new HtmlTag("option", "","",$data->getAno(),null,new HtmlTagAttr("selected","name","value"),array("true",$data->getAno(),$data->getAno())));
				//completa a selecao com os ultimos 5 anos
				for ($i = 1; $i < 6; $i++) 
					$select->setChildren(new HtmlTag("option", "","",$data->getAno()-$i,null,new HtmlTagAttr("name","value"),array($data->getAno()-$i,$data->getAno()-$i)));
				//cria a opcao 'outros' e o codigo JS para mudar lidar com os campos
				$select->setChildren(new HtmlTag("option", $campo->offsetGet("nome")."outroAno","","Outro",null,new HtmlTagAttr("name","value"),array($campo->offsetGet("nome")."outroAno","")));
				$tag->setNext($select);
				$script = new HtmlTag("script", "","","",null,new HtmlTagAttr("type","text/javascript"));
				$script->setVar("content", JS::getLabelCampoAnoSelect($campo->offsetGet("nome")));
				$tag->setNext($script);
			}
		}
		$campo->offsetSet("cod", $tag->toString());
		//valor do ano nao sofre tratamento
		if(isset($valor[$c]) && is_array($valor))
			$campo->offsetSet("valor", $valor[$c]); 
		else
			$campo->offsetSet("valor", $data->getAno());
	}
	
	private function auxTypeData(ArrayObj &$campo, $c, $valor){
		if(is_array($valor))
			if(isset($valor[$c]) && $valor[$c] > 0)
				$campo->offsetSet("valor", $valor[$c]); 
			else
				$campo->offsetSet("valor", 0);
		else
			$campo->offsetSet("valor", 0);
		if($this->tipo == 'edt') {
			$dstr = '';
			if ($campo['valor'] > 0) {
				$data = new DataObj($campo['valor']);
				$dstr = $data->getData();
			}
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name","value","maxlength"),array("text",$campo->offsetGet("nome"),$dstr,"10")),$campo->offsetGet("attr"));
		}
		else {
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name"),array("text",$campo->offsetGet("nome"))));
		}
		$cod = $tag->toString();
		if (stripos( $campo->offsetGet("extra"), "noDatePicker") === false) 
			$cod.=JS::generateJSDocReady(JS::getDatePicker($campo->offsetGet("nome")));
		$campo->offsetSet("cod", $cod);
	}
	
	private function auxTypeEmpresa(ArrayObj &$campo, $c, $valor){
		if(is_array($valor)){
			if (isset($valor[$c]) && $valor[$c] > 0) {
				$e = new Empresas();
				$emp = $e->select("id",$valor[$c]);
				if(count($emp)==0)
					$campo->offsetSet("valor", "Nenhuma empresa selecionada.");
				else 
					$campo->offsetSet("valor", $emp[0]["nome"]);
			}
			else {
				$campo->offsetSet("valor", "Nenhuma empresa selecionada.");
			}
		}
		else {
			$campo->offsetSet("valor", "Nenhuma empresa selecionada.");
		}
		$cod = "";
		if ($this->tipo == 'cad') {
			$e = new Empresas();
			$cod = $e->getHtmlTagSelect($campo->offsetGet("nome"),"wrapSelect",array("id","nome"));
			if (checkPermission(94)) {
				$aux = new HtmlTag("a", "","novaEmpresa","[Cadastrar Empresa]");
				$aux->setAttr("href", "#");
				$cod->setNext($aux);
			}
			$cod = $cod->toString();
		}
		if ($this->tipo == 'edt') {
			$cod = $e->getHtmlTagSelect($campo->offsetGet("nome"),"wrapSelect",array("id","nome"),$valor[$c]);
			$cod = $cod->toString();
		}
		if ($this->tipo == 'bus') {
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","name"),array("text",$campo->offsetGet("nome"))),$campo->offsetGet("attr"));
			$cod = $tag->toString();
		}
		else if ($this->tipo != 'bus') {
			$campo['cod'] .= ' ';
		}
		$campo->offsetSet("cod", $cod);
	}
	
	private function auxTypeContRep(ArrayObj &$campo, $c, $valor){
		$campo->offsetSet("valor", null);
		if($this->tipo != 'cad')
			$campo->offsetSet("cod", null);
		else {
			$tag = new HtmlTable("", "", 7);
			$tr = new HtmlTag("tr", "","c");
			$tr->setChildren(new HtmlTag("td", "","","",null,new HtmlTagAttr("colspan","7")));
			$tag->setChildren($tag);
			$tag->appendLine(array("<b>n&deg;</b>","","<b>Tipo de Documento Gen&eacute;rico</b>","<b>N&uacute;mero do doc.</b>","<b>Ano</b>","<b>Assunto</b>","<b>Anexos/Outros</b>"));
			$cod = '</td></tr></table>'.$tag->toString();
			
			$dt = new DocumentosTipo();
			$cod .= $dt->getHtmlTableLines()->toString();
			
			$script = new HtmlTag("script", "","","",null,new HtmlTagAttr(array("type","src"),array("text/javascript",'scripts/rep.js?r={$randNum}')));
			$script->setNext(new HtmlTag("div", "cadRepError"));
			$cod .= $script->toString();
			
			$campo->offsetSet("cod", $cod);
		}
	}
	
	private function auxTypeSpan(ArrayObj &$campo, $c, $valor){
		if(isset($valor[$c]))
			$campo->offsetSet("valor", $valor[$c]);
		if($this->tipo == "cad") 
			$tag = new HtmlTag("span", $campo->offsetGet("nome"));
		if($this->tipo == "bus") 
			$tag = new HtmlTag("", "");
		if($this->tipo == "edt") 
			$tag = new HtmlTag("input", $campo->offsetGet("nome"),"","",null,new HtmlTagAttr(array("type","size"),array("text","35")));
		$campo->offsetSet("cod", $tag->toString());
	}
	
	private function getCampoOperador($op){
		switch ($op) {
			case 'eq':
				return "=";
			case 'lt':
				return "<";
			case 'leq':
				return "<=";
			case 'gt':
				return ">";
			case 'geq':
				return ">=";
			default:
				return "=";
		}
	}
}
?>
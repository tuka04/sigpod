<?php
/**
 * @author Leandro Kümmel Tria Mendes
 * @since 23/07/2013
 * @version 1.1.1.5
 * @desc Classe que manipula os campos de busca
 */

class BuscaCampos{
	/**
	 * campos basicos
	 * @var ArrayObj
	 */
	private $camposBasicos;
	/**
	 * campos do documento
	 * @var ArrayObj
	 */
	private $camposDoc;
	
	public function __construct(){
		if(!isset($_REQUEST["docs"]))
			$_REQUEST["docs"]="";
		requireSubModule("frontend");
	}
	/**
	 * Retorna uma tagHtml contendo os campos para busca
	 * @return HtmlTag
	 */
	public function getCampos(){
		$divPai = new HtmlTag("div", "camposBuscaElemPai");
		$divEsq = new HtmlTag("div", "camposBuscaEsq");
		$divDir = new HtmlTag("div", "camposBuscaDir");
		$divPai->setChildren($divEsq);
		$divPai->setChildren($divDir);
		//estilos
		$divEsq->setStyle(array("float","width"), array("left","50%"));
		$divDir->setStyle(array("float","width"), array("right","50%"));
		//campos
		if(!isset($_REQUEST["docs"])){
			$msg = '<b>Pelo menos um tipo de documento deve ser escolhido.</b>';
			$divPai->setVar("content", $msg);
		}
		$camposPartes = '';
		
		$_REQUEST['docs'] = rtrim($_REQUEST['docs'], ',');
		$tipos = explode(',', $_REQUEST['docs']);
		$this->loadCamposBasicos();//carregando basicos
		$buscar = new HtmlTag("input", "btnBuscar","campoDoc","",null,new HtmlTagAttr(array("type","value"),array("submit","Buscar")));
		if (count($tipos) == 0 || (count($tipos) == 1 && $tipos[0] == '')) {
			$msg = '<b>Pelo menos um tipo de documento deve ser escolhido.</b>';
			$divPai->setVar("content", $msg);
		} elseif (count($tipos) == 1) {
			$doc = new Documento(0);
			$doc->dadosTipo['nomeAbrv'] = $tipos[0];
			$doc->loadTipoData();
			$campos = explode(',',$doc->dadosTipo['campos']);
			$this->loadCamposDoc(new ArrayObj($campos));//carregando campos do doc
			//escrevendo campos basicos			
			$tableEsq = new HtmlTable("tabelaCamposEsq", "", 2);
			$tableEsq->setChildren(new HtmlTag("input", "tipos","","",null,new HtmlTagAttr(array("type","value"),array("hidden",$_REQUEST["docs"]))));
			$labels = $this->camposBasicos->offsetGet("labels");
			$inputs = $this->camposBasicos->offsetGet("inputs");
			foreach ($labels as $k=>$l){
				if(is_object($inputs[$k]))
					$tableEsq->appendLine(array($l,$inputs[$k]->toString()),"c");
				else 
					$tableEsq->appendLine(array($l,$inputs[$k]),"c");
			}
			//campos de acordo com tipo de doc
			$tableDir = new HtmlTable("tabelaCamposDir", "", 2);
			$tableDir->setChildren(new HtmlTag("input", "camposNomes","","",null,new HtmlTagAttr(array("type","value"),array("hidden",$this->camposDoc->offsetGet("nomes")->toString()))));
			$labels = $this->camposDoc->offsetGet("labels");
			$inputs = $this->camposDoc->offsetGet("inputs");
			foreach ($labels as $k=>$l){
				if(is_object($inputs[$k]))
					$tableDir->appendLine(array($l,$inputs[$k]->toString()),"c");
				else 
					$tableDir->appendLine(array($l,$inputs[$k]),"c");
			}
			$divEsq->setChildren($tableEsq);
			$divDir->setChildren($tableDir);
			$divDir->setNext($buscar);
		} 
		else {
			//escrevendo campos basicos
			$tableEsq = new HtmlTable("tabelaCamposEsq", "", 2);
			$tableEsq->setChildren(new HtmlTag("input", "tipos","","",null,new HtmlTagAttr(array("type","value"),array("hidden",$_REQUEST["docs"]))));
			$labels = $this->camposBasicos->offsetGet("labels");
			$inputs = $this->camposBasicos->offsetGet("inputs");
			foreach ($labels as $k=>$l){
				if(is_object($inputs[$k]))
					$tableEsq->appendLine(array($l,$inputs[$k]->toString()),"c");
				else
					$tableEsq->appendLine(array($l,$inputs[$k]),"c");
			}
			$divEsq->setChildren($tableEsq);
			$divEsq->setNext($buscar);
		}
		return $divPai;
	}
	/**
	 * carrega os campos de acordo com o $doc->dadosTipo
	 */
	private function loadCamposDoc(ArrayObj $campos){
		$ret = new ArrayObj();
		$nome = new ArrayObj();
		$label = new ArrayObj();
		$input = new ArrayObj();
		foreach ($campos->getArrayCopy() as $c) {
			$campoHtml = montaCampo($c,'bus');
			$nome->append($campoHtml['nome']);
			if ($campoHtml['verAcao'] < 0 || ($campoHtml['verAcao'] > 0 && !checkPermission($campoHtml['verAcao'])))
				continue;
			elseif (stripos($campoHtml['nome'],"docResp") === false){
				$label->append($campoHtml['label']);
				$input->append($campoHtml['cod']);
			}
		}
		$ret->offsetSet("labels", $label);
		$ret->offsetSet("inputs", $input);
		$ret->offsetSet("nomes", $nome);
		$this->camposDoc = $ret;
	}
	/**
	 * carrega os campos basicos
	 */
	private function loadCamposBasicos(){
		$this->camposBasicos = new ArrayObj();
		$nomesBasicos = array("N&deg; CPO","N&deg; do documento","Criado em/entre",
				"Despachado em/entre","Despachado para",
				"Recebido em/entre","Recebido de",
				"Conte&uacute;do do despacho","Conte&uacute;do de <b>qualquer</b> campo"
		);
		$dataCriacao = new HtmlTag("input", "dataCriacao1","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true)));
		$dataCriacao->setNext(new HtmlTag("span", "",""," e "));
		$dataCriacao->setNext(new HtmlTag("input", "dataCriacao2","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true))));
		$dataDespacho = new HtmlTag("input", "dataDespacho1","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true)));
		$dataDespacho->setNext(new HtmlTag("span", "",""," e "));
		$dataDespacho->setNext(new HtmlTag("input", "dataDespacho2","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true))));
		$dataReceb = new HtmlTag("input", "dataReceb1","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true)));
		$dataReceb->setNext(new HtmlTag("span", "",""," e "));
		$dataReceb->setNext(new HtmlTag("input", "dataReceb2","","",null,new HtmlTagAttr(array("type","size","maxlength","datepicker"),array("text","15","10",true))));
		$inputsBasicos = array(new HtmlTag("input", "numCPO","","",null,new HtmlTagAttr(array("type","size","maxlength"),array("text","5","5"))),
				new HtmlTag("input", "numDoc","","",null,new HtmlTagAttr(array("type","size","maxlength"),array("text","10","10"))),
				$dataCriacao,
				$dataDespacho,
				new HtmlTag("input", "unDespacho","","",null,new HtmlTagAttr(array("type","size","maxlength"),array("text","40","250"))),
				$dataReceb,
				new HtmlTag("input", "unReceb","","",null,new HtmlTagAttr(array("type","size","maxlength"),array("text","40","250"))),
				new HtmlTag("input", "contDesp","","",null,new HtmlTagAttr(array("type","size"),array("text","40"))),
				new HtmlTag("input", "contGen","","",null,new HtmlTagAttr(array("type","size"),array("text","40")))
		);
		$this->camposBasicos->offsetSet("labels", new ArrayObj($nomesBasicos));
		$this->camposBasicos->offsetSet("inputs", new ArrayObj($inputsBasicos));
		// verifica se o usuario tem permissao para realizar busca no arquivo
		if (checkPermission(68)){
			$nome = "Arquivado?";
			$input = new HtmlTag("input", "buscaArquivo","","",null,new HtmlTagAttr(array("type","name","value"),array("radio","buscaArquivo","1")));
			$input->setNext(new HtmlTag("span", "","","Sim"));
			$input->setNext(new HtmlTag("input", "buscaArquivo","","",null,new HtmlTagAttr(array("type","name","value"),array("radio","buscaArquivo","0"))));
			$input->setNext(new HtmlTag("span", "","","N&atilde;o"));
			$input->setNext(new HtmlTag("input", "buscaArquivo","","",null,new HtmlTagAttr(array("type","name","value","checked"),array("radio","buscaArquivo","-1",true))));
			$input->setNext(new HtmlTag("span", "","","Ambos"));
			$this->camposBasicos->offsetGet("inputs")->append($input);
			$this->camposBasicos->offsetGet("labels")->append($nome);
		}
		if(isset($_REQUEST['anex']) && $_REQUEST['anex'] == 'true') {
			$nome = "A&ccedil;&atilde;o de anexar";
			$input = new HtmlTag("input", "actionAnex","","",null,new HtmlTagAttr(array("type","name","value","checked"),array("radio","actionAnex","1",true)));
			$this->camposBasicos->offsetGet("inputs")->append($input);
			$this->camposBasicos->offsetGet("labels")->append($nome);
		}
	}
}
?>
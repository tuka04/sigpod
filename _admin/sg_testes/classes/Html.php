<?php
/**
 * @version 0.1 (9/2/2011)
 * @package geral
 * @author Mario Akita
 * @desc contem a classe HTML e seus metodos que lidam com o posicionamento do conteudo na tela.
 */

/**
 * @package geral
 * @subpackage classes
 * @desc classe HTML lida com o template e o posicionamento de conteudo na tela.
 */

class html{
	
	/**
	 * cabecalho que vai entre <head> e </head>
	 * @var string
	 */
	public $head;
	
	/**
	 * titulo da pagina
	 * @var string
	 */
	public $title;
	
	/**
	 * texto do cabecalho
	 * @var string
	 */
	public $header;
	
	
	/**
	 * string da barra de navegacao
	 * @var string
	 */
	public $path;
	
	/**
	 * nome do usuario
	 * @var string
	 */
	public $user;
	
	/**
	 * nome da pagina de logout (logout.php e o padrao)
	 * @var string
	 */
	public $logout_page;
	
	/**
	 * nome da pagina de in (login.php e o padrao)
	 * @var string
	 */
	public $login_page;
	
	/**
	 * menu do sistema 
	 * @var string
	 */
	public $menu;

	/**
	 * html do conteudo da pagina
	 * @var string
	 */
	public $content;
	
	/**
	 * conteudo do rodape
	 * @var string
	 */
	public $footer;
	
	/**
	 * código HTML da pagina a ser mostrada
	 * @var string
	 */
	private $html;
	
	/**
	 * arquivo que contem o codigo HTML do template
	 * @var string
	 */
	private $template;
	
	/**
	 * outros campos.
	 * @var array
	 */
	public $campos;
	
	/**
	 * versão arquivos js
	 * @var string
	 */
	private $jsversion;
	
	/**
	 * codificação da página
	 * @var string
	 */
	private $charset;
	
	/**
	 * @param string $template 
	 * @desc cria uma pagina html com conteudo passado por parametro (se existir)
	 * @uses $this->html, $this->template
	 */
	public function __construct($defaults, $template = NULL) {
		//carrega template padrao se nao for especificado nenhum template
		if($template == null)
			$this->template = $defaults['template'];
		else
			$this->template = $template;
		
		$this->html = file_get_contents($this->template);
		
		//carregando valores padrao
		if(isset($_SESSION['perm'][20]) && $_SESSION['perm'][20] > 0) {
			$this->campos['admLink'] = ' <a href="adm.php">Administra&ccedil;&atilde;o</a>';
		} else {
			$this->campos['admLink'] = '';
		}
		$this->title = $defaults['title'];
		$this->head = $defaults['head'];
		$this->footer = $defaults['footer'];
		$this->login_page = $defaults['login_page'];
		$this->logout_page = $defaults['logout_page'];
		$this->jsversion = $defaults['jsversion'];
		$this->charset = $defaults['charset'];
	}
	
	public function setVar($var,$val){
		if(isset($this->$var))
			$this->$var = $val;
	}
	
	/**
	 * @desc troca o template da pagina
	 * @param string $templateFile
	 */
	public function setTemplate($templateFile) {
		$this->template = $templateFile;
		$this->html = file_get_contents($this->template);
	}
	
	/**
	 * @desc troca os conteudos das variaveis e mostra a pagina na tela
	 * @uses $this->html
	 */
	public function showPage(){		
		$this->html = str_replace('{$title}',       $this->title,       $this->html);
		$this->html = str_replace('{$head}',        $this->head,        $this->html);
		$this->html = str_replace('{$header}',      $this->header,      $this->html);
		$this->html = str_replace('{$path}',        $this->path,        $this->html);
		$this->html = str_replace('{$user}',        $this->user,        $this->html);
		$this->html = str_replace('{$login_page}',  $this->login_page,  $this->html);
		$this->html = str_replace('{$logout_page}', $this->logout_page, $this->html);
		$this->html = str_replace('{$menu}',        $this->menu,        $this->html);
		$this->html = str_replace('{$footer}',      $this->footer,      $this->html);
		$this->html = str_replace('{$randNum}', 	$this->jsversion,	$this->html);
		$this->html = str_replace('{$charset}', 	$this->charset, 	$this->html);
		
		
		for ($i = 1; $i <= count($this->content); $i++) {
			$this->content[$i] = str_replace('{$randNum}', $this->jsversion, $this->content[$i]);
			$this->html = str_replace('{$content'.$i.'}',$this->content[$i], $this->html);
			$nextContent = $i+1;
		}
		//replace dos restantes dos contents q ainda persistem em aparecer
		
		
		if ($this->campos != null){
			foreach ($this->campos as $key => $campo) {
				$this->html = str_replace('{$campos_'.$key.'}', $campo, $this->html);
			}
		}
		while(stripos($this->html,'{$content'.$nextContent.'}')!==false){
			$this->html = str_replace('{$content'.$nextContent.'}', JS::generateJSTagHtml(JS::getHideElem("#c".$nextContent)), $this->html);
			$nextContent++;
		}
		print($this->html);
	}
 		
}
?>
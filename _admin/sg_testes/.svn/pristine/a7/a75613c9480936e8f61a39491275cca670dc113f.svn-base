<?php
/**
 * Esta classe modela o quadro de novidades do SiGPOD
 * @author Mario Akita
 *
 */
class QuadroNovidades {
	
	private $username;
	private $lastLogin;
	private $news;
	private $visible; 
	private $bd;
	
	function __construct($userID, $visible) {
		$this->visible = $visible;
		$this->username = $userID;
		global $bd;
		$this->bd = $bd;
		$this->lastLogin = $this->getLastLoginDate();
		$this->news = $this->getNews();
		
	}
	
	function getLastLoginDate() {
		$res = $this->bd->query("SELECT data FROM data_log WHERE username = '{$this->username}' AND acao = 'Efetuou login.' ORDER BY data DESC LIMIT 2");
		if(count($res) != 2) return 0;
		return $res[1]['data'];
	}
	
	function getNews(){
		$res = $this->bd->query("SELECT data, texto FROM novidades WHERE data > {$this->lastLogin} ORDER BY data DESC");
		return $res;
	}
	
	function geraHTML() {
		include_once 'quadroNovidades_interface.php';
		if(!$this->visible || count($this->news) == 0) {
			return '';
		} else {
			$template = quadroNovidades_template();
			$html = $template['quadro'];
			$novidades = '';
			
			foreach ($this->news as $n) {
				$novidades .= str_replace(array('{$data}','{$texto}'), array(date('j/n/Y',$n['data']), $n['texto']), $template['linha_novidade']);
			}
			
			$html = str_replace('{$novidades}', $novidades, $html);
			return $html;
		}
	}
}

?>
<?php	
	include_once('conf.inc.php');
	include_once('error.php');
	include_once('modules.php');
	include_once('interfaces.php');
	include_once('sgd_interface.php');
	include_once('sgd_modules.php');
	include_once('queries.php');
	/* include de classes */
	include_once('classes/Html.php');
	include_once('classes/Pessoa.php');
	include_once('classes/BD.php');
	include_once('classes/Documento.php');
	include_once('classes/Empresa.php');
	include_once('classes/QuadroNovidades.php');
	include_once('classes/Contrato.php');
	include_once('classes/Historico.php');
	include_once('classes/HistFactory.php');
	include_once('classes/Historico_Doc.php');
	
	//if(isset($_GET['-s'])) exit();
	
	ini_set('default_charset', $conf['charset']);
	ini_set('mbstring.internal_encoding', $conf['charset']);
	
	foreach ($_POST as $pk => $pv) {
		$ascii = false;
		
		if (mb_detect_encoding($pv) == 'ASCII') {
			$ascii = true;
		}
		
		$pv = urldecode($pv);
		//$pv = to_utf8($pv);
		
		if(mb_detect_encoding($pv) === 'UTF-8') {
			if ($ascii) {
				$_POST[$pk] = $pv;
			}
			continue;
		}
		
		$pv = mb_convert_encoding($pv, 'UTF-8');
		$_POST[$pk] = SGEncode($pv);
		
		//if ($pk == "newVal") var_dump($_POST[$pk]);
	} //var_dump($_POST);
	
	foreach ($_GET as $pk => $pv) {
		$pv = urldecode($pv);
		$_GET[$pk] = to_utf8($pv);
		if(mb_detect_encoding($pv) === 'UTF-8')
			continue;
		$pv = mb_convert_encoding($pv, 'UTF-8');
		$_GET[$pk] = SGEncode($pv);
	}
	//var_dump($_GET);
	
	if(isset($_GET['alert']) && $_GET['alert'] != '')
		$conf['head'] =  '<script type="text/javascript">alert(\''.$_GET['alert'].'\');</script>';		
		
	date_default_timezone_set($conf['timezone']);
	
	session_set_cookie_params(36000);
	session_cache_expire(600); // em minutos -> 10 h
	ini_set('session.gc_maxlifetime', 36000);
	//var_dump(ini_get('session.gc_maxlifetime'));
	
	session_start();
	
	// variavel de controle de ultima modificação/acesso...
	$_SESSION['ultimaModificacao'] = time();
	
	function includeModule($name){
		if ($name == 'sgo') {
			include_once('sgo_queries.php');
			include_once('sgo_modules.php');
			include_once('sgo_interface.php');
			include_once('classes/Empreendimento.php');
			include_once('classes/EntradaHistoricoObra.php');
			include_once('classes/Obra.php');
			include_once('classes/Recurso.php');
			include_once('classes/Etapa.php');
			include_once('classes/Fase.php');
			include_once('classes/SGO.php');
			include_once('classes/Historico_Empreend.php');
			include_once('classes/Historico_Obra.php');
		}
		if ($name == 'sgp') {
			include_once('sgp_modules.php');
			include_once('sgp_interface.php');
		}
	}
	/**
	 * Solicitacao 003
	 * Faz a requisicao de submodulos.
	 * @param string $name
	 */
	function requireSubModule($name){
		if(!is_array($name))
			$nameArray[] = $name;
		else
		$nameArray=$name;
		foreach ($nameArray as $n){
			if($n=='aditivo'){
				require_once 'classes/contrato/Aditivo.class.php';
			}
			else if($n=='frontend'){
				require_once 'classes/frontend/html/HtmlTag.class.php';
				require_once 'classes/frontend/html/HtmlTagStyle.class.php';
				require_once 'classes/frontend/html/HtmlTagAttr.class.php';
				require_once 'classes/frontend/html/HtmlTable.class.php';
			}
			else if($n=='alerta'){
				require_once 'alerta.php';
				require_once 'classes/system/alerta/SysAlerta.class.php';
				require_once 'classes/contrato/alerta/Alerta.class.php';
				require_once 'classes/contrato/alerta/Vencimento.class.php';
				require_once 'classes/usuario/alerta/UsuarioAlerta.class.php';
			}
		}
	}
	
	function getVal($array, $key) {
		if(isset($array[$key]))
			return $array[$key];
		else return null;
	}
?>
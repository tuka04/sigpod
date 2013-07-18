<?php
/**
 * @version 0.9 (13/5/2011)
 * @package geral
 * @author Mario Akita
 * @desc contem a classe Pessoa e os metodos relativos ao gerenciamento de usuarios.
 */

/**
 * @package geral
 * @subpackage classes
 * @desc lida com o gerenciamento de usuarios (adicao, remocao, leitura dos dados de usuarios, autenticacao
 */

class Pessoa{
	/**
	 * nome de usuario
	 * @var string
	 */
	private $username;
	
	private $matricula;
	
	private $descr;
	
	/**
	 * nome completo do usuario
	 * @var string
	 */
	private $nome;
	
	private $sobrenome;
	
	private $nomeCompl;
	
	/**
	 * email do usuario
	 * @var string
	 */
	private $email;
	
	/**
	 * area do usuario
	 * @var string
	 */
	private $area;
	
	/**
	 * cargo do usuario
	 * @var string
	 */
	private $cargo;
	
	/**
	 * grupo do usuario
	 * @var string
	 */
	private $grupo;
	
	/**
	 * permissoes do usuario
	 * @var array
	 */
	private $perm;
	
	/**
	 * indica se o usuario esta ativo ou ja foi desligado
	 * @var boolean
	 */
	private $ativo;
	
	/**
	 * chave se sessao do usuario
	 * @var string
	 */
	private $chave;
	
	/**
	 * identifica se o usuario esta logado ou nao
	 * @var boolean
	 */
	private $logado;
	
	/**
	 * identifica o gerente direto desta pessoa
	 * @var string
	 */
	private $gerente;
	
	private $id;
	
	/**
	 * @desc inicia uma nova variavel com valores nulos
	 */
	public function __construct() {
		//session_start();
		
		$this->setNull();
		//loga com os dados da sessao ou seta todas as variaveis = null
		if(isset($_SESSION['username'])){
			$this->id        = $_SESSION['id'];
			$this->username  = $_SESSION['username'];
			$this->nome      = $_SESSION['nome'];
			$this->sobrenome = $_SESSION['sobrenome'];
			$this->nomeCompl = $_SESSION['nomeCompl'];
			$this->email     = $_SESSION['email'];
			$this->area      = $_SESSION['area'];
			$this->cargo     = $_SESSION['cargo'];
			$this->descr     = $_SESSION['descr'];
			$this->grupo     = $_SESSION['grupo'];
			$this->perm      = $_SESSION['perm'];
			$this->ativo     = $_SESSION['ativo'];
			$this->logado    = $_SESSION['logado'];
			$this->matricula = $_SESSION['matricula'];
			if (isset($_SESSION['gerente'])) $this->gerente	 = $_SESSION['gerente'];
		}
	}
	
	/**
	 * @desc efetua login e preenche os atributos da classe com os dados do usuario
	 * @return true se login foi bem sucedido, false caso contrario
	 */
	public function login($username, $senha, $bd){
		require_once('adLDAP/adLDAP.php');
		//vetor de dados do usuario
		$user = null;
		
		//se true, ignora autenticacao e prossegue com login (AD Down ou debug)
		$AD_override = false; 
		
		//modo normal de autenticacao
		if(!$AD_override){
			try {
				//inicia conexao com adLDAP
				$adldap = new adLDAP();
				//realiza autenticacao
				if($adldap->authenticate($username, $senha)){
					//verifica se o usuario existe no AD e pega os dados
					$userdataAD = $adldap->user()->info($username,array('displayname','samAccountName','sn','GivenName','userPrincipalName','telephoneNumber','mail','title','department','description','initials','AccountDisabled','enabled','useraccountcontrol','manager'));
					//pega os dados salvos no BD
					$userdataBD = $this->getUserData($username,$bd);
					//atualiza os dados do usuario no BD
					$this->updateUserData($userdataAD,$userdataBD,$bd);
					
					//pega os dados salvos no BD novamente para refletir qualquer mudanca
					$userdataBD = $this->getUserData($username,$bd);
					
					//seta array de dados
					if(isset($userdataBD[0])){
						$user['username']  = $userdataBD[0]['username'];
						$user['nome']      = $userdataBD[0]['nome'];
						$user['sobrenome'] = $userdataBD[0]['sobrenome'];
						$user['nomeCompl'] = $userdataBD[0]['nomeCompl'];
						$user['email']     = $userdataBD[0]['email'];
						$user['area']      = SGDecode(SGDecode($userdataBD[0]['area']));
						$user['cargo']     = $userdataBD[0]['cargo'];
						$user['matricula'] = $userdataBD[0]['matr'];
						$user['descr']     = $userdataBD[0]['descr'];
						$user['id']        = $userdataBD[0]['id'];
						$user['grupo']     = $userdataBD[0]['gid'];
						$user['gerente']   = $userdataBD[0]['gerente'];
						$user['perm']      = $this->getPermission($user['grupo'],$bd);
						
						// usuario esta ativo? (para mais informacoes: http://adldap.sourceforge.net/wiki/doku.php?id=api_examples)
						$ativo = (($userdataAD[0]['useraccountcontrol'][0] & 2) == 0);
						
						if ($ativo) { // usuario esta ativado no AD ?
							$user['ativo'] = true;
							$user['logado'] = true;
						}
						else { // o usuario nao esta ativado...
							$user['ativo'] = false;
							$user['logado'] = false;
						}
					}
				}
			} catch (Exception $e) {
				//se pegar excecao, loga no BD
				doLog('ERROAD', 'ERRO AD: '. $e->getMessage(), $bd);
				//mostra erro de falha de conexao com o BD
				showError(13,'login.php');
				exit();
			}
		
		//modo logar sem autenticacao (debug ou AD Down)
		} else {
			//pega os dados salvos no BD
			$userdataBD = $this->getUserData($username,$bd);
			//seta array de dados
			if(isset($userdataBD[0])){
				$user['username']  = $userdataBD[0]['username'];
				$user['nome']      = $userdataBD[0]['nome'];
				$user['sobrenome'] = $userdataBD[0]['sobrenome'];
				$user['nomeCompl'] = $userdataBD[0]['nomeCompl'];
				$user['email']     = $userdataBD[0]['email'];
				$user['area']      = $userdataBD[0]['area'];
				$user['cargo']     = $userdataBD[0]['cargo'];
				$user['matricula'] = $userdataBD[0]['matr'];
				$user['descr']     = $userdataBD[0]['descr'];
				$user['id']        = $userdataBD[0]['id'];
				$user['grupo']     = $userdataBD[0]['gid'];
				$user['gerente']   = $userdataBD[0]['gerente'];
				$user['perm']      = $this->getPermission($user['grupo'],$bd);
				$user['ativo']     = true;
				$user['logado']    = true;
				$user['ultimoLogin'] = date('d/n/Y \&\a\a\c\u\t\e\;\s h:i', $userdataBD[0]['ultimoLogin']);
			}
		}
		
		
		
		//se o usuario se logou com sucesso, loga e retorna true
		if($user['logado']){
			$_SESSION = $user;
			$_SESSION['novidades'] = true;
			doLog($_SESSION['username'],'Efetuou login.',$bd);
			
			return TRUE;
		}else{
			//senao, retorna false
			$_SESSION = $user;
			return FALSE;
		}
	}
	
	/**
	 * @desc efetua logout do usuario e encerra a sessao
	 * @return true se bem sucedido e false caso contrario
	 */
	public function logout($bd) {
		//loga que o usuario saiu
		if (isset($_SESSION['username'])) doLog($_SESSION['username'],'Efetuou logout.',$bd);
		//destroi a sessao
		session_destroy();
		//seta o usuario como null
		$this->setNull();
		//retorna o sucesso da operacao
		return TRUE;
	}
	
	/**
	 * @desc seta todas as variaveis como null e logado como false
	 * @uses $this->username, $this->nome, $this->email, $this->area, $this->cargo, $this->grupo, $this->permissoes, $this->ativo, $this->logado
	 */
	private function setNull() {
			$this->id		 = null;
			$this->username  = null;
			$this->nome      = null;
			$this->sobrenome = null;
			$this->nomeCompl = null;
			$this->email     = null;
			$this->area      = null;
			$this->cargo     = null;
			$this->matricula = null;
			$this->descr     = null;
			$this->userID    = null;
			$this->grupo     = null;
			$this->perm      = null;
			$this->ativo     = false;
			$this->logado    = false;
			$this->gerente 	 = null;
	}
	/**
	 * @desc consulta os dados do usuario no AD
	 * @param string $username
	 * @param musql_link $bd
	 */
	public function getUserData($username,$bd) {
		//seleciona o usuario
		$res = $bd->query("SELECT * FROM usuarios WHERE username = '$username'");
		return $res;
	}
	
	/**
	 * @desc atualiza os dados do usuario se houver necessidade
	 * @param array $ADdata
	 * @param uarray $BDdata
	 * @param mysql_link $bd
	 */
	public function updateUserData($ADdata,$BDdata,$bd){
		//seleciona o primeiro usuario retornado pelo AD
		$ADdata = $ADdata[0];
		//
		if (!count($BDdata)){			
			if(isset($ADdata['initials'][0])
			&& isset($ADdata['samaccountname'][0])
			&& isset($ADdata['givenname'][0]) 
			&& isset($ADdata['sn'][0]) 
			&& isset($ADdata['title'][0]) 
			&& isset($ADdata['department'][0])
			&& isset($ADdata['telephonenumber'][0])
			&& isset($ADdata['description'][0])
			&& isset($ADdata['manager'][0])
			&& isset($ADdata['mail'][0])){
				$gerente = $ADdata['manager'][0];
				$parteGerente = explode(",", $gerente);
				$parteGerente = explode("=", $parteGerente[0]);
				$gerente = $parteGerente[1];
				if (strcasecmp(SGEncode($gerente,ENT_QUOTES,'UTF-8',false), SGEncode($ADdata['samaccountname'][0],ENT_QUOTES,'UTF-8',false)) == 0)
					$gerente = "";
				$sql = "INSERT INTO usuarios (matr,username,gid,gerente,nome,sobrenome,nomeCompl,cargo,area,ramal,email,descr,ultimoLogin) VALUES ("
					."'".$ADdata['initials'][0]."',"
					."'".SGEncode($ADdata['samaccountname'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'1',"
					."'".SGEncode($gerente,ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['givenname'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['sn'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['displayname'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['title'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['department'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".SGEncode($ADdata['telephonenumber'][0],ENT_QUOTES,'UTF-8',false)."',"
					."'".$ADdata['mail'][0]."',"
					."'".SGEncode($ADdata['description'][0],ENT_QUOTES,'UTF-8',false)."',"
					.time()
					.")";
				$bd->query($sql);
				doLog($ADdata['samaccountname'][0], "Dados de usuario inseridos no BD", $bd);
			}
		} else {
			$BDdata = $BDdata[0];
			$CAtualizados = '';
			$ativo = (($ADdata['useraccountcontrol'][0] & 2) == 0);
			$ativo = ($ativo == true ? 1 : 0);
			
			if (SGEncode($ADdata['givenname'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['nome'] && $ADdata['givenname'][0] != ""){
				$bd->query("UPDATE usuarios SET nome='".SGEncode($ADdata['givenname'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Nome";
			}
			if (SGEncode($ADdata['initials'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['matr'] && $ADdata['initials'][0] != ""){
				$bd->query("UPDATE usuarios SET matr='".SGEncode($ADdata['initials'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Matr";
			}
			if (SGEncode($ADdata['sn'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['sobrenome'] && $ADdata['sn'][0] != ""){
				$bd->query("UPDATE usuarios SET sobrenome='".SGEncode($ADdata['sn'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Sobrenome";
			}
			if (SGEncode($ADdata['displayname'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['nomeCompl'] && $ADdata['displayname'][0] != ""){
				$bd->query("UPDATE usuarios SET nomeCompl='".SGEncode($ADdata['displayname'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Nome Completo";
			}
			if (SGEncode($ADdata['title'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['cargo'] && $ADdata['title'][0] != ""){
				$bd->query("UPDATE usuarios SET cargo='".SGEncode($ADdata['title'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Cargo";
			}
			if (SGEncode($ADdata['department'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['area'] && $ADdata['department'][0] != ""){
				$bd->query("UPDATE usuarios SET area='".SGEncode($ADdata['department'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Area";
			}
			if (SGEncode($ADdata['telephonenumber'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['ramal'] && $ADdata['telephonenumber'][0] != ""){
				$bd->query("UPDATE usuarios SET ramal='".SGEncode($ADdata['telephonenumber'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Ramal";
			}
			if (SGEncode($ADdata['mail'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['email'] && $ADdata['mail'][0] != ""){
				$bd->query("UPDATE usuarios SET email='".SGEncode($ADdata['mail'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Email";
			}
			if (isset($ADdata['description'][0]) && SGEncode($ADdata['description'][0],ENT_QUOTES,'UTF-8',false) != $BDdata['descr'] && $ADdata['description'][0] != ""){
				$bd->query("UPDATE usuarios SET descr='".SGEncode($ADdata['description'][0],ENT_QUOTES,'UTF-8',false)."' WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Desc";
			}
			if (isset($ADdata['manager'][0])) {
				$gerente = $ADdata['manager'][0];
				$parteGerente = explode(",", $gerente);
				$parteGerente = explode("=", $parteGerente[0]);
				$gerente = $parteGerente[1];
				$gerente = SGEncode($gerente,ENT_QUOTES,'UTF-8',false);
				if ($gerente != $BDdata['gerente'] && strcasecmp($gerente,$BDdata['username']) != 0) {
					$bd->query("UPDATE usuarios SET gerente ='".$gerente."' WHERE username = '".$BDdata['username']."'");
					$CAtualizados .= " Gerente";
				}
			}
			else {
				if ($BDdata['gerente'] != "") {
					$bd->query("UPDATE usuarios SET gerente ='' WHERE username = '".$BDdata['username']."'");
					$CAtualizados .= " Gerente";
				}
			}
			if (isset($ativo) && $ativo != $BDdata['ativo']) {
				$bd->query("UPDATE usuarios SET ativo = ".$ativo." WHERE username = '".$BDdata['username']."'");
				$CAtualizados .= " Ativo";
			}
			$bd->query("UPDATE usuarios SET ultimoLogin = ".time()." WHERE username = '".$BDdata['username']."'");
			
			if($CAtualizados)
				doLog($ADdata['samaccountname'][0], "Dados do usuario atualizados no BD:".$CAtualizados, $bd);
		}
		return true;
	}
	
	/**
	 * @desc retorna array de permissoes para o usuario
	 * @param int $gid
	 */
	function getPermission($gid = -1,$bd = null) {
		if($bd == null || $bd == NULL || !is_object($bd)) {
			global $bd;//solicitacao 004
			if($bd==NULL){
				global $conf;
				$bd = new BD();
			}
		}
		/*
		//seleciona as permissoes do grupo ao qual o usuario pertence
		$res = $bd->query("SELECT id,G$gid FROM label_acao");
		//inicializa o vetor
		$perm[0] = 0;
		//se houver o grupo no BD
		if(count($res)) {
			foreach ($res as $r) {
				//pega a permissao para a acao i
				$perm[$r['id']] = $r["G$gid"];
			}
			return $perm;
		}
		else return null;*/

		$sql = "SELECT id FROM label_acao ORDER BY id";
		$acoes = $bd->query($sql);
		
		
		if (isset($gid)) {
			$sql = "SELECT acaoID FROM permissoes WHERE grupoID = '$gid' AND permissao = 1 ORDER BY acaoID";
		}
		else {
			$sql = "SELECT gid FROM usuarios WHERE id = ".$_SESSION['id'];
			$res = $bd->query($sql);
			
			$sql = "SELECT acaoID FROM permissoes WHERE grupoID = ".$res[0]['gid']." AND permissao = 1 ORDER BY acaoID";
		}
		$permissoes = $bd->query($sql);
		
		$perm = array();
		
		foreach ($acoes as $a) {
			$perm[$a['id']] = 0;
		}
		foreach ($permissoes as $p) {
			$perm[$p['acaoID']] = 1;
		}
		return $perm;
		
	}
	
	/**
	 * Consulta dados do AD
	 * @param string $username
	 * @param string $campo
	 */
	function getADdata ($username, $campo) {
		require_once('adLDAP/adLDAP.php');
		$ad = new adLDAP();
		$dado = $ad->user()->info($username, array($campo));
		return $dado[0][$campo][0];
	}
	
	/**
	 * Le o template do perfil
	 */
	static function getUserProfileTemplate(){
		return array(
			'template' => '
				<h3>{$desc} {$nomeCompl}</h3>
				<table style="width:100%">
					<thead>
						<tr><td class="c" colspan="2"></td></tr>
					</head>
					<tbody>
						<tr class="c"><td class="c" style="width:35%"><b>Matr&iacute;cula:</b></td><td class="c">{$matricula}</td></tr>
						<tr class="c"><td class="c" style="width:35%"><b>Cargo:</b></td><td class="c">{$cargo}</td></tr>
						<tr class="c"><td class="c"><b>&Aacute;rea:</td><td class="c">{$area}</td></tr>
						<tr class="c"><td class="c"><b>Ramal:</b></td><td class="c">{$ramal}</td></tr>
						<tr class="c"><td class="c"><b>E-mail:</b></td><td class="c">{$email}</td></tr>
						<tr><td class="c" colspan="2"></td></tr>
						<tr class="c"><td class="c"><b>F&eacute;rias:</b></td><td class="c">{$ferias}</td></tr>
						<tr><td class="c" colspan="2"></td></tr>
						<tr class="c"><td class="c"><b>Diretor Imediato:</b></td><td class="c">{$diretor_nome}</td></tr>
						<tr><td class="c" colspan="2"></td></tr>
						<tr class="c"><td class="c"><b>Grupo de Permiss&otilde;es:</b></td><td class="c">{$grupo_nome}</td></tr>
						<tr><td class="c" colspan="2"></td></tr>
						<tr class="c"><td class="c"><b>&Uacute;ltimo login:</b></td><td class="c">{$ultimo_login}</td></tr>
						<tr><td class="c" colspan="2"><b>&Uacute;ltimas a&ccedil;&otilde;es:</b></td></tr>
						<tr><td class="c" colspan="2">
							<table id="user_profile_ult_desp" style="width:100%">
								<thead>
									<tr><td class="c" colspan="3"></td></tr>
									<tr class="c"><td class="c" style="width:20%"><b>Data</b></td><td class="c" style="width:30%"><b>Documento</b></td><td class="c"><b>A&ccedil;&atilde;o</b></td></tr>
								</head>
								<tbody>
									{$ultimos_desp_doc}
								</tbody>
							</table>
						</td></tr>
						<tr><td class="c" colspan="2">
							<table id="user_profile_ult_desp" style="width:100%">
								<thead>
									<tr><td class="c" colspan="3"></td></tr>
									<tr class="c"><td class="c" style="width:20%"><b>Data</b></td><td class="c" style="width:30%"><b>Empreendimento</b></td><td class="c"><b>A&ccedil;&atilde;o</b></td></tr>
								</head>
								<tbody>
									{$ultimos_desp_empreend}
								</tbody>
							</table>
						</td></tr>
						<tr><td class="c" colspan="2">
							<table id="user_profile_ult_desp" style="width:100%">
								<thead>
									<tr><td class="c" colspan="3"></td></tr>
									<tr class="c"><td class="c" style="width:20%"><b>Data</b></td><td class="c" style="width:30%"><b>Obra</b></td><td class="c"><b>A&ccedil;&atilde;o</b></td></tr>
								</head>
								<tbody>
									{$ultimos_desp_obra}
								</tbody>
							</table>
						</td></tr>
					</tbody>
				</table>
			',
			'ultimos_desp_tr' => '<tr class="c"><td class="c">{$data}</td><td class="c">{$obj_nome}</td><td class="c">{$acao}</td></tr>',
			'no_hist_tr' => '<tr class="c"><td class="c" colspan="3" style="font-weight: bold; text-align:center;">Nenhuma entrada encontrada.</td></tr>',
		
		);
	}
}

?>
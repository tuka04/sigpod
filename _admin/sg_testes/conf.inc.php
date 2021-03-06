<?php
/**
 * @version 0.1 11/2/2011 
 * @package geral
 * @author Mario Akita
 * @desc contem as variaveis de configuracao do sistema
 */

require_once ('senha.php');
$version = "1.1.1.1c";

$conf = array(

"charset" => "UTF-8",

/**
 * caminho para o arquivo de template principal do sistema, nova janela (mini) e login
 * @var string
 */

"template" => "templates/template.php",
"template_mini" => "templates/template_mini.php",
"template_login" => "templates/template_login.php",
"template_menu" => "templates/template_menu.php",

/**
  * tamanho das novas janelas (pop-ups) a serem abertas em % do tamanho máximo
  * @var int
  */
"newWindowHeight" => 0.9,
"newWindowWidth" => 0.95,

/**
  * paginas de login e logout
  * @var string
  */
"login_page" => "login.php",
"logout_page" => "logout.php",

/**
  * texto (HTML) padrao do rodape
  * @var string
  */
"title" => "SiGPOD - CPO/Unicamp - ",
"footer" => "2011. CPO/Inform&aacute;tica (v. ".$version." - 26/04/2012)",
"head" => '',

/**
  * variaveis de configuração do BD
  * @var string
  */
 
"DBLogin" => $userBD,
"DBPassword" => $senhaBD,
"DBhost" => array('master' => 'arquiteto.cpo.unicamp.br', 'slave' => 'engenheiro.cpo.unicamp.br'), //array('master' => 'master host', 'slave' => 'slave host'), tambem pode ser usado
"DBport" => 3306,
"DBTable" => $baseBD,

'debugMode' => false,

/**
  * Zona Temporal para personalização do PHP
  * @var Int
  */
'timezone' => "America/Sao_Paulo",

/**
 * variável para evitar cache dos browsers para javascript. Esta variável vai ser utilizada para a inclusão dos scripts
 * @var string 
 */
"jsversion" => $version,

/**
  * Tempo, em segundos, do timeout da sessao
  * @var int
  */
'timeout' => 36000,

/**
 * Configuracoes do Active Directory
 */

'accountSuffix' => "@cpo.unicamp.br", //Sufixo das contas nesse dominio
'baseDn' => "DC=cpo,DC=unicamp,DC=br", //Endereco base (se for NULL, o adLDAP vai tentar descobrir sozinho, o que eh mais lento)
'domainControllers' => array("arquiteto.cpo.unicamp.br"), // Array of domain controllers. Specifiy multiple controllers if you would like the class to balance the LDAP queries amongst multiple servers 
'adminUsername' => 'sistema-gerencial', //Username de uma conta com privilegios de administrador para realizar as consultas 
'adminPassword' => $senhaAD, //Senha para o username configurado acima
'realPrimaryGroup' => true, //Necessario quando o grupo primario dos usuarios nao eh "Usuarios do Dominio"
'useSSL' => false, //Usar camada de seguranca SSL
'useTLS' => false, //Usar camada de seguranca TLS (eh possivel usar apenas SSL OU TLS e NAO OS DOIS AO MESMO TEMPO!)
'useSSO' => false, //Ativar reuso de senhas
'recursiveGroups' => true //Ativar deteccao recursiva de grupos (para grupos dentro de outros grupos)
 );
 
 ?>
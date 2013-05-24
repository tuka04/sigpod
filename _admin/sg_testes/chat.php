<?php

/*

Copyright (c) 2009 Anant Garg (anantgarg.com | inscripts.com)

This script may be used for non-commercial purposes only. For any
commercial purposes, please contact the author at 
anant.garg@inscripts.com

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

*/

require_once('conf.inc.php');

define ('DBPATH', $conf['DBhost']['master'].':'.$conf['DBport']);
define ('DBUSER', $conf['DBLogin']);
define ('DBPASS', $conf['DBPassword']);
define ('DBNAME', $conf['DBTable']);

date_default_timezone_set($conf['timezone']);

/* funções para definir tempo da sessão */
session_set_cookie_params(36000);
session_cache_expire(600); // em minutos -> 10 h
ini_set('session.gc_maxlifetime', 36000);
/* /funções para definir tempo da sessão */

session_start();

// não está logado ou a sessão expirou
if (!isset($_SESSION['username']))
	return;

$_SESSION['ultimaModificacao'] = time();
	
global $dbh;
$dbh = mysql_connect(DBPATH,DBUSER,DBPASS);
mysql_selectdb(DBNAME,$dbh);

if ($_GET['action'] == "chatheartbeat") { chatHeartbeat(); } 
if ($_GET['action'] == "sendchat") { sendChat(); } 
if ($_GET['action'] == "closechat") { closeChat(); } 
if ($_GET['action'] == "startchatsession") { startChatSession(); } 
if ($_GET['action'] == "ausente" && isset($_GET['ausente'])) { ausente($_GET['ausente']); }
if ($_GET['action'] == "updateList") { ListaChat(); }

if (!isset($_SESSION['chatHistory'])) {
	$_SESSION['chatHistory'] = array();	
}

if (!isset($_SESSION['openChatBoxes'])) {
	$_SESSION['openChatBoxes'] = array();	
}

function chatHeartbeat() {
	
	$sql = "select * from chat where (chat.to = '".mysql_real_escape_string($_SESSION['username'])."' AND (recd = 0 OR (sent - 0) > (NOW() - 1))) order by id ASC";
	$query = mysql_query($sql);
	$items = '';
	
	$novaMensagem = false;

	$chatBoxes = array();
	
	while ($chat = mysql_fetch_array($query)) {

		if (!isset($_SESSION['openChatBoxes'][$chat['from']]) && isset($_SESSION['chatHistory'][$chat['from']])) {
			$items = $_SESSION['chatHistory'][$chat['from']];
		}

		$chat['message'] = sanitize($chat['message']);
		
		$usuario = mysql_query("SELECT * FROM usuarios WHERE username = '".$chat['from']."'");
		$usuario = mysql_fetch_array($usuario);
		
		if ($usuario['nomeCompl'] == null && $chat['from'] == 'SiGPOD')
			$usuario['nomeCompl'] = 'SiGPOD';

		$novaMensagem = true;
			
		$items .= <<<EOD
					   {
			"s": "0",
			"f": "{$chat['from']}",
			"m": "{$chat['message']}",
			"n": "{$usuario['nomeCompl']}"
	   },
EOD;

	if (!isset($_SESSION['chatHistory'][$chat['from']])) {
		$_SESSION['chatHistory'][$chat['from']] = '';
	}

	$_SESSION['chatHistory'][$chat['from']] .= <<<EOD
						   {
			"s": "0",
			"f": "{$chat['from']}",
			"m": "{$chat['message']}",
			"n": "{$usuario['nomeCompl']}"
	   },
EOD;
		
		unset($_SESSION['tsChatBoxes'][$chat['from']]);
		$_SESSION['openChatBoxes'][$chat['from']] = $chat['sent'];
	}

	if (!empty($_SESSION['openChatBoxes'])) {
	foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
		if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
			$now = time()-strtotime($time);
			$time = date('H:i - d/m/Y', strtotime($time));

			$message = "Enviado &agrave;s $time";
			if ($now > 180) {
				$usuario = mysql_query("SELECT * FROM usuarios WHERE username = '".$chatbox."'");
				$usuario = mysql_fetch_array($usuario);
				
				if ($usuario['nomeCompl'] == null && $chatbox == 'SiGPOD')
					$usuario['nomeCompl'] = 'SiGPOD';
				$items .= <<<EOD
{
"s": "2",
"f": "$chatbox",
"m": "{$message}",
"n": "{$usuario['nomeCompl']}"
},
EOD;

	if (!isset($_SESSION['chatHistory'][$chatbox])) {
		$_SESSION['chatHistory'][$chatbox] = '';
	}

	$_SESSION['chatHistory'][$chatbox] .= <<<EOD
		{
"s": "2",
"f": "$chatbox",
"m": "{$message}",
"n": "{$usuario['nomeCompl']}"
},
EOD;
			$_SESSION['tsChatBoxes'][$chatbox] = 1;
		}
		}
	}
}

	if ($novaMensagem) {
		$sql = "update chat set recd = 1 where chat.to = '".mysql_real_escape_string($_SESSION['username'])."' and recd = 0";
		$query = mysql_query($sql);
	}

	if ($items != '') {
		$items = substr($items, 0, -1);
	}
header('Content-type: application/json');
?>
{
		"items": [
			<?php echo $items;?>
        ]
}

<?php
			exit(0);
}

function chatBoxSession($chatbox) {
	
	$items = '';
	
	if (isset($_SESSION['chatHistory'][$chatbox])) {
		$items = $_SESSION['chatHistory'][$chatbox];
	}

	return $items;
}

function startChatSession() {
	$items = '';
	if (!empty($_SESSION['openChatBoxes'])) {
		foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
			$items .= chatBoxSession($chatbox);
		}
	}


	if ($items != '') {
		$items = substr($items, 0, -1);
	}

header('Content-type: application/json');
?>
{
		"username": "<?php echo $_SESSION['username'];?>",
		"items": [
			<?php echo $items;?>
        ]
}

<?php


	exit(0);
}

function sendChat() {
	$from = $_SESSION['username'];
	$to = $_POST['to'];
	$message = $_POST['message'];

	$_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());
	
	$messagesan = sanitize($message);

	if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
		$_SESSION['chatHistory'][$_POST['to']] = '';
	}

	$_SESSION['chatHistory'][$_POST['to']] .= <<<EOD
					   {
			"s": "1",
			"f": "{$to}",
			"m": "{$messagesan}"
	   },
EOD;


	unset($_SESSION['tsChatBoxes'][$_POST['to']]);

	$sql = "insert into chat (chat.from,chat.to,message,sent) values ('".mysql_real_escape_string($from)."', '".mysql_real_escape_string($to)."','".mysql_real_escape_string($message)."',NOW())";
	$query = mysql_query($sql);
	echo "1";
	exit(0);
}

function closeChat() {

	unset($_SESSION['openChatBoxes'][$_POST['chatbox']]);
	
	echo "1";
	exit(0);
}

function sanitize($text) {
	//$text = urldecode($text);
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = str_replace("\n\r","\n",$text);
	$text = str_replace("\r\n","\n",$text);
	$text = str_replace("\n","<br>",$text);
	return $text;
}

/**
 * função que marca usuário como ausente ou não
 * @param bool $status (true = usuario ausente, false = usuario não ausente)
 */
function ausente($status) {
	if (!isset($_SESSION['username'])) return;
	
	$sql = "SELECT * FROM chat_status WHERE username = '".$_SESSION['username']."'";
	$entrada = mysql_query($sql);
	
	if ($status == 'true') { // está ausente
		//$sql = "UPDATE usuarios SET ausente = 1 WHERE username = '".$_SESSION['username']."'";
		//mysql_query($sql);
		if (mysql_num_rows($entrada) <= 0) {
			$sql = "INSERT INTO chat_status (username, estado, data) VALUES ('".$_SESSION['username']."', 1, ".time().")";
			mysql_query($sql);
		}
		else {
			$sql = "UPDATE chat_status SET estado = 1, data = ".time()." WHERE username = '".$_SESSION['username']."'";
			mysql_query($sql);
		}
		
	}
	else {
		//$sql = "UPDATE usuarios SET ausente = 0 WHERE username = '".$_SESSION['username']."'";
		//mysql_query($sql);
		if (mysql_num_rows($entrada) <= 0) {
			$sql = "INSERT INTO chat_status (username, estado, data) VALUES ('".$_SESSION['username']."', 0, ".time().")";
			mysql_query($sql);
		}
		else {
			$sql = "UPDATE chat_status SET estado = 0, data = ".time()." WHERE username = '".$_SESSION['username']."'";
			mysql_query($sql);
		}
	}
}

function ListaChat() {
	//global $bd;
	$alguemOn = false;	
	
	$html = '<table style="width: 100%">';
	
	$sql = "SELECT u.username, u.nomeCompl, c.data, c.estado
			FROM usuarios AS u INNER JOIN chat_status AS c ON u.username = c.username
			WHERE u.username <> '".$_SESSION['username']."' AND u.ativo > 0
			AND ((c.estado = 1 AND data > ".(time() - 10*60).") OR (c.estado = 0 AND data > ".(time() - 5*60)."))
			ORDER BY estado ASC, nomeCompl ASC";
	//$res = $bd->query($sql);
	$res = mysql_query($sql);
	while ($r = mysql_fetch_array($res)) {
		if ($r['username'] == "acompanhamento-tec" || $r['username'] == "passivo-do") continue;
		$alguemOn = true;
		$estiloLink = '';
		if ($r['estado'] == "") {
			$img = '<img id="chatStatImg_'.$r['username'].'" src="img/off.png">';
			$stat = "off";
			$estiloLink = 'style="color: #000000;"';
		}
		elseif ($r['estado'] == 1 && $r['data'] <= (time() - 10*60)) {
			$img = '<img id="chatStatImg_'.$r['username'].'" src="img/off.png">';
			$stat = "off";
			$estiloLink = 'style="color: #000000;"';
		}
		elseif ($r['estado'] == 1 && ($r['data'] > (time() - 10*60))) {
			$img = '<img id="chatStatImg_'.$r['username'].'" src="img/afk.png">';
			$stat = "afk";
		}
		elseif ($r['estado'] == 0 && $r['data'] <= (time() - 5*60)) {
			$img = '<img id="chatStatImg_'.$r['username'].'" src="img/off.png">';
			$stat = "off";
			$estiloLink = 'style="color: #000000;"';
		}
		else {
			$img = '<img id="chatStatImg_'.$r['username'].'" src="img/on.png">';
			$stat = "on";
		}

		$img .= '<input type="hidden" id="chatStat_'.$r['username'].'" value="'.$stat.'">';
		$html .= '<tr><td width="20" style="vertical-align: middle">'.$img.'</td>
		<td><a href="javascript:void(0)" onclick="javascript:chatWith(\''.$r['username'].'\', \''.$r['nomeCompl'].'\')" id="chatUser_'.$r['username'].'" '.$estiloLink.'>'.$r['nomeCompl'].'</a></td></tr>';
	}
		
	$sql = "SELECT u.username, u.nomeCompl, c.data, c.estado
			FROM usuarios AS u LEFT JOIN chat_status AS c ON u.username = c.username
			WHERE u.username <> '".$_SESSION['username']."' AND c.username IS NULL AND u.ativo = 1
			OR NOT ((c.estado = 1 AND data > ".(time() - 10*60).") OR (c.estado = 0 AND data > ".(time() - 5*60).") OR u.username = '".$_SESSION['username']."' OR u.ativo = 0)
			ORDER BY nomeCompl ASC";
	$res = mysql_query($sql);
	while ($r = mysql_fetch_array($res)) {
		if ($r['username'] == "acompanhamento-tec" || $r['username'] == "passivo-do") continue;
		$alguemOn = true;
		$img = '<img id="chatStatImg_'.$r['username'].'" src="img/off.png">';
		$stat = "off";
		$estiloLink = 'style="color: #000000;"';

		$img .= '<input type="hidden" id="chatStat_'.$r['username'].'" value="'.$stat.'">';
		$html .= '<tr><td width="20" style="vertical-align: middle">'.$img.'</td>
		<td><a href="javascript:void(0)" onclick="javascript:chatWith(\''.$r['username'].'\', \''.$r['nomeCompl'].'\')" id="chatUser_'.$r['username'].'" '.$estiloLink.'>'.$r['nomeCompl'].'</a></td></tr>';
	}
		
	$html .= "</table>";
		
	if ($alguemOn == false)
		$html = 'Ningu&eacute;m dispon&iacute;vel.';

	print json_encode($html);
}
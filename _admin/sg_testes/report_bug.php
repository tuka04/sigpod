<?php
	/**
	 * @version 0.1 29/4/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina para usuarios relatarem erros no sistema. 
	 */

	include_once('includeAll.php');
	include_once('sgd_modules.php');
	
	//inicia conexao com o banco de dados
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	//verifica se o usuario esta logado
	checkLogin(6);
	
	//cria uma nova pagina HTML
	$html = new html($conf);
	//seta o texto de cabecalho da pagina
	$html->header = "Reportar Erro";
	//gera o codigo de tela para esta pagina, titulo, nome de usuario, caminho e menu
	$html->setTemplate($conf["template"]);
	$html->title .= "Reportar Erro";
	$html->user = $_SESSION['nomeCompl'];
	$html->path = showNavBar(array(array("url" => "","name" => "Reportar Erros/Sugest&otilde;es")));
	$html->menu = showMenu($conf['template_menu'],$_SESSION["perm"],2310,$bd);
	$html->campos['codPag'] = showCodTela();
	//adiciona tag de script
	$html->content[1] = '<script type="text/javascript" src="scripts/reportbug.js?r={$randNum}"></script>';
	
	if(isset($_GET['acao']) && $_GET['acao'] == 'enviar'){
		//realizar o envio de dados do formulario
		$html->content[1] .= enviarBugReport($_POST, $bd);
		
	}elseif(isset($_GET['acao']) && $_GET['acao'] == 'ver'){
		//ver bug report
		$html->content[1] .= verTabBugs($bd);
	}elseif (isset($_GET['acao']) && $_GET['acao'] == 'editar'){	
		//salvar modificacoes
		if(!isset($_GET['estado']) || !isset($_GET['descr']) || !isset($_GET['id']))
			exit();
		
		$r = salvaEdicao($bd);
			
		if ($r)//se a atualizacao foi bem sucedida imprime true
			print "true";
		else//senao, imprime false
			print "false";
		
		//evita que imprima o resto
		exit();
	}else{
		//cadastro
		$html->content[1] .= montaBugForm();
		$html->content[1] .= verTabBugs($bd);
		
	}
	
	$html->showPage();
	$bd->disconnect();
	
	function montaBugForm(){
		global $conf;
		if(isset($_SERVER['HTTP_REFERER'])){//se o usuario tiver vindo de um alguma pagina
			$codAnterior = showCodTela($_SERVER['HTTP_REFERER']);//gera codigo da tela anterior
		} else {//se nao houver pagina anterior
			$codAnterior = ' ';
		}
		//gera o codigo html do formulario
		$html = '
		<form accept-charset="'.$conf['charset'].'" action="report_bug.php?acao=enviar" method="post">
		<table border=0 cellpadding=0 cellspacing=0 width="100%">
		<tbody>
		  <tr class="c"><td width="250px"><b>Usu&aacute;rio:</b></td><td><input type="text" name="username" value="'.$_SESSION['username'].'" disabled="disabled" size=20></td></tr>
		  <tr class="c"><td><b>C&oacutedigo da tela com problemas:</b></td><td><input type="text" size=2 id="cod1" name="cod1" maxlength="3" value="'.substr($codAnterior,6,3).'" />.<input type="text" size=2 id="cod2" name="cod2" maxlength=2 value="'.substr($codAnterior,10,2).'" />.<input type="text" size=2 id="cod3" maxlength=5 name="cod3" value="'.substr($codAnterior,13,5).'" /></td></tr>
		  <tr class="c"><td><b>Descri&ccedil;&atilde;o do erro obtido:</b></td><td><textarea name="descricao" rows=5 cols=50></textarea></td></tr>
		  <tr><td colspan=2 style="text-align: center;"><input type="submit" value="Enviar"></td></tr>
		  <tr class="c"><td><b></b></td></td></td></tr>
		  <tr class="c"><td><b></b></td></td></td></tr>
		</tbody>
		</table>
		</form>';
		
		return $html;
	}
	
	function verTabBugs($bd){
		$html = '<br/><span class="header">Erros j&aacute; reportados:</span><br/>';
		//gera o cabecalho da tabela
		$html .= '<table border=0 cellpadding=0 cellspacing=0 width="100%"><tbody>
		<tr>
		<td class="cc" width="250px" ></td>
		<td class="c"  ></td>
		<td class="c"  width="75px" ></td></tr>';
		//seleciona todos as entradas da tabela de bugs
		$res = $bd->query("SELECT * FROM bug ORDER BY dataReceb DESC");
		//cria uma linha da tabela pra cada entrada
		foreach ($res as $r) {
			if (mb_detect_encoding($r['descricao'], "iso-8859-1,utf-8") != "utf-8") {
				$r['descricao'] = mb_convert_encoding($r['descricao'], "utf-8", "iso-8859-1");
			}
			
			if($r['dataConserto'] == 0) $r['dataConserto'] = "-";
			else $r['dataConserto'] =  date("d/m/Y H:i",$r['dataConserto']);
			$html .= '<tr><td><a name="l'.($r['id']-1).'"></a></td></tr>
			<tr class="c">
			<td class="c" id="id'.$r['id'].'" style="vertical-align:middle;">
			<b>ID:</b> '.$r['id'].'<br />
			<b>Se&ccedil;&atilde;o:</b> '.$r['secao'].'<br />
			<b>Estado:</b> <div id="es'.$r['id'].'" style="display:inline;">'.trataEstado($r['estado']).'</div><br />
			<b>Data de Finaliza&ccedil;&atilde;o:</b> <div id="dc'.$r['id'].'" style="display:inline;">'.$r['dataConserto'].'</div>
			</td>
			<td class="c"  id="de'.$r['id'].'" >'.$r['descricao'].'</td>';
			if($_SESSION['perm'][20]){
				$html .= '<td class="c" id="ad'.$r['id'].'" style="vertical-align:middle;"><a href="#l'.$r['id'].'" onclick="editarBug('.$r['id'].')">Editar</a></td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}
	
	/**
	 * Recebe uma string de estado e a converte para o modo 'amigavel'
	 * @param string $estado
	 * @return string amigavel
	 */
	function trataEstado($estado){
		switch ($estado) {
			case 'receb':	return "Recebido";
							break;
		 	case 'anali':	return "Analisando";
							break;
			case 'final':	return "Finalizado" ;
							break;
			
				 default:	return "Desconhecido";
							break;
		}
	}
	
	/**
	 * Faz a insercao dos dados recebidos dentro do BD de bugs mantendo a descricao anterior
	 * @param array $dados
	 * @param mysql link $bd
	 * @return true caso tenha sucesso, false caso contrario
	 */
	function enviarBugReport($dados,$bd){
		//monta mensagem original
		$descr = '<i>Mensagem Original em '.date('d/m/Y H:i').' por '.$_SESSION['username'].':</i><br />'.SGEncode($dados['descricao'],ENT_QUOTES, null, false);
		
		//monta a string para consulta sql a ser executada (insercao)
		$sql = "INSERT INTO bug (dataReceb,username,secao,descricao,estado,dataConserto)
		 VALUES (".time().",'".$_SESSION['username']."','".$dados['cod1'].".".$dados['cod2'].".".$dados['cod3']."','".$descr."','receb',0)";
		//executa efetivamente a consulta
		$res = $bd->query($sql);
		
		//caso obtenha sucesso, monta mensagem de feedback
		if($res){
			return '<center><b>Seu relato foi salvo com sucesso!</b></center>
			<br /><br /><a href="report_bug.php?acao=ver">Ver todos os erros j&aacute; relatados</a>
			<br /><a href="report_bug.php">Relatar outro erro</a>';
		} else {
			//caso contrario, comunica o erro
			return "<center><b>Erro ao salvar os dados! Por favor, contate o administrador do sistema.</b></center>";
		}
	}
	
	/**
	 * Salva o conteudo da edicao no BD
	 * @param mysql_link $bd
	 * @return true caso atualizacao seja bem sucedida, false caso contrario
	 */
	function salvaEdicao($bd){
		//converte o nome amigavel em cod do estado
		$abrev = array("Recebido" => "receb", "Analisando" => "anali" , "Finalizado" => "final");
		$estado = $abrev[$_GET['estado']];
		
		//se estado for final, coloca a hora de finalizacao
		if ($estado == 'final')
			$dataFinal= time();
		else
			$dataFinal = 0;
		
		//executa atualizacao no BD
		$r = $bd->query("UPDATE bug SET estado='".$estado."', descricao=CONCAT(descricao,'<br /><br /><i>Adicionado em ".date('d/m/Y H:i')." por ".$_SESSION['username'].":</i><br />".$_GET['descr']."'), dataConserto='".$dataFinal."' WHERE id=".$_GET['id']);
		
		return $r;
	}
?>
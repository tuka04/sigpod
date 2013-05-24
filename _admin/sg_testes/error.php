<?php
	/**
	 * @version 0.1 16/2/2011 
	 * @package geral
	 * @author Mario Akita
	 * @desc pagina que lida com erros do sistema
	 */

	/**
	 * @desc Mostra um alert na tela informando erro de sistema e redireciona
	 * @param int $cod Codigo de erro
	 * @param string $redir Endereco de redirecionamento (se houver)
	 */
	function showError($cod, $redir = "index.php"){
		$erro[0] = "";
		$erro[1] = "Erro ao efetuar login. Por favor, tente novamente."; //login.php
		$erro[2] = "Erro ao conectar-se com o banco de dados."; //classes/BD.php
		$erro[3] = "Erro ao selecionar base de dados"; //classes/BD.php
		$erro[4] = "Erro ao efetuar logout. Por favor, tente novamente."; //logout.php
		$erro[5] = "Erro ao ler os dados do documento."; //Documento.php
		$erro[6] = "Você deve estar logado para visualizar a página. Por favor, efetue o login no sistema para prosseguir."; //modules.php
		$erro[7] = "Erro ao selecionar documento para visualização.";
		$erro[9] = "Nao foi possivel realizar a busca.";
		$erro[10]= "Erro. Você não tem permissão para realizar esta ação";
		$erro[11]= "Erro. Não há dados suficientes pra realizar esta ação.";
		$erro[12]= "Erro. Este usuário não tem privilégios suficentes para realizar esta operação.";
		$erro[13]= "Erro ao se conectar ao Active Directory. Contate o administrador de redes.";
		$erro[14]= "Erro. Há dados que provavelmente foram perdidos.";
		$erro[15]= "Erro ao ler permissão. Efetue o login e tente novamente";
		$erro[16]= "Erro. Usuario e senha invalidos.";
		$erro[17]= "Erro. Conta de usuario desativada.";
		
		if(!isset($_SESSION['id'])) {
			$id = 0;
		} else {
			$id = $_SESSION['id'];
		}
		
		if($cod == -1) {
			header("Location: ".$redir);
			exit();
		} elseif($cod != 0 && $cod != 2 && $cod != 3) {
			doLog($id, 'Obteve Erro '.$cod.': '.$erro[$cod].' ao acessar '.$_SERVER['REQUEST_URI']);
		
			if (strpos($redir, "?") === FALSE)
				header("Location: ".$redir."?alert=".$erro[$cod]);
			else
				header("Location: ".$redir."&alert=".$erro[$cod]);
				
			exit();
		}
}
?>
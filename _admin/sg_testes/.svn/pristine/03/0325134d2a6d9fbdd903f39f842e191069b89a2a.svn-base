function mostrarAjuda(secao){
	//criacao da estrutura basica de uma janela de ajuda
	//quadro sobreposto, em formato barra lateral, a direita
	
	//criacao do div
	$("#container").append('<div id="ajuda"></div>');
	
	//get do conteudo em ajax referente a pagina
	$.get("ajuda.php?acao=geraMini&secao="+secao,function(html){
		$("#ajuda").html(html);
		
		
	});
	
	//mostra a janela
	$("#ajuda").slideDown();
}

//gera funcao para fechar a barra de ajuda
function fechaAjuda(){
	$("#ajuda").slideUp();
}
/*$(document).ready(function(){
	$("#closeNovidadesBtn").click()
});
*/

function closeNovidades(){
	var desativar = false;
	
	if($("#closeNovidadesCbx").attr('checked') == 'checked') {
		$.get('index.php', {
			acao: 'desativa_novidades'
		}, function(d){
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(!d[0].success) alert("Erro ao desativar novidades. Elas continuarão a aparecer na sua página inicial até o próximo login");
		} );
		$("#news").slideUp('slow');
	} else {
		$("#news").slideUp('slow');
	}
}
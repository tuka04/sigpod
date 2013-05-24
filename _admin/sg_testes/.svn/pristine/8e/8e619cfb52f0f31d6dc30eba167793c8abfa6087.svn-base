$(document).ready(function(){
	$("#emitente_pessoa").hide();
	
	$("#emitente_deptos").change(function(){
		if($("#emitente_user").attr("selected")){
			var campo = $("#emitente_campo").val();
			$("#"+campo).val($("#emitente_user").val());
			$("#emitente_pessoa").hide();
		}else{
			loadNomes($("#emitente_deptos option:selected").val());
		}
	});
	
	$("#emitente_pessoa").change(function() {
		var campo = $("#emitente_campo").val();
		$("#"+campo).val($("#emitente_pessoa option:selected").val());
	});
});

function loadNomes(area){//completa os nomes dos funcionarios de um depto CPO
	$("#emitente_pessoa").html("");
	$.get("unSearch.php?show=pessoas&area="+escape(area),function(d){
		//var data = eval(d);
		try {
			data = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		var i = 0;
		var selected = "";
		var campo = $("#emitente_campo").val();
		$.each(data,function(){
			if (i == 0) { 
				selected = "selected";
				$("#"+campo).val(data[i].id);
			}
			else selected = "";
			$("#emitente_pessoa").append('<option id="'+data[i].id+'" name="'+data[i].id+'" value="'+data[i].id+'" '+selected+'>'+data[i].nome+'</option>');
			i++;
		});
	});
	
	
	$("#emitente_pessoa").show();
}
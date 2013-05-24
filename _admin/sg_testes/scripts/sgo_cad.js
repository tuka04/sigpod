

$(document).ready(function(){
	/*$("#unOrgInput").autocomplete(
		"unSearch.php",{
			minChars:2,
			matchSubset:1,
			matchContains:true,
			maxCacheLength:20,
			extraParams:{'show':'un'},
			selectFirst:true,
			onItemSelect: function(){
				$("#unOrgInput").focus();
				//alert($("#unOrgInput").val());
				$("#unOrgSolic").val($("#unOrgInput").val());
				$("#unOrgSolic").removeClass("ERRADO");
				$("#unOrgInput").css("background-color","#DDFFDD");
				$("#unOrgInput").removeClass("ERRADO");
				
			}
		}
	);*/
	// auto-complete
	$("#unOrgInput").autocomplete({
		source: function(request, response) { 
			$.get("unSearch.php", {
				q: request.term,
				show: "un"
			}, function(data) {
				//data = eval(data);
				try {
					data = eval(data);
				} catch(e) {
					if (e instanceof SyntaxError) {
						alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
					}
				}
				
				response(data);
			});
		},
		minLength: 2,
		autoFocus: true,
		select: function() {
			$("#unOrgInput").focus();
			$("#unOrgSolic").val($("#unOrgInput").val());
			$("#unOrgSolic").removeClass("ERRADO");
			$("#unOrgInput").css("background-color","#DDFFDD");
			$("#unOrgInput").removeClass("ERRADO");
		},
		change: function(){
			//$("#unOrgInput").focus();
			$(":input:eq(" + ($(":input").index(this) + 1) + ")").focus();
			$("#unOrgSolic").val($("#unOrgInput").val());
			$("#unOrgSolic").removeClass("ERRADO");
			$("#unOrgInput").css("background-color","#DDFFDD");
			$("#unOrgInput").removeClass("ERRADO");
		}
	});
	
	
	$("#unOrgInput").keyup(function(e) {
		if (e.keyCode == 13 || e.keyCode == 9) return; // tab ou enter
		$("#unOrgInput").css("background-color","#FFDDDD");
		$("#unOrgInput").addClass("ERRADO");
		$("#unOrgSolic").val("");
		$("#unOrgSolic").addClass("ERRADO");
			
		v = $("#unOrgInput").val();
		
		v = v.replace(/\./g,""); 
		
		var expReg  = /^[0-9]{2,12}$/i;
		
		if (expReg.test(v)){
			var i, vn="";
			for(i=0 ; i<v.length ; i++){
				if(i%2 == 0 && i != 0)
					vn += ".";
				vn += v[i];
			}				
			$("#unOrgInput").val(vn);
		}
	});
	
	$("#unOrgSolic").change(function() {
		if ($("#unOrgSolic").val() != "") {
			$("#unOrgInput").val($("#unOrgSolic").val());
		}
	});
	
	$("#nome").keyup(function(e){
		sugereObra();
	});
	
	$("input").focusout(function(e){
		validateFields(e.currentTarget);
	});
	
	$("textarea").focusout(function(e){
		validateFields(e.currentTarget);
	});
	
	$("#tipo").click(function(e){
		if(e.currentTarget.value != "") {
			$("#"+e.currentTarget.id).css("background-color","#DDFFDD");
		}else {
			$("#"+e.currentTarget.id).css("background-color","#FFDDDD");
		}
	});
	
	$("#tipo").click(function(e){
		if(e.currentTarget.value != "") {
			$("#"+e.currentTarget.id).css("background-color","#DDFFDD");
		}else {
			$("#"+e.currentTarget.id).css("background-color","#FFDDDD");
		}
	});
	
	$("#caract").focusout(function(e){
		if(e.currentTarget.value != "") {
			$("#"+e.currentTarget.id).css("background-color","#DDFFDD");
		}else {
			$("#"+e.currentTarget.id).css("background-color","#FFDDDD");
		}
	});
	
	$("#caract").focusout(function(e){
		if(e.currentTarget.value != "") {
			$("#"+e.currentTarget.id).css("background-color","#DDFFDD");
		}else {
			$("#"+e.currentTarget.id).css("background-color","#FFDDDD");
		}
	});
	
	$("#recursos1").click(function(){
		enableRecursoForm(true);
	});
	
	$("#recursos0").click(function(){
		enableRecursoForm(false);
	});
	
	$("#cadNovaObra").submit(function(event){
		validateFields(null);
		verificaFormObra(event);
	});
	
	
	
	validateFields(null);
});

function validateFields (target) {
	var color = 'white';
	if (target != null) { //verif de apenas 1 elemento
		if(target.value != "") {
			if (target.id == 'unOrgInput') return;
			var int = /[a-zA-Z.,]/i;
			var float = /[a-zA-Z]/i;
			var unOrg = /^[^0-9.]{6}/i;
			if ($("#"+target.id).hasClass("int") && int.test($("#"+target.id).val())) {//se eh int e tem letra
				color = 'red';
			} else if($("#"+target.id).hasClass("float") && float.test($("#"+target.id).val())) {//se eh int e tem letra
				color = 'red';
			} else if(target.id == 'unOrgSolic' && !unOrg.test($("#unOrg").val())) {
				color = 'red';
			
			}
			else if (target.id == 'unOrgSolic') {
				if ($("#unOrgSolic").val != "") {
					color = 'green';
					$("#unOrgInput").css("background-color","#DDFFDD");
					$("#unOrgInput").removeClass("ERRADO");
				}
				else {
					$("#unOrgInput").css("background-color","#FFDDDD");
					$("#unOrgInput").addClass("ERRADO");
					color = 'red';
				}
				//var unOrgPartes = $("#unOrgSolic").val().split("-");
				
				/*if (unOrgPartes.length < 2) {
					color = 'red';
				}
				else {
					if (unOrgPartes[unOrgPartes.length-1].indexOf('(') == -1 && unOrgPartes[unOrgPartes.length-1].indexOf(')') == -1) {
						color = 'red';
					}
					else
						color = 'green';
				}*/
			}
			else {
				color = 'green';
			}
			
		} else if(target.className.match("obrigatorio") != null) {
			color = 'red';
		}
		
		//atribui background
		if(color == 'green') {
			$("#"+target.id).css("background-color","#DDFFDD");
			$("#"+target.id).removeClass("ERRADO");
		} else if(color == 'red') {
			$("#"+target.id).css("background-color","#FFDDDD");
			$("#"+target.id).addClass("ERRADO");
		} else {
			$("#"+target.id).css("background-color","white");
			$("#"+target.id).removeClass("ERRADO");
		}

	} else {//verif de varios elementos - chama a funcao para cada elemento do form recursivamente
		var inputs = $('input[type="text"]');
		
		$.each(inputs,function(i){
			if(!inputs[i].disabled)
				validateFields(inputs[i]);
		});
		
		if ($("#unOrgInput").val() == "") {
			$("#unOrgInput").css("background-color","#FFDDDD");
			$("#unOrgInput").addClass("ERRADO");
		}
		
		if($("#tipo").val() == ''){
			$("#tipo").css("background-color","#FFDDDD");
			$("#tipo").addClass("ERRADO");
		} else {
			$("#tipo").css("background-color","#DDFFDD");
			$("#tipo").removeClass("ERRADO");
		}
		
		if($("#caract").val() == ''){
			$("#caract").css("background-color","#FFDDDD");
			$("#caract").addClass("ERRADO");
		} else {
			$("#caract").css("background-color","#DDFFDD");
			$("#caract").removeClass("ERRADO");
		}
		
		if($("#descricao").html() == '') {
			$("#descricao").css("background-color","white");
		} else {
			$("#descricao").css("background-color","#DDFFDD");
		}
		
		
	}
}	
	

function verificaFormObra(event){
	var vazios = $(".obrigatorio").filter(function() { return $(this).val() == ""; });
	var errados = $('.ERRADO');
	
	validateFields(null);
	
	//alert(vazios.length)
	//alert(errados.length)
	
	/*$.each(errados, function() {
		alert($(this).attr("id"));
	});*/
	
	if(vazios.length > 0 || errados.length > 0) {
		alert("Erro. Verifique se todos os campos obrigatórios estão preenchidos e não há dados inválidos digitados (ex: letras na área ou no montante de recursos)");
		validateFields(null);
		event.preventDefault();
		return;
	}
	
	
	
	//alert("continua");
}

function enableRecursoForm(ativar){
	if(ativar){
		$("#montanteRec").removeAttr("disabled");
		$("#origemRec").removeAttr("disabled");
		$("#prazoRec").removeAttr("disabled");
	} else {
		$("#montanteRec").attr("disabled","disabled");
		$("#origemRec").attr("disabled","disabled");
		$("#prazoRec").attr("disabled","disabled");
	}
}




function validateDoc(target){

	if(target == 'ofir') {
		$("#unOrgSolic").val('');
		$("#unOrgInput").val('');
		$("#nomeSolic").val('');
		$("#deptoSolic").val('');
		$("#emailSolic").val('');
		$("#ramalSolic").val('');
		$("#unOrgSolic").css("background-color","#FFDDDD");
		$("#unOrgInput").css("background-color","#FFDDDD");
		$("#nomeSolic").css("background-color","white");
		$("#deptoSolic").css("background-color","white");
		$("#emailSolic").css("background-color","white");
		$("#ramalSolic").css("background-color","white");
		
		if($("#ofir").val().match(",") != null){
			$("#passo1").css("background-color","#FFDDDD");
			$("#caixaAviso").html('Erro! Selecione apenas um documento.');
			$("#caixaAviso").show();
		} else {
			$.get('sgd_busca.php',{
				tipoBusca:    "numCPO", 
				docID:       $("#ofir").val()
			}, function(data){
				//var doc = eval(data);
				try {
					doc = eval(data);
				} catch(e) {
					if (e instanceof SyntaxError) {
						alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
					}
				}
				
				if(doc[0].tipo.nomeAbrv != 'ofe') {
					$("#passo1").css("background-color","#FFDDDD");
					$("#caixaAviso").html('Erro! O documento deve ser um oficio.');
					$("#caixaAviso").show();
				} else {
					$("#passo1").css("background-color","#DDFFDD");
					$("#caixaAviso").html('');
					$("#caixaAviso").hide();
					
					if(doc[0].unOrg) {
						$("#unOrgSolic").val(doc[0].unOrg);
						$("#unOrgInput").val(doc[0].unOrg);
						$("#unOrgSolic").css("background-color","#DDFFDD");
						$("#unOrgInput").css("background-color","#DDFFDD");
						$("#unOrgSolic").removeClass("ERRADO");
						$("#unOrgInput").removeClass("ERRADO");
					}
					if(doc[0].solicNome) {
						$("#nomeSolic").val(doc[0].solicNome);
						$("#nomeSolic").css("background-color","#DDFFDD");
					}
					if(doc[0].solicDepto) {
						$("#deptoSolic").val(doc[0].solicDepto);
						$("#deptoSolic").css("background-color","#DDFFDD");
					}
					if(doc[0].solicEmail) {
						$("#emailSolic").val(doc[0].solicEmail);
						$("#emailSolic").css("background-color","#DDFFDD");
					}
					if(doc[0].solicRamal) {
						$("#ramalSolic").val(doc[0].solicRamal);
						$("#ramalSolic").css("background-color","#DDFFDD");
					}
				}
			});
		}
	} else if (target == 'saa') {
		if($("#saa").val().match(",") != null){
			$("#passo5").css("background-color","#FFDDDD");
			$("#caixaAviso2").html('Erro! Selecione apenas um documento.');
			$("#caixaAviso2").show();
		} else {
			$.get('sgd_busca.php',{
				tipoBusca:    "numCPO", 
				docID:       $("#saa").val()
			}, function(data){
				//var doc = eval(data);
				try {
					doc = eval(data);
				} catch(e) {
					if (e instanceof SyntaxError) {
						alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
					}
				}
				
				if(doc[0].tipo.nomeAbrv != 'sap') {
					$("#passo5").css("background-color","#FFDDDD");
					$("#caixaAviso2").html('Erro! O documento deve ser um oficio.');
					$("#caixaAviso2").show();
				} else {
					$("#passo5").css("background-color","#DDFFDD");
					$("#caixaAviso2").html('');
					$("#caixaAviso2").hide();
				}
			});
		}
	}
}

function sugereObra() { return null;
	if($("#nome").val().length == 5 || ($("#nome").val().length > 5 && $("#nome").val().length % 2 == 1)){
		$.get('sgo_busca.php',{
			tipoBusca:    "sugestao", 
			nome:         escape($("#nome").val()),
			unOrg:        $("#unOrgSolic").val()
		}, function(data){
			//var obras = eval(data);
			try {
				obras = eval(data);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(obras.length == 0)
				$("#sugestoesObra").hide();
			else {
				$("#sugestoesObra").show();
				$("#sugestoesObra").html('Obras cadastradas sugeridas:<br />');
			}
			
			$.each(obras,function(i){
				$("#sugestoesObra").append(obras[i].nome+'<br />')
			});
		});
	}
}

function validateObra(){
	if($("#ofir").val() != '')
		$("#row1").css("background-color","#CCFFCC");
}

function selectPlace(lat, lng){
	$("#latObra").val(Math.round(lat*1000000)/1000000);
	$("#lngObra").val(Math.round(lng*1000000)/1000000);
}

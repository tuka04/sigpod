$(document).ready(function(){
	var arquivo = window.location.href.split("/");
	arquivo = arquivo[arquivo.length-1].split("?");
	arquivo = arquivo[0];
	//alert(arquivo);
	
	if (arquivo != "sgo.php") {
		//alert(arquivo)
		if (arquivo == 'sgd.php')
			inicializaDesp();
		if (arquivo == 'index.php' || arquivo == 'sgp.php' || arquivo == '')
			inicializaDesp(undefined, true);
	}
	
});

function inicializaDesp(panel, docsPend) {
	if (docsPend == undefined)
		docsPend = false;
	
	//alert(docsPend)
	
	//var panel = panel;
	if (panel == undefined) {
		panel = $("body");
	}

	//$("#despExt", panel).autocomplete("unSearch.php",{minChars:2,matchSubset:1,matchContains:true,maxCacheLength:1,extraParams:{'show':'un'},selectFirst:true,onItemSelect: function(){$("#unOrgReceb", panel).focus();}});
	
	// auto-complete
	$("#despExt").autocomplete({
		source: function(request, response) { 
			$.get("unSearch.php", {
				q: request.term,
				show: "un"
			}, function(data) {
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
		select: function(){
			$("#despExt").focus();
		}
	});
	
	$("#despExt", panel).keyup(function(){
		
		var v = $("#despExt", panel).val();
		
		v = v.replace(/\./g,''); 
		
		var expReg  = /^[0-9]{2,12}$/i;
		
		if (expReg.test(v)){
			var i, vn="";
			for(i=0 ; i<v.length ; i++){
				if(i%2 == 0 && i != 0)
					vn += '.';
				vn += v[i];
			}				
			$("#despExt", panel).val(vn);
		}
	});
	
	
	clearAll(panel);
	
	// esconde caixa de alerta
	$("#alerta", panel).hide();
		
	//alert(panel.prop("tagName"));
	if (!docsPend) {
		$("#para", panel).change(function(){
			//alert(panel.attr("id"));
			clearAll(panel);
			//alert($("#para option:selected", panel).val()+" "+$("#para option:selected").val());
			if($("#outr", panel).attr("selected")){
				$("#outro", panel).show();
			}else if($("#ext", panel).attr("selected")){
				$("#despExt", panel).show();
			}else if($("#arq", panel).attr("selected")||$("#solic", panel).attr("selected")){
				
			}else{
				loadNames($("#para option:selected", panel).val(), panel);
			}
		});
	}
	else {
		$("#para", panel).change(function(){
			//alert(panel.attr("id"));
			clearAll(panel);
			//alert($("#para option:selected", panel).val()+" "+$("#para option:selected").val());
			//alert($("#rr", panel).length)
			if ($("#rr", panel).length <= 0) {
				var html = '<span id="spanRR"><input type="checkbox" name="rr" id="rr" checked="checked"> ';
				html += 'Gerar Rela&ccedil;&atilde;o de Remessa<br><br></span>';
				
				$(html).insertAfter('#camposDespacho');
			}
				
			
			if($("#outr", panel).attr("selected")){
				$("#outro", panel).show();
				$("#spanRR").hide();
			}else if($("#ext", panel).attr("selected")){
				$("#despExt", panel).show();
				$("#spanRR").show();
			}else if($("#arq", panel).attr("selected")){
				$("#spanRR").hide();
			}else if($("#solic", panel).attr("selected")) {
				$("#spanRR").hide();
			}else{
				$("#spanRR").hide();
				loadNames($("#para option:selected", panel).val(), panel);
			}
		});
	}
	
	$("#subp", panel).change(function() {
		$("#alerta", panel).hide();
		if ($("#_todos", panel).attr("selected")) {
			return;
		}
		else {
			//alert($("#subp option:selected").attr("id"));
			verificaFerias($("#subp option:selected", panel).attr("id"), panel);
		}
	});
	
	$("#despacho", panel).click(function(){
		if($("#despacho", panel).val() == 'Digite a instrução aqui.') {
			$("#despacho", panel).html("");
		}
	});
	
	$("#despachoForm", panel).submit(function(){
		if($("#despacho", panel).val() == 'Digite a instrução aqui.') {
			$("#despacho", panel).html("");
		}
	});
	
	if ($("#solicitante", panel).length > 0 && $("#solicitante", panel).val() != "") {
		$("#despAlerta", panel).html('<span style="color: red; font-weight: bold">AVISO</span>: <b> '+$("#solicitante", panel).val()+' havia solicitado este documento. Favor entregar este documento a ele/ela.</b>.');
		$("#despAlerta", panel).dialog({ 
			resizable: false,
			height: 200,
			width: 350,
			modal: true,
			buttons: { "OK": function() { $(this).dialog('close'); } }
		});
	}
}

function clearAll(panel) { //inicializa os campos de despacho (V1)

	$("#subp", panel).hide();
	$("#outro", panel).hide();
	$("#despExt", panel).hide();
	$("#subp", panel).val("");
	$("#outro", panel).val("");
	$("#despExt", panel).val("");
}

function loadNames(area, panel){//completa os nomes dos funcionarios de um depto CPO
	$("#subp", panel).html('<option id="_todos" value="_todos" selected="selected" >Todos nesse Depto.</option>');
	$.ajax({
		url: "unSearch.php?show=pessoas",
		data: {'area' : escape(area)},
		type: 'get',
		async: false,
		success: function(d){
			try {
				data = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: "+d);
				}
			}
			var i = 0;
			
			$.each(data,function(){	
				$("#subp", panel).append('<option id="'+data[i].id+'" name="'+data[i].id+'" value="'+data[i].id+'">'+data[i].nome+'</option>');
				i++;
			});
		}
	});
	
	$("#subp", panel).show();
}

function verificaFerias(id, panel) {
	$.get('sgp.php', {
		acao: 'checkDesp',
		usuario: id
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if (d[0].success == true) {
			$("#despAlerta", panel).attr("title", "Férias");
			$("#despAlerta", panel).html('<span style="color: red; font-weight: bold">AVISO</span>: Este usuário entrará em férias dia '+d[0].dataIni+' e só voltará dia '+d[0].dataFim+'.');
			$("#despAlerta", panel).dialog({ 
				resizable: false,
				height: 200,
				width: 350,
				modal: true,
				buttons: { "OK": function() { $(this).dialog('close'); } }
			});
		}
	});
}
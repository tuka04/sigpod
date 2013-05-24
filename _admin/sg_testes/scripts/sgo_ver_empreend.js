var despInicializados = new Array();
var TempGlobalUiSiGPOD = null;

$(document).ready(function(){
	//$("#c2").hide();
	$("#c3").hide();
	$("#c4").hide();
	$("#c5").hide();
	$("#c6").hide();
	$("#c7").hide();
	$("#c8").hide();
	$("#c9").hide();
	$("#c10").hide();
	$("#c11").hide();
	$("#boxLeft").hide();
	//$("#c2").slideDown();
	
	/*$("#resumo_link").click(function(){
		showSecaoObra(1);
	});
	
	$("#docs_link").click(function(){
		showSecaoObra(2);
	});
	
	$("#recursos_link").click(function(){
		showSecaoObra(3);
	});
	
	$("#livro_link").click(function(){
		showSecaoObra(4);
	});
	
	$("#questoes_link").click(function(){
		showSecaoObra(5);
	});
	
	$("#contratos_link").click(function(){
		showSecaoObra(6);
	});
	
	$("#medicoes_link").click(function(){
		showSecaoObra(7);
	});
	
	$("#mensagens_link").click(function(){
		showSecaoObra(8);
	});
	
	$("#historico_link").click(function() {
		showSecaoObra(9);
	});*/
	
	
	// cria as tabs
	$("#tabs").tabs({
		load: function(event, ui) {
			//alert(ui.panel);
			inicializaTabs2(event, ui);
			TempGlobalUiSiGPOD = ui;
		},
		select: function(event, ui) {
			hideResumo();
			$("#r2", ui.panel).show();
		},
		ajaxOptions: {
			error: function(xhr, status, index, anchor) {
				$(anchor.hash).html("Erro ao carregar página de obras.");
			},
			success: function(xhr, status, index, anchor) {
				//alert("Teste");
				inicializaTabs(null, TempGlobalUiSiGPOD);
			}
		}
	});
	
	// mostra feedback, se estiver algum conteudo
	if ($("#feedback").length > 0 && $("#feedback").html() != "") {
		$("#feedback").dialog({ 
			resizable: false,
			height: 120,
			width: 300,
			modal: true,
			buttons: {
				"OK": function() { $(this).dialog('close'); }
			}
		});
	}
	
	// estilo
	var diff = ($(".ui-tabs-nav").height()+1) % $("li").height();
	//alert($(".ui-tabs-nav").height());
	$(".ui-tabs-nav").height(($(".ui-tabs-nav").height() - diff/3));
});

function inicializaTabs(event, ui) {
	//alert("q!");
	// seta overflow, assim conteudo não fica fora do div
	$(ui.panel).attr("style", "overflow: auto;");
	
	// mostra o div #r2 dentro do painel das tabs por padrão
	if ($("#r2", ui.panel).length > 0) $("#r2", ui.panel).show();
	
	
	// inicializa todos ckeditos em campos de conteudo para criação de novos docs
	// Obs: para evitar conflito de instancias do CKEditor (D:) os campos são renomeados
	var i = 0;
	$("textarea", ui.panel).each(function() {
		if ($(this).attr("id") == "conteudo") {
			$(this).attr("id", "conteudo"+i);
			i++;
		}
	});
	
	var j = 0;
	for (j = 0; j < i; j++) {
		var instance = CKEDITOR.instances["conteudo"+j];
		if (instance) {
			CKEDITOR.remove(instance);
		}
		$("#conteudo"+j, ui.panel).ckeditor();
	}
	
	$(".link_preview", ui.panel).each(function() {
		$(this).removeAttr('onclick');
	});
	
	$(".link_preview", ui.panel).click(function() {
		var tipoPreview = 'cad';
		
		if ($(this).hasClass('prev_edit'))
			tipoPreview = 'edit';
		
		visualizarDoc(tipoPreview, $(this));
	});
	
	// faz bind de campos de autocomplete
	$("input[autocomplete=off]", ui.panel).each(function() {
		var name = $(this).attr("id");
		if (name.indexOf("unOrg") != -1) {
			var ref = $(this);
			//$(this).autocomplete("unSearch.php",{minChars:2,matchSubset:1,matchContains:true,maxCacheLength:20,extraParams:{'show':'un'},selectFirst:true,onItemSelect: function(){ ref.focus(); }});
			$(this).autocomplete({
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
				select: function(){
					ref.focus();
				}
			});
			
			$(this).keyup(function(){
				v = $(this).val();
				
				v = v.replace(/\./g,""); 
				
				var expReg  = /^[0-9]{2,12}$/i;
				
				if (expReg.test(v)){
					var i, vn="";
					for(i=0 ; i<v.length ; i++){
						if(i%2 == 0 && i != 0)
							vn += ".";
						vn += v[i];
					}				
					$(this).val(vn);
				}
			});
		}
	});
	
	// faz bind nos formulários (para verificação de campos obrigatórios
	$("form", ui.panel).each(function() {
		var ref = $(this);
		$(this).submit(function(submit){
			var obrigatorios = $('input.obrigatorio:text[value=""]', ref);
			
			//verificacao dos campos obrigatorios
			if(obrigatorios[0] != undefined){
				alert("Há campos obrigatórios não preenchidos. Por favor, preencha-os e envie novamente.");
				submit.preventDefault();
			}
			
		});
	});
	
	// percorre os links do menu lateral dentro da aba carregada e faz binds
	$(".mini_link", ui.panel).each(function(i) {
		$(this).click(function() {
			// o id deste link deve conter o numero X do div a qual este link deve abrir quando clicado
			// o id do div tem o formato "rX" onde X é um inteiro
			var container = $(this).attr("id");
			
			// percorre todos os links e oculta o conteudo dos divs correspondentes de cada um
			$(".mini_link", ui.panel).each(function() {
				$("#r"+$(this).attr("id"), ui.panel).hide();
			});
			
			$("#anoE2", ui.panel).hide();
			
			// inicializa campos de despacho, caso este conteudo contenha os campos (página de criação de doc)
			if ($("#camposDespacho", $("#r"+$(this).attr("id"), ui.panel)).length > 0 && (despInicializados[$(this).attr("id")] != true || $(this).attr("id") == 11)) {
				//alert($(this).attr("id"))
				// incializando despacho
				//alert("#r"+$(this).attr("id"))
				inicializaDesp($("#r"+$(this).attr("id"), ui.panel));
				// seta que já foi inicializado
				despInicializados[$(this).attr("id")] = true;
			}
			
			//alert($("input[autocomplete=off]").length);
			
			// abre o div deste link
			$("#r"+container, ui.panel).slideDown();
		});
	});
	
	
}


function inicializaTabs2(event, ui) {
	// seta overflow, assim conteudo não fica fora do div
	$(ui.panel).attr("style", "overflow: auto;");
	
	// mostra o div #r2 dentro do painel das tabs por padrão
	if ($("#r2", ui.panel).length > 0) $("#r2", ui.panel).show();
	
	
	// inicializa todos ckeditos em campos de conteudo para criação de novos docs
	// Obs: para evitar conflito de instancias do CKEditor (D:) os campos são renomeados
	var i = 0;
	$("textarea", ui.panel).each(function() {
		if ($(this).attr("id") == "conteudo") {
			$(this).attr("id", "conteudo"+i);
			i++;
		}
	});
	
	var j = 0;
	for (j = 0; j < i; j++) {
		var instance = CKEDITOR.instances["conteudo"+j];
		if (instance) {
			CKEDITOR.remove(instance);
		}
		$("#conteudo"+j, ui.panel).ckeditor();
	}
	/*$("textarea", ui.panel).each(function() {
		if ($(this).hasClass('ckeditor')) {
			var instance = CKEDITOR.instances[$(this).attr("name")];
			if (instance) {
				CKEDITOR.remove(instance);
			}
			$("#"+$(this).attr("id"), ui.panel).ckeditor();
		}
	});*/
	
	/*$(".link_preview", ui.panel).each(function() {
		$(this).removeAttr('onclick');
	});
	
	$(".link_preview", ui.panel).click(function() {
		var tipoPreview = 'cad';
		
		if ($(this).hasClass('prev_edit'))
			tipoPreview = 'edit';
		
		visualizarDoc(tipoPreview, $(this));
	});*/
	
	// faz bind de campos de autocomplete
	$("input[autocomplete=off]", ui.panel).each(function() {
		var name = $(this).attr("id");
		if (name.indexOf("unOrg") != -1) {
			var ref = $(this);
			//$(this).autocomplete("unSearch.php",{minChars:2,matchSubset:1,matchContains:true,maxCacheLength:20,extraParams:{'show':'un'},selectFirst:true,onItemSelect: function(){ ref.focus(); }});
			$(this).autocomplete({
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
				select: function(){
					ref.focus();
				}
			});
			
			$(this).keyup(function(){
				v = $(this).val();
				
				v = v.replace(/\./g,""); 
				
				var expReg  = /^[0-9]{2,12}$/i;
				
				if (expReg.test(v)){
					var i, vn="";
					for(i=0 ; i<v.length ; i++){
						if(i%2 == 0 && i != 0)
							vn += ".";
						vn += v[i];
					}				
					$(this).val(vn);
				}
			});
		}
	});
	
	// faz bind nos formulários (para verificação de campos obrigatórios
	$("form", ui.panel).each(function() {
		var ref = $(this);
		$(this).submit(function(submit){
			var obrigatorios = $('input.obrigatorio:text[value=""]', ref);
			
			//verificacao dos campos obrigatorios
			if(obrigatorios[0] != undefined){
				alert("Há campos obrigatórios não preenchidos. Por favor, preencha-os e envie novamente.");
				submit.preventDefault();
			}
			
		});
	});
	
	//inicializaDesp($("#r11", ui.panel));
	/*$('.mini_link', ui.panel).filter("[id=11]").click(function() {
		$("#r2").hide();
		$("#r10").hide();
		//$("#r2").html(d);
		$("#r11").slideDown();
	});*/
	
	/*$('.mini_link', ui.panel).filter("[id=10]").click(function() {
		$("#r2").hide();
		//$("#r2").html(d);
		$("#r10").slideDown();
	});*/
	//$(".mini_link", ui.panel).filter("[id=2]");
	
	// percorre os links do menu lateral dentro da aba carregada e faz binds
	/*$(".mini_link", ui.panel).each(function(i) {
		$(this).click(function() {
			// o id deste link deve conter o numero X do div a qual este link deve abrir quando clicado
			// o id do div tem o formato "rX" onde X é um inteiro
			/*var container = $(this).attr("id");
			
			// percorre todos os links e oculta o conteudo dos divs correspondentes de cada um
			$(".mini_link", ui.panel).each(function() {
				$("#r"+$(this).attr("id"), ui.panel).hide();
			});
			
			$("#anoE2", ui.panel).hide();
			
			// inicializa campos de despacho, caso este conteudo contenha os campos (página de criação de doc)
			if ($("#camposDespacho", $("#r"+$(this).attr("id"), ui.panel)).length > 0 && (despInicializados[$(this).attr("id")] != true || $(this).attr("id") == 11)) {
				//alert($(this).attr("id"))
				// incializando despacho
				//alert("#r"+$(this).attr("id"))
				inicializaDesp($("#r"+$(this).attr("id"), ui.panel));
				// seta que já foi inicializado
				despInicializados[$(this).attr("id")] = true;
			}
			
			//alert($("input[autocomplete=off]").length);
			
			// abre o div deste link
			$("#r"+container, ui.panel).slideDown();*/
		/*});
	});*/
	
	
}

function showItSuplementar(empreendID, recarregar) {
	if (recarregar == undefined || recarregar == null) {
		recarregar = false;
	}
	
	$.get('sgo.php', {
		'acao': 'showItSuplementarAjax',
		'empreendID': empreendID,
		'recarrega': recarregar
	}, function(d) {
		//$("#r10").hide();
		//$("#r11").hide();
		$("#r2").hide();
		$("#r2").html(d);
		
		if ($("#camposDespacho", $("#r2")).length > 0) {
			inicializaDesp($("#r2"));
		}
		
		if ($(".ckeditor", $("#r2")).length > 0) {
			$(".ckeditor", $("#r2")).each(function() {
				var instance = CKEDITOR.instances[$(this).attr("id")];
				
				if (instance) {
					CKEDITOR.remove(instance);
				}
				
				$(this).ckeditor();
			});
		}
		
		if (recarregar == true) {
			restaurarDoc('formFase', true);
		}
		
		$("#r2").slideDown();
	});
}


function carregaFase(empreendID, obraID, etapaTipoID, faseTipoID, recarrega) {
	clearTimeout(_autoSaveTimer);
	if (recarrega == undefined || recarrega == null) {
		recarrega = false;
	}
	
	$.get('sgo.php', {
		'acao': 'showFaseAjax',
		'empreendID': empreendID,
		'obraID': obraID,
		'etapaTipoID': etapaTipoID,
		'faseTipoID': faseTipoID,
		'recarrega': recarrega
	}, function(d) {
		try {
			//d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				alert(d);
			}
		}
		
		//$("#r10").hide();
		//$("#r11").hide();
		$("#r2").hide();
		$("#r2").html(d);
		
		if ($("#camposDespacho", $("#r2")).length > 0) {
			inicializaDesp($("#r2"));
		}
		
		if ($(".ckeditor", $("#r2")).length > 0) {
			$(".ckeditor", $("#r2")).each(function() {
				var instance = CKEDITOR.instances[$(this).attr("id")];
				
				if (instance) {
					CKEDITOR.remove(instance);
				}
				
				$(this).ckeditor();
			});
		}
		
		$("#r2").slideDown();
		
		if (recarrega == true) {
			restaurarDoc('formFase', true);
			
			//$("#novoForm"+etapaTipoID+""+faseTipoID).show();
			showItForm(etapaTipoID, faseTipoID);
			
		}
	});
}

function showResponsaveisEtapa(empreendID, obraID, etapaTipoID) {
	$.get('sgo.php', {
		'acao': 'showRespEtapaAjax',
		'empreendID': empreendID,
		'obraID': obraID,
		'etapaTipoID': etapaTipoID,
	}, function(d) {
		
		$("#r2").hide();
		//$("#r11").hide();
		//$("#r10").hide();
		
		$("#r2").html(d);
		$("#r2").slideDown();
	});
}

function showSecaoObra(sec) {
	$("#c2").hide();
	$("#c3").hide();
	$("#c4").hide();
	$("#c5").hide();
	$("#c6").hide();
	$("#c7").hide();
	$("#c8").hide();
	$("#c9").hide();
	$("#c10").hide();
	$("#c11").hide();
	
	//hideContrCad();
	
	if(sec == 1) {
		$("#c2").slideDown();
	} else if(sec == 2) {
		$("#c3").slideDown();
	} else if(sec == 3) {
		$("#c4").slideDown();
	} else if(sec == 4) {
		$("#c5").slideDown();
	}else if(sec == 5) {
		$("#c6").slideDown();
	}else if(sec == 6) {
		$("#c7").slideDown();
	}else if(sec == 7) {
		$("#c8").slideDown();
	}else if(sec == 8) {
		$("#c9").slideDown();
	}else if(sec == 9) {
		$("#c11").slideDown();
	}
}

function addEtapa(){
	
	$("#addEtapaTable").show();
	$(".addEtapaLink").hide();
	showSecaoObra(4);
}

function addRecurso(primeiro){
	$("#noRecRow").hide();
	$("#addRecTable").show();
	$(".addRecLink").hide();
	showSecaoObra(3);
}

function salvaRec(id) {
	if(id == 0){
		if($("#valor").val() == '' && $("#origem").val() == '' && $("#prazo").val() == '')
			return;
		
		$.get('sgo.php',{
			'acao'      : 'salvaRec',
			'obraID'    : $("#obraID").val(),
			'rec_valor' : $("#valor").val(),
			'rec_origem': escape($("#origem").val()),
			'rec_prazo' : $("#prazo").val()
		}, function(fb){
			//var fb = eval(fb);
			try {
				fb = eval(fb);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(fb[0].success == true) {
				
				$("#recTable").append('<tr class="c"><td class="c">R$ '+ $("#valor").val() +'</td><td class="c">'+ $("#origem").val() +'</td><td class="c">'+ $("#prazo").val() +'</td><td class="c"></td></tr>')
				$("#valor").val('');
				$("#origem").val('');
				$("#prazo").val('');
			} else {
				$("#salvaRec").html('Erro. Tentar novamente');
			}
		});
	}
}

function salvaEtapa(id) {
	if(id == 0) {
		if($("#tipoEtapa").val() == '' || $("#respEtapa").val() == '' || $("#procEtapa").val() == '') {
			return;
		}
		$("#noEtapaRow").hide();
		$("#salvaEtapa").val("Adicionando Etapa...");
		
		$.get('sgo.php',{
			'acao'   : 'salvaEtapa',
			'obraID' : $("#obraID").val(),
			'tipoID' : $("#tipoEtapa").val(),
			'respID' : $("#respEtapa").val(),
			'procID' : $("#procEtapa").val()
		}, function(fb) {
			//var fb = eval(fb);
			try {
				fb = eval(fb);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(fb[0].success == true) {
				$("#addEtapaTable").hide();
				$(".addEtapaLink").show();
				$("#etapasTable").append('<tr class="c"><td class="c">'+ $("option:selected").html() +'</td><td class="c"><a href="javascript:void(0);" onclick="window.open(\'sgd.php?acao=ver&amp;docID='+ $("#procEtapa").val() +'\',\'detalheEtapa\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\').focus()">'+ $("#detalhe"+$("#procEtapa").val()).html() +'</a></td><td class="c"><a href="javascript:void(0)" onclick="window.open(\'sgo.php?acao=verEtapa&amp;etapaID='+ fb[0].etapaID +'\',\'detalheEtapa\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\')">Ver detalhes</a></td></tr>');
				$("#salvaEtapa").val("Adicionar");
			} else {
				$("#salvaEtapa").val("Erro! Tentar Novamente");
			}
		});
	}
}

function showBuscarMapa(){
	$("#selMap").show();
}

function selectPlace(lat, lng){
	$("#latObra").val(Math.round(lat*1000000)/1000000);
	$("#lngObra").val(Math.round(lng*1000000)/1000000);
}

function showEtapaDet(id) {
	$("#det"+id).show();
}

function mostraFilhos(id, tipo) {
	var tabela = $("#mostra_filho"+id).parents('table');
	if (tipo == "show") {
		
		// seleciona todos os TRs de filhos que estão escondidos e que não possuem a classe "filtrado"
		var c = $("tr.docfilho"+id, tabela).filter(function() {
		     return ($(this).css('display') == 'none' && !$(this).hasClass("filtrado"));
		});
		
		c.slideToggle("slow");
		c.addClass("show");
		c.removeClass("hide");
		$("#mostra_filho"+id, tabela).html('Esconder filhos');
		$("#mostra_filho"+id, tabela).attr('href', "javascript:mostraFilhos("+id+", 'hide')");
	}
	else {
		// seleciona todos os TRs de filhos que estão escondidos e que não possuem a classe "filtrado"
		var c = $("tr.docfilho"+id, tabela).filter(function() {
		     return ($(this).css('display') != 'none' && !$(this).hasClass("filtrado"));
		});
		
		var cc = $("tr.docfilho"+id, tabela).filter(function() {
		     return ($(this).css('display') == 'none' && $(this).hasClass("filtrado"));
		});
		
		c.slideToggle("slow");
		c.removeClass("show");
		cc.removeClass("show");
		c.addClass("hide");
		cc.addClass("hide");
		$("#mostra_filho"+id, tabela).html('Mostrar filhos');
		$("#mostra_filho"+id, tabela).attr('href', "javascript:mostraFilhos("+id+", 'show')");
	}
	
	/*var c = $("tr.docfilho"+id).filter(function() {
	     return $(this).css('display') == 'none';
	});
	if(c.length == 0) {
		$("#mostra_filho"+id).html('Mostrar filhos');
	} else {
		$("#mostra_filho"+id).html('Esconder filhos');
	}
	
		
	$("tr.docfilho"+id).each(function() {
		if (c.length == 0) { 
			
		}
		
		
		$(this).slideToggle('slow');
		if ($(this).hasClass('hidden')) { 
			$(this).removeClass('hidden');
		}
		else {
			$(this).addClass('hidden');
		}
	});*/
	
}


function addObra(empreendID, containerID) {
	$.get('sgo.php', {
		acao : 'newObra',
		empreendID : empreendID
	}, function (data) {
		$("#r2").hide();
		$("#r3").hide();
		$("#r4").hide();
		$("#r5").hide();
		$("#r6").hide();
		$("#r7").hide();
		$("#r8").hide();
		$("#r9").hide();
		$("#r10").hide();
		$("#r11").hide();
		$("#r"+containerID).html(data);
		$("#r"+containerID).slideDown();
	});
}

function showEditLink(campoID,atribEspeciais){
	if(atribEspeciais.indexOf("multifile") == -1)
		$("#atual_"+campoID).hide();
	else
		$("#"+campoID+"_edit_link").hide();
	$("#edit_"+campoID).show();
}

function showItForm(etapaID, faseID) {
	//alert($("#novoDiv"+etapaID+faseID).length);
	$("#novoForm"+etapaID+faseID).toggle();
	$("#submitFase"+faseID).toggle();
}

function toggleResumo() {
	$("#empreendResumo").toggle(); 
}

function hideResumo() {
	$("#empreendResumo").hide();
}

function filtraDocPend(tipo, atualizaCheckBox) {
	if (tipo == undefined || tipo == null)
		return;
	
	if (atualizaCheckBox == undefined || atualizaCheckBox == null)
		atualizaCheckBox = true;

	// seleciona todos os links do tipo
	var docs = $("a[name="+tipo+"]");
	
	// percorre todos os links e seleciona a linha
	$.each(docs, function() {
		var linkID = $(this).attr("id");
		var dataDesp = $("td#dataDesp"+linkID);
		
		var tr = $(this).closest("tr");
		
		if (!tr.hasClass("hide")) {
			tr.toggle();
		}
		
		tr.toggleClass("filtrado");
	});
	
	if (atualizaCheckBox == true) {
		if ($("#check_"+tipo).attr("checked") == "checked")
			$("#check_"+tipo).removeAttr("checked");
		else
			$("#check_"+tipo).attr("checked", "checked");
	}
	
	if ($("#check_"+tipo).attr("checked") != "checked") {
		$("#cb_selectAll").removeAttr("checked");
	}
	else {
		var cbs = $("input[type=checkbox]");
		var allSelected = true;
		$.each(cbs, function() {
			if ($(this).attr("id") == "cb_selectAll") {
				return true; // continue
			}
			if ($(this).attr("checked") != "checked") {
				allSelected = false;
				// break
				return false;
			}
		});
		
		if (allSelected)
			$("#cb_selectAll").attr("checked", "checked");
	}
}

function toggleSelectAll(atualizaCheckbox) {
	var cbs = $("input[type=checkbox]");
	
	var action = "select";
	if ($("#cb_selectAll").attr("checked") == "checked") {
		action = "clear";
	}
	
	
	$.each(cbs, function() {
		if ($(this).attr("id") == "cb_selectAll") {
			return true; // continue
		}
		
		if ($(this).attr("checked") == "checked" && action == "clear") {
			$(this).click();
			return true; // continue
		}
		
		if ($(this).attr("checked") != "checked" && action == "select") {
			$(this).click();
			return true; // continue
		}
	});
	
	if (atualizaCheckbox) {
		if ($("#cb_selectAll").attr("checked") == "checked") {
			$("#cb_selectAll").removeAttr("checked");
		}
		else {
			$("#cb_selectAll").attr("checked", "checked");
		}
	}
}
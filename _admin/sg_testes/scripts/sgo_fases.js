LOCALCARACTESPEC = new Array();
LOCAIS = new Array();

//
function addNewFile(id){
	var j = 0;
	var vazios = 0;
	
	for(j=0 ; $("#"+id+j).length != 0 ; j++) {
		if($("#"+id+(j)).val() == '')
			vazios++;
	}
	
	if(vazios <= 1)
		$("#"+id+"_arqDiv").append('<input name="'+id+j+'" id="'+id+j+'" type="file" class="multifile" onclick="javascript:addNewFile(\''+id +'\','+(j+1)+')"><br />');
}

//mostra a area de adicionar link
function showAddLocal(cancel){
	if($("#addLocalLink").hasClass("addLocalLink")){
		$("#addLocalLink").html("[Salvar Local]");
		$(".addLocal").show();
		$("input[type=submit]").hide();
		$(".editLocalLink").hide();
	} else {
		if(!cancel) preencheTabelaLocal();
		$("#addLocalLink").html("[Adicionar Local]");
		$(".addLocal").hide();
		$("input[type=submit]").show();
		$(".editLocalLink").show();
	}
	$("#addLocalLink").toggleClass("addLocalLink");
	$("#addLocalLink").toggleClass("saveLocalLink");
	$(".cancelarAddLocalLink").toggle();
}

//mostra a area de adicionar caract especifica
function showAddCaractEspec(){
	$("#div_alerta").attr("title","Adicionar Caracter&iacute;stica Espec&iacute;fica");
	
	//carregamento dos inputs de caract espec para o formulario
	for(var i in LOCALCARACTESPEC){
		//ativa o checkbox da caract espec do local
		$("#caract_espec_"+LOCALCARACTESPEC[i].nome).attr("checked","checked");
		//se o valor nao for vazio
		if(LOCALCARACTESPEC[i].valor.val != '')
			//seta o valor da caract espec
			$("#"+LOCALCARACTESPEC[i].nome).val(LOCALCARACTESPEC[i].valor.val);
		//se houver observacao
		if(LOCALCARACTESPEC[i].obs != '')
			//seta a obs no input
			$("#caract_espec_obs_"+LOCALCARACTESPEC[i].nome).val(LOCALCARACTESPEC[i].obs);
	}
	
	$("#caract_especifica").change(function(){
		carregaCaractEspecCampo($(".caract_espec_option:selected").val());
	});
	
	//cria dialog das caract especificas
	$( "#div_alerta" ).dialog({
		height: 450,
		width: 800,
		modal: true,
		buttons : {
			"OK" : function(){
				addCaractEspec();
				$(this).dialog("close");
			},
			"Cancelar" : function(){
				$(this).dialog("close");
			}
		}
	});
}

//adiciona as caract especificas
function addCaractEspec(){
	
	//limpa tabela de caract especificas
	limpa_caract_espec();
	
	//para cada caract especifica
	$(".caract_espec").each(function(){
		//se o usuario marcou o checkbox
		if($(this).attr("checked") == 'checked'){
			//le qual o nome da caract espec selecionada
			var campoNome = $(this).val();
			//se o campo for select seleciona o nome do filho selecionado para mostrar na tabela e gravar como label
			if($("#"+campoNome).prop("tagName") == 'SELECT')
				var valor = {"label" : $("#"+campoNome).children("option[selected=selected]").html(), "val" : $("#"+campoNome).val()};
			//senao le o valor do campo (se existir) e o utiliza como label tambem
			else {
				if($("#"+campoNome).length == 0)
					var valor = {"label" :  '', "val" : ''};
				else
					var valor = {"label" :  $("#"+campoNome).val(), "val" : $("#"+campoNome).val()};
			}
			//preenche a linha da tabela com os dados lidos
			preencheTabelaCaractEspec(campoNome, $(this).attr('title'), valor, $("#caract_espec_obs_"+campoNome).val());
		}
	});
	//limpa o diag das caract especiais
	limpa_caract_espec_dialog();
}

//coloca os dados da caract espec na tabela e na var global
function preencheTabelaCaractEspec(nome,label, valor, obs){
	LOCALCARACTESPEC.push({"nome" : nome, "label" : label, "valor" : valor, "obs" : obs});
	
	$("#caract_especif_table").append(
		'<tr class="c caractEspecifLinha"><td class="c">'+label+'</td><td class="c">'+valor.label+'</td><td class="c">'+obs+'</td></tr>'
	);
}

//preenche e tabela de locais, coloca os dados na var global e no input em JSON
function preencheTabelaLocal(){
	//le os dados dos campos de caract gerais e monta o obj
	var local = {
		nome   : $("#nome_local").val(),
		caract : $("#caract_local").val(),
		clima  : {"val" : $("#climatiz_local").val(), "label" : $("#climatiz_local").children("option[selected=selected]").html()},
		dados  : $("input[name=telef_local]:checked").val(),
		estab  : $("input[name=redeEstab_local]:checked").val(),
		gases  : $("input[name=gases_local]:checked").val(),
		area   : $("#area_local").val(),
		obs    : $("#obs_local").val()
	}
	//trata alguns valores especiais
	if(local.clima.val == '') local.clima.label = '';
	if(local.dados == undefined) local.dados = '';
	if(local.estab == undefined) local.estab = '';
	if(local.gases == undefined) local.gases = '';
	local.especificos = LOCALCARACTESPEC;
	
	//se nao houver locais na var global e nao houver algo pra carregar no input hidden
	if(LOCAIS.length == 0 && $("#incl_local").val() != '')
		LOCAIS = JSON.parse($("#incl_local").val());//carrega a estrutura JSON do input
	
	//se o local nao for edicao de um local ja existente
	if($("#local_id").val() == "") {
		//adiciona o local na var global
		LOCAIS.push(local);
		//seta o ID do local para futura edicao
		var local_id = LOCAIS.length - 1;
	//senao, modifica o local armazenado na tabela
	} else {
		//sobrescreve o local
		var local_id = $("#local_id").val();
		LOCAIS[local_id] = local;
		//remove a linha correspondente a esse local na tabela de locais
		$("#local_"+local_id).remove();
	}
	//grava os locais no campo hidden
	$("#incl_local").val(JSON.stringify(LOCAIS));
	
	//gera linha de caract especificas
	espec = '';
	i = 0;
	while(i < local.especificos.length) {
		espec += local.especificos[i].label+': '+local.especificos[i].valor.label+' (Obs.: '+local.especificos[i].obs+') <br />';
		i++
	}
	
	//trata campos de radio para colocar sim ou nao na tabela
	var boolean = new Array("N&atilde;o","Sim");
	boolean[''] = '';
	
	//trata campos de radio
	local.dados = boolean[local.dados];
	local.estab = boolean[local.estab];
	local.gases = boolean[local.gases];
	
	//coloca a nova linha de local na tabela de locais
	$("#table_locais").append(
		'<tr class="c" id="local_'+local_id+'">' +
		'<td class="c">'+local.nome+'</td>' +
		'<td class="c">'+local.caract+'</td>' +
		'<td class="c">'+local.clima.label+'</td>' +
		'<td class="c">'+local.dados+'</td>' +
		'<td class="c">'+local.estab+'</td>' +
		'<td class="c">'+local.gases+'</td>' +
		'<td class="c">'+local.area+'</td>' +
		'<td class="c">'+local.obs+'</td>' +
		'<td class="c">'+espec+'</td>' +
		'<td class="c"><a href="javascript:void(0)" class="editLocalLink" onclick="editarLocal('+local_id+')">[Editar]</a></td>' +
		'</tr>'
	);
	
	//limpa campos de caract gerais do local
	limpa_campos_local();
}

//limpa todos os campos de adicao de local
function limpa_campos_local(){
	$("#nome_local").val("");
	$("#caract_local").val("");
	$("#climatiz_local").val("");
	$("input[name=telef_local]:checked").removeAttr("checked");
	$("input[name=redeEstab_local]:checked").removeAttr("checked");
	$("input[name=gases_local]:checked").removeAttr("checked");
	$("#area_local").val("");
	$("#obs_local").val("");
	$("#local_id").val("");
	
	limpa_caract_espec();
}

//limpa todas as linhas da tabela de caract especiais do dialogo e reseta a variavel global
function limpa_caract_espec(){
	$(".caractEspecifLinha").each(function(){
		$(this).remove();
	});
	LOCALCARACTESPEC = new Array();
}

//limpa os inputs do dialog de adicao de caract especifica
function limpa_caract_espec_dialog(){
	$(".caract_espec").removeAttr("checked");
	$(".caract_espec_obs").val('');
	$("#gde_potencia").val('');
	$("#divisorias").val('');
	$("#forro").val('');
}

//mostra area para editar local
//mesmo que adicionar local mas colocando os dados da obra no form
function editarLocal(id){
	//limpa campos de adicao de local
	limpa_campos_local();
	//se nao houver locais carregados na var global, carrega os locais para var global
	if(LOCAIS.length == 0)
		LOCAIS = JSON.parse($("#incl_local").val());
	//"seleciona" o local a ser editado
	var local = LOCAIS[id];
	//preenche os campos
	$("#nome_local").val(local.nome);
	$("#caract_local").val(local.caract);
	$("#climatiz_local").val(local.clima.val);
	if(local.dados == '1')
		$("#telef_local_yes").attr("checked","checked");
	else if(local.dados == '0')
		$("#telef_local_no").attr("checked","checked");
	
	if(local.estab == '1')
		$("#redeEstab_local_yes").attr("checked","checked");
	else if(local.estab == '0')
		$("#redeEstab_local_no").attr("checked","checked");
	
	if(local.gases == '1')
		$("#gases_local_yes").attr("checked","checked");
	else if(local.gases == '0')
		$("#gases_local_no").attr("checked","checked");
	
	$("#area_local").val(local.area);
	$("#obs_local").val(local.obs);
	$("#local_id").val(id);
	
	//gera a tabela de caract especificos novamente
	for ( var i in local.especificos ) {
		preencheTabelaCaractEspec(local.especificos[i].nome, local.especificos[i].label, local.especificos[i].valor, local.especificos[i].obs);
	}
	//mostra a tab de edicao/adicao de local
	showAddLocal(true);
}

//cancela a acao de adicionar local
function cancelAddLocal(){
	limpa_campos_local();
	showAddLocal(true);
}
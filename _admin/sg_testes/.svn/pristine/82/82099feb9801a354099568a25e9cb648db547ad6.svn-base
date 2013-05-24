$(document).ready(function(){
	
	resetCampos();
	
	$(".tipoDoc").click(function(){
		resetCampos();
		selecionaCampos();
	});
	
	$("#buscaForm").submit(function(submit){
		submit.preventDefault();
<<<<<<< HEAD
		doBusca();
=======
		/*if($("#s_cpo").val() == '' && $("#s_numero").val() == '' && $("#s_criacao").val() == '' && 
		$("#s_emitente").val() == '' && $("#s_assunto").val() == '' && $("#s_unOrg").val() == '' &&
		$("#s_tipoProc").val() == '' && $("#s_situacao").val() == '' && $("#s_rrext").val() == '' && 
		$("#s_desp").val() == ''){
			alert("Pelo menos um campo deve ser preenchido.");
		}else{*/
			doBusca();
		//}
>>>>>>> 4dd0e794cea62da21cb2ef318d6662dd305d5638
	});//closes click
	
});//closes ready

function resetCampos(){//esconde todos os campos a espera do preenchimento do tipo de doc
	$(".campoDoc").hide();
	$(".campoDocEsp").hide();
	
	//limpa o conteudo do formulario
	$("input[type=text]").val("");
	
}

function selecionaCampos(){//mostra apenas os campos pertinentes ao tipo de doc escolhido
	//le o tipo de doc selecionado
	var tipoSel = $("input:radio:checked.tipoDoc");
	tipoSel = tipoSel[0].id;
	
	//coloca o tipo de documento em um campo oculto
	$("#s_tipoDoc").val(tipoSel);
	
	//mostra o div relativo ao documento
	$("#campos_"+tipoSel).show();
	
	//seta a variavel relativa aos nomes dos campos do documento
	$('#s_selectedDocCampos').val($("#camposEsp_"+tipoSel).val());
	
	//adiciona os campos comuns a todos os tipos de documento
	$(".campoDoc").show();
}

function doBusca(){//faz a busca
	$("#btnBuscar").val("Buscando...");
	$("#btnBuscar").attr("disabled","disabled");
	
	var numCPO = escape($("#numCPO").val().replace(',',''));
	var dataCr = $("#dataCr").val().replace(',','');
	var desp = escape($("#desp").val().replace(',',''));
	var urlVar = getUrlVars();
	
	if(urlVar['onclick'] == undefined)
		urlVar['onclick'] = 'ver';
	
	//montagem da consulta dos campos comuns
	var q = "tipoBusca=buscaSearch&tipoDoc="+$("#s_tipoDoc").val()+"&numCPO="+numCPO+"&dataCr="+dataCr+"&desp="+desp;
	
	//montagem da consulta dos campos especificos
	var campos = $("#s_selectedDocCampos").val().split(",");
	$.each(campos,function(i){
		if(campos[i] != ''){
			if($("#"+campos[i]).attr('type') == 'checkbox'){
				if($("#"+campos[i]).attr('checked')){
					q += '&'+campos[i].replace($("#s_tipoDoc").val()+'_','')+'=1';
				} else {
					q += '&'+campos[i].replace($("#s_tipoDoc").val()+'_','')+'=0';
				}
			} else {
				q += '&'+campos[i].replace($("#s_tipoDoc").val()+'_','')+'='+escape($("#"+campos[i]).val().replace(',',''));
				//alert($("#"+campos[i]).val());
			}
		}
	});

	//$("#c1").append("sgd_busca.php?"+q+'<br />');
	$.get("sgd_busca.php?"+q, function(d){
		//alert(d);
		var data = eval(d);
		if(data.length != 0){
			$("#resBusca").html('<table width="100%" id="res"><tr><td class="cc"><b>n° Doc.</b></td><td class="cc"><b>Tipo/Número</b></td><td class="cc"><b>Emitente</b></td><td class="cc"><b>Assunto</b></td><td class="cc"><b>A&ccedil;&atilde;o</b></td></tr>');
			var i = 0;
			$.each(data,function(){
				var id = data[i].id;
				var nome = data[i].nome;
				var emissor = data[i].emitente;
				var assunto = data[i].assunto;
				var lk = newWinLink('sgd.php?acao=ver&docID='+id,'detalhe'+id,950,650,nome);
				var acao = addAction(urlVar['onclick'],id,nome,data[i].anexado,urlVar['target']);
				$("#res").append('<tr class="c"><td class="cc">'+id+'</td><td class="cc">'+lk+'</td><td class="cc">'+emissor+'</td><td class="cc">'+assunto+'</td><td class="cc">'+acao+'</td></tr>');
				i++;
			});
			$("#resBusca").append("</table>");
		}else{
			$("#resBusca").html("<center><b>N&atilde;o foi encontrado nenhum documento.</b></center>");
		}
		$("#c1").slideToggle();
		$("#c2").slideToggle();
		$("#c3").slideToggle();
		$("#btnBuscar").val("Buscar");
		$("#btnBuscar").removeAttr("disabled");
	});//close get
	
}

//reseta os campos de busca e esconte/mpostra os divs para nova busca
function novaBusca(){
	$("input:radio:checked.tipoDoc")[0].checked = "";
	resetCampos();
	$("#c1").slideToggle();
	$("#c2").slideToggle();
	$("#c3").slideToggle();
}

//adiciona os links referentes a acoes
function addAction(action,id,nome,anexado,target){
	action = action.split(",");
	var i = 0, ret = '';
	for(i = 0 ; i < action.length ; i++){
		if(action[i] == 'adicionarCampo') ret += '<a onclick="addDoc('+id+',\''+nome+'\',\''+target+'\',\'<br>\')">Adicionar Documento</a>';
		if(action[i] == 'adicionar' && !anexado) ret += '<a onclick="addDoc('+id+',\''+nome+'\',\''+target+'\',\'<br>\')">Adicionar Documento</a>';
		if(action[i] == 'adicionar' && anexado) ret += 'J&aacute; anexado.';
		if(action[i] == 'ver') ret += newWinLink('sgd.php?acao=ver&docID='+id,'detalhe'+id,screen.width*0.9,screen.height*0.9,'Ver Documento');
	}
	return ret;
}

function addDoc(id,nome,target,sep){
	window.opener.newDocLink(id,nome,target,'<br>');
	if(confirm("Documento adicionado com sucesso.\nClique OK para fechar a janela de busca."))
		self.close();
}

//cria um link para abertura em nova pagina
function newWinLink(url,name,w,h,label){
	return '<a onclick="window.open(\''+url+'\',\''+name+'\',\'width='+w+',height='+h+',scrollbars=yes,resizable=yes\')">'+label+'</a>';
}
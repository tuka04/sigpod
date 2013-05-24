$(document).ready(function(){
	$("#unOrg").autocomplete("../unSearch.php",
		{
			minChars:2,
			matchSubset:1,
			matchContains:true,
			maxCacheLength:20,
			extraParams:{'show':'un2'},
			selectFirst:true,
			onItemSelect: function(){
				$("#unOrg").focus();
			}
		});

	$("#buscaObraForm").submit(function(event){
		applyFilter();
		
		event.preventDefault();
	});
});

function applyFilter(){
	//campus
	var attr = $(".campus");
	var campus = '';
	$.each(attr,function(i) {
		if(attr[i].checked)
			campus += attr[i].value + '|';
	});
	if(campus.charAt(campus.length-1) == '|')
		campus = campus.slice(0 , campus.length-1);

	//nome
	var nome = $("#nome").val();
	
	//unOrg
	var unOrg = $("#unOrg").val();
	
	//tipo
	var attr = $(".tipo");
	var tipo = '';
	$.each(attr,function(i) {
		if(attr[i].checked)
			tipo += attr[i].value + '|';
	});
	if(tipo.charAt(tipo.length-1) == '|')
		tipo = tipo.slice(0 , tipo.length-1);
	
	//caract
	var attr = $(".caract");
	var caract = '';
	$.each(attr,function(i) {
		if(attr[i].checked)
			caract += attr[i].value + '|';
	});
	if(tipo.charAt(tipo.length-1) == '|')
		caract = caract.slice(0 , caract.length-1);
	
	
	//area
	var area = '', min = '', max = '';
	
	if(!$("#area1").attr("checked") && $("#area2").attr("checked")) {
		min = $("#a2").val();
	} else if(!$("#area1").attr("checked") && $("#area3").attr("checked")) {
		min = $("#a4").val();
	}
	
	if(!$("#area3").attr("checked") && $("#area2").attr("checked")) {
		max = $("#a3").val();
	} else if($("#area1").attr("checked")) {
		max = $("#a1").val();
	}
	
	if($("#area0").attr("checked")) {
		area = 'N|' + min + '-' + max;
	} else {
		area = min + '-' + max;
	}
	
	var param = {
		'campus'   : campus,
		'nome'     : escape(nome),
		'unOrg'    : unOrg,
		'tipo'     : tipo,
		'caract'   : caract,
		'area'     : area
	}
	//alert('campus:' + campus + "\nnome:" + nome + "\nunOrg:" + unOrg + "\ntipo:" + tipo + "\narea:" + area + "\npav:" + pav + "\nelev:" + elev + "\nrec:" + rec + "\nrec_total:" + rec_total);
	
	document.getElementById('gmapsRes').contentWindow.filterResults(param);
}

function listarObras(empreend){
	if(empreend.length == 0) {
		$("#listaRes").html('<br /><center><b>N&atilde;o h&aacute; obras com os crit&eacute;rios escolhidos.</b></center>');
	} else {
		$("#listaRes").html('<span style="text-align: right; display:block;"><b>'+empreend.length+'</b> empreendimentos encontrados com os par&acirc;metros escolhidos.</span>'+
				'<table id="listaObrasRes" width="100%"><tr><td class="c"></td></tr><tr class="c"><td class="c"><b>Nome/Unidade</b></td></tr></table>');
	
		var j, i;
	
	   	for(i = 0; i < empreend.length; i++){
	   		var obrashtml = '';
	   		if(!empreend[i].descricao)
	   			var descr = '';
			else
				var descr = empreend[i].descricao+'<br /><br />';
				
		   	for(j = 0; j < empreend[i].obras.length; j++){
		   		if(empreend[i].obras[j].id)
			   		//$("#listaObrasRes").append('<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID='+empreend[i].id+'\',\'obra_det\',\'width=900,height=650,scrollbars=yes,resizable=yes\')"><b>' + empreend[i].nome + '</b></a><br />' + empreend[i].unOrg.compl + '<br />' + obrashtml + '</td></tr>');
					obrashtml +='<div id="obraDetCompl'+empreend[i].obras[j].id+'" style="margin: 5px; padding: 5px"><a href="javascript:void(0)" onclick="showObraDet('+empreend[i].obras[j].id+')"><b>' + empreend[i].obras[j].nome + '</b></a><br />'+
					'<div id="obraDet'+empreend[i].obras[j].id+'" style="display:none"> <br />'
					+descr
					+'<b>&Aacute;rea: </b>'+empreend[i].obras[j].area.compl+'<br />'
					+'<b>Caracter&iacute;stica: </b>'+empreend[i].obras[j].caract.label+'<BR />'
					+'<b>Tipo: </b>'+empreend[i].obras[j].tipo.label+'<BR />'
					+'<b>Estado: </b>'+empreend[i].obras[j].estado.label+' </div>'+
					'</div>';
		   	}
		   	$("#listaObrasRes").append('<tr class="c"><td class="c"><b>'+empreend[i].nome+'</b> <br />'+empreend[i].unOrg.compl+'<br /><table style="width: 100%"><tr class="c"><td class="c">'+obrashtml+'</td></tr></td></tr>');
		}
	}
}

function showObraDet(id){
	if($("#obraDet"+id).css("display") == 'none'){
		$("#obraDet"+id).slideDown();
		$("#obraDetCompl"+id).css("border","1px solid #BE1010");
	} else {
		$("#obraDet"+id).slideUp();
		$("#obraDetCompl"+id).css("border","0");
	}
	
}

function showMap(){
	$("#listaRes").hide();
	$("#gmapsRes").show();
	$("#show_list").css('text-decoration','none');
	$("#show_map").css('text-decoration','underline');
}

function showList(){
	$("#listaRes").show();
	$("#gmapsRes").hide();
	$("#show_list").css('text-decoration','underline');
	$("#show_map").css('text-decoration','none');
}

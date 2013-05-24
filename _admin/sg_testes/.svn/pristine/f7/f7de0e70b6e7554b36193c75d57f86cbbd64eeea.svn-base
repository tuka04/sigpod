$(document).ready(function(){
	/*$("#unOrg").autocomplete("unSearch.php",
	   {
		minChars:2,
		matchSubset:1,
		matchContains:true,
		maxCacheLength:20,
		extraParams:{'show':'un'},
		selectFirst:true,
		onItemSelect: function(){
			$("#unOrg").focus();
			//document.getElementById('gmapsRes').contentWindow.filterResults('unOrg',$("#unOrg").val(),null,null);
			}
		});*/
	
	// auto-complete
	$("#unOrg").autocomplete({
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
			$("#unOrg").focus();
		}
	});
	
	/*
	$("#nome").focusout(function(){
		document.getElementById('gmapsRes').contentWindow.filterResults('nome',$("#nome").val(),null,null);
	});
	
	$("#unOrg").focusout(event, function(){
		document.getElementById('gmapsRes').contentWindow.filterResults('unOrg',$("#unOrg").val(),null,null);
	});
	
	$('input[type="checkbox"]').click(function(event){
		if(event.currentTarget.className == 'campus' || event.currentTarget.className == 'tipo' || event.currentTarget.className == 'elev' || event.currentTarget.className == 'todos_rec' || event.currentTarget.className == 'pav') {
			document.getElementById('gmapsRes').contentWindow.filterResults(event.currentTarget.className , event.currentTarget.value , null , event.currentTarget.checked);
		} else if(event.currentTarget.className == 'area') {	
			if(event.currentTarget.value == '0') {
				min = null;
				max = null;
			} else if(event.currentTarget.value == '1') {
				min = null;
				max = $("#a1").val();
			} else if(event.currentTarget.value == '2') {
				min = $("#a2").val();
				max = $("#a3").val();
			} else if(event.currentTarget.value == '3') {
				min = $("#a4").val();
				max = null;
			} else  {
				return;
			} 
			document.getElementById('gmapsRes').contentWindow.filterResults('area',min,max,event.currentTarget.checked);
		} else if(event.currentTarget.className == 'rec') {
			if(event.currentTarget.value == '0') {
				min = null;
				max = null;
			} else if(event.currentTarget.value == '1') {
				min = null;
				max = $("#r1").val();
			} else if(event.currentTarget.value == '2') {
				min = $("#r2").val();
				max = $("#r3").val();
			} else if(event.currentTarget.value == '3') {
				min = $("#r4").val();
				max = null;
			} else  {
				return;
			} 
			document.getElementById('gmapsRes').contentWindow.filterResults('rec',min,max,event.currentTarget.checked);
		
		} else {
			alert("Evento não tratado.");
		}
		
		//document.getElementById('gmapsRes').contentWindow.filterResults(event.id);
	});
	*/
	
	$("#unOrg").keyup(function(){
		
		var v = $("#unOrg").val();
		
		v = v.replace(/\./g,''); 
		
		var expReg  = /^[0-9]{2,12}$/i;
		
		if (expReg.test(v)){
			var i, vn="";
			for(i=0 ; i<v.length ; i++){
				if(i%2 == 0 && i != 0)
					vn += '.';
				vn += v[i];
			}				
			$("#unOrg").val(vn);
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
	
	//pav
	var attr = $(".pav");
	var pav = '';
	$.each(attr,function(i) {
		if(attr[i].checked)
			pav += attr[i].value + '|';
	});
	if(pav.charAt(pav.length-1) == '|')
		pav = pav.slice(0 , pav.length-1);
	
	
	//elev
	var elev = '';
	if($("#elevador1").attr('checked'))
		if($("#elevador0").attr('checked'))
			elev = "0-1";
		else
			elev = "1";
	else if($("#elevador0").attr('checked'))
		elev = "0";
	
	//rec
	var rec = '', min = '', max = '';
	
	if($("#rec2").attr("checked")) {
		if(min > parseInt($("#r2").val()) || min == '') min = $("#r2").val();
		if(max < parseInt($("#r3").val()) || max == '') max = $("#r3").val();
	}
	if($("#rec1").attr("checked")) {
		if(max < parseInt($("#r1").val())) max = $("#r1").val();
		min = '';
	}
	if($("#rec3").attr("checked")) {
		if(min > parseInt($("#r4").val())) min = $("#r4").val();
		max = '';
	}
	if($("#rec0").attr("checked")) {
		rec = 'N|' + min + '-' + max;
	} else {
		rec = min + '-' + max;
	}
	
	//rec_total
	var rec_total = null;
	
	
	
	var param = {
		'campus'   : campus,
		'nome'     : escape(nome),
		'unOrg'    : unOrg,
		'tipo'     : tipo,
		'caract'   : caract,
		'area'     : area,
		'pav'      : pav,
		'elev'     : elev,
		'rec'      : rec,
		'rec_total': rec_total
	}
	//alert('campus:' + campus + "\nnome:" + nome + "\nunOrg:" + unOrg + "\ntipo:" + tipo + "\narea:" + area + "\npav:" + pav + "\nelev:" + elev + "\nrec:" + rec + "\nrec_total:" + rec_total);
	
	$("#divLoading").show();
	
	document.getElementById('gmapsRes').contentWindow.filterResults(param);
}

function listarObras(empreend){
	if(empreend.length == 0) {
		$("#numRes").html("");
		$("#listaRes").html('<br /><center><b>N&atilde;o h&aacute; obras com os crit&eacute;rios escolhidos.</b></center>');
	} else {
		$("#numRes").html('<b>' + empreend.length + '</b> empreendimentos encontrados com os par&acirc;metros escolhidos.');
		$("#listaRes").html('<table id="listaObrasRes" width="100%"><tr><td class="c"></td></tr><tr class="c"><td class="c"><b>Nome/Unidade</b></td></tr></table>');
		
		var j, i;
	   	for(i = 0; i < empreend.length; i++){
	   		var obrashtml = '';
	   		for(j = 0; j < empreend[i].obras.length; j++){
	   			if(empreend[i].obras[j].id)
	   				obrashtml += '&nbsp;&nbsp;' + (j+1) + '. <a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verObra&amp;obraID='+empreend[i].obras[j].id+'\',\'obra\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\').focus()">'+ empreend[i].obras[j].nome + '</a><br />';
			}
	   		$("#listaObrasRes").append('<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID='+empreend[i].id+'\',\'obra\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\').focus()"><b>' + empreend[i].nome + '</b></a><br />' + empreend[i].unOrg.compl + '<br />' + obrashtml + '</td></tr>');
		}
	}
	
	$("#divLoading").hide();
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

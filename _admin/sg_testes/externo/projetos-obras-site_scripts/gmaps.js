google.maps.Map.prototype.clearMarkers = function() {
	for(var i=0; i < this.openMarkers.length; i++){
		this.openMarkers[i].setMap(null);
	}
	this.openMarkers = [];
};

google.maps.Map.prototype.clearInfoWindows = function() {
	for(var i=0; i < this.openInfoWindows.length; i++){
		this.openInfoWindows[i].close();
	}
	this.openInfoWindows = [];
};

function showGMaps() {
	//conf da lat/lng
    var latlng = new google.maps.LatLng(-22.822,-47.067);
    //opcoes
    var myOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.HYBRID
    };
    //carrega mapa
    map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
    map.openMarkers = new Array();
    map.openInfoWindows = new Array();
  }

function filterResults(param){
	//faz chamada assincrona para carregar as obras iniciais
    $.get('projetos-obras-site_busca.php', {
    	'campus'     : param.campus,
	   	'nome'       : param.nome,
	   	'unOrg'      : param.unOrg,
	   	'tipo'       : param.tipo,
	   	'caract'     : param.caract,
	   	'area'       : param.area
    }, function(data) {
    	var empreend = eval(data);
    	var semLatLng = false;
    	//resetando mapa e alerta de obras sem coordenadas
    	$("#alert").hide();
    	map.clearMarkers();
    	
    	if(empreend.length == 0) {
    		$("#alert_noObras").show();
    	} else {
    		$("#alert_noObras").hide();
    	}
    	
    	//para cada obra encontrada, cria um marcador e uma entrada na tabela
    	$.each(empreend,function(i, empreendimento){
	   		if(empreendimento.obras)
	   			var obras_deste_empreend = empreendimento.obras;
	   		else
	   			return;
	   		$.each(obras_deste_empreend,function(j, obra){
		   		//verifica se tem coordenadas para  mostrar o aviso
			   	if(!obra.lat && !obra.lng) {
				   	semLatLng = true;
				}
			   	
			   	if(!obra.caract.abrv)
			   		var icon = 'def';
			   	else
			   		var icon = obra.caract.abrv;
			   	
			   	//seta paramentros do marcador
				var latlng = new google.maps.LatLng(obra.lat,obra.lng);
				var marker = new google.maps.Marker({
					clickable: true,
					position: latlng, 
					map: map,
					icon : "projetos-obras-site_icons/"+icon+".png",
					title: html_entity_decode(obra.nome)
				});
				
				//if(empreend[i].descricao.v)
				//	var descr = '';
				//else
					var descr = empreend[i].descricao;
				
				//adiciona evento para clique
				google.maps.event.addListener(marker, 'click', function(event) {
					map.clearInfoWindows();
					var infowindow = new google.maps.InfoWindow({
						content: '<span style="font-family: arial, sans-serif; font-weight: bold;">'+obra.nome+'</span><br />'
							+'<span style="font-family: arial, sans-serif; font-size: 10pt">'+empreend[i].unOrg.compl+'</span><br /><br />'
							+descr+'<br /><br />'
							+'<b>Area</b>: '+obra.area.compl+'<br />'
							+'<b>Caracter&iacute;stica</b>: '+obra.caract.label+'<br />'
							+'<b>Tipo</b>: '+obra.tipo.label+'<br />'
							+'<b>Estado</b>: '+obra.estado.label+'<br />'
					});
					//se clickar no marcador, abre a janela de informacao
					infowindow.open(map,marker);
					map.openInfoWindows.push(infowindow);
				});
				//empilha os marcadores do mapa
				map.openMarkers.push(marker);
	   		});
	   	});
	   	//adiciona linha na tabela de resultados
		parent.listarObras(empreend);
	   	
	   	if(semLatLng)
	   		$("#alert").show();
    });
	
}

function focusCampus(campusName) {
	var coord = new Array();
	
	switch(campusName) {
	case 'unicamp' :	coord['lat'] = -22.822;
						coord['lng'] = -47.067;
						coord['zoom'] = 15;
			  			break;
	
	case 'cotuca' :		coord['lat'] = -22.9023;
						coord['lng'] = -47.0670;
						coord['zoom'] = 19;
		  				break;
	
	case 'cpqba' :		coord['lat'] = -22.7972;
						coord['lng'] = -47.1151;
						coord['zoom'] = 17;
						break;
	
	case 'lim1' :		coord['lat'] = -22.5616;
						coord['lng'] = -47.4241;
						coord['zoom'] = 17;
						break;
	
	case 'fca' :		coord['lat'] = -22.5524;
						coord['lng'] = -47.4289;
						coord['zoom'] = 16;
		  				break;
	
	case 'fop' :		coord['lat'] = -22.7015;
						coord['lng'] = -47.6479;
						coord['zoom'] = 17;
		  				break;

	case 'pircentro' :	coord['lat'] = -22.7275;
						coord['lng'] = -47.6514;
						coord['zoom'] = 19;
				 		break;
	}
	
	var latlng = new google.maps.LatLng(coord['lat'],coord['lng']);
	
	map.setCenter(latlng);
	map.setZoom(coord['zoom']);
}
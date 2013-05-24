function showGMap() {
    var latlng = new google.maps.LatLng(-22.822,-47.064);
    var myOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.HYBRID
    };
    map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
    
    var newObraMarker = new google.maps.Marker({
    	clickable: true,
        map: map,
        title:"Adicionar Obra Aqui"
    });
    var newObraInfoWindow = new google.maps.InfoWindow({
		content: ''
	});
    
    google.maps.event.addListener(map, 'rightclick', function(event) {
    	newObraMarker.setVisible(true);
    	newObraMarker.setPosition(event.latLng);
    	newObraInfoWindow.setPosition(event.latLng);
    	newObraInfoWindow.setContent(
    			'<b>Local escolhido com sucesso!</b>');
    	parent.selectPlace(event.latLng.lat(),event.latLng.lng());
    	newObraInfoWindow.open(map,newObraMarker);
    });

    google.maps.event.addListener(newObraInfoWindow, 'closeclick', function(event) {
    	newObraMarker.setVisible(false);
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
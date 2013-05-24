function showGMap() {
    var latlng = new google.maps.LatLng(-22.822,-47.064);
    var myOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.HYBRID
    };
    var map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
    
    var i;
    for(i=0;i<10;i++){
    	latlng = new google.maps.LatLng((-22.813-0.02*Math.random()),(-47.060-0.007*Math.random()));
	    var marker = new google.maps.Marker({
	    	clickable: true,
	        position: latlng, 
	        map: map,
	        title:"Obra DJKKDSA (DXX)"
	    });
    }
    
    google.maps.event.addListener(marker, 'click', function(event) {
    	var infowindow = new google.maps.InfoWindow({
    		content: "XXXXX"
    	});
    	infowindow.open(map,marker);
    });
    
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
    	newObraInfoWindow.setContent('<a href="sgo.php?acao=cadastrar&amp;coord='+event.latLng.lat()+'|'+event.latLng.lng()+'" target="_parent" style="font-size: 10pt; font-family: Arial, sans-serif; color:#BE1010;">Adicionar Nova Obra neste local</a><br />'+
    			'<a href="sgo.php?acao=cadastrar&amp;coord='+event.latLng.lat()+'|'+event.latLng.lng()+'" target="_parent" style="font-size: 10pt; font-family: Arial, sans-serif; color:#BE1010;">Selecionar obra para esse local</a>');
    	newObraInfoWindow.open(map,newObraMarker);
    });

    google.maps.event.addListener(newObraInfoWindow, 'closeclick', function(event) {
    	newObraMarker.setVisible(false);
    });
    
  }

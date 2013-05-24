function showDet(id){
	$("#c1").hide();
	$("#c2").hide();
	$("#c3").hide();
	$("#c4").hide();
	$("#c5").hide();
	
	if(id == 1){
		$("#c1").slideDown();
		$("#c2").slideDown();
		$("#c3").slideDown();
	}else if(id == 2){
		$("#c4").slideDown();
	}else if(id == 3){
		$("#c5").slideDown();
	}
}
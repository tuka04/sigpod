function showMenu(abertas){
	$("#cadDoc").hide();
	$("#novoDoc").hide();
	if(abertas % 2 != 0){
		$("#menuObras").hide();
	}
	if(abertas % 3 != 0){
		$("#menuDoc").hide();
	}
	if(abertas % 5 != 0){
		$("#menuOS").hide();
	}
	if(abertas % 7 != 0) {
		$("#menuPessoas").hide();
	}
	
	//alert(abertas)
	if(abertas % 11 != 0) {
		$("#menuEmpr").hide()
	}
}

$(document).ready(function(){
	$("#cadDocLink").click(function(){
		$("#cadDoc").slideToggle();
	});
	
	$("#novoDocLink").click(function(){
		$("#novoDoc").slideToggle();
	});
	
	$("#labObras").click(function(){
		$("#menuObras").slideToggle();
	});
	
	$("#labDoc").click(function(){
		$("#menuDoc").slideToggle();
	});
	
	$("#labOS").click(function(){
		$("#menuOS").slideToggle();
	});
	
	$("#labPessoas").click(function(){
		$("#menuPessoas").slideToggle();
	});
	
	$("#labEmpr").click(function() {
		$("#menuEmpr").slideToggle();
	});
	
	$("#chat").click(function() {
		$("#listaChat").slideToggle();
	});
});
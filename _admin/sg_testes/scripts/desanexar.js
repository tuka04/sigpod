$(document).ready(function(){
	var name=new Array();
	var ids=new Array();
	$('div.unappendDocA').each(function(){
		var aux = $(this).attr('id').split('_');
		ids.push(aux[aux.length-1]);
		name.push($(this).children('a').html());
	});
	$('div[class="unappendDoc"]').each(function(i){
		$("#button_"+ids[i]).css('cursor','pointer');
		
		$("#button_"+ids[i]).on("mouseover",function(){
			$(this).children().css("width","15px");
			$(this).children().css("height","15px");
		});
		$("#button_"+ids[i]).on("mouseout",function(){
			$(this).children().css("width","14px");
			$(this).children().css("height","14px");
		});
		$(this).children('div[attr="unappendDialog"]').each(function(){
			$(this).parent().attr("dialogId","unappendDialog"+i);
			$(this).attr('id',$(this).parent().attr("dialogId"));
			$(this).dialog({
			      resizable: false,
			      autoOpen:false,
			      height:140,
			      width:480,
			      modal: true,
			      title:"Deseja desanexar "+name[i], 
			      buttons: {
			        "Sim": function() {
			          $(this).dialog("close");
			          $.ajax({
						  type: "POST",
						  url: "sgd.php",
						  dataType:"json",
						  data: {id:ids[i],acao:"unappendDoc"}
						}).done(function(msg) {
						  	if(msg.success){
						  		$p = $('#doc_'+ids[i]).parent();						  	
						  		$('#doc_'+ids[i]).fadeOut("slow").remove();
						  		$('#anexo_'+ids[i]).fadeOut("slow").remove();
						  		$("#c4").html(msg.historico);
						  		if($p.children().length==0){
						  			$pp = $p.parent();
						  			$pp.fadeOut("slow").remove();
						  		}
						  	}
						  	else{
						  		alert(msg.error);
						  	}
						});
			        },
			        "Não": function() {
			           $( this ).dialog( "close" );
			        }
			      }
		    });		
		});
		$(this).on('click',function(){
			$('#'+$(this).attr("dialogId")).dialog('open');
		});
	});
});
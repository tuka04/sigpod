/*

Copyright (c) 2009 Anant Garg (anantgarg.com | inscripts.com)

This script may be used for non-commercial purposes only. For any
commercial purposes, please contact the author at 
anant.garg@inscripts.com

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

*/

var winFocus = true;

var username;
var ausente = false;
var minChatHeartbeat = 1000; // 1 s
var chatHeartbeatTime = minChatHeartbeat;
var originalTitle;
var blinkOrder = 0;
var blinkDelay = 1000; // 1 segundo

var ausenteTimer;
var tempoAFK = 5 * 60 * 1000; // 5 (min) * 60 (s) * 1000 (ms) -> 5 min em milisec
var refreshMenu = 1 * 60 * 1000; // 1 min
var menuTimer;
var heartBeatTimer;

var chatboxFocus = new Array();
var newMessages = new Array();
var newMessagesWin = new Array();
var chatBoxes = new Array();

$(document).ready(function(){
	originalTitle = document.title;
	
	// se houver problema de concorrência dos chats, descomente o bloco a seguir
	// nota: isto fará com que o chat suma das janelas "não principais" do sigpod
	// no entanto, se o usuário fechar a janela principal, o chat não voltará nas outras janelas
	
	/*if (history.length <= 1 || window.name == "sg_newWin") {
		$("#chat").hide();
		$("#listaChat").hide();
		if (window.name == "") {
			window.name = "sg_newWin";
		}
		//alert($(":contains('sg_main')").length);
		return;
	}
	else {
		window.name = "sg_main";
	}*/	
	
	startChatSession();
	
	setTimeout('blinkTitle();', blinkDelay);
	setAusente(false, false);
	
	// monta menu
	updateMenu(false); // primeira chamada síncrona, para evitar problemas de chatbox sem nome (ou undefined)
	
	$([window, document]).blur(function () {
		winFocus = false;
	});
	
	$(this).mousemove(function (e) {
		setAusente(false, true);
		winFocus = true;
	});
	
	$(this).keypress(function () {
		setAusente(false, true);
		winFocus = true;
	});

});

function restructureChatBoxes() {
	align = 0;
	for (x in chatBoxes) {
		chatboxtitle = chatBoxes[x];

		if ($("#chatbox_"+chatboxtitle).css('display') != 'none') {
			if (align == 0) {
				$("#chatbox_"+chatboxtitle).css('right', '20px');
			} else {
				width = (align)*(225+7)+20;
				$("#chatbox_"+chatboxtitle).css('right', width+'px');
			}
			align++;
		}
	}
}

function chatWith(chatuser, nome) {
	createChatBox(chatuser, nome);
	$("#chatbox_"+chatuser+" .chatboxtextarea").focus();
}

function createChatBox(chatboxtitle, nome, minimizeChatBox) {
	if (nome == undefined || nome == "") {
		if (chatboxtitle == 'SiGPOD') {
			nome = 'SiGPOD';
		}
		else {
			nome = $("#chatUser_"+chatboxtitle).html();
			// para não aparecer undefined no título do chatbox
			if (nome == undefined || nome == "") {
				updateMenu(false);
				nome = $("#chatUser_"+chatboxtitle).html();
			}
		}
	}
	
	if ($("#chatbox_"+chatboxtitle).length > 0) {
		if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
			$("#chatbox_"+chatboxtitle).css('display','block');
			restructureChatBoxes();
		}
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		toggleChatBoxGrowth(chatboxtitle);
		return;
	}

	$(" <div />" ).attr("id","chatbox_"+chatboxtitle)
	.addClass("chatbox")
	.html('<div class="chatboxhead"><div class="chatboxtitle" onclick="javascript:toggleChatBoxGrowth(\''+chatboxtitle+'\')">'+nome+'</div><div class="chatboxoptions"><a href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\''+chatboxtitle+'\')">-</a> <a href="javascript:void(0)" onclick="javascript:closeChatBox(\''+chatboxtitle+'\')">X</a></div><br clear="all"/></div><div class="chatboxcontent""></div><div class="chatboxinput"><textarea class="chatboxtextarea" onkeydown="javascript:return checkChatBoxInputKey(event,this,\''+chatboxtitle+'\');"></textarea></div>')
	.appendTo($( "body" ));
			   
	$("#chatbox_"+chatboxtitle).css('bottom', '0px');
	
	chatBoxeslength = 0;

	for (x in chatBoxes) {
		if ($("#chatbox_"+chatBoxes[x]).css('display') != 'none') {
			chatBoxeslength++;
		}
	}

	if (chatBoxeslength == 0) {
		$("#chatbox_"+chatboxtitle).css('right', '20px');
	} else {
		width = (chatBoxeslength)*(225+7)+20;
		$("#chatbox_"+chatboxtitle).css('right', width+'px');
	}
	
	chatBoxes.push(chatboxtitle);

	if (minimizeChatBox == 1) {
		minimizedChatBoxes = new Array();

		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}
		minimize = 0;
		for (j=0;j<minimizedChatBoxes.length;j++) {
			if (minimizedChatBoxes[j] == chatboxtitle) {
				minimize = 1;
			}
		}

		if (minimize == 1) {
			$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
			$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
		}
	}

	chatboxFocus[chatboxtitle] = false;

	$("#chatbox_"+chatboxtitle+" .chatboxtextarea").blur(function(){
		chatboxFocus[chatboxtitle] = false;
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").removeClass('chatboxtextareaselected');
	}).focus(function(){
		chatboxFocus[chatboxtitle] = true;
		newMessages[chatboxtitle] = false;
		$('#chatbox_'+chatboxtitle+' .chatboxhead').removeClass('chatboxblink');
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").addClass('chatboxtextareaselected');
	});

	$("#chatbox_"+chatboxtitle).click(function() {
		if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') != 'none') {
			$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		}
	});

	if ($("#chatStat_"+chatboxtitle).val() == "afk") {
		//updateMenu(false);
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">Esta pessoa está ausente. Talvez ela não esteja na frente do computador. :(</span></div>');
	}
	else if ($("#chatStat_"+chatboxtitle).val() == "off") {
		//updateMenu(false);
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">Esta pessoa está desconectada.</span></div>');
	}
	
	$("#chatbox_"+chatboxtitle).show();
}


function chatHeartbeat(){
	if (ausente == true) {
		clearTimeout(heartBeatTimer);
		heartBeatTimer = setTimeout('chatHeartbeat();', minChatHeartbeat);
		return;
	}

	var itemsfound = 0;
	
	$.ajax({
	  url: "chat.php?action=chatheartbeat",
	  cache: false,
	  dataType: "json",
	  success: function(data) {

		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug
				
				chatboxtitle = item.f;
				nome = item.n;
				item.m = trataLink(unescape(item.m));

				// se recebeu mensagem de um usuário e não existe caixa de chat criada para ele, abre o chatbox
				if ($("#chatbox_"+chatboxtitle).length <= 0) {
					// antes, atualiza o menu caso o usuario esteja offline no menu atual
					if ($("#chatStat_"+chatboxtitle).val() != "on") {
						updateMenu(false);
					}
					
					// cria chatbox
					createChatBox(chatboxtitle, nome, 0);
				}
				if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
					$("#chatbox_"+chatboxtitle).css('display','block');
					restructureChatBoxes();
				}

				if (item.s == 1) {
					item.f = username;
				}

				if (item.s == 2) {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					newMessages[chatboxtitle] = true;
					newMessagesWin[chatboxtitle] = true;
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}

				$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
				itemsfound += 1;
			}
		});

		if (itemsfound > 0) {
			chatHeartbeatTime = minChatHeartbeat;
		}
		
		heartBeatTimer = setTimeout('chatHeartbeat();',chatHeartbeatTime);
	}});
}

function updateMenu(assincrono) {
	// limpa o timer
	clearTimeout(menuTimer);
	
	if (assincrono == undefined || assincrono == null)
		assincrono = true;
	
	// faz requisição ajax para atualizar lista de chat
	/*$.get('chat.php?action=updateList', { }, function(d) {
		d = eval(d);
		$("#listaChat").html(d);
	});*/
	
	$.ajax({
		async: assincrono,
		type: 'GET',
		url: 'chat.php?action=updateList',
		success: function (d) {
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			$("#listaChat").html(d);
		}
	});
	
	// seta novo timer
	menuTimer = setTimeout('updateMenu(true);', refreshMenu);
}

function blinkTitle() {
	var algumPiscando = false;
	
	var titleChanged;
	if (document.title == originalTitle)
		titleChanged = false;
	else
		titleChanged = true;
	
	for (x in newMessages) {
		if (newMessages[x] == true) {
			algumPiscando = true;
			if (chatboxFocus[x] == false) {
				if (titleChanged == false) {
					document.title = x+ ' disse...';
				}
				else {
					document.title = originalTitle;
				}
				//FIXME: add toggle all or none policy, otherwise it looks funny
				$('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
			}
		}
	}

	if (algumPiscando == false) {
		document.title = originalTitle;
	}
	
	setTimeout('blinkTitle();', blinkDelay);
}

function closeChatBox(chatboxtitle) {
	$('#chatbox_'+chatboxtitle).css('display','none');
	restructureChatBoxes();

	$.post("chat.php?action=closechat", { chatbox: chatboxtitle} , function(data){	
	});

}

function toggleChatBoxGrowth(chatboxtitle) {
	if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') == 'none') {  
		
		var minimizedChatBoxes = new Array();
		
		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}

		var newCookie = '';

		for (i=0;i<minimizedChatBoxes.length;i++) {
			if (minimizedChatBoxes[i] != chatboxtitle) {
				newCookie += chatboxtitle+'|';
			}
		}

		newCookie = newCookie.slice(0, -1)


		$.cookie('chatbox_minimized', newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','block');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','block');
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
	} else {
		
		var newCookie = chatboxtitle;

		if ($.cookie('chatbox_minimized')) {
			newCookie += '|'+$.cookie('chatbox_minimized');
		}


		$.cookie('chatbox_minimized',newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
	}
	
	chatboxFocus[chatboxtitle] = true;
	if ($("#chatbox_"+chatboxtitle+" .chatboxhead").hasClass('chatboxblink')) {
		$('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
	}
	if (document.title != originalTitle) {
		document.title = originalTitle;
	}
	
}

function checkChatBoxInputKey(event,chatboxtextarea,chatboxtitle) {
	 
	if(event.keyCode == 13 && event.shiftKey == 0)  {
		message = $(chatboxtextarea).val();
		message = message.replace(/^\s+|\s+$/g,"");

		$(chatboxtextarea).val('');
		$(chatboxtextarea).focus();
		$(chatboxtextarea).css('height','44px');
		if (message != '') {
			$.post("chat.php?action=sendchat", {to: chatboxtitle, message: escape(message)} , function(data){
				message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
				message = trataLink(message);
				$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+username+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
				$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			});
		}
		chatHeartbeatTime = minChatHeartbeat;

		return false;
	}

	var adjustedHeight = chatboxtextarea.clientHeight;
	var maxHeight = 94;

	if (maxHeight > adjustedHeight) {
		adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
		if (maxHeight)
			adjustedHeight = Math.min(maxHeight, adjustedHeight);
		if (adjustedHeight > chatboxtextarea.clientHeight)
			$(chatboxtextarea).css('height',adjustedHeight+8 +'px');
	} else {
		$(chatboxtextarea).css('overflow-y','auto');
	}
	 
}

function startChatSession(){  
	$.ajax({
	  url: "chat.php?action=startchatsession",
	  cache: false,
	  dataType: "json",
	  success: function(data) {
		
		username = data.username;
		primeiroNome = data.nomeComp;

		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug
				
				item.m = unescape(item.m);
				
				chatboxtitle = item.f;
				
				if (item.n != undefined && item.n != "") nome = item.n;
				else nome = primeiroNome;

				if ($("#chatbox_"+chatboxtitle).length <= 0) {
					createChatBox(chatboxtitle, nome, 1);
				}
				
				if (item.s == 1) {
					item.f = username;
				}
				
				item.m = trataLink(unescape(item.m));

				if (item.s == 2) {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}
			}
		});
		
		for (i=0;i<chatBoxes.length;i++) {
			chatboxtitle = chatBoxes[i];
			$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			setTimeout('$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
		}
	
		heartBeatTimer = setTimeout('chatHeartbeat();',chatHeartbeatTime);
		
	}});
}

/**
 * Função que começa o timer de verificao de ausente
 * @param bool status (true se o usuario estiver ausente, false caso contrario)
 */
function setAusente(status, atualizaBD) {
	if (status == true) { // está ausente
		ausente = true;
		
		$.get('chat.php?action=ausente', { ausente: true }, "");
	}
	else { // saiu do ausente
			
		ausente = false;
		
		// limpa timer
		clearTimeout(ausenteTimer);
		
		if (winFocus == false) {
			$.get('chat.php?action=ausente', { ausente: false }, "");
		}
		
	}
	
	// recomeça timer
	ausenteTimer = setTimeout('setAusente(true, true);', tempoAFK); // tempoAFK para ficar ausente
}

/**
 * Função que trata os links das mensagens.
 * @param string mensagem
 */
function trataLink(mensagem) {
	//var msg = mensagem.toLowerCase();
	var msg = mensagem;
	
	var pos = -1; // inicio de onde encontrou uma url
	
	// array de pedaços identificadores de url
	var arrayUrl = new Array();
	arrayUrl[0] = "http://";
	arrayUrl[1] = "https://";
	arrayUrl[2] = "www.";
	arrayUrl[3] = ".com";
	arrayUrl[4] = ".com.br";
	arrayUrl[5] = ".org";
	arrayUrl[6] = ".net";
	arrayUrl[7] = ".gov";
	arrayUrl[8] = ".br";
	
	// percorre array procurando pedaços de endereço
	var i;
	for (i = 0; i < arrayUrl.length; i++) {
		pos = msg.toLowerCase().indexOf(arrayUrl[i]);
		
		// se achou algum, sai do for
		if (pos != -1)
			break;
	}
	
	// se não achou, retorna
	if (pos == -1) return mensagem;
	
	// verifica os espaços que delimitam o endereço
	var primeiroEspaco = 0;
	var segundoEspaco = 0;
	
	primeiroEspaco = msg.lastIndexOf(" ", pos);
	segundoEspaco = msg.indexOf(" ", pos);
	
	// não existe link de comprimento 0
	if (len == 0 && primeiroEspaco == -1 && segundoEspaco == -1) return mensagem;

	if (primeiroEspaco != -1 && segundoEspaco != -1) {
		// calcula o tamanho do link
		var len = segundoEspaco - primeiroEspaco;
		
		// pega o url da mensagem
		var url = msg.substr(primeiroEspaco, len);
		
		// monta o url para o código html
		if (url.indexOf("http://") == -1)
			if (url.indexOf("https://") == -1) 
				url = "http://" + $.trim(url);
		
		var urlOriginal = mensagem.substr(primeiroEspaco, len);
	
		// divide a mensagem em 2 partes...
		var parte1 = mensagem.substr(0, segundoEspaco);
		var parte2 = mensagem.substr(segundoEspaco, mensagem.length);
		
		// chama recursivamente para o resto da mensagem
		if (parte2.length > 0)
			parte2 = trataLink(parte2);
		
		// monta a mensagem
		msg = parte1.replace(urlOriginal, '<a href="'+url+'" target="_blank">' + urlOriginal + '</a>') + parte2;
	}
	else if (primeiroEspaco == -1 && segundoEspaco != -1) {
		// o tamanho do link é a posicao do segundo espaco, uma vez que primeiroEspaco == -1
		// i.e., não há espaço antes deste link = primeira palavra da string
		var len = segundoEspaco;
		
		// pega a url da msg
		var url = msg.substr(0, len);
		
		// monta o url para o código html
		if (url.indexOf("http://") == -1)
			if (url.indexOf("https://") == -1)
				url = "http://" + $.trim(url);
		
		var urlOriginal = mensagem.substr(0, len);
		
		// divide a mensagem em 2 partes
		var parte1 = mensagem.substr(0, len);
		var parte2 = mensagem.substr(segundoEspaco, mensagem.length);
		
		// chama recursivamente para o resto da msg
		if (parte2.length > 0)
			parte2 = trataLink(parte2);
		
		// monta a msg
		msg = parte1.replace(urlOriginal, '<a href="'+url+'" target="_blank">' + urlOriginal + '</a>') + parte2;
	}
	else if (primeiroEspaco != -1 && segundoEspaco == -1) {
		// o link é a última palavra da string
		var len = msg.len;
		
		// pega a url
		var url = msg.substr(primeiroEspaco, len);
		
		// monta a url
		if (url.indexOf("http://") == -1)
			if (url.indexOf("https://") == -1)
				url = "http://" + $.trim(url);
		
		var urlOriginal = mensagem.substr(primeiroEspaco, len);
		
		// monta a msg
		msg = msg.replace(urlOriginal, '<a href="'+url+'" target="_blank">' + urlOriginal + '</a>');
	}
	else { // primeiro == segundo == -1
		// a mensagem só possui o link e nada mais
		// monta o link
		if (msg.indexOf("http://") == -1)
			if (msg.indexOf("https://") == -1)
				msg = "http://" + $.trim(msg);
		
		// monta a msg		
		msg = '<a href="'+msg+'" target="_blank">' + mensagem + '</a>';
	}
	
	// retorna
	return msg;
}


/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
/*
* @Copyright (c) 2011 AurÃ©lio Saraiva, Diego Plentz
* @Page http://github.com/plentz/jquery-maskmoney
* try at http://plentz.org/maskmoney

* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following
* conditions:
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
* OTHER DEALINGS IN THE SOFTWARE.
*/

/*
* @Version: 1.4.1
* @Release: 2011-11-01
*/
(function($){$.fn.maskMoney=function(settings){settings=$.extend({symbol:'US$',showSymbol:false,symbolStay:false,thousands:',',decimal:'.',precision:2,defaultZero:true,allowZero:false,allowNegative:false},settings);return this.each(function(){var input=$(this);var dirty=false;function markAsDirty(){dirty=true}function clearDirt(){dirty=false}function keypressEvent(e){e=e||window.event;var k=e.charCode||e.keyCode||e.which;if(k==undefined)return false;if(input.attr('readonly')&&(k!=13&&k!=9))return false;if(k<48||k>57){if(k==45){markAsDirty();input.val(changeSign(input));return false}else if(k==43){markAsDirty();input.val(input.val().replace('-',''));return false}else if(k==13||k==9){if(dirty){clearDirt();$(this).change()}return true}else if(k==37||k==39){return true}else{preventDefault(e);return true}}else if(input.val().length>=input.attr('maxlength')){return false}else{preventDefault(e);var key=String.fromCharCode(k);var x=input.get(0);var selection=input.getInputSelection(x);var startPos=selection.start;var endPos=selection.end;x.value=x.value.substring(0,startPos)+key+x.value.substring(endPos,x.value.length);maskAndPosition(x,startPos+1);markAsDirty();return false}}function keydownEvent(e){e=e||window.event;var k=e.charCode||e.keyCode||e.which;if(k==undefined)return false;if(input.attr('readonly')&&(k!=13&&k!=9))return false;var x=input.get(0);var selection=input.getInputSelection(x);var startPos=selection.start;var endPos=selection.end;if(k==8){preventDefault(e);if(startPos==endPos){x.value=x.value.substring(0,startPos-1)+x.value.substring(endPos,x.value.length);startPos=startPos-1}else{x.value=x.value.substring(0,startPos)+x.value.substring(endPos,x.value.length)}maskAndPosition(x,startPos);markAsDirty();return false}else if(k==9){if(dirty){$(this).change();clearDirt()}return true}else if(k==46||k==63272){preventDefault(e);if(x.selectionStart==x.selectionEnd){x.value=x.value.substring(0,startPos)+x.value.substring(endPos+1,x.value.length)}else{x.value=x.value.substring(0,startPos)+x.value.substring(endPos,x.value.length)}maskAndPosition(x,startPos);markAsDirty();return false}else{return true}}function focusEvent(e){var mask=getDefaultMask();if(input.val()==mask){input.val('')}else if(input.val()==''&&settings.defaultZero){input.val(setSymbol(mask))}else{input.val(setSymbol(input.val()))}if(this.createTextRange){var textRange=this.createTextRange();textRange.collapse(false);textRange.select()}}function blurEvent(e){if($.browser.msie){keypressEvent(e)}if(input.val()==''||input.val()==setSymbol(getDefaultMask())||input.val()==settings.symbol){if(!settings.allowZero)input.val('');else if(!settings.symbolStay)input.val(getDefaultMask());else input.val(setSymbol(getDefaultMask()))}else{if(!settings.symbolStay)input.val(input.val().replace(settings.symbol,''));else if(settings.symbolStay&&input.val()==settings.symbol)input.val(setSymbol(getDefaultMask()))}}function preventDefault(e){if(e.preventDefault){e.preventDefault()}else{e.returnValue=false}}function maskAndPosition(x,startPos){var originalLen=input.val().length;input.val(maskValue(x.value));var newLen=input.val().length;startPos=startPos-(originalLen-newLen);input.setCursorPosition(startPos)}function maskValue(v){v=v.replace(settings.symbol,'');var strCheck='0123456789';var len=v.length;var a='',t='',neg='';if(len!=0&&v.charAt(0)=='-'){v=v.replace('-','');if(settings.allowNegative){neg='-'}}if(len==0){if(!settings.defaultZero)return t;t='0.00'}for(var i=0;i<len;i++){if((v.charAt(i)!='0')&&(v.charAt(i)!=settings.decimal))break}for(;i<len;i++){if(strCheck.indexOf(v.charAt(i))!=-1)a+=v.charAt(i)}var n=parseFloat(a);n=isNaN(n)?0:n/Math.pow(10,settings.precision);t=n.toFixed(settings.precision);i=settings.precision==0?0:1;var p,d=(t=t.split('.'))[i].substr(0,settings.precision);for(p=(t=t[0]).length;(p-=3)>=1;){t=t.substr(0,p)+settings.thousands+t.substr(p)}return(settings.precision>0)?setSymbol(neg+t+settings.decimal+d+Array((settings.precision+1)-d.length).join(0)):setSymbol(neg+t)}function mask(){var value=input.val();input.val(maskValue(value))}function getDefaultMask(){var n=parseFloat('0')/Math.pow(10,settings.precision);return(n.toFixed(settings.precision)).replace(new RegExp('\\.','g'),settings.decimal)}function setSymbol(v){if(settings.showSymbol){if(v.substr(0,settings.symbol.length)!=settings.symbol)return settings.symbol+v}return v}function changeSign(i){if(settings.allowNegative){var vic=i.val();if(i.val()!=''&&i.val().charAt(0)=='-'){return i.val().replace('-','')}else{return'-'+i.val()}}else{return i.val()}}input.bind('keypress.maskMoney',keypressEvent);input.bind('keydown.maskMoney',keydownEvent);input.bind('blur.maskMoney',blurEvent);input.bind('focus.maskMoney',focusEvent);input.bind('mask',mask);input.one('unmaskMoney',function(){input.unbind('.maskMoney');if($.browser.msie){this.onpaste=null}else if($.browser.mozilla){this.removeEventListener('input',blurEvent,false)}})})};$.fn.unmaskMoney=function(){return this.trigger('unmaskMoney')};$.fn.mask=function(){return this.trigger('mask')};$.fn.setCursorPosition=function(pos){this.each(function(index,elem){if(elem.setSelectionRange){elem.focus();elem.setSelectionRange(pos,pos)}else if(elem.createTextRange){var range=elem.createTextRange();range.collapse(true);range.moveEnd('character',pos);range.moveStart('character',pos);range.select()}});return this};$.fn.getInputSelection=function(el){var start=0,end=0,normalizedValue,range,textInputRange,len,endRange;if(typeof el.selectionStart=="number"&&typeof el.selectionEnd=="number"){start=el.selectionStart;end=el.selectionEnd}else{range=document.selection.createRange();if(range&&range.parentElement()==el){len=el.value.length;normalizedValue=el.value.replace(/\r\n/g,"\n");textInputRange=el.createTextRange();textInputRange.moveToBookmark(range.getBookmark());endRange=el.createTextRange();endRange.collapse(false);if(textInputRange.compareEndPoints("StartToEnd",endRange)>-1){start=end=len}else{start=-textInputRange.moveStart("character",-len);start+=normalizedValue.slice(0,start).split("\n").length-1;if(textInputRange.compareEndPoints("EndToEnd",endRange)>-1){end=len}else{end=-textInputRange.moveEnd("character",-len);end+=normalizedValue.slice(0,end).split("\n").length-1}}}}return{start:start,end:end}}})(jQuery);
/*
Masked Input plugin for jQuery
Copyright (c) 2007-2011 Josh Bush (digitalbush.com)
Licensed under the MIT license (http://digitalbush.com/projects/masked-input-plugin/#license) 
Version: 1.3
*/
(function(a){var b=(a.browser.msie?"paste":"input")+".mask",c=window.orientation!=undefined;a.mask={definitions:{9:"[0-9]",a:"[A-Za-z]","*":"[A-Za-z0-9]"},dataName:"rawMaskFn"},a.fn.extend({caret:function(a,b){if(this.length!=0){if(typeof a=="number"){b=typeof b=="number"?b:a;return this.each(function(){if(this.setSelectionRange)this.setSelectionRange(a,b);else if(this.createTextRange){var c=this.createTextRange();c.collapse(!0),c.moveEnd("character",b),c.moveStart("character",a),c.select()}})}if(this[0].setSelectionRange)a=this[0].selectionStart,b=this[0].selectionEnd;else if(document.selection&&document.selection.createRange){var c=document.selection.createRange();a=0-c.duplicate().moveStart("character",-1e5),b=a+c.text.length}return{begin:a,end:b}}},unmask:function(){return this.trigger("unmask")},mask:function(d,e){if(!d&&this.length>0){var f=a(this[0]);return f.data(a.mask.dataName)()}e=a.extend({placeholder:"_",completed:null},e);var g=a.mask.definitions,h=[],i=d.length,j=null,k=d.length;a.each(d.split(""),function(a,b){b=="?"?(k--,i=a):g[b]?(h.push(new RegExp(g[b])),j==null&&(j=h.length-1)):h.push(null)});return this.trigger("unmask").each(function(){function v(a){var b=f.val(),c=-1;for(var d=0,g=0;d<k;d++)if(h[d]){l[d]=e.placeholder;while(g++<b.length){var m=b.charAt(g-1);if(h[d].test(m)){l[d]=m,c=d;break}}if(g>b.length)break}else l[d]==b.charAt(g)&&d!=i&&(g++,c=d);if(!a&&c+1<i)f.val(""),t(0,k);else if(a||c+1>=i)u(),a||f.val(f.val().substring(0,c+1));return i?d:j}function u(){return f.val(l.join("")).val()}function t(a,b){for(var c=a;c<b&&c<k;c++)h[c]&&(l[c]=e.placeholder)}function s(a){var b=a.which,c=f.caret();if(a.ctrlKey||a.altKey||a.metaKey||b<32)return!0;if(b){c.end-c.begin!=0&&(t(c.begin,c.end),p(c.begin,c.end-1));var d=n(c.begin-1);if(d<k){var g=String.fromCharCode(b);if(h[d].test(g)){q(d),l[d]=g,u();var i=n(d);f.caret(i),e.completed&&i>=k&&e.completed.call(f)}}return!1}}function r(a){var b=a.which;if(b==8||b==46||c&&b==127){var d=f.caret(),e=d.begin,g=d.end;g-e==0&&(e=b!=46?o(e):g=n(e-1),g=b==46?n(g):g),t(e,g),p(e,g-1);return!1}if(b==27){f.val(m),f.caret(0,v());return!1}}function q(a){for(var b=a,c=e.placeholder;b<k;b++)if(h[b]){var d=n(b),f=l[b];l[b]=c;if(d<k&&h[d].test(f))c=f;else break}}function p(a,b){if(!(a<0)){for(var c=a,d=n(b);c<k;c++)if(h[c]){if(d<k&&h[c].test(l[d]))l[c]=l[d],l[d]=e.placeholder;else break;d=n(d)}u(),f.caret(Math.max(j,a))}}function o(a){while(--a>=0&&!h[a]);return a}function n(a){while(++a<=k&&!h[a]);return a}var f=a(this),l=a.map(d.split(""),function(a,b){if(a!="?")return g[a]?e.placeholder:a}),m=f.val();f.data(a.mask.dataName,function(){return a.map(l,function(a,b){return h[b]&&a!=e.placeholder?a:null}).join("")}),f.attr("readonly")||f.one("unmask",function(){f.unbind(".mask").removeData(a.mask.dataName)}).bind("focus.mask",function(){m=f.val();var b=v();u();var c=function(){b==d.length?f.caret(0,b):f.caret(b)};(a.browser.msie?c:function(){setTimeout(c,0)})()}).bind("blur.mask",function(){v(),f.val()!=m&&f.change()}).bind("keydown.mask",r).bind("keypress.mask",s).bind(b,function(){setTimeout(function(){f.caret(v(!0))},0)}),v()})}})})(jQuery);


/**
 * Jquery para as Máscaras.
 * Recebe o tipo da mascara q a aplica no elemento
 */
$.fn.mascara = function(t){
	if($(this).attr('mask'))
		return;
	$(this).attr('mask',true);
	t = t.toLowerCase();
	if(t=='moeda'){
		$(this).maskMoney({showSymbol:false, symbol:"R$", decimal:",", thousands:"."});
		return;
	}
	else if(t=='porcentagem'){
		$(this).maskMoney({showSymbol:false, symbol:"R$", decimal:".", thousands:""});
		return;
	}
	else if(t=='cpf'){
		t = '999.999.999-99';
	}
	else if(t=='cep'){
		t = '99999-999';
	}
	else if(t=='telefone')
		t = '(99)9999-9999';
	else if(t=='cnpj')
		t = '99.999.999/9999-99';
	else if(t=='validadecartao')
		t = '99/99';
	else if(t=='email'||t=='numero')
		return;
	else if(t=='data'){
		$(this).datepicker({
			showAnim:'slideDown',
			changeMonth: true,
			changeYear: true,
			dateFormat:'dd/mm/yy'
		});
		t='99/99/9999';
	}
	$(this).mask(t);
};
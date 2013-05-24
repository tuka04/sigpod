<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset={$charset}" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<meta http-equiv="Pragma-directive" content="no-cache" />
		<meta http-equiv="Cache-Directive" content="no-cache" />
		<meta http-equiv="Expires" content="-1" />
		<link rel="stylesheet" type="text/css" href="css/geral.css" />
		<link rel="stylesheet" type="text/css" href="css/layout_mini.css" />
		<script type="text/javascript" src="scripts/jquery.js?r={$randNum}"> </script>
		<script type="text/javascript" src="scripts/commom.js?r={$randNum}"> </script>
		{$head}
		<title>{$title}</title>
	</head>
	<body>
		<img src="img/logocpo.png" height="22" width="102" alt="CPO - Coordenadoria de Projetos e Obras" /> <br />
  		<div class="container">
    		<div class="boxLeft">
      			{$menu}
    		</div>
	    	<div class="boxRight">
		    	<div class="boxCont">
        			<table width="100%">
          				<tr><td>{$path}</td><td><span class="par"><a href="javascript:window.close()">[fechar janela]</a></span></td></tr>
        			</table>
      			</div>
	    		<div class="boxCont" id="c1">
        			{$content1}
	      		</div>
	      		<div class="boxCont" id="c2">
        			{$content2}
	      		</div>
	      		<div class="boxCont" id="c3">
        			{$content3}
	      		</div>
	      		<div class="boxCont" id="c4">
        			{$content4}
	      		</div>
	      		<div class="boxCont" id="c5">
        			{$content5}
	      		</div>
    		</div>
		</div>
		<div class="footer">
			<hr />
      <table border="0" width=100%>
      <tbody>
      <tr>
        <td><span class="footer">{$footer}</span></td>
        <td style="text-align: right;"><span class="footer">{$campos_codPag}</span></td>
      </tr>
      </tbody>
      </table>
  		</div>
  		<div id="userProfile" style="display: none;"></div>
	</body>
</html>
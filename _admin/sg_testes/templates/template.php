<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="Content-type" content="text/html;charset={$charset}" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0" />
  <meta http-equiv="Pragma-directive" content="no-cache" />
  <meta http-equiv="Cache-Directive" content="no-cache" />
  <meta http-equiv="Expires" content="-1" />
  <link rel="stylesheet" type="text/css" href="css/geral.css" />
  <link rel="stylesheet" type="text/css" href="css/layout.css" />
  <script type="text/javascript" src="scripts/jquery.js?r={$randNum}"></script>
  <script type="text/javascript" src="scripts/menu.js?r={$randNum}"></script>
  <script type="text/javascript" src="scripts/commom.js?r={$randNum}"></script>
  <script type="text/javascript" src="scripts/ajuda.js?r={$randNum}"></script>
  <link type="text/css" rel="stylesheet" media="all" href="css/chat.css" />
  <link type="text/css" rel="stylesheet" media="all" href="css/screen.css" />
  <script type="text/javascript" src="scripts/chat.js?r={$randNum}"></script>
  <script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>
  <link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.18.custom.css" />
  <script type="text/javascript" src="scripts/jquery.tablesorter.min.js?r=1.1.1.1c"></script>
  {$head}
  <title>{$title}</title>
</head>
<body>
  <div id="container">
    <div id="header">
      <table style="position: absolute; left: 15%">
      	<tr>
      		<td width="20%"><h2>&Aacute;rea de Testes</h2></td>
      		<td width="80%"><h3>{$header}</h3></td>
      	</tr>
      </table>
      <a href="index.php"><img src="img/logocpo50.png" title="Voltar para In&iacute;cio" style="float:left;" /></a>
    </div>
    <div id="conteudoDiv">
      <div class="boxLeft">
        {$menu}
      </div>

      <div class="boxRight">

        <div class="boxCont">
          <table width="100%">
            <tr><td>{$path}</td><td><span class="par">Bem-vindo(a), {$user} <a href="{$logout_page}">[sair]</a></span></td></tr>
          </table>
        </div>

        <div class="boxCont" id="c1">
          {$content1}
        </div>
      </div>
    </div>
    <div id="footer">
      <hr />
      <table border="0" width="100%">
      <tbody>
      <tr>
        <td width="33%"><span class="footer">{$campos_codPag} </span></td>
        <td width="33%" style="text-align: center;">{$campos_admLink}</td>
        <td width="33%" style="text-align: right;"><span class="footer">{$footer}</span></td>
      </tr>
      </tbody>
      </table>
    </div>
  </div>
  <div id="userProfile" style="display: none;"></div>
  
</body>
</html>
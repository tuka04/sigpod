<?php 
if(isset($_GET['mode'])) {
	if($_GET['mode'] == 'cad') $script = 'sgo_gmaps_cad.js';
	elseif ($_GET['mode'] == 'bus') $script = 'sgo_gmaps_bus.js';
	elseif ($_GET['mode']) $script = 'sgo_gmaps_sel.js';
} else {
	exit();
}
print '
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<script type="text/javascript" src="scripts/commom.js?r={$randNum}"></script>
	<script type="text/javascript" src="scripts/jquery.js?r={$randNum}"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<script type="text/javascript" src="scripts/'.$script.'"></script>
	<link rel="stylesheet" type="text/css" href="css/geral.css" />
</head>
<body onload="showGMap()" style="border-width: 0; padding: 0; background-image: none; padding: 0; margin: 0;">
	<div class="alert" id="alert_noObras"><span style="font-weight: bold; text-align: center;">Nenhuma obra com essas especifica&ccedil;&otilde;es foi encontrada.</span></div>
	<div class="alert" id="alert"><span style="color: red; font-weight: bold">Aten&ccedil;&atilde;o:</span> Algumas obras sem coordenadas foram encontradas. Para visualiz&aacute;-las, selecione o modo lista.</div>
	<div id="gmap_canvas" style="width:100%; height:100%"></div>
</body>
</html>';

?>
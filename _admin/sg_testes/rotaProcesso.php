<?php
include_once('includeAll.php');
include_once('sgd_modules.php');
include_once('classes/adLDAP/adLDAP.php');
include_once('classes/PHPExcel.php');
include_once('classes/PHPExcel/Writer/Excel2007.php');
require_once 'relatorio/processos/RelatorioProcessos.class.php';
require_once 'classes/Documento.php';
$rp = new RelatorioProcessos();
$rp->rotaPorUser("2012");
?>
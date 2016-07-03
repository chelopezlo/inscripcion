<?
session_start();
require_once("../conexion/class.conexionDB.inc.php");
require_once("../lib/parametros.php");
require_once("../js/xajax/xajax.inc.php");

$Identificacion = $_SESSION['_usuario_rut'];
extract($_GET);

$codMod = 9;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
        <title>Inscripcibete en el congreso!</title>
		<link type="text/css" href="../css/eggplant/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
		<link type="text/css" href="../css/style-4.css" rel="stylesheet" />
		<script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>

				
        <style type="text/css">@import url(../js/jscalendar/calendar-blue.css);</style>
        <script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
        <script type="text/javascript" src="../js/jscalendar/lang/calendar-es.js"></script>
        <script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.core.js"></script>
		<script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.widget.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.draggable.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.resizable.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.dialog.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.ui.tabs.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.effects.core.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.effects.highlight.js"></script>
        <script type="text/javascript" src="../js/ui/development-bundle/ui/jquery.effects.blind.js"></script>
		
		
<script type="text/javascript">

$(document).ready(function(){

    $(".tab").click(function(){
        $("#" + $(".activa").attr("title")).hide("fast");
        $(".activa").removeClass("activa");

        $(this).addClass("activa");
        $("#" + $(this).attr("title")).show("slow");
    });

    $("#tabs").tabs({
        collapsible: true
    });

});

</script>


</head>
<body>
<!-- iframe frameborder="0" scrolling="no" width="100%" height="100px" allowtransparency="yes" src="blank.html" style="z-index:100; float:none; position:absolute; display:block;"></iframe -->
<div id="container" class="container">
    <div class="pad2"></div>
    <div id="cuerpo" class="contenedor">
        <form id="proyecto" name="proyecto" onsubmit="return false;">
            <h1>Congreso Territorial de Jóvenes 2012 - Territorio Sur-Centro</h1>
            <fieldset class="ui-widget ui-widget-content" id="tabs">
                <ul>
                    <li><a href="#tabs-1">Ya est&aacute;s inscrito!</a></li>
                </ul>
				<div id="tabs-1" style="height: auto;" class="tab">
				<div class="ui-widget-content ui-corner-all ui-state-highlight" ><h1>&nbsp;Anota tu número de inscripción: <strong>CTJSC<?php echo $codInscripcion; ?></strong></h1></div>
				</div>
            <div class="clear"></div>
			<br />
            <div class="clear"></div>
            <br />
        </form>
        
    </div>
        <br />
</div>
</body>
</html>
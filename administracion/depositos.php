<?php
session_start();
require("../lib/utiles.php");
require_once("../conexion/class.conexionDB.inc.php");
require_once '../js/xajax/xajax.inc.php';
require_once("../lib/parametros.php");

$Identificacion = $_SESSION['_usuario_rut'];

$codMod = 10;


function quitarElem($formulario){
    require("../lib/parametros.php");
    $respuesta = new xajaxResponse();
    $flag = 0;
    extract($formulario);
    if($hdnIdEmpresa == ""){
        $respuesta->addAlert("Error: Debe seleccionar una Empresa desde la lista.");
        return $respuesta;
    }

    $conn = new conexionBD ( );
    $conn->SeleccionBBDD($_BASE_SIS);

    $conn->EjecutarSQL("BEGIN TRANSACTION A1");

    $Str_SQL = "DELETE FROM
                    EMPRESA
                WHERE
                    EMP_IDEMPRESA = " .$hdnIdEmpresa;
    //$respuesta->addAlert($Str_SQL);
    if($conn->EjecutarSQL($Str_SQL)){
        $conn->EjecutarSQL("COMMIT TRANSACTION A1");
        $MSG = "Datos guardados con exito";
    }
    //$srt = "" . print_r($encabezado);
    $respuesta->addAlert($MSG);
    $respuesta->addRedirect("empresa.php");
    return $respuesta;
}

function cargarElem($idElem){
    require("../lib/parametros.php");
    $respuesta = new xajaxResponse();
//    cancelar($respuesta);   //Limpio los campos
    $conn = new conexionBD();
    $conn->SeleccionBBDD($_BASE_SIS);

    $Str_SQL = "SELECT 
					`DEP_ID_DEPOSITO`, 
					`DEP_NUMERO_DEPOSITO`, 
					`DEP_FECHA_DEPOSITO`, 
					`DEP_MONTO_DEPOSITO`, 
					`DEP_CANTIDAD_REGISTROS`, 
					`DEP_CANTIDAD_OCUPADOS`, 
					`DEP_OBSERVACIONES` 
				FROM `deposito` 
				WHERE `DEP_ID_DEPOSITO` = $idElem";

    $resultENC = $conn->EjecutarSQL($Str_SQL);
    if($rowsENC = $conn->FetchArray($resultENC)){ // Cargo el grupo
        $respuesta->addAssign("hdnIdElemento", "value", $rowsENC['DEP_ID_DEPOSITO']);
        $respuesta->addAssign("txtNumDeposito", "value", $rowsENC['DEP_NUMERO_DEPOSITO']);
        $respuesta->addAssign("txtDesc", "innerHTML", $rowsENC['DEP_OBSERVACIONES']);
        $respuesta->addAssign("txtFechaDep", "value", formatDate($rowsENC['DEP_FECHA_DEPOSITO']));
        $respuesta->addAssign("txtNumIncritos", "value", $rowsENC['DEP_CANTIDAD_REGISTROS']);
        $respuesta->addAssign("txtMonto", "value", $rowsENC['DEP_MONTO_DEPOSITO']);
    }
    return $respuesta;
}

function cancelar($objXajax = ''){
    if($objXajax == ''){
        $respuesta = new xajaxResponse();
    }
    else{
        $respuesta = $objXajax;
    }
    $respuesta->addRedirect($_SERVER['PHP_SELF']);
//    $respuesta->addAssign("txtNombre", "value", "");
//    $respuesta->addAssign("txtDesc", "innerHTML", "");
//    $respuesta->addAssign("txtFechaDep", "value", "");
//    $respuesta->addAssign("txtFechaFin", "value", "");
//    $respuesta->addAssign("hdnIdElemento", "value", "");
//    $respuesta->addAssign("hdnEstado", "value", "");
//    $respuesta->addAssign("divEstado", "innerHTML", "");
//    $respuesta->addAssign("btnEliminar", "innerHTML", '<div style="float:left" class="ui-icon ui-icon-circle-minus"></div>Desactivar');
    return $respuesta;
}

function agregarElem($formulario, $objXajax = ''){
    $flag = "";
    if($objXajax == ''){
        $respuesta = new xajaxResponse();
        $flag = '1';
    }
    else{
        $respuesta = $objXajax;
    }
	extract($formulario);

	$respuesta->addRemove("rowDetalle_$hdnIdEmpresa");
	$str_html_td1 = "$hdnIdEmpresa";
    $str_html_td2 = "$txtNombreEmp";
    $str_html_td3 = "$hdnBaseDatos";
    $str_html_td4 = "$_VAL_ESTADOS[$selEstado]";
    $str_html_td5 = "$hdnBaseDatos";
    $str_html_td6 = '<img src="../images/page_white_edit.png" width="16" height="16" alt="Modificar" onclick="xajax_cargarElem(' . $hdnIdEmpresa . ');"/>';
	//print_r($str_html);
	//$respuesta->addAlert($str_html);

    $idRow = "rowDetalle_$hdnIdEmpresa";
    $idTd = "tdDetalle_$hdnIdEmpresa";
	$respuesta->addCreate("tbDetalle", "tr", $idRow);
    $respuesta->addCreate($idRow, "td", $idTd."1");
    $respuesta->addCreate($idRow, "td", $idTd."2");
    $respuesta->addCreate($idRow, "td", $idTd."3");
    $respuesta->addCreate($idRow, "td", $idTd."4");


    $respuesta->addAssign($idTd."1", "innerHTML", $str_html_td1);
    $respuesta->addAssign($idTd."2", "innerHTML", $str_html_td2);
    $respuesta->addAssign($idTd."3", "innerHTML", $str_html_td3);
    $respuesta->addAssign($idTd."4", "innerHTML", $str_html_td4);

    $respuesta->addAssign($idTd."4", "className", "tdCentro");

	$respuesta->addAssign("hdnIdEmpresa", "value", $rowsENC['EMP_IDEMPRESA']);
    $respuesta->addAssign("txtNombreEmp", "value", $rowsENC['EMP_NOMBRE']);
    $respuesta->addAssign("txtBaseDatos", "value", $rowsENC['EMP_BASEDATOS']);
    $respuesta->addAssign("hdnBaseDatos", "value", $rowsENC['EMP_BASEDATOS']);

	return $respuesta;
}

function modificarElem($formulario, $estado = ''){
    require("../lib/parametros.php");
    $flag = 0;
    extract($formulario);
	$respuesta = new xajaxResponse();
    $conn = new conexionBD ( );
    $conn->SeleccionBBDD($_BASE_SIS);

    $conn->EjecutarSQL("BEGIN TRANSACTION A1");

	if($txtFechaDep == ''){$txtFechaDep = "NULL";}
	else{
		$fecaux = explode("/", $txtFechaDep);
		$fechaIni = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
		$txtFechaDep = "'$fechaIni'";
	}

	$Str_SQL = "UPDATE `deposito` 
				SET `DEP_NUMERO_DEPOSITO`= '$txtNumDeposito',
					`DEP_FECHA_DEPOSITO`= $txtFechaDep,
					`DEP_MONTO_DEPOSITO`= '$txtMonto',
					`DEP_CANTIDAD_REGISTROS`= '$txtNumIncritos',
					`DEP_OBSERVACIONES`= '$txtDesc' 
				WHERE `DEP_ID_DEPOSITO` = '$hdnIdElemento'";
				
    //$respuesta->addAlert($Str_SQL);

    if(!@$conn->EjecutarSQL($Str_SQL)){
        $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
        $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
        $MSG .= $conn->ObtUltError();
        $MSG .= "En la consulta:\n\n:$Str_SQL";
        $respuesta->addAlert($MSG);
        return $respuesta;
    }

    $conn->EjecutarSQL("COMMIT TRANSACTION A1");
    $MSG = "Datos guardados con exito";
    $respuesta->addAlert($MSG);
    $respuesta->addRedirect($_SERVER['PHP_SELF']);
    //agregarElem($formulario, $respuesta);
    return $respuesta;
}

function guardarElem($formulario){

    if($formulario['hdnIdElemento']){
        return modificarElem($formulario);
    }
    else{
        require("../lib/parametros.php");
        $flag = 0;
        extract($formulario);
        $respuesta = new xajaxResponse();
        $conn = new conexionBD ( );
        $conn->SeleccionBBDD($_BASE_SIS);
        $i=0;

        $conn->EjecutarSQL("BEGIN TRANSACTION A1");

        if($txtFechaDep == ''){$txtFechaDep = "NULL";}
        else{
            $fecaux = explode("/", $txtFechaDep);
            $fechaIni = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFechaDep = "'$fechaIni'";
        }

        $Str_SQL = "INSERT INTO
                    deposito(DEP_NUMERO_DEPOSITO, DEP_OBSERVACIONES, DEP_FECHA_DEPOSITO, DEP_MONTO_DEPOSITO, DEP_CANTIDAD_REGISTROS)
                    VALUES('$txtNumDeposito', '$txtDesc', $txtFechaDep, '$txtMonto', '$txtNumIncritos')";
        //$respuesta->addAlert($Str_SQL);
        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n:$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }
        $conn->EjecutarSQL("COMMIT TRANSACTION A1");
        $MSG = "Datos guardados con exito";
        $respuesta->addAlert($MSG);
        $respuesta->addRedirect($_SERVER['PHP_SELF']);
        return $respuesta;
    }

}

$xajax=new xajax();

$xajax->setCharEncoding("iso-8859-1");
$xajax->decodeUTF8InputOn();
$xajax->registerFunction("agregarElem");
$xajax->registerFunction("eliminar_paquetes");
$xajax->registerFunction("quitarElem");
$xajax->registerFunction("guardarElem");
$xajax->registerFunction("cancelar");
$xajax->registerFunction("modificarElem");
$xajax->registerFunction("cargarElem");


$xajax->processRequests();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
        <title>Administrador de Depósitos</title>
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

        <?php $xajax->printJavascript("../js/xajax"); ?>
		
<script type="text/javascript">

$(document).ready(function(){

    $("#mensajes").dialog({
        bigframe    :   true,
        modal       :   true,
        autoOpen    :   false,
        buttons: {
            Ok: function() {
                $(this).dialog('close');
            }
        }
    });

    $("#btnGuardar").click(function(){
        var obj
        if( (obj = $("#txtNombre")).val() == ""){
            $("#mensajes").attr("innerHTML", "Debe indicar el <b>nombre de la materia</b>.");
            obj.focus();
            obj.addClass("ui-state-highlight");
            $("#mensajes").dialog("open");
            return false
        }
        else if((obj = $("#txtFechaDep")).val() == ""){
            $("#mensajes").attr("innerHTML", "Debe indicar la <b>fecha de inicio</b>.");
            obj.addClass("ui-state-highlight");
            $("#mensajes").dialog("open");
            obj.focus();
            return false
        }
        else{
            xajax_guardarElem(xajax.getFormValues('proyecto'));
        }
    });

    $("#btnEliminar").click(function(){
        if($("#hdnIdElemento").val() == ""){alert("Debe seleccionar un elemento de la lista.");}
        else{xajax_modificarElem(xajax.getFormValues('proyecto'), $("#hdnEstado").val());}
    });

    $("#btnCancelar").click(function(){
       xajax_cancelar();
    });

    $(".textbox").blur(function(){
        $(this).val(($(this).val()).toUpperCase());
    });

});

var num_campos=0;

function showCalendar(id)
{
    Calendar.setup(
        {
            inputField     :    id,
            ifFormat       :    "%d-%m-%Y",
            align          :    "C1",
            singleClick    :    true
        }
    )
}

var modificando = false;

function  selecciona_combo(id_combo,texto)
{

  var i = 0;
  objeto=document.getElementById(id_combo);
  //alert(texto);
  while (objeto.options[i].value != texto)
		  {
			 i ++;

		  }

	objeto.options[i].selected=true;
}

</script>

<style type="text/css">
	html .jquerycssmenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>
	</head>
<body>
<div id="container" class="container">
    <div class="pad2"></div>
    <div id="cuerpo" class="contenedor">
        <form id="proyecto" name="proyecto" onsubmit="return false;">
            <input type="hidden" id="hdnIdElemento" name="hdnIdElemento" value="" />
            <input type="hidden" id="hdnEstado" name="hdnEstado" value="" />
            <h1>Administrar Dep&oacute;sitos</h1>
            <fieldset class="ui-widget ui-widget-content ui-tabs ui-corner-all ui-tabs-collapsible ">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                    <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a style="cursor:text;">Informaci&oacute;n del dep&oacute;sito</a></li>
                </ul>
                <div class="ui-tabs-panel">
                <div id="form1" class="formleft">
                    <div class="clear"></div>
                    <label class="label" for="txtNumDeposito">Numero Dep&oacute;sito:</label>
                    <div id="divNumDeposito" class="div_texbox"><input type="text" name="txtNumDeposito" id="txtNumDeposito" class="textbox txtCodigo"/></div>
                    <label class="label" for="txtFechaDep">Fecha Deposito:</label>
                    <div id="divFechaIni" class="div_texbox"><input type="text" name="txtFechaDep" id="txtFechaDep" class="textbox txtFec Lmedium"/></div>
                    <script type="text/javascript">
                        Calendar.setup({
                            inputField     :    "txtFechaDep",
                            ifFormat       :    "%d/%m/%Y",
                            button         :    "txtFechaDep",
                            align          :    "Bc",
                            singleClick    :    true,
                            electric        :   false
                        });
                    </script>
                    <label class="label" for="txtMonto">Monto:</label>
                    <div id="divMonto" class="div_texbox"><input type="text" name="txtMonto" id="txtMonto" class="textbox txtMoney Lshort"/></div>
                    <label class="label" for="txtNumIncritos">Cantidad Inscritos:</label>
                    <div id="divNumDeposito" class="div_texbox"><input type="text" name="txtNumIncritos" id="txtNumIncritos" class="textbox txtUser Lshort"/></div>
                    <label class="labelObs ui-corner-all" for="txtDesc">Observaci&oacute;n:</label>
                    <div id="divDesc" class="div_texbox-Obs ui-corner-all">
                    <textarea name="txtDesc" id="txtDesc" class="textbox text-area txtCmt Llarge"></textarea>
                    </div>
				</div>
                <div id="form2" class="formright">
                </div>
            </fieldset>
            <div class="button_div ui-corner-all ui-widget-content">
                <button id="btnGuardar" name="btnGuardar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-disk"></div>Grabar</button>
                <button id="btnEliminar" name="btnEliminar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-circle-minus"></div>Desactivar</button>
                <button id="btnCancelar" name="btnCancelar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-cancel"></div>Cancelar</button>
            </div>
            <div class="clear"></div>
            <br />
            
        </form>
    </div>
    <fieldset class="ui-widget ui-widget-content ui-tabs ui-corner-all ui-tabs-collapsible" style="width:96%; margin-left:auto; margin-right:auto;">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a style="cursor:text;">Listado de Dep&oacute;sitos Ingresados</a></li>
        </ul>
<?php
$conn = new conexionBD();
$conn->SeleccionBBDD("$_BASE_SIS");
$Str_SQL = "SELECT 
				`DEP_ID_DEPOSITO`, 
				`DEP_NUMERO_DEPOSITO`, 
				`DEP_FECHA_DEPOSITO`, 
				`DEP_MONTO_DEPOSITO`, 
				`DEP_CANTIDAD_REGISTROS`, 
				`DEP_CANTIDAD_OCUPADOS`, 
				`DEP_OBSERVACIONES` 
			FROM `deposito`";

if(! ($resultENC = @$conn->EjecutarSQL($Str_SQL)) ){
    echo alertError("'Ha ocurrido un error al consultar los datos.El error fue:'");
    echo alertError("'".$conn->ObtUltError()."'");
    echo alertError("'En la consulta:$Str_SQL'");
//            echo alertError($MSG);
//            return $respuesta;
}
?>
            <table width="100%" id="tblDepositos" class="listado">
                <thead>
                    <tr>
                        <th align="center" width="10%">C&oacute;digo</th>
                        <th align="center" width="10%">Número</th>
                        <th align="center" width="33%">Observaci&oacute;n</th>
                        <th align="center" width="10%">Fecha Deposito</th>
                        <th align="center" width="10%">Monto</th>
                        <th align="center" width="15%">Registros V&aacute;lidos</th>
                        <th align="center" width="15%">Registros Utilizados</th>
                        <th align="center" width="10%">Editar</th>
                    </tr>
                </thead>
                <tbody id="tbDetalle">
<?php
while($rowsENC = $conn->FetchArray($resultENC)){
?>
                    <tr id="rowDetalle_<?php echo $rowsENC['DEP_ID_DEPOSITO'];?>">
                        <td><?php echo $rowsENC['DEP_ID_DEPOSITO']; ?></td>
                        <td><?php echo $rowsENC['DEP_NUMERO_DEPOSITO']; ?></td>
                        <td><?php echo $rowsENC['DEP_OBSERVACIONES']; ?></td>
                        <td class="tdDerecha"><?php echo formatDate($rowsENC['DEP_FECHA_DEPOSITO']); ?></td>
                        <td class="tdDerecha"><?php echo $rowsENC['DEP_MONTO_DEPOSITO']; ?></td>
                        <td class="tdDerecha"><?php echo $rowsENC['DEP_CANTIDAD_REGISTROS']; ?></td>
                        <td class="tdDerecha"><?php echo $rowsENC['DEP_CANTIDAD_OCUPADOS']; ?></td>
                        <td class="tdCentro"><img src="../images/page_white_edit.png" onclick="xajax_cargarElem(<?php echo $rowsENC['DEP_ID_DEPOSITO']; ?>)" /> </td>
                    </tr>
<?php
}
?>
                </tbody>
            </table>
    </fieldset>
    <br />
    <br />
</div>
</body>
</html>
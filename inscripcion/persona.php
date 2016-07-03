<?php
session_start();
require("../lib/utiles.php");
require_once("../conexion/class.conexionDB.inc.php");
require_once("../js/xajax/xajax.inc.php");
require_once("../lib/parametros.php");

$Identificacion = $_SESSION['_usuario_rut'];

$codMod = 9;


function cargarIgle($idElem, $respuesta = ''){
    require("../lib/parametros.php");
    if($respuesta == ''){$respuesta = new xajaxResponse();}
    $conn = new conexionBD();
    $conn->SeleccionBBDD($_BASE_SIS);

    $Str_SQL = "SELECT iglesia.IGL_IDIGLESIA, iglesia.IGL_NOMBRE, iglesia.IGL_DIRECCION, iglesia.IGL_FONO1, iglesia.IGL_FONO2,
                iglesia.IGL_PASTOR, iglesia.IGL_PASTORA, iglesia.IGL_DIRPASTOR, iglesia.IGL_FONOPASTOR, iglesia.IGL_FONOPASTOR2,
                iglesia.COM_IDCOMUNA, iglesia.IGL_PERSJURIDICA, iglesia.IGL_COMPASTOR, iglesia.IGL_ESTADO, region.REG_IDREGION as REG_IDREGIONIGLE,
                ciudad.CIU_IDCIUDAD AS CIU_IDCIUDADIGLE, ciudad1.CIU_IDCIUDAD AS CIU_IDCIUDADPAST, region1.REG_IDREGION AS REG_IDREGIONPAST
                FROM comuna  INNER JOIN iglesia ON (comuna.COM_IDCOMUNA = iglesia.COM_IDCOMUNA)
                INNER JOIN ciudad ON (comuna.CIU_IDCIUDAD = ciudad.CIU_IDCIUDAD) INNER JOIN region ON (ciudad.REG_IDREGION = region.REG_IDREGION)
                INNER JOIN comuna comuna1 ON (iglesia.IGL_COMPASTOR = comuna1.COM_IDCOMUNA) INNER JOIN ciudad ciudad1 ON (comuna1.CIU_IDCIUDAD = ciudad1.CIU_IDCIUDAD)
                INNER JOIN region region1 ON (ciudad1.REG_IDREGION = region1.REG_IDREGION)
                WHERE IGL_IDIGLESIA = $idElem";
    $resultIGL = $conn->EjecutarSQL($Str_SQL);

    if($rowsIGL = $conn->FetchArray($resultIGL)){

        $respuesta->addAssign("hdnIdIglesia", "value", $rowsIGL['IGL_IDIGLESIA']);
        $respuesta->addAssign("txtNombreIglesia", "value", $rowsIGL['IGL_NOMBRE']);
        $respuesta->addAssign("txtDirIglesia", "value", $rowsIGL['IGL_DIRECCION']);
        $respuesta->addAssign("txtFonoIglesia", "value", $rowsIGL['IGL_FONO1']);
        $respuesta->addAssign("txtFonoIglesia2", "value", $rowsIGL['IGL_FONO2']);
        $respuesta->addAssign("txtPerJurid", "value", $rowsIGL['IGL_PERSJURIDICA']);
        $respuesta->addAssign("txtNombrePastor", "value", $rowsIGL['IGL_PASTOR']);
        $respuesta->addAssign("txtNombrePastora", "value", $rowsIGL['IGL_PASTORA']);
        $respuesta->addAssign("txtFonoPastor", "value", $rowsIGL['IGL_FONOPASTOR']);
        $respuesta->addAssign("txtFonoPastor2", "value", $rowsIGL['IGL_FONOPASTOR2']);
        $respuesta->addAssign("txtDirPastor", "value", $rowsIGL['IGL_DIRPASTOR']);

        $respuesta->addScriptCall("selecciona_combo", "selRegionIgle", "$rowsIGL[REG_IDREGIONIGLE]");
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[REG_IDREGIONIGLE]", "1", 'Igle');
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[CIU_IDCIUDADIGLE]", "2", 'Igle');

        $respuesta->addScript("setTimeout(\"selecciona_combo('selProvinciaIgle', $rowsIGL[CIU_IDCIUDADIGLE])\", 1200)");
        $respuesta->addScript("setTimeout(\"selecciona_combo('selComunaIgle', $rowsIGL[COM_IDCOMUNA])\", 1200)");

        $respuesta->addScriptCall("selecciona_combo", "selRegionPastor", "$rowsIGL[REG_IDREGIONPAST]");
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[REG_IDREGIONIGLE]", "1", 'Pastor');
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[CIU_IDCIUDADIGLE]", "2", 'Pastor');

        $respuesta->addScript("setTimeout(\"selecciona_combo('selProvinciaPastor', $rowsIGL[CIU_IDCIUDADPAST])\", 1200)");
        $respuesta->addScript("setTimeout(\"selecciona_combo('selComunaPastor', $rowsIGL[COM_IDCOMUNA])\", 1200)");

    }

    return $respuesta;
}

function selectDinamico($idElemento, $tipo, $destino=''){
    require("../lib/parametros.php");
    $respuesta = new xajaxResponse();
    $conn = new conexionBD();
    $conn->SeleccionBBDD($_BASE_SIS);

    $REG = 1; $COM = 3; $PROV = 2;

    if($idElemento == ""){return $respuesta;}
    if($tipo == $REG){
        $Str_SQL = "SELECT CIU_IDCIUDAD, CIU_NOMBRE, CIU_CODIGO
                    FROM ciudad
                    WHERE REG_IDREGION = $idElemento";
        if(!$result = @$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n:$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }
        else{
            $valorAAsignar = "<select name='selProvincia$destino' id='selProvincia$destino' class='textbox select Llarge' onChange='xajax_selectDinamico(this.value, $PROV, \"$destino\")'>
                                <option value=''>Seleccione Provincia</option>";
            while($rows = $conn->FetchArray($result)){
                $valorAAsignar .= "<option value='$rows[CIU_IDCIUDAD]'>$rows[CIU_NOMBRE]</option>";
            }
            $valorAAsignar .= "</select>";
            $divAAsignar = "divProvincia$destino";
        }
    }
    elseif($tipo == $PROV){
        $Str_SQL = "SELECT COM_IDCOMUNA, COM_NOMBRE, COM_CODIGO
                    FROM comuna
                    WHERE CIU_IDCIUDAD = $idElemento";
        if(!$result = @$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n:$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }
        else{
            $valorAAsignar = "<select class=\"textbox select Llarge\" name=\"selComuna$destino\" id=\"selComuna$destino\" >
                                <option value=''>Seleccione Comuna</option>";
            while($rows = $conn->FetchArray($result)){
                $valorAAsignar .= "<option value='$rows[COM_IDCOMUNA]'>$rows[COM_NOMBRE]</option>";
            }
            $valorAAsignar .= "</select>";
            $divAAsignar = "divComuna$destino";
        }
    }

    $respuesta->addAssign("$divAAsignar", "innerHTML", $valorAAsignar);
    return $respuesta;

}

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

    $Str_SQL = "SELECT persona.PER_IDPERSONA, persona.SED_IDSEDE, persona.TP_IDTIPOPERSONA, persona.PER_RUT, persona.PER_NOMBRE,
                persona.PER_APELLIDOS, persona.PER_FECNAC, persona.PER_FECING, persona.PER_DIRECCION, persona.PER_SEXO,
                persona.PER_FONO1, persona.PER_FONO2, persona.PER_FONO3, persona.PER_NACIONALIDAD, persona.PER_ESTCIVIL,
                persona.PER_MAIL, persona.PER_OBSERVACION1, persona.PER_OBSERVACION2, persona.PER_PROFESION, persona.COM_IDCOMUNA,
                persona.PER_FECCONV, persona.PER_CONTACTO1, persona.PER_FONOCONTACTO1, persona.PER_CONTACTO2, persona.PER_FONOCONTACTO2,
                persona.PER_ESTADO, persona.IGL_IDIGLESIA, persona.PER_OCUPACION, persona.PER_FICHA,
                persona.PER_FECREG, persona.PER_USUARIO, region.REG_IDREGION, ciudad.CIU_IDCIUDAD
                FROM persona INNER JOIN comuna ON (persona.COM_IDCOMUNA = comuna.COM_IDCOMUNA)
                INNER JOIN ciudad ON (comuna.CIU_IDCIUDAD = ciudad.CIU_IDCIUDAD)
                INNER JOIN region ON (ciudad.REG_IDREGION = region.REG_IDREGION)
                WHERE persona.PER_IDPERSONA = $idElem";

    $resultENC = $conn->EjecutarSQL($Str_SQL);
    if($rowsENC = $conn->FetchArray($resultENC)){ // Cargo el grupo

        if($rowsENC['PER_ESTADO'] == $_VAL_ACTIVO){
            $respuesta->addAssign("btnEliminar", "innerHTML", '<div style="float:left" class="ui-icon ui-icon-circle-minus"></div>Desactivar');
        }
        else{
            $respuesta->addAssign("btnEliminar", "innerHTML", '<div style="float:left" class="ui-icon ui-icon-circle-check"></div>Activar');
        }
        $respuesta->addAssign("hdnIdElemento", "value", $rowsENC['PER_IDPERSONA']);
        $respuesta->addAssign("hdnIdIglesia", "value", $rowsENC['IGL_IDIGLESIA']);
        $respuesta->addAssign("hdnEstado", "value", $rowsENC['PER_ESTADO']);
        $respuesta->addAssign("txtNumFicha", "value", $rowsENC['PER_FICHA']);
        $respuesta->addAssign("txtNombre", "value", $rowsENC['PER_NOMBRE']);
        $respuesta->addAssign("txtApellidos", "value", $rowsENC['PER_APELLIDOS']);
        $respuesta->addAssign("txtRUT", "value", $rowsENC['PER_RUT']);
        $respuesta->addAssign("txtFechaNac", "value", formatDate($rowsENC['PER_FECNAC']));
        $respuesta->addAssign("txtNacionalidad", "value", $rowsENC['PER_NACIONALIDAD']);
        $respuesta->addAssign("txtProfesion", "value", $rowsENC['PER_PROFESION']);
        $respuesta->addAssign("txtFechaIng", "value", formatDate($rowsENC['PER_FECING']));
        $respuesta->addAssign("txtDir", "value", $rowsENC['PER_DIRECCION']);
        $respuesta->addAssign("txtFono1", "value", $rowsENC['PER_FONO1']);
        $respuesta->addAssign("txtFono2", "value", $rowsENC['PER_FONO2']);
        $respuesta->addAssign("txtFono3", "value", $rowsENC['PER_FONO3']);
        $respuesta->addAssign("txtMail", "value", $rowsENC['PER_MAIL']);
        $respuesta->addAssign("txtDesc", "value", $rowsENC['PER_OBSERVACION1']);
        $respuesta->addAssign("txtContacto", "value", $rowsENC['PER_CONTACTO1']);
        $respuesta->addAssign("txtFonoContacto", "value", $rowsENC['PER_FONOCONTACTO1']);
        $respuesta->addAssign("txtContacto2", "value", $rowsENC['PER_CONTACTO2']);
        $respuesta->addAssign("txtFonoContacto2", "value", $rowsENC['PER_FONOCONTACTO2']);
        $respuesta->addAssign("txtOcupacion", "value", $rowsENC['PER_OCUPACION']);
        $respuesta->addAssign("txtFecConv", "value", formatDate($rowsENC['PER_FECCONV']));


        $respuesta->addScriptCall("selecciona_combo", "selGenero", "$rowsENC[PER_SEXO]");
        $respuesta->addScriptCall("selecciona_combo", "selSede", "$rowsENC[SED_IDSEDE]");
        $respuesta->addScriptCall("selecciona_combo", "selEstCivil", "$rowsENC[PER_ESTCIVIL]");
        $respuesta->addScriptCall("selecciona_combo", "selTipo", "$rowsENC[TP_IDTIPOPERSONA]");
        $respuesta->addScriptCall("selecciona_combo", "selRegion", "$rowsENC[REG_IDREGION]");
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsENC[REG_IDREGION]", "1", '');
        $respuesta->addScriptCall("xajax_selectDinamico", "$rowsENC[CIU_IDCIUDAD]", "2", '');

        $respuesta->addScript("setTimeout(\"selecciona_combo('selProvincia', $rowsENC[CIU_IDCIUDAD])\", 1000)");
        $respuesta->addScript("setTimeout(\"selecciona_combo('selComuna', $rowsENC[COM_IDCOMUNA])\", 1000)");

        cargarIgle($rowsENC['IGL_IDIGLESIA'], $respuesta);

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
    $respuesta->addAssign("hdnIdEmpresa", "value", $rowsENC['EMP_IDEMPRESA']);
    $respuesta->addAssign("txtNombreEmp", "value", $rowsENC['EMP_NOMBRE']);
    $respuesta->addAssign("txtBaseDatos", "value", $rowsENC['EMP_BASEDATOS']);
    $respuesta->addAssign("hdnBaseDatos", "value", $rowsENC['EMP_BASEDATOS']);

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
    if($estado != ''){
        $Str_SQL = "UPDATE persona
                    SET PER_ESTADO = ". abs($estado - 1) ."
                    WHERE PER_IDPERSONA = " .$hdnIdElemento;

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
            $MSG .= $conn->ObtUltError();
            $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
            $respuesta->addAssign("mensajes", "innerHTML", $MSG);
            $respuesta->addScript('$("#mensajes").dialog("open");');
            return $respuesta;
        }
    }
    else{

        if($txtFechaNac == ''){$txtFechaNac = "NULL";}
        else{
            $fecaux = split("/", $txtFechaNac);
            $fechaIni = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFechaNac = "'$fechaIni'";
        }
        if($txtFechaIng == ''){$txtFechaIng = 'NULL';}
        else{
            $fecaux = split("/", $txtFechaIng);
            $fechaFin = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFechaIng = "'$fechaFin'";
        }
        if($txtFecConv == ''){$txtFecConv = 'NULL';}
        else{
            $fecaux = split("/", $txtFecConv);
            $fechaFin = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFecConv = "'$fechaFin'";
        }

        $Str_SQL = "UPDATE persona SET
        SED_IDSEDE = '$selSede',
        TP_IDTIPOPERSONA = '$selTipo',
        PER_RUT = '$txtRUT',
        PER_NOMBRE = '$txtNombre',
        PER_APELLIDOS = '$txtApellidos',
        PER_FECNAC = $txtFechaNac,
        PER_FECING = $txtFechaIng,
        PER_DIRECCION = '$txtDir',
        PER_SEXO = '$selGenero',
        PER_FONO1 = '$txtFono1',
        PER_FONO2 = '$txtFono2',
        PER_FONO3 = '$txtFono3',
        PER_NACIONALIDAD = '$txtNacionalidad',
        PER_ESTCIVIL = '$selEstCivil',
        PER_MAIL = '$txtMail',
        PER_OBSERVACION1 = '$txtDesc',
        PER_PROFESION = '$txtProfesion',
        COM_IDCOMUNA = '$selComuna',
        PER_FECCONV = $txtFecConv,
        PER_CONTACTO1 = '$txtContacto',
        PER_FONOCONTACTO1 = '$txtFonoContacto',
        PER_CONTACTO2 = '$txtContacto2',
        PER_FONOCONTACTO2 = '$txtFonoContacto2',
        PER_OCUPACION = '$txtOcupacion',
        PER_FICHA = '$txtNumFicha',
        IGL_IDIGLESIA = $hdnIdIglesia,
        PER_USUARIO = '$_SESSION[_usuario_rut]' ";
        if($hdnImagen != ''){
            $nomImagen = "../uploads/$hdnImagen";
            $imagen = addslashes(fread(fopen($nomImagen, "rb"), filesize($nomImagen)));
            $Str_SQL .= "  ,
                            PER_TIPOIMAGEN = '$hdnTipo',
                            PER_PESOIMAGEN = '$hdnPeso',
                            PER_NOMBREIMAGEN = '$hdnImagen',
                            PER_FOTO = '$imagen'";
        }
        $Str_SQL .= "WHERE PER_IDPERSONA = " .$hdnIdElemento;

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
            $MSG .= $conn->ObtUltError();
            $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
            $respuesta->addAssign("mensajes", "innerHTML", $MSG);
            $respuesta->addScript('$("#mensajes").dialog("open");');
            return $respuesta;
        }
    }

    $conn->EjecutarSQL("COMMIT TRANSACTION A1");
    $MSG = "Datos guardados con exito";
    $respuesta->addAssign("mensajes", "innerHTML", $MSG);
    $respuesta->addScript('$("#mensajes").dialog("open");');
    $respuesta->addRedirect($_SERVER['PHP_SELF'],2);
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

        $nomImagen = "../uploads/$hdnImagen";
        $imagen = addslashes(fread(fopen($nomImagen, "rb"), filesize($nomImagen)));

        $conn->EjecutarSQL("BEGIN TRANSACTION A1");

        $idIglesia = $hdnIdIglesia;

        if($txtFechaNac == ''){$txtFechaNac = "NULL";}
        else{
            $fecaux = split("/", $txtFechaNac);
            $fechaIni = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFechaNac = "'$fechaIni'";
        }

        if($txtFechaIng == ''){$txtFechaIng = 'NULL';}
        else{
            $fecaux = split("/", $txtFechaIng);
            $fechaFin = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFechaIng = "'$fechaFin'";
        }
        if($txtFecConv == ''){$txtFecConv = 'NULL';}
        else{
            $fecaux = split("/", $txtFecConv);
            $fechaFin = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
            $txtFecConv = "'$fechaFin'";
        }

        $Str_SQL = "INSERT INTO persona(
                    SED_IDSEDE, TP_IDTIPOPERSONA, PER_RUT, PER_NOMBRE, PER_APELLIDOS,
                    PER_FECNAC, PER_FECING, PER_DIRECCION, PER_SEXO, PER_FONO1,
                    PER_FONO2, PER_FONO3, PER_NACIONALIDAD, PER_ESTCIVIL, PER_MAIL,
                    PER_OBSERVACION1, PER_OBSERVACION2, PER_PROFESION, COM_IDCOMUNA, PER_FECCONV,
                    PER_CONTACTO1, PER_FONOCONTACTO1, PER_CONTACTO2, PER_FONOCONTACTO2, PER_FOTO,
                    PER_ESTADO, IGL_IDIGLESIA, PER_OCUPACION, PER_FICHA, PER_FECREG,
                    PER_USUARIO, PER_TIPOIMAGEN, PER_PESOIMAGEN, PER_NOMBREIMAGEN)
                    VALUES(
                    '$selSede', '$selTipo', '$txtRUT', '$txtNombre', '$txtApellidos',
                    $txtFechaNac, $txtFechaIng, '$txtDir', '$selGenero', '$txtFono1',
                    '$txtFono2', '$txtFono3', '$txtNacionalidad', '$selEstCivil', '$txtMail',
                    '$txtDesc', '', '$txtProfesion', '$selComuna', $txtFecConv,
                    '$txtContacto', '$txtFonoContacto', '$txtContacto2', '$txtFonoContacto2', '$imagen',
                    '$_VAL_ACTIVO', '$idIglesia', '$txtOcupacion', '$txtNumFicha', NOW(),
                    '$_SESSION[_usuario_rut]', '$hdnTipo', '$hdnPeso', '$hdnImagen')";
        //$respuesta->addAlert($Str_SQL);

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
            $MSG .= $conn->ObtUltError();
            $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
            $respuesta->addAssign("mensajes", "innerHTML", $MSG);
            $respuesta->addScript('$("#mensajes").dialog("open");');
            return $respuesta;
        }

        $conn->EjecutarSQL("COMMIT TRANSACTION A1");
        $MSG = "Datos guardados con exito";
        $respuesta->addAssign("mensajes", "innerHTML", $MSG);
        $respuesta->addScript('$("#mensajes").dialog("open");');
        $respuesta->addRedirect($_SERVER['PHP_SELF'],2);
        return $respuesta;
    }

}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
        <title>Inscripcibete en el congreso!</title>
		<link type="text/css" href="../css/eggplant/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
		
		
        <style type="text/css">@import url(../js/jscalendar/calendar-blue.css);</style>
        <script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
        <script type="text/javascript" src="../js/jscalendar/lang/calendar-es.js"></script>
        <script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
        <link rel="stylesheet" type="text/css" href="../Css/jqueryslidemenu-3.css" />
        <link href="../Css/style-4.css" rel="stylesheet" type="text/css" />
        <link href="../Css/jquery-ui-1.7.2.custom.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" src="../js/ui/ui.core.js"></script>
        <script type="text/javascript" src="../js/ui/ui.draggable.js"></script>
        <script type="text/javascript" src="../js/ui/ui.resizable.js"></script>
        <script type="text/javascript" src="../js/ui/ui.dialog.js"></script>
        <script type="text/javascript" src="../js/ui/ui.tabs.js"></script>
        <script type="text/javascript" src="../js/ui/effects.core.js"></script>
        <script type="text/javascript" src="../js/ui/effects.highlight.js"></script>
        <script type="text/javascript" src="../js/ui/effects.blind.js"></script>
        <script type="text/javascript" src="../js/jquery.field.js"></script>
        <script type='text/javascript' src="../js/jquery.autocomplete.js"></script>
        <script type='text/javascript' src="../js/bgiframe/jquery.bgiframe.js"></script>
        <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
        <script type="text/javascript" src="../js/jquerymenu/jqueryslidemenu.js"></script>
        <script type='text/javascript' src="../js/jquery.autocomplete.js"></script>
        <script type='text/javascript' src="../js/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){

    $("#mensajes").dialog({
        bigframe    :   true,
        modal       :   false,
        autoOpen    :   false,
        width       :   400,
        show        :   'blind',
        buttons: {
            Ok: function() {
                $(this).dialog('close');
            }
        }
    });
    $("#listado").dialog({
        bigframe    :   true,
        modal       :   true,
        autoOpen    :   false,
        width       :   950,
        buttons: {
            Ok: function() {
                $(this).dialog('close');
            }
        }
    });

    $('#txtNombreIglesia').keyup(function(e){
        if(e.keyCode == 115){
            $("#listado").load("listariglesia.php", '', function(){
                $("#listado").dialog("open");
            });
            return false
        }
    });
    $('#btnBuscarIgle').click(function(){
        $("#listado").load("listariglesia.php", '', function(){
            $("#listado").dialog("open");
        });
    });

    $("#btnGuardar").click(function(){
        xajax_guardarElem(xajax.getFormValues('proyecto'));
    });

    $("#btnEliminar").click(function(){
        if($("#hdnIdElemento").val() == ''){alert("Debe seleccionar un elemento de la lista.");}
        else{xajax_modificarElem(xajax.getFormValues('proyecto'), $("#hdnEstado").val());}
    });

    $(".tab").click(function(){
        $("#" + $(".activa").attr("title")).hide("fast");
        $(".activa").removeClass("activa");

        $(this).addClass("activa");
        $("#" + $(this).attr("title")).show("slow");
    });

    $("#tabs").tabs({
        collapsible: true
    });


    $(".textbox").blur(function(){
        $(this).val(($(this).val()).toUpperCase());
    });

    $("#proyecto").validate();

    $("#btnNuevaIgle").click(function(){
        window.open("iglesia.php", "Crear nueva Iglesia", "");
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

function subir(index){
    ancho=600;
    largo=300;
    datos="../lib/subirimagen.php";
    window.open(datos,"","toolbar=no,scrollbars=yes,Status=yes,Menubar=no,resizable=yes,width="+ancho+",height="+largo+",top=0,left=250")
}

function llenarIglesia(data)
{
    var id = data[5]
    xajax_cargaAlumno(id);

}

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

    var arrowimages={down:['downarrowclass', '<? echo $_SESSION['_url']; ?>/images/down.gif', 23], right:['rightarrowclass', '<? echo $_SESSION['_url']; ?>/images/right.gif']}
    jqueryslidemenu.buildmenu("elMenu", arrowimages)
</script>

<style type="text/css">
	html .jquerycssmenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>

	</head>
<body>
<? mostrar_header(); ?>
<!-- iframe frameborder="0" scrolling="no" width="100%" height="100px" allowtransparency="yes" src="blank.html" style="z-index:100; float:none; position:absolute; display:block;"></iframe -->
<div id="container" class="container">
    <div class="pad2"></div>
    <div id="cuerpo" class="contenedor">
        <form id="proyecto" name="proyecto" onsubmit="return false;">
            <input type="hidden" id="hdnIdElemento" name="hdnIdElemento" value="" />
            <input type="hidden" id="hdnIdIglesia" name="hdnIdIglesia" value="" />
            <input type="hidden" id="hdnRutaImagen" name="hdnRutaImagen" value="" />
            <input type="hidden" id="hdnEstado" name="hdnEstado" value="" />
            <input type="hidden" id="hdnImagen" name="hdnImagen" value="" />
            <input type="hidden" id="hdnTipo" name="hdnTipo" value="" />
            <input type="hidden" id="hdnPeso" name="hdnPeso" value="" />
            <h1><? echo nombreArea($codMod); ?></h1>
            <fieldset class="ui-widget ui-widget-content" id="tabs">
                <ul>
                    <li><a href="#tabs-1">Datos de la persona</a></li>
                    <li><a href="#tabs-2">Datos eclesiasticos</a></li>
                </ul>
                <div id="tabs-1" style="height: auto;">
                <div id="form1" class="formleft">
                    <div class="clear"></div>
                    <label class="label ui-corner-all" for="txtNumFicha">Num. Ficha:</label>
                    <div id="divNumFicha" class="div_texbox ui-corner-all"><input type="text" name="txtNumFicha" id="txtNumFicha" class="textbox txtCodigo Lmedium"/></div>
                    <label class="label ui-corner-all" for="txtNombre">Nombre:</label>
                    <div id="divNombre" class="div_texbox ui-corner-all"><input type="text" name="txtNombre" id="txtNombre" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtApellidos">Apellidos:</label>
                    <div id="divApellidos" class="div_texbox ui-corner-all"><input type="text" name="txtApellidos" id="txtApellidos" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtRUT">RUT:</label>
                    <div id="divRUT" class="div_texbox ui-corner-all"><input type="text" name="txtRUT" id="txtRUT" class="textbox txtCodigo"/></div>
                    <label class="label ui-corner-all" for="selGenero">Genero:</label>
                    <div id="divGenero" class="div_texbox ui-corner-all"><select class="textbox select" name="selGenero" id="selGenero">
                        <option value="<? echo $_VAL_MASCULINO; ?>"><? echo $_VAL_GENERO[$_VAL_MASCULINO]; ?></option>
                        <option value="<? echo $_VAL_FEMENINO; ?>"><? echo $_VAL_GENERO[$_VAL_FEMENINO]; ?></option>
                    </select></div>
                    <label class="label ui-corner-all" for="txtFechaNac">Fecha Nacimiento:</label>
                    <div id="divFechaNac" class="div_texbox ui-corner-all"><input type="text" name="txtFechaNac" id="txtFechaNac" class="textbox txtFec Lmedium"/></div>
                    <script type="text/javascript">
                        Calendar.setup({
                            inputField     :    "txtFechaNac",
                            ifFormat       :    "%d/%m/%Y",
                            button         :    "txtFechaNac",
                            align          :    "Bc",
                            singleClick    :    true,
                            electric        :   false
                        });
                    </script>
                    <label class="label ui-corner-all" for="selEstCivil">Estado Civil:</label>
                    <div id="divEstCivil" class="div_texbox ui-corner-all"><select class="select" name="selEstCivil" id="selEstCivil">
<?
    foreach($_VAL_ESTCIVIL as $indice => $valor){
        echo"<option value=\"$indice\">". $valor . "</option>";
    }
?>
                    </select></div>
                    <label class="label ui-corner-all" for="txtNacionalidad">Nacionalidad:</label>
                    <div id="divNacionalidad" class="div_texbox ui-corner-all"><input type="text" name="txtNacionalidad" id="txtNacionalidad" class="textbox"/></div>
                    <label class="label ui-corner-all" for="txtProfesion">Profesi&oacute;n/Oficio:</label>
                    <div id="divNacionalidad" class="div_texbox ui-corner-all"><input type="text" name="txtProfesion" id="txtProfesion" class="textbox"/></div>
                    <label class="label ui-corner-all" for="txtFechaIng">Fecha Ingreso:</label>
                    <div id="divFechaIng" class="div_texbox ui-corner-all"><input type="text" name="txtFechaIng" id="txtFechaIng" class="textbox txtFec Lmedium"/></div>
                    <script type="text/javascript">
                        Calendar.setup({
                            inputField     :    "txtFechaIng",
                            ifFormat       :    "%d/%m/%Y",
                            button         :    "txtFechaIng",
                            align          :    "Bc",
                            singleClick    :    true,
                            electric        :   false
                        });
                    </script>
                    <label class="label ui-corner-all" for="txtDir">Direcci&oacute;n:</label>
                    <div id="divDir" class="div_texbox ui-corner-all"><input type="text" name="txtDir" id="txtDir" class="textbox txtObs Llarge"/></div>
                    <label class="label ui-corner-all" for="selRegion">Regi&oacute;n:</label>
                    <div id="divRegion" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selRegion" id="selRegion" onchange="xajax_selectDinamico(this.value, 1)">
                            <option value="">Seleccione Regi&oacute;n</option>
<?

$conn = new conexionBD();
$Str_SQL = "SELECT REG_IDREGION, REG_NOMBRE
            FROM region
            WHERE (REG_ESTADO = $_VAL_ACTIVO)";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['REG_IDREGION']; ?>"><? echo $rows['REG_NOMBRE']; ?></option>
<?
}
?>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selProvincia">Provincia:</label>
                    <div id="divProvincia" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selProvincia" id="selProvincia">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>

                    <label class="label ui-corner-all" for="selComuna">Comuna:</label>
                    <div id="divComuna" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selComuna" id="selComuna">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="txtFono1">Fono Casa:</label>
                    <div id="divFono1" class="div_texbox ui-corner-all"><input type="text" name="txtFono1" id="txtFono1" class="textbox txtFono"/></div>
                </div>
                <div id="form2" class="formright">
                    
                    <label class="label ui-corner-all" for="txtFono2">Fono Mobil:</label>
                    <div id="divFono2" class="div_texbox ui-corner-all"><input type="text" name="txtFono2" id="txtFono2" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtFono3">Fono Oficina:</label>
                    <div id="divFono3" class="div_texbox ui-corner-all"><input type="text" name="txtFono3" id="txtFono3" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtMail">Mail:</label>
                    <div id="divMail" class="div_texbox ui-corner-all"><input type="text" name="txtMail" id="txtMail" class="textbox txtMail Llarge"/></div>
                    <label class="labelObs ui-corner-all" for="txtDesc">Observaci&oacute;n:</label>
                    <div id="divDesc" class="div_texbox-Obs ui-corner-all">
                    <textarea name="txtDesc" id="txtDesc" class="text-area Llarge"></textarea>
                    </div>
                    <label class="label ui-corner-all" for="txtContacto">Contacto:</label>
                    <div id="divContacto" class="div_texbox ui-corner-all"><input type="text" name="txtContacto" id="txtContacto" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtFonoContacto">Fono Contacto:</label>
                    <div id="divFonoContacto" class="div_texbox ui-corner-all"><input type="text" name="txtFonoContacto" id="txtFonoContacto" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtContacto2">Contacto 2:</label>
                    <div id="divContacto2" class="div_texbox ui-corner-all"><input type="text" name="txtContacto2" id="txtContacto2" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtFonoContacto2">Fono Contacto 2:</label>
                    <div id="divFonoContacto2" class="div_texbox ui-corner-all"><input type="text" name="txtFonoContacto2" id="txtFonoContacto2" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtImagen1S">Seleccione Im&aacute;gen:</label>
                    <div id="divImagen" class="div_texbox ui-corner-all"><button id="btnSubirImagen" type="button" name="btnSubirImagen" onclick="subir();" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-image"></div>Subir Imagen</button></div>
                    <label class="label ui-corner-all" for="txtOcupacion">Grado Academico:</label>
                    <div id="divOcupacion" class="div_texbox ui-corner-all"><input type="text" name="txtOcupacion" id="txtOcupacion" class="textbox Llarge"/></div>
                    <label class="label ui-corner-all" for="txtFecConv">Fecha Conversi&oacute;n:</label>
                    <div id="divFecConv" class="div_texbox ui-corner-all"><input type="text" name="txtFecConv" id="txtFecConv" class="textbox txtFec Lmedium"/></div>
                    <script type="text/javascript">
                        Calendar.setup({
                            inputField     :    "txtFecConv",
                            ifFormat       :    "%d/%m/%Y",
                            button         :    "txtFecConv",
                            align          :    "Bc",
                            singleClick    :    true,
                            electric        :   false
                        });
                    </script>
                    <label class="label ui-corner-all" for="selSede">Sede:</label>
                    <div id="divSede" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selSede" id="selSede" onchange="">
                            <option value="">Seleccione Sede</option>
<?

$conn = new conexionBD();
$Str_SQL = "SELECT sede.SED_IDESEDE, sede.SED_NOMBRE
            FROM sede
            WHERE (SED_ESTADO = $_VAL_ACTIVO)";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['SED_IDESEDE']; ?>"><? echo $rows['SED_NOMBRE']; ?></option>
<?
}
?>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selTipo">Tipificaci&oacute;n:</label>
                    <div id="divTipo" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selTipo" id="selTipo" onchange="">
                            <option value="">Seleccione Tipificaci&oacute;n</option>
<?

$conn = new conexionBD();
$Str_SQL = "SELECT tipopersona.TP_IDTIPOPERSONA, tipopersona.TP_NOMBRE
            FROM tipopersona
            WHERE (TP_ESTADO = $_VAL_ACTIVO)";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['TP_IDTIPOPERSONA']; ?>"><? echo $rows['TP_NOMBRE']; ?></option>
<?
}
?>
                        </select>
                    </div>
                </div>
                </div>
                <div id="tabs-2">
                <div id="form3" class="formleft">
                    <div class="clear"></div>
                    <label class="label ui-corner-all" for="txtNombreIglesia">Nombre Iglesia:&nbsp;&nbsp;&nbsp;&nbsp;<button id="btnBuscarIgle" type="button" name="btnBuscarIgle" class="ui-corner-all ui-state-default" title="Buscar Iglesia"><div style="float:left" class="ui-icon ui-icon-search"></div></button>&nbsp;<button id="btnNuevaIgle" type="button" name="btnNuevaIgle" class="ui-corner-all ui-state-default" title="Crear Iglesia"><div style="float:left" class="ui-icon ui-icon-document"></div></button></label>
                    <div id="divNombreIglesia" class="div_texbox ui-corner-all"><input type="text" name="txtNombreIglesia" id="txtNombreIglesia" class="textbox txtUser Llarge"/>  </div>
                    <label class="label ui-corner-all" for="txtDirIglesia">Direcci&oacute;n:</label>
                    <div id="divDirIglesia" class="div_texbox ui-corner-all"><input type="text" name="txtDirIglesia" id="txtDirIglesia" class="textbox txtObs Llarge"/></div>
                    <label class="label ui-corner-all" for="selRegionIgle">Regi&oacute;n:</label>
                    <div id="divRegionIgle" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selRegionIgle" id="selRegionIgle" onchange="xajax_selectDinamico(this.value, 1, 'Igle')">
                            <option value="">Seleccione Regi&oacute;n</option>
<?

$conn = new conexionBD();
$Str_SQL = "SELECT REG_IDREGION, REG_NOMBRE
            FROM region
            WHERE (REG_ESTADO = $_VAL_ACTIVO)";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['REG_IDREGION']; ?>"><? echo $rows['REG_NOMBRE']; ?></option>
<?
}
?>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selProvinciaIgle">Provincia:</label>
                    <div id="divProvinciaIgle" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selProvinciaIgle" id="selProvinciaIgle">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selComunaIgle">Comuna:</label>
                    <div id="divComunaIgle" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selComunaIgle" id="selComunaIgle">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="txtFonoIglesia">Fono Iglesia:</label>
                    <div id="divFonoIglesia" class="div_texbox ui-corner-all"><input type="text" name="txtFonoIglesia" id="txtFonoIglesia" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtFonoIglesia2">Fono Iglesia 2:</label>
                    <div id="divFonoIglesia2" class="div_texbox ui-corner-all"><input type="text" name="txtFonoIglesia2" id="txtFonoIglesia2" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtPerJurid">Personalidad Jur&iacute;idica:</label>
                    <div id="divPerJurid" class="div_texbox ui-corner-all"><input type="text" name="txtPerJurid" id="txtPerJurid" class="textbox txtCodigo Lshort"/></div>
                </div>
                <div id="form4" class="formright">
                    <label class="label ui-corner-all" for="txtNombrePastor">Nombre Pastor:</label>
                    <div id="divNombrePastor" class="div_texbox ui-corner-all"><input type="text" name="txtNombrePastor" id="txtNombrePastor" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtNombrePastora">Nombre Conyuge Pastor:</label>
                    <div id="divNombrePastora" class="div_texbox ui-corner-all"><input type="text" name="txtNombrePastora" id="txtNombrePastora" class="textbox txtUser"/></div>
                    <label class="label ui-corner-all" for="txtFonoPastor">Fono Pastor:</label>
                    <div id="divFonoPastor" class="div_texbox ui-corner-all"><input type="text" name="txtFonoPastor" id="txtFonoPastor" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtFonoPastor2">Fono Pastor 2:</label>
                    <div id="divFonoPastor2" class="div_texbox ui-corner-all"><input type="text" name="txtFonoPastor2" id="txtFonoPastor2" class="textbox txtFono"/></div>
                    <label class="label ui-corner-all" for="txtDirPastor">Direcci&oacute;n Pastor:</label>
                    <div id="divDirPastor" class="div_texbox ui-corner-all"><input type="text" name="txtDirPastor" id="txtDirPastor" class="textbox txtObs Llarge"/></div>
                    <label class="label ui-corner-all" for="selRegionPastor">Regi&oacute;n:</label>
                    <div id="divRegionPastor" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selRegionPastor" id="selRegionPastor" onchange="xajax_selectDinamico(this.value, 1, 'Pastor')">
                            <option value="">Seleccione Regi&oacute;n</option>
<?

$conn = new conexionBD();
$Str_SQL = "SELECT REG_IDREGION, REG_NOMBRE
            FROM region
            WHERE (REG_ESTADO = $_VAL_ACTIVO)";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['REG_IDREGION']; ?>"><? echo $rows['REG_NOMBRE']; ?></option>
<?
}
?>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selProvinciaPastor">Provincia:</label>
                    <div id="divProvinciaPastor" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selProvinciaPastore" id="selProvinciaPastor">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>
                    <label class="label ui-corner-all" for="selComunaPastor">Comuna:</label>
                    <div id="divComunaPastor" class="div_texbox ui-corner-all">
                        <select class="textbox select Llarge" name="selComunaPastor" id="selComunaPastor">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                    <br />
                </div>
                
                </div>
            </fieldset>
            <!--/div-->
            <div class="button_div ui-corner-all ui-widget-content">
                <button id="btnGuardar" type="button" name="btnGuardar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-disk"></div>Grabar</button>
                <button id="btnEliminar" type="button" name="btnEliminar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-circle-minus"></div>Desactivar</button>
                <button id="btnCancelar" type="button" name="btnCancelar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-cancel"></div>Cancelar</button>
            </div>
            <div class="clear"></div>
            <br />
        </form>
        
    </div>
        <fieldset class="ui-widget ui-widget-content ui-tabs ui-corner-all ui-tabs-collapsible" style="width:96%; margin-left:auto; margin-right:auto;">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                    <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a style="cursor:text;">Listado de Personas ingresadas</a></li>
                </ul>
<?
    $conn = new conexionBD();
    $conn->SeleccionBBDD("$_BASE_SIS");
    $Str_SQL = "SELECT
  persona.PER_IDPERSONA,
  tipopersona.TP_NOMBRE,
  persona.PER_RUT,
  persona.PER_NOMBRE,
  persona.PER_APELLIDOS,
  persona.PER_DIRECCION,
  persona.PER_FONO1,
  persona.PER_SEXO,
  persona.PER_ESTADO,
  persona.PER_OBSERVACION1,
  sede.SED_NOMBRE,
  persona.PER_FICHA,
  comuna.COM_NOMBRE
FROM
  tipopersona
  INNER JOIN persona ON (tipopersona.TP_IDTIPOPERSONA = persona.TP_IDTIPOPERSONA)
  INNER JOIN sede ON (persona.SED_IDSEDE = sede.SED_IDESEDE)
  INNER JOIN comuna ON (persona.COM_IDCOMUNA = comuna.COM_IDCOMUNA)";

    $resultENC = $conn->EjecutarSQL($Str_SQL);
?>
                    <table width="100%" id="tblGuiasAnt" class="listado">
                        <thead>
                            <tr>
                                <th align="center" width="5%">Ficha</th>
                                <th align="center" width="10%">Tipo</th>
                                <th align="center" width="12%">Sede</th>
                                <th align="center" width="10%">RUT</th>
                                <th align="center" width="12%">Nombre</th>
                                <th align="center" width="12%">Direcci&oacute;n</th>
                                <th align="center" width="10%">Comuna</th>
                                <th align="center" width="3%">Sexo</th>
                                <th align="center" width="3%">Estado</th>
                                <th align="center" width="5%">Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody id="tbDetalle">
<?
    while($rowsENC = $conn->FetchArray($resultENC)){
?>
                            <tr id="rowDetalle_<? echo $rowsENC['PER_IDPERSONA'];?>">
                                <td><? echo $rowsENC['PER_FICHA']; ?></td>
                                <td><? echo $rowsENC['TP_NOMBRE'];?></td>
                                <td><? echo $rowsENC['SED_NOMBRE'];?></td>
                                <td><? echo $rowsENC['PER_RUT']; ?></td>
                                <td><? echo $rowsENC['PER_NOMBRE'] . " " . $rowsENC['PER_APELLIDOS'] ; ?></td>
                                <td><? echo $rowsENC['PER_DIRECCION']; ?></td>
                                <td><? echo $rowsENC['COM_NOMBRE']; ?></td>
                                <td class="tdCentro"><img src="../images/<? echo $_VAL_GENERO_IMAGEN[$rowsENC['PER_SEXO']]; ?>" align="center" alt="<? echo $_VAL_GENERO[$rowsENC['PER_SEXO']]; ?>" title="<? echo $_VAL_GENERO[$rowsENC['PER_SEXO']]; ?>"> </td>
                                <td class="tdCentro"><? echo $_VAL_ESTADOS[ $rowsENC['PER_ESTADO'] ]; ?></td>
                                <td class="tdCentro"><img src="<?echo $_SESSION['_url']; ?>/images/page_white_edit.png" onclick="xajax_cargarElem(<? echo $rowsENC['PER_IDPERSONA']; ?>)" /> </td>
                            </tr>
<?
    }
?>
                        </tbody>
                    </table>
            </fieldset>
            <br />
        <br />
</div>
<? mostrar_footer($codMod); ?>
<div id="mensajes" title="<? echo nombreSistema(); ?>"></div>
<div id="listado" title="Buscar :: <? echo nombreSistema(); ?>"></div>
</body>
</html>
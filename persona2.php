<?
session_start();
require("lib/utiles.php");
require_once("conexion/class.conexionDB.inc.php");
require_once 'js/xajax/xajax.inc.php';
require_once("lib/parametros.php");

$Identificacion = $_SESSION['_usuario_rut'];

$codMod = 9;

function selectDinamico($idElemento, $tipo, $destino=''){
    require("lib/parametros.php");
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
    cancelar($respuesta);   //Limpio los campos
    $conn = new conexionBD();
    $conn->SeleccionBBDD($_BASE_SIS);

    $Str_SQL = "SELECT persona.PER_IDPERSONA, persona.SED_IDSEDE, persona.TP_IDTIPOPERSONA, persona.PER_RUT, persona.PER_NOMBRE,
                persona.PER_APELLIDOS, persona.PER_FECNAC, persona.PER_FECING, persona.PER_DIRECCION, persona.PER_SEXO,
                persona.PER_FONO1, persona.PER_FONO2, persona.PER_FONO3, persona.PER_NACIONALIDAD, persona.PER_ESTCIVIL,
                persona.PER_MAIL, persona.PER_OBSERVACION1, persona.PER_OBSERVACION2, persona.PER_PROFESION, persona.COM_IDCOMUNA,
                persona.PER_FECCONV, persona.PER_CONTACTO1, persona.PER_FONOCONTACTO1, persona.PER_CONTACTO2, persona.PER_FONOCONTACTO2,
                persona.PER_FOTO, persona.PER_ESTADO, persona.IGL_IDIGLESIA, persona.PER_OCUPACION, persona.PER_FICHA,
                persona.PER_FECREG, persona.PER_USUARIO, region.REG_IDREGION, ciudad.CIU_IDCIUDAD
                FROM persona INNER JOIN comuna ON (persona.COM_IDCOMUNA = comuna.COM_IDCOMUNA)
                INNER JOIN ciudad ON (comuna.CIU_IDCIUDAD = ciudad.CIU_IDCIUDAD)
                INNER JOIN region ON (ciudad.REG_IDREGION = region.REG_IDREGION)
                WHERE persona.PER_IDPERSONA = $idElem";

    $resultENC = $conn->EjecutarSQL($Str_SQL);
    if($rowsENC = $conn->FetchArray($resultENC)){ // Cargo el grupo

        if($rowsENC['PER_ESTADO'] == $_VAL_ACTIVO){
            $respuesta->addAssign("btnEliminar", "className", "button btnDel");
            $respuesta->addAssign("btnEliminar", "value", "Desactivar");
        }
        else{
            $respuesta->addAssign("btnEliminar", "className", "button btnOK");
            $respuesta->addAssign("btnEliminar", "value", "Activar");
        }
        $respuesta->addAssign("hdnIdElemento", "value", $rowsENC['PER_IDPERSONA']);
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
        $respuesta->addAssign("hdnRutaImagen", "value", $rowsENC['PER_FOTO']);
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
//
//        $respuesta->addScript("selecciona_combo", "selProvincia", "$rowsENC[CIU_IDCIUDAD]");
//        $respuesta->addScriptCall("selecciona_combo", "selComuna", "$rowsENC[COM_IDCOMUNA]");

        $Str_SQL = "SELECT iglesia.IGL_IDIGLESIA, iglesia.IGL_NOMBRE, iglesia.IGL_DIRECCION, iglesia.IGL_FONO1, iglesia.IGL_FONO2,
                    iglesia.IGL_PASTOR, iglesia.IGL_PASTORA, iglesia.IGL_DIRPASTOR, iglesia.IGL_FONOPASTOR, iglesia.IGL_FONOPASTOR2,
                    iglesia.COM_IDCOMUNA, iglesia.IGL_PERSJURIDICA, iglesia.IGL_COMPASTOR, region.REG_IDREGION as REG_IDREGIONIGLE,
                    ciudad.CIU_IDCIUDAD AS CIU_IDCIUDADIGLE, ciudad1.CIU_IDCIUDAD AS CIU_IDCIUDADPAST, region1.REG_IDREGION AS REG_IDREGIONPAST
                    FROM comuna  INNER JOIN iglesia ON (comuna.COM_IDCOMUNA = iglesia.COM_IDCOMUNA)
                    INNER JOIN ciudad ON (comuna.CIU_IDCIUDAD = ciudad.CIU_IDCIUDAD) INNER JOIN region ON (ciudad.REG_IDREGION = region.REG_IDREGION)
                    INNER JOIN comuna comuna1 ON (iglesia.IGL_COMPASTOR = comuna1.COM_IDCOMUNA) INNER JOIN ciudad ciudad1 ON (comuna1.CIU_IDCIUDAD = ciudad1.CIU_IDCIUDAD)
                    INNER JOIN region region1 ON (ciudad1.REG_IDREGION = region1.REG_IDREGION)
                    WHERE IGL_IDIGLESIA = $rowsENC[IGL_IDIGLESIA]";
        $resultIGL = $conn->EjecutarSQL($Str_SQL);
        if($rowsIGL = $conn->FetchArray($resultIGL)){
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

            $respuesta->addScript("setTimeout(\"selecciona_combo('selProvinciaIgle', $rowsIGL[CIU_IDCIUDADIGLE])\", 1000)");
            $respuesta->addScript("setTimeout(\"selecciona_combo('selComunaIgle', $rowsIGL[COM_IDCOMUNA])\", 1000)");

            $respuesta->addScriptCall("selecciona_combo", "selRegionPastor", "$rowsIGL[REG_IDREGIONPAST]");
            $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[REG_IDREGIONIGLE]", "1", 'Pastor');
            $respuesta->addScriptCall("xajax_selectDinamico", "$rowsIGL[CIU_IDCIUDADIGLE]", "2", 'Pastor');

            $respuesta->addScript("setTimeout(\"selecciona_combo('selProvinciaPastor', $rowsIGL[CIU_IDCIUDADPAST])\", 1000)");
            $respuesta->addScript("setTimeout(\"selecciona_combo('selComunaPastor', $rowsIGL[COM_IDCOMUNA])\", 1000)");

        }


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
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n$Str_SQL";
            $respuesta->addAlert($MSG);
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
        PER_FOTO = '$hdnRutaImagen',
        PER_OCUPACION = '$txtOcupacion',
        PER_FICHA = '$txtNumFicha',
        PER_USUARIO = '$_SESSION[_usuario_rut]'";

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }

        $Str_SQL = "UPDATE iglesia SET
        IGL_NOMBRE = '$txtNombreIglesia',
        IGL_DIRECCION = '$txtDirIglesia',
        IGL_FONO1 = '$txtFonoIglesia',
        IGL_FONO2 = '$txtFonoIglesia2',
        IGL_PASTOR = '$txtNombrePastor',
        IGL_PASTORA = '$txtNombrePastora',
        IGL_DIRPASTOR = '$txtDirPastor',
        IGL_FONOPASTOR = '$txtFonoPastor',
        IGL_FONOPASTOR2 = '$txtFonoPastor2',
        COM_IDCOMUNA = '$selComunaIgle',
        IGL_PERSJURIDICA = '$txtPerJurid',
        IGL_COMPASTOR = '$selComunaPastor'";

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }
    }

    $conn->EjecutarSQL("COMMIT TRANSACTION A1");
    $MSG = "Datos guardados con exito";
    $respuesta->addAlert($MSG);
    $respuesta->addRedirect($_SERVER['PHP_SELF']);
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


        $Str_SQL = "INSERT INTO iglesia(
                    IGL_NOMBRE, IGL_DIRECCION, IGL_FONO1, IGL_FONO2, IGL_PASTOR,
                    IGL_PASTORA, IGL_DIRPASTOR, IGL_FONOPASTOR, IGL_FONOPASTOR2, COM_IDCOMUNA,
                    IGL_PERSJURIDICA, IGL_COMPASTOR)
                    VALUES( '$txtNombreIglesia', '$txtDirIglesia', '$txtFonoIglesia', '$txtFonoIglesia2', '$txtNombrePastor',
                    '$txtNombrePastora', '$txtDirPastor', '$txtFonoPastor', '$txtFonoPastor2', '$selComunaIgle',
                    '$txtPerJurid', '$selComunaPastor')";

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n$Str_SQL";
            $respuesta->addAlert($MSG);
            return $respuesta;
        }

        $idIglesia = $conn->ObtUltID();


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
                    PER_USUARIO)
                    VALUES(
                    '$selSede', '$selTipo', '$txtRUT', '$txtNombre', '$txtApellidos',
                    $txtFechaNac, $txtFechaIng, '$txtDir', '$selGenero', '$txtFono1',
                    '$txtFono2', '$txtFono3', '$txtNacionalidad', '$selEstCivil', '$txtMail',
                    '$txtDesc', '', '$txtProfesion', '$selComuna', $txtFecConv,
                    '$txtContacto', '$txtFonoContacto', '$txtContacto2', '$txtFonoContacto2', '$hdnRutaImagen',
                    '$_VAL_ACTIVO', '$idIglesia', '$txtOcupacion', '$txtNumFicha', NOW(),
                    '$_SESSION[_usuario_rut]')";
        //$respuesta->addAlert($Str_SQL);

        if(!@$conn->EjecutarSQL($Str_SQL)){
            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";
            $MSG .= $conn->ObtUltError();
            $MSG .= "En la consulta:\n\n$Str_SQL";
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
$xajax->registerFunction("modificarElem");
$xajax->registerFunction("quitarElem");
$xajax->registerFunction("guardarElem");
$xajax->registerFunction("cancelar");
$xajax->registerFunction("selectDinamico");
$xajax->registerFunction("cargarElem");


$xajax->processRequests();



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
			<title><? echo nombreModulo($codMod); ?></title>
			<style type="text/css">@import url(../js/jscalendar/calendar-brown.css);</style>
			<script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
			<script type="text/javascript" src="../js/jscalendar/lang/calendar-es.js"></script>
			<script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>

			<script type="text/javascript" src="../js/jquery.js"></script>
			<script type="text/javascript" src="../js/ui/ui.core.js"></script>
			<script type="text/javascript" src="../js/ui/ui.tabs.js"></script>
			<script type="text/javascript" src="../js/jquery.field.js"></script>
			<script type='text/javascript' src="../js/jquery.autocomplete.js"></script>
			<script type='text/javascript' src="../js/bgiframe/jquery.bgiframe.js"></script>
			<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
			<script type="text/javascript" src="../js/jquerymenu/jqueryslidemenu.js"></script>
			<script type='text/javascript' src="../js/jquery.autocomplete.js"></script>
			<link rel="stylesheet" type="text/css" href="../js/jquerymenu/jqueryslidemenu-3.css" />

			<script type="text/javascript">

$(document).ready(function(){

    $("#btnGuardar").click(function(){
        xajax_guardarElem(xajax.getFormValues('proyecto'));
    });

    $("#btnEliminar").click(function(){
        if($("#hdnElemento").val() == ''){alert("Debe seleccionar un elemento de la lista.");}
        else{xajax_modificarElem(xajax.getFormValues('proyecto'), $("#hdnEstado").val());}
    });

    $(".tab").click(function(){
        $("#" + $(".activa").attr("title")).hide("fast");
        $(".activa").removeClass("activa");

        $(this).addClass("activa");
        $("#" + $(this).attr("title")).show("slow");
    });

    $("#tabs").tabs();


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

    var arrowimages={down:['downarrowclass', '<? echo $_SESSION['_url']; ?>/images/down.gif', 23], right:['rightarrowclass', '<? echo $_SESSION['_url']; ?>/images/right.gif']}
    jqueryslidemenu.buildmenu("elMenu", arrowimages)
</script>

<style type="text/css">
	html .jquerycssmenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>
			<link href="../Css/style-4.css" rel="stylesheet" type="text/css" />
			<?php $xajax->printJavascript("../xajax"); ?>

	</head>
<body>
<? mostrar_header(); ?>
<!-- iframe frameborder="0" scrolling="no" width="100%" height="100px" allowtransparency="yes" src="blank.html" style="z-index:100; float:none; position:absolute; display:block;"></iframe -->
<div id="container" class="container">
    <div class="pad2"></div>
    <div id="cuerpo" class="contenedor">
        <form id="proyecto" name="proyecto">
            <input type="hidden" id="hdnIdElemento" name="hdnIdElemento" value="" />
            <input type="hidden" id="hdnRutaImagen" name="hdnRutaImagen" value="" />
            <input type="hidden" id="hdnEstado" name="hdnEstado" value="" />
            <h1><? echo nombreArea($codMod); ?></h1>
            <legend class="legend"><span title="fieldPersonales" class="tab activa">&nbsp;Datos Personales&nbsp;</span> <span class="tab" title="fieldIglesia">&nbsp;Datos Eclesiasticos&nbsp;</span> </legend>
            <fieldset class="fieldset" id="fieldPersonales">
                <div id="tabs">
                    <ui>
                        <li><a href="#tabs-1">Datos de la persona</a></li>
                        <li><a href="#tabs-2">Datos eclesiasticos</a></li>
                    </ui>
                <div id="tabs-1">
                <div id="form1" class="formleft">
                    <div class="clear"></div>
                    <label class="label" for="txtNumFicha">Num. Ficha:</label>
                    <div id="divNumFicha" class="div_texbox"><input type="text" name="txtNumFicha" id="txtNumFicha" class="textbox txtCodigo Lmedium"/></div>
                    <label class="label" for="txtNombre">Nombre:</label>
                    <div id="divNombre" class="div_texbox"><input type="text" name="txtNombre" id="txtNombre" class="textbox txtUser"/></div>
                    <label class="label" for="txtApellidos">Apellidos:</label>
                    <div id="divApellidos" class="div_texbox"><input type="text" name="txtApellidos" id="txtApellidos" class="textbox txtUser"/></div>
                    <label class="label" for="txtRUT">RUT:</label>
                    <div id="divRUT" class="div_texbox"><input type="text" name="txtRUT" id="txtRUT" class="textbox txtCodigo"/></div>
                    <label class="label" for="selGenero">Genero:</label>
                    <div id="divGenero" class="div_texbox"><select class="textbox select" name="selGenero" id="selGenero">
                        <option value="<? echo $_VAL_MASCULINO; ?>"><? echo $_VAL_GENERO[$_VAL_MASCULINO]; ?></option>
                        <option value="<? echo $_VAL_FEMENINO; ?>"><? echo $_VAL_GENERO[$_VAL_FEMENINO]; ?></option>
                    </select></div>
                    <label class="label" for="txtFechaNac">Fecha Nacimiento:</label>
                    <div id="divFechaNac" class="div_texbox"><input type="text" name="txtFechaNac" id="txtFechaNac" class="textbox txtFec Lmedium"/></div>
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
                    <label class="label" for="selEstCivil">Estado Civil:</label>
                    <div id="divEstCivil" class="div_texbox"><select class="select" name="selEstCivil" id="selEstCivil">
<?
    foreach($_VAL_ESTCIVIL as $indice => $valor){
        echo"<option value=\"$indice\">". $valor . "</option>";
    }
?>
                    </select></div>
                    <label class="label" for="txtNacionalidad">Nacionalidad:</label>
                    <div id="divNacionalidad" class="div_texbox"><input type="text" name="txtNacionalidad" id="txtNacionalidad" class="textbox"/></div>
                    <label class="label" for="txtProfesion">Profesi&oacute;n/Oficio:</label>
                    <div id="divNacionalidad" class="div_texbox"><input type="text" name="txtProfesion" id="txtProfesion" class="textbox"/></div>
                    <label class="label" for="txtFechaIng">Fecha Ingreso:</label>
                    <div id="divFechaIng" class="div_texbox"><input type="text" name="txtFechaIng" id="txtFechaIng" class="textbox txtFec Lmedium"/></div>
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
                    <label class="label" for="txtDir">Direcci&oacute;n:</label>
                    <div id="divDir" class="div_texbox"><input type="text" name="txtDir" id="txtDir" class="textbox txtObs Llarge"/></div>
                    <label class="label" for="selRegion">Regi&oacute;n:</label>
                    <div id="divRegion" class="div_texbox">
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
                    <label class="label" for="selProvincia">Provincia:</label>
                    <div id="divProvincia" class="div_texbox">
                        <select class="textbox select Llarge" name="selProvincia" id="selProvincia">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>

                    <label class="label" for="selComuna">Comuna:</label>
                    <div id="divComuna" class="div_texbox">
                        <select class="textbox select Llarge" name="selComuna" id="selComuna">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                </div>
                <div id="form2" class="formright">
                    <label class="label" for="txtFono1">Fono Casa:</label>
                    <div id="divFono1" class="div_texbox"><input type="text" name="txtFono1" id="txtFono1" class="textbox txtFono"/></div>
                    <label class="label" for="txtFono2">Fono Mobil:</label>
                    <div id="divFono2" class="div_texbox"><input type="text" name="txtFono2" id="txtFono2" class="textbox txtFono"/></div>
                    <label class="label" for="txtFono3">Fono Oficina:</label>
                    <div id="divFono3" class="div_texbox"><input type="text" name="txtFono3" id="txtFono3" class="textbox txtFono"/></div>
                    <label class="label" for="txtMail">Mail:</label>
                    <div id="divMail" class="div_texbox"><input type="text" name="txtMail" id="txtMail" class="textbox txtMail Llarge"/></div>
                    <label class="label" for="txtDesc">Observaci&oacute;n:</label>
                    <div id="divDesc" class="div_texbox"><input type="text" name="txtDesc" id="txtDesc" class="textbox txtCmt Llarge"/></div>
                    <label class="label" for="txtContacto">Contacto:</label>
                    <div id="divContacto" class="div_texbox"><input type="text" name="txtContacto" id="txtContacto" class="textbox txtUser"/></div>
                    <label class="label" for="txtFonoContacto">Fono Contacto:</label>
                    <div id="divFonoContacto" class="div_texbox"><input type="text" name="txtFonoContacto" id="txtFonoContacto" class="textbox txtFono"/></div>
                    <label class="label" for="txtContacto2">Contacto 2:</label>
                    <div id="divContacto2" class="div_texbox"><input type="text" name="txtContacto2" id="txtContacto2" class="textbox txtUser"/></div>
                    <label class="label" for="txtFonoContacto2">Fono Contacto 2:</label>
                    <div id="divFonoContacto2" class="div_texbox"><input type="text" name="txtFonoContacto2" id="txtFonoContacto2" class="textbox txtFono"/></div>
                    <label class="label" for="txtImagen1S">Seleccione Im&aacute;gen:</label>
                    <div id="divImagen" class="div_texbox"><input type="button" id="txtImagen1S" onclick="subir()" class="button btnUpload" value="Subir Imagen" /></div>
                    <label class="label" for="txtOcupacion">Grado Academico:</label>
                    <div id="divOcupacion" class="div_texbox"><input type="text" name="txtOcupacion" id="txtOcupacion" class="textbox Llarge"/></div>
                    <label class="label" for="txtFecConv">Fecha Conversi&oacute;n:</label>
                    <div id="divFecConv" class="div_texbox"><input type="text" name="txtFecConv" id="txtFecConv" class="textbox txtFec Lmedium"/></div>
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
                    <label class="label" for="selSede">Sede:</label>
                    <div id="divSede" class="div_texbox">
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
                    <label class="label" for="selTipo">Tipificaci&oacute;n:</label>
                    <div id="divTipo" class="div_texbox">
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
                </div>
            </fieldset>
            <fieldset class="fieldset" style="display:none;" id="fieldIglesia">
                <div>
                <div id="form3" class="formleft">
                    <div class="clear"></div>
                    <label class="label" for="txtNombreIglesia">Nombre Iglesia:</label>
                    <div id="divNombreIglesia" class="div_texbox"><input type="text" name="txtNombreIglesia" id="txtNombreIglesia" class="textbox txtUser"/></div>
                    <label class="label" for="txtDirIglesia">Direcci&oacute;n:</label>
                    <div id="divDirIglesia" class="div_texbox"><input type="text" name="txtDirIglesia" id="txtDirIglesia" class="textbox txtObs Llarge"/></div>
                    <label class="label" for="selRegionIgle">Regi&oacute;n:</label>
                    <div id="divRegionIgle" class="div_texbox">
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
                    <label class="label" for="selProvinciaIgle">Provincia:</label>
                    <div id="divProvinciaIgle" class="div_texbox">
                        <select class="textbox select Llarge" name="selProvinciaIgle" id="selProvinciaIgle">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>
                    <label class="label" for="selComunaIgle">Comuna:</label>
                    <div id="divComunaIgle" class="div_texbox">
                        <select class="textbox select Llarge" name="selComunaIgle" id="selComunaIgle">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                    <label class="label" for="txtFonoIglesia">Fono Iglesia:</label>
                    <div id="divFonoIglesia" class="div_texbox"><input type="text" name="txtFonoIglesia" id="txtFonoIglesia" class="textbox txtFono"/></div>
                    <label class="label" for="txtFonoIglesia2">Fono Iglesia 2:</label>
                    <div id="divFonoIglesia2" class="div_texbox"><input type="text" name="txtFonoIglesia2" id="txtFonoIglesia2" class="textbox txtFono"/></div>
                    <label class="label" for="txtPerJurid">Personalidad Jur&iacute;idica:</label>
                    <div id="divPerJurid" class="div_texbox"><input type="text" name="txtPerJurid" id="txtPerJurid" class="textbox txtCodigo Lshort"/></div>
                </div>
                <div id="form4" class="formright">
                    <label class="label" for="txtNombrePastor">Nombre Pastor:</label>
                    <div id="divNombrePastor" class="div_texbox"><input type="text" name="txtNombrePastor" id="txtNombrePastor" class="textbox txtUser"/></div>
                    <label class="label" for="txtNombrePastora">Nombre Conyuge Pastor:</label>
                    <div id="divNombrePastora" class="div_texbox"><input type="text" name="txtNombrePastora" id="txtNombrePastora" class="textbox txtUser"/></div>
                    <label class="label" for="txtFonoPastor">Fono Pastor:</label>
                    <div id="divFonoPastor" class="div_texbox"><input type="text" name="txtFonoPastor" id="txtFonoPastor" class="textbox txtFono"/></div>
                    <label class="label" for="txtFonoPastor2">Fono Pastor 2:</label>
                    <div id="divFonoPastor2" class="div_texbox"><input type="text" name="txtFonoPastor2" id="txtFonoPastor2" class="textbox txtFono"/></div>
                    <label class="label" for="txtDirPastor">Direcci&oacute;n Pastor:</label>
                    <div id="divDirPastor" class="div_texbox"><input type="text" name="txtDirPastor" id="txtDirPastor" class="textbox txtObs Llarge"/></div>
                    <label class="label" for="selRegionPastor">Regi&oacute;n:</label>
                    <div id="divRegionPastor" class="div_texbox">
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
                    <label class="label" for="selProvinciaPastor">Provincia:</label>
                    <div id="divProvinciaPastor" class="div_texbox">
                        <select class="textbox select Llarge" name="selProvinciaPastore" id="selProvinciaPastor">
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>
                    <label class="label" for="selComunaPastor">Comuna:</label>
                    <div id="divComunaPastor" class="div_texbox">
                        <select class="textbox select Llarge" name="selComunaPastor" id="selComunaPastor">
                            <option value="">Seleccione Comuna</option>
                        </select>
                    </div>
                </div>
                </div>
            </fieldset>

            <div class="button_div">
                <input id="btnGuardar" name="btnGuardar" type="button" class="button btnSave" onclick="" value="Guardar" />
                <input name="btnEliminar" id="btnEliminar" type="button" class="button btnDel" onclick="" value="Desactivar" />
                <input type="button" class="button btnCancel" onclick="xajax_limpiar_campos();" value="Cancelar" />
            </div>
            <div class="clear"></div>
            <br />
            <fieldset class="fieldset">
				<legend class="legend">Listado de Personas ingresadas</legend>
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
        </form>
        <br />
        <br />
    </div>
</div>
<? mostrar_footer($codMod); ?>
</body>
</html>
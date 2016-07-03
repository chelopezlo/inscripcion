<?php
require_once("../conexion/class.conexionDB.inc.php");
require_once("../lib/parametros.php");
//require_once("../js/xajax/xajax.inc.php");

if(isset($_POST) && isset($_POST["action"]))
{
	switch($_POST["action"])
	{
		case "select":
			echo selectDinamico($_POST["value"], $_POST["field"], '');
			
			break;

		case 'save':
			header('Content-Type: application/json');
			$post = array();
			parse_str($_POST['field'], $post);
			//echo print_r();
			echo json_encode(guardarElem($post));

			break;	

	}
exit(0);	
}

function selectDinamico($idElemento, $tipo, $destino=''){
    require("../lib/parametros.php");
    //$respuesta = new xajaxResponse();
    $conn = new conexionBD();
    $conn->SeleccionBBDD($_BASE_SIS);

    $REG = "selRegion"; $COM = "selComuna"; $PROV = "selProvincia";

    if($idElemento == ""){return $respuesta;}

    if($tipo == $REG){
        $Str_SQL = "SELECT PROVINCIA_ID, PROVINCIA_NOMBRE
                    FROM provincia
                    WHERE PROVINCIA_REGION_ID = $idElemento";

        if(!$result = @$conn->EjecutarSQL($Str_SQL)){

            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");

            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";

            $MSG .= $conn->ObtUltError();

            $MSG .= "En la consulta:\n\n:$Str_SQL";

            $respuesta->addAlert($MSG);

            return $respuesta;

        }

        else{

            $valorAAsignar = "<option value=''>Seleccione Provincia</option>";

            while($rows = $conn->FetchArray($result)){

                $valorAAsignar .= "<option value='$rows[PROVINCIA_ID]'>$rows[PROVINCIA_NOMBRE]</option>";

            }

            //$valorAAsignar .= "</select>";

            $divAAsignar = "divProvincia$destino";

        }

    }

    elseif($tipo == $PROV){

        $Str_SQL = "SELECT COMUNA_ID, COMUNA_NOMBRE

                    FROM comuna

                    WHERE COMUNA_PROVINCIA_ID = $idElemento";

        if(!$result = @$conn->EjecutarSQL($Str_SQL)){

            $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");

            $MSG = "Ha ocurrido un error al eliminar los datos.\nEl error fue:\n";

            $MSG .= $conn->ObtUltError();

            $MSG .= "En la consulta:\n\n:$Str_SQL";

            $respuesta->addAlert($MSG);

            return $respuesta;

        }

        else{

            $valorAAsignar = "<option value=''>Seleccione Comuna</option>";

            while($rows = $conn->FetchArray($result)){

                $valorAAsignar .= "<option value='$rows[COMUNA_ID]'>$rows[COMUNA_NOMBRE]</option>";

            }                        

        }

    }



    //$respuesta->addAssign("$divAAsignar", "innerHTML", $valorAAsignar);

    return $valorAAsignar;



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
	$mensajes = array();
	$respuesta = array('result' => 'ok', 'mensajes' => $mensajes);
        //$respuesta = new xajaxResponse();
        $conn = new conexionBD ( );
        $conn->SeleccionBBDD($_BASE_SIS);
        $i=0;
        $validacionDeposito = validarDeposito($txtDeposito);
 
        if($validacionDeposito["descripcion"] == "")
        {
            $idDeposito = $validacionDeposito["deposito"];
            $conn->EjecutarSQL("BEGIN TRANSACTION A1");
            if($txtFechaNac == ''){$txtFechaNac = "NULL";}
            else{
                $fecaux = split("/", $txtFechaNac);
                $fechaIni = date("Y-m-d", mktime(0,0,0, $fecaux[1], $fecaux[0], $fecaux[2]));
                $txtFechaNac = "'$fechaIni'";
            }

            $Str_SQL = "
                        INSERT INTO persona(
                          PER_RUT,
                          PER_NOMBRECOMPLETO,
                          PER_GENERO,
                          PER_FECHANAC,
                          PER_OCUPACION,
                          PER_DIRECCION,
                          COMUNA_ID,
                          PER_FONO,
                          PER_EMAIL,
                          IGL_ID_IGLESIA,
                          PER_OBSERVACIONES,
                          PER_FACEBOOK,
                          PER_TWITTER,
                          PER_ESLIDER,
                          PER_FECHAINGRESO)
                        VALUES(
                          '$txtRUT',
                          '$txtNombre',
                          '$selGenero',
                          $txtFechaNac,
                          '$txtProfesion',
                          '$txtDir',
                          '$selComuna',
                          '$txtFono1',
                          '$txtMail',
                          '$selIglesia',
                          '$txtComentarios',
                          '$txtFacebook',
                          '$txtTwitter',
                          '$ckbLider',
                          NOW())";

             if(!@$conn->EjecutarSQL($Str_SQL)){
                $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
                $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
                $MSG .= $conn->ObtUltError();
                $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
		$mensaje = array('tipo' => 'error', 'texto' => $MSG);
		array_push($respuesta['mensajes'], $mensaje);
		$respuesta['result'] = 'KO';

		return $respuesta;
            }
            
            $idPersona = mysql_insert_id($conn->id);

            $Str_SQL = "
                        INSERT INTO  inscripcion(
                          INS_FECHA_INSCRIPCION,
                          INS_NUMERODEPOSITO,
                          PER_ID_PERSONA)
                        VALUES(
                          NOW(),
                          '$idDeposito',
                          '$idPersona')";
                  
            //$respuesta->addAlert($Str_SQL);

            if(!@$conn->EjecutarSQL($Str_SQL)){
                $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
                $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
                $MSG .= $conn->ObtUltError();
                $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
                $mensaje = array('tipo' => 'error', 'texto' => $MSG);
		array_push($respuesta['mensajes'], $mensaje);
		$respuesta['result'] = 'KO';
                return $respuesta;
            }
            
            $codInscripcion = mysql_insert_id($conn->id);
            
            $numeroInscripciones = $validacionDeposito["numeroInscripciones"] + 1;

            $Str_SQL = "UPDATE `deposito` SET `DEP_CANTIDAD_OCUPADOS` = '$numeroInscripciones' WHERE `DEP_ID_DEPOSITO` = '$idDeposito'";
                  
            //$respuesta->addAlert($Str_SQL);

            if(!@$conn->EjecutarSQL($Str_SQL)){
                $conn->EjecutarSQL("ROLLBACK TRANSACTION A1");
                $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
                $MSG .= $conn->ObtUltError();
                $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
                $mensaje = array('tipo' => 'error', 'texto' => $MSG);
		array_push($respuesta['mensajes'], $mensaje);
		$respuesta['result'] = 'KO';
                return $respuesta;
            }

            $conn->EjecutarSQL("COMMIT TRANSACTION A1");
            $MSG = "Datos guardados con exito";
            $mensaje = array('tipo' => 'exito', 'texto' => $MSG);
	    array_push($respuesta['mensajes'], $mensaje);
	    $respuesta['result'] = 'OK';
            return $respuesta;
        }
        else
	{
	    $mensaje = array('tipo' => 'error', 'texto' => $validacionDeposito['descripcion']);
	    array_push($respuesta['mensajes'], $mensaje);
            $respuesta['result'] = 'KO';
        }
    }
    return $respuesta;
}

function validarDeposito($numeroDeposito)
{
    require("../lib/parametros.php");
    $flag = 0;
    $conn = new conexionBD ( );
    $conn->SeleccionBBDD($_BASE_SIS);
    $i=0;
    
    $result = array(
    "deposito" => 0,
    "descripcion" => "",
    "numeroInscripciones" => 0
    );

    $Str_SQL = "SELECT 
                    `DEP_ID_DEPOSITO`, 
                    `DEP_NUMERO_DEPOSITO`, 
                    `DEP_FECHA_DEPOSITO`, 
                    `DEP_MONTO_DEPOSITO`, 
                    `DEP_CANTIDAD_REGISTROS`, 
                    `DEP_CANTIDAD_OCUPADOS`, 
                    `DEP_OBSERVACIONES` 
                FROM `deposito` 
                WHERE `DEP_NUMERO_DEPOSITO` = '$numeroDeposito'";

    if(!($resultENC = @$conn->EjecutarSQL($Str_SQL))){
        $MSG = "<b>Ha ocurrido un error al guardar los datos</b>.<br /><br />El error fue:<br />";
        $MSG .= $conn->ObtUltError();
        $MSG .= "<br />En la consulta:<br /><br />$Str_SQL";
		$result["descripcion"] = $MSG;
        return $result;
    }

    $contador = 0;
    if($conn->NumRows($resultENC) > 0)
    {
        while($rowsENC = $conn->FetchArray($resultENC)){
            $contador++;
            if($rowsENC['DEP_CANTIDAD_REGISTROS'] > $rowsENC['DEP_CANTIDAD_OCUPADOS'])
            {
                $result["deposito"] = $rowsENC['DEP_ID_DEPOSITO'];
                $result["numeroInscripciones"] = $rowsENC['DEP_CANTIDAD_OCUPADOS'];
                return $result;
            }
            else
            {
				$result["descripcion"] = "Este depósito ha alcanzado el número máximo de registros permitido.";
                return $result;
            }
        }
    }
    $result["descripcion"] = "No se ha encontrado el depósito indicado.";
    return $result;

}

function buscarElem($formulario){

    require("../lib/parametros.php");

    $respuesta = new xajaxResponse();

//    cancelar($respuesta);   //Limpio los campos
    extract($formulario);
    
    $conn = new conexionBD();

    $conn->SeleccionBBDD($_BASE_SIS);



    $Str_SQL = "SELECT 
                    `PER_ID_PERSONA`, 
                    `PER_RUT`, 
                    `PER_NOMBRECOMPLETO`, 
                    `PER_GENERO`, 
                    `PER_FECHANAC`, 
                    `PER_OCUPACION`, 
                    `PER_DIRECCION`, 
                    `COMUNA_ID`, 
                    `PER_FONO`, 
                    `PER_EMAIL`, 
                    `IGL_ID_IGLESIA`, 
                    `PER_OBSERVACIONES`, 
                    `PER_DEPOSITO`, 
                    `PER_FACEBOOK`, 
                    `PER_TWITTER`, 
                    `PER_ESLIDER`, 
                    `PER_FECHAINGRESO` 
                FROM `persona` 
                WHERE PER_RUT = '$txtRut'";

    $resultENC = $conn->EjecutarSQL($Str_SQL);
    if($rowsENC = $conn->FetchArray($resultENC)){ // Cargo el grupo
        if($rowsENC['PER_ESTADO'] == $_VAL_ACTIVO){
            $respuesta->addAssign("btnEliminar", "innerHTML", '<div style="float:left" class="ui-icon ui-icon-circle-minus"></div>Desactivar');
        }
        else{
            $respuesta->addAssign("btnEliminar", "innerHTML", '<div style="float:left" class="ui-icon ui-icon-circle-check"></div>Activar');
        }

        $respuesta->addAssign("hdnIdElemento", "value", $rowsENC['PER_ID_PERSONA']);

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




/*
$xajax=new xajax();



$xajax->setCharEncoding("iso-8859-1");

$xajax->decodeUTF8InputOn();

$xajax->registerFunction("modificarElem");

$xajax->registerFunction("guardarElem");

$xajax->registerFunction("cancelar");

$xajax->registerFunction("selectDinamico");

$xajax->registerFunction("cargarElem");

$xajax->processRequests();
*/


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inscríbete en el congreso!</title>
    <link href="../js/jqueryui/jquery-ui.css" rel="stylesheet">
    <link type="text/css" href="../css/style-4.css" rel="stylesheet" />
    <link type="text/css" href="../js/jscalendar/calendar-blue.css" rel="stylesheet" />
    <script src="../js/jqueryui/external/jquery/jquery.js"></script>
    <script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
    <script type="text/javascript" src="../js/jscalendar/lang/calendar-es.js"></script>
    <script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
	<script src="../js/jqueryui/jquery-ui.js"></script>
    <script type="text/javascript" src="../js/jquery.Rut.min.js"></script>
    
    <?php //$xajax->printJavascript("../js/xajax"); ?>
    
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

            $("#btnGuardar").click(function(){
				$.ajax({
				  type: "POST",
				  url: "registro.php",
				  data: {action: "save", field: $('#proyecto').serialize()},
				  dataType: 'json',
				  success: function(resp){
					  if(resp.mensajes.lenght() > 0)
					  {
					       showMessages(resp.mensajes, $("#mensajes"));
					  }
					  //$("#txtComentarios").html(resp);
				  }
				});
                //guardarElem($('#proyecto').serialize());
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

            $("#btnNuevaIgle").click(function(){
                window.open("iglesia.php", "Crear nueva Iglesia", "");
            });
    
            /*$("#txtRut").Rut({
                on_error: function(){alert("El Rut ingresado no es correcto.");}
                on_success: function(){ xajax_guardarElem(xajax.getFormValues('proyecto')); }
            });*/
			
			$("#selRegion, #selProvincia").change(function(){
				var destino = $(this).attr("related")
				$.ajax({
				  type: "POST",
				  url: "registro.php",
				  data: {action: "select", field: $(this).attr("id"), value: $(this).val()},
				  success: function(resp){
					  $("#" + destino).html(resp);
					  //$("#selProvincia").html(resp);
				  }
				});
			});
        });

        var num_campos=0;

        function showCalendar(id) {
            Calendar.setup(
                {
                    inputField     :    id,
                    ifFormat       :    "%d-%m-%Y",
                    align          :    "C1",
                    singleClick    :    true
                }
            )
        }

        function  selecciona_combo(id_combo,texto) {
            var i = 0;
            objeto=document.getElementById(id_combo);
            //alert(texto);
            while (objeto.options[i].value != texto) {
                i ++;
            }

            objeto.options[i].selected=true;
        }
		
		function showMessages(messages, dialog)
		{
			if(messages.lenght() > 0 )
			{
				for(var i = 0; i < messages.lenght; i++)
				{
					var msj = '';
					if(messages[i].tipo == 'exito')
					{
						msj = "<div class='ui-state-highlight ui-corner-all'>" + messages[i].texto + "</div>";
					}
					else if(messages[i].tipo == 'error')
					{
						msj = "<div class='ui-state-error ui-corner-all'>" + messages[i].texto + "</div>";
					}
					dialog.append(msj);
				}
				
				dialog.dialog('open');
			}
		}
    </script>
</head>
<body>
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
            <h1>Congreso Territorial de Jóvenes 2012 - Territorio Sur-Centro</h1>
            <fieldset class="ui-widget ui-widget-content" id="tabs">
                <ul>
                    <li><a href="#tabs-1">An&iacute;mate a ser transformado!</a></li>
                    <li><a href="#tabs-2">Quiero inscribirme!</a></li>
                </ul>
                <div id="tabs-1" style="height: auto;" class="tab">
                    <img src="../images/fondo.jpg"  style="display:block;margin:0 auto 0 auto;" />
                </div>
                <div id="tabs-2" style="height: auto;" class="tab">
                <div id="form1" class="formleft">
                    <div class="clear"></div>
                    <label class="label ui-corner-all" for="txtDeposito">El número de deposito es:</label>
                    <div id="divRUT" class="div_texbox ui-corner-all"><input type="text" name="txtDeposito" id="txtDeposito" class="textbox txtCodigo Llarge"/></div>
                    <label class="label ui-corner-all" for="txtRUT">Mi RUT es:</label>
                    <div id="divRUT" class="div_texbox ui-corner-all"><input type="text" name="txtRUT" id="txtRUT" class="textbox txtIdentificacion"/></div>
                    <label class="label ui-corner-all" for="txtNombre">Me llamo:</label>
                    <div id="divNombre" class="div_texbox ui-corner-all"><input type="text" name="txtNombre" id="txtNombre" class="textbox txtUser Llarge"/></div>
                    <label class="label ui-corner-all" for="selGenero">Soy:</label>
                    <div id="divGenero" class="div_texbox ui-corner-all"><select class="selGenero select Llarge" name="selGenero" id="selGenero">
                        <option value="<? echo $_VAL_MASCULINO; ?>"><?php echo $_VAL_GENERO[$_VAL_MASCULINO]; ?><img src='../images/<?php echo $_VAL_GENERO_IMAGEN[$_VAL_MASCULINO]; ?>' /></option>
                        <option value="<? echo $_VAL_FEMENINO; ?>"><? echo $_VAL_GENERO[$_VAL_FEMENINO]; ?></option>
                    </select></div>
                    <label class="label ui-corner-all" for="txtFechaNac">Nací el:</label>
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

                    <label class="label ui-corner-all" for="txtProfesion">Me dedico a (Profesi&oacute;n/Oficio):</label>

                    <div id="divNacionalidad" class="div_texbox ui-corner-all"><input type="text" name="txtProfesion" id="txtProfesion" class="textbox txtOcupacion Llarge"/></div>

                    <label class="label ui-corner-all" for="txtDir">Vivo en:</label>

                    <div id="divDir" class="div_texbox ui-corner-all"><input type="text" name="txtDir" id="txtDir" class="textbox txtDireccion Llarge"/></div>

                    <label class="label ui-corner-all" for="selRegion">Regi&oacute;n:</label>

                    <div id="divRegion" class="div_texbox ui-corner-all">

                        <select class="selSuc select Llarge" related="selProvincia" name="selRegion" id="selRegion" >

                            <option value="">Seleccione Regi&oacute;n</option>

<?



$conn = new conexionBD();
$Str_SQL = "SELECT REGION_ID, REGION_NOMBRE
            FROM region";
$result = $conn->EjecutarSQL($Str_SQL);
while($rows = $conn->FetchArray($result)){
?>
                            <option value="<? echo $rows['REGION_ID']; ?>"><? echo $rows['REGION_NOMBRE']; ?></option>
<?
}
?>

                        </select>

                    </div>

                    <label class="label ui-corner-all" for="selProvincia">Provincia:</label>

                    <div id="divProvincia" class="div_texbox ui-corner-all">

                        <select class="selSuc select Llarge" related="selComuna" name="selProvincia" id="selProvincia">

                            <option value="">Seleccione Provincia</option>

                        </select>

                    </div>



                    <label class="label ui-corner-all" for="selComuna">Comuna:</label>

                    <div id="divComuna" class="div_texbox ui-corner-all">

                        <select class="selSuc select Llarge" related="selComuna" name="selComuna" id="selComuna">

                            <option value="">Seleccione Comuna</option>

                        </select>

                    </div>

                    <label class="label ui-corner-all" for="txtFono1">Mi tel&eacute;fono es:</label>

                    <div id="divFono1" class="div_texbox ui-corner-all"><input type="text" name="txtFono1" id="txtFono1" class="textbox txtFono"/></div>

                    <label class="label ui-corner-all" for="txtMail">Mi e-mail es:</label>

                    <div id="divMail" class="div_texbox ui-corner-all"><input type="text" name="txtMail" id="txtMail" class="textbox txtMail Llarge"/></div>

                    <label class="label ui-corner-all" for="selIglesia">Pertenezco a la Iglesia de Dios...:</label>

                    <div id="divIglesia" class="div_texbox ui-corner-all">

                        <select class="selIglesia select Llarge" name="selIglesia" id="selIglesia" >

                            <option value="">Selecciona tu Iglesia</option>

                            

<?



$conn = new conexionBD();

$Str_SQL = "SELECT 

              iglesia.IGL_ID_IGLESIA,

              iglesia.IGL_DESCRIPCION

            FROM

              iglesia";

$result = $conn->EjecutarSQL($Str_SQL);

while($rows = $conn->FetchArray($result)){

?>

                            <option value="<? echo $rows['IGL_ID_IGLESIA']; ?>"><? echo $rows['IGL_DESCRIPCION']; ?></option>

<?

}

?>

                            <option value="0">Otra (Pon el nombre en las observaciones!)</option>

                        </select>

                    </div>

                    <label class="labelObs label ui-corner-all" for="txtComentarios">Observaciones:</label>

                    <div id="divComentarios" class="div_texbox-Obs ui-corner-all"><textarea rows="3" name="txtComentarios" id="txtComentarios" class="textbox text-area txtCmt Llarge"></textarea></div>

                    <label class="label ui-corner-all" for="txtFacebook">Tengo Facebook!:</label>

                    <div id="divFacebook" class="div_texbox ui-corner-all"><input type="text" name="txtFacebook" id="txtFacebook" class="textbox txtFacebook"/></div>

                    <label class="label ui-corner-all" for="txtTwitter">Tengo Twitter!:</label>

                    <div id="divTwitter" class="div_texbox ui-corner-all"><input type="text" name="txtTwitter" id="txtTwitter" class="textbox txtTwitter"/></div>

                    <div class="clear"></div>

                    <br />

                    <div class="button_div ui-corner-all ui-widget-content">

                        <button id="btnGuardar" type="button" name="btnGuardar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-disk"></div>Inscribeme!</button>              

                        <button id="btnCancelar" type="button" name="btnCancelar" class="ui-corner-all button  ui-state-default"><div style="float:left" class="ui-icon ui-icon-cancel"></div>Cancelar</button>

                    </div>

                </div>

                <div id="form1" class="formright">

                    <div class="clear"></div>

                    <div class="ui-widget-content ui-corner-all ui-state-highlight" ><h1>Antes de inscribirte asegurate de realizar tu dep&oacute;sito bancario!</h1></div>

                </div>

            </fieldset>

            <!--/div-->            

            <div class="clear"></div>

            <br />

        </form>

        

    </div>

        <br />

</div>

</body>

</html>

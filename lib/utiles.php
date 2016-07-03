<?php
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

extract($_POST);
if($cerrar == 1){	session_start();	session_destroy();	session_unset();	header("Location: ../index.php");}

function mostrar_header(){
    session_start();
	$str_html =
	'<div id="head">
		<div class="menu">
            <div id="elMenu" class="jqueryslidemenu"> ';
	$str_html .= $_SESSION['_usuario_menu'];
	$str_html .=
	'       </div>
		</div>
		<div class="divInicio"><button class="btnInicio" onclick="location.href=\''.$_SESSION['_url'].'/seguridad/inicio.php\'">&nbsp;</button></div>
	 </div>';

	 print $str_html;
}

function nombreArea($codMod){
    require('parametros.php');
    //require("../conexion/class.conexionDB.inc.php");
    $_conn = new conexionBD();
    $_conn->SeleccionBBDD($_BD_SEGURIDAD);

    $Str_SQL = "SELECT
                  seg_modulo1.MOD_NOMBREMODULO AS sistema,
                  seg_modulo.MOD_NOMBREMODULO
                FROM
                  seg_modulo
                  INNER JOIN seg_modulo seg_modulo1 ON (seg_modulo.MOD_SUPMODULO = seg_modulo1.MOD_IDMODULO)
                WHERE
                  seg_modulo.MOD_IDMODULO = $codMod";
    //echo $Str_SQL;
    $result = $_conn->EjecutarSQL($Str_SQL);
    $rows = $_conn->FetchArray($result);
    return $rows['sistema'] . " -> " . $rows['MOD_NOMBREMODULO'];
}
function nombreSistema($codMod=''){
    require('parametros.php');
    //require("../conexion/class.conexionDB.inc.php");
    if($codMod == ''){$codMod = 1;}
    $_conn = new conexionBD();
    $_conn->SeleccionBBDD($_BD_SEGURIDAD);

    $Str_SQL = "SELECT
                  seg_parametros.par_nombreSistema
                FROM
                  seg_parametros
                WHERE
                  seg_parametros.par_idparametros = $codMod";
    //echo $Str_SQL;
    $result = $_conn->EjecutarSQL($Str_SQL);
    $rows = $_conn->FetchArray($result);
    return $rows['par_nombreSistema'];
}
function nombreEmpresa($codMod=''){
    require('parametros.php');
    //require("../conexion/class.conexionDB.inc.php");
    if($codMod == ''){$codMod = 1;}
    $_conn = new conexionBD();
    $_conn->SeleccionBBDD($_BD_SEGURIDAD);

    $Str_SQL = "SELECT
                  seg_parametros.par_nombreEmpresa
                FROM
                  seg_parametros
                WHERE
                  seg_parametros.par_idparametros = $codMod";
    //echo $Str_SQL;
    $result = $_conn->EjecutarSQL($Str_SQL);
    $rows = $_conn->FetchArray($result);
    return $rows['par_nombreEmpresa'];
}

function nombreModulo($codMod){
    require('parametros.php');
    //require("../conexion/class.conexionDB.inc.php");
    $_conn = new conexionBD();
    $_conn->SeleccionBBDD($_BD_SEGURIDAD);

    $Str_SQL = "SELECT seg_modulo.MOD_NOMBREMODULO
                FROM   seg_modulo
                WHERE  seg_modulo.MOD_IDMODULO = $codMod";
    //echo $Str_SQL;
    $result = $_conn->EjecutarSQL($Str_SQL);
    $rows = $_conn->FetchArray($result);
    return $rows['MOD_NOMBREMODULO'];
}
function descripcionModulo($codMod){
    require('parametros.php');
    //require("../conexion/class.conexionDB.inc.php");
    $_conn = new conexionBD();
    $_conn->SeleccionBBDD($_BD_SEGURIDAD);

    $Str_SQL = "SELECT seg_modulo.MOD_DESCRIPCION
                FROM   seg_modulo
                WHERE  seg_modulo.MOD_IDMODULO = $codMod";
    //echo $Str_SQL;
    $result = $_conn->EjecutarSQL($Str_SQL);
    $rows = $_conn->FetchArray($result);
    return $rows['MOD_DESCRIPCION'];
}

function mostrar_footer($codMod){
    session_start();
	$str_html =
	'<div id="foot">
		<div id="pie" class="footer">
		<div class="menu">
		<table class="infoSis">
			<tr style="background-color:transparent !important;">
                <td width="2%" align="right"></td>
				<td width="10%" align="left"><span style="font-size:xx-small !important;">' . date("d/m/Y h:i:s") . '</span></td>
				<td width="20%" align="center">Modulo:&nbsp;<b>' . nombreModulo($codMod) . '</b></td>
				<td width="20%" align="center">Sede:&nbsp;<b>' . $_SESSION['_sede_nom'] . '</b></td>
				<td width="20%" align="center">Usuario:&nbsp;<b>' . $_SESSION['_usuario_nombre'] . '</b></td>
				<td width="10%" align="right"></td>
			</tr>
		</table>
		</div>
		<div class="divInicio"><button class="btnSalir" onclick="location.href=\''.$_SESSION['_url'].'/lib/utiles.php?cerrar=1\'">&nbsp;</button></div>
		</div>
	 </div>';
	 print $str_html;
}

function alertError($Str_ERROR){
    $Str_Salida = "
<script type=\"text/javascript\">
    console.log($Str_ERROR);
</script>";
    return $Str_Salida;
}

function refresca_menu(){
    require('parametros.php');
    $conn = new conexionBD();
    $Str_SQL = "SELECT MOD_IDMODULO, MOD_NOMBREMODULO, MOD_DESCRIPCION, MOD_URLMODULO, MOD_ORDENMODULO,
                MOD_NIVELMODULO, MOD_URLIMAGEN, MOD_ESTADO
                FROM seg_modulo
                WHERE MOD_NIVELMODULO = 1 AND MOD_ESTADO = $_VAL_ACTIVO";
    //echo $Str_SQL;
    $conn->SeleccionBBDD($_BD_SEGURIDAD);
    if(!$res = @$conn->EjecutarSQL($Str_SQL)){
        $MSG = "Error al conectar a la base de datos.\El error fue:\n\n";
        $MSG .= "". $conn->ObtUltError();
        $MSG .= "\nEn la consulta\n\n" . $Str_SQL;
        echo $MSG;
        exit;
    }
    $html = "";
    $fin = $conn->NumRows($res);
    $html .= "<ul>";
    while($rowsSup = @$conn->FetchArray($res))
    {
        $Str_SQL = "SELECT seg_modulo.MOD_IDMODULO, seg_modulo.MOD_NOMBREMODULO, seg_modulo.MOD_DESCRIPCION, seg_modulo.MOD_URLMODULO, seg_modulo.MOD_ORDENMODULO,
            seg_modulo.MOD_NIVELMODULO, seg_modulo.MOD_URLIMAGEN, seg_modulo.MOD_ESTADO
            FROM seg_privilegios INNER JOIN seg_modulo ON (seg_privilegios.MOD_IDMODULO = seg_modulo.MOD_IDMODULO)
            INNER JOIN seg_tipousuario ON (seg_privilegios.TU_IDTIPOUSUARIO = seg_tipousuario.TU_IDTIPOUSUARIO)
            WHERE seg_tipousuario.TU_IDTIPOUSUARIO = $_SESSION[_usuario_tipo] AND seg_modulo.MOD_NIVELMODULO = 2 AND seg_modulo.MOD_ESTADO = $_VAL_ACTIVO AND seg_modulo.MOD_SUPMODULO = $rowsSup[MOD_IDMODULO]";
        if(!$resultSub = @$conn->EjecutarSQL($Str_SQL)){
            $MSG = "Error al conectar a la base de datos.\El error fue:\n\n";
            $MSG .= "". $conn->ObtUltError();
            $MSG .= "\nEn la consulta\n\n" . $Str_SQL;
            echo $MSG;
            exit;
        }

        while( $rowsSub = $conn->FetchArray($resultSub)){
            if($flag == 0){
                $html .= "\n<li><a href=\"". $_SESSION['_url'] . "/$rowsSup[MOD_NOMBREMODULO]\"><img src='". $_SESSION['_url'] . "/" . $rowsSup['MOD_URLIMAGEN'] . "' />$rowsSup[MOD_NOMBREMODULO]</a>\n";
                $html .= "\t<ul>\n\t";
                $flag = 1;
            }
            $html .= "<li><a href='". $_SESSION['_url'] . "/$rowsSub[MOD_URLMODULO]'>$rowsSub[MOD_NOMBREMODULO]</a></li>\n\t" . chr(13);
        }
        if($flag == 1){
            $html .= "</ul>\n";
            $html .= "</li>\n";
        }
        $flag = 0;
    }
    $html .= "</ul>";
    $_SESSION['_usuario_menu'] = $html;
}

function formatDate($fecha){
    if($fecha){
        $fecha1 = split("-", $fecha);
        $fecha1[0] = substr($fecha1[0], 0, 4);
        $fecha2 = date("d/m/Y", mktime(0,0,0, $fecha1[1], $fecha1[2], $fecha1[0]));
    }
    else{
        $fecha2 = $fecha;
    }
    return $fecha2;
}
?>

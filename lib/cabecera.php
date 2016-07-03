<?
require_once('../xajax/xajax.inc.php');
require_once("../conexion/class.conexionDB.inc.php");
$sql = "exec FP_SP_VERIFICA_USUARIO '$_SESSION[_usuario_rut]', '$_SESSION[_usuario_tipo]', '$codMod'";
$conn = new conexionBD();
$res = $conn->EjecutarSQL($sql);
$res2 = mssql_fetch_array($res);
//print $res2[0];
if(strcmp($res2[0], "OK") != 0){
	header("Location: index.php?err=1");
	exit;
}
?>
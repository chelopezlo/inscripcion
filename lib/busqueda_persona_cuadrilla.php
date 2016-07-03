<?
session_start();

require_once ("../conexion/class.conexionDB.inc.php");
$conn = new conexionBD ( );
extract ( $_GET );
$rutusu = $_SESSION ['_usuario_rut'];


if ($opt == 4) {
	if (isset ( $tip ) && $tip == 'c')
		//SELECT PER_IDPERSONA,PER_RUT,AnaNomPil,AnaApePat,AnaApeMat FROM personal where PerSucCod = $_SESSION[_seccion] AND (PerCod LIKE '%$percod%') AND (PerDVe LIKE '%$perdve%
		$sql = "SELECT PER_IDPERSONA,PER_RUT,AnaNomPil,AnaApePat,AnaApeMat FROM V_CUADRILLA_DETALLE_PERSONAS_ACTIVAS where PER_RUT LIKE '%$q%'";
	elseif (isset ( $tip ) && $tip == 'd')
		
		$sql = "SELECT PER_IDPERSONA,PER_RUT,AnaNomPil,AnaApePat,AnaApeMat FROM V_CUADRILLA_DETALLE_PERSONAS_ACTIVAS where (AnaNomPil LIKE '%$q%') OR (AnaApePat LIKE '%$q%') OR (AnaApeMat LIKE '%$q%')";
} 

if (isset ( $tip ) && isset ( $opt )) {
	if ($tip == 'c')
	{
		$op1 = 0;
		$op2 = 1;
	}
	else
	{
		$op1 = 1;
		$op2 = 0;
	}
}
$sp = $conn->EjecutarSQL ( $sql );

while ( $row5 = mssql_fetch_array ( $sp ) ) {
	
	$valor1[0] = rtrim($row5[AnaNomPil]).' '.rtrim($row5[AnaApePat]).' '.rtrim($row5[AnaApeMat]);
	$valor1[1] = $row5['PER_RUT'];
	
	$valor2[0] = rtrim($row5[AnaNomPil]).' '.rtrim($row5[AnaApePat]).' '.rtrim($row5[AnaApeMat]);
	$valor2[1] = $row5['PER_RUT'];
	
	$str .= trim ( $valor1 ["$op2"] ) . " <br />(" . trim ( $valor2["$op1"] ) . ")";
	$str .= '|';
	$str .= trim ( $valor2["$op1"] );
	$str .= '|';
	$str .= trim ( $valor2["$op2"] );
	$str .= '|';
	$str .= trim ( $row5 ['PER_IDPERSONA']);
	$str .= "\n";
}
echo utf8_encode($str);
?>
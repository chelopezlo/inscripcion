<?php
session_start();
require_once ("../conexion/class.conexionDB.inc.php");
require_once ("parametros.php");
$conn = new conexionBD ( );
$conn->SeleccionBBDD($_BASE_SIS);
extract ( $_GET );
$rutusu = $_SESSION ['_usuario_rut'];

/* Consulta a Bases de datos. */
if (isset ( $tip ) && $tip == 'a'){
    if ($opt == 1) {
            $sql = " SELECT DISTINCT
                        UV_IDUNIVEND,
                        UV_NUMERO,
                        UV_CODUNIVEND,
                        MOD_DESCRIPCION,
                        TUV_DESCRIPION
                    FROM
                        V_AVANCE_PAQUETE ";
            if($q != ""){
                $sql .= " WHERE
                            (UV_CODUNIVEND like N'%$q%')  ";
            }
            $sp = $conn->EjecutarSQL ( $sql );
            //$separador = "<br />MOD: ";
            while ( $row5 = mssql_fetch_array ( $sp ) ) {
                $str .= trim ( $row5 [2] ) . "<br /> $row5[4] - $row5[3] ";
                $str .= '|';
                $str .= trim ( $row5 [2] );
                $str .= '|';
                $str .= trim ( $row5 [0] );
                $str .= '|';
                $str .= trim ( $row5 [1] );
                $str .= "\n";
            }
    }

    /* Consulta Empresas */
    elseif ($opt == 2) {        
        $uv = $uv == '' ? 0 : $uv;
        $sql = "SELECT
                    UPM_IDUNIVENPAQMOD,
                    PM_CODPAQMOD,
                    AVANCES
                FROM
                    V_AVANCE_PAQUETE_LISTADO
                where
                    UV_IDUNIVEND = $uv AND
                    PM_CODPAQMOD like '%$q%'";
        $separador = "<br />Avance &#37;:&nbsp;";
        $sp = $conn->EjecutarSQL ( $sql );

        while ( $row5 = mssql_fetch_array ( $sp ) ) {
            $str .= trim ( $row5 [1] ) . $separador . ( $row5 [2] );
            $str .= '|';
            $str .= trim ( $row5 [1] );
            $str .= '|';
            $str .= trim ( $row5 [0] );
            $str .= '|';
            $str .= trim ( $row5 [2] );
            $str .= "\n";
        }
    }

    if ($opt == 3 || $opt == 4) {
            $sql = " SELECT
                        PER_IDPERSONA,
                        PER_RUT,
                        RTRIM(AnaNomPil) AS AnaNomPil,
                        RTRIM(AnaApePat) AS AnaApePat,
                        RTRIM(AnaApeMat) AS AnaApeMat
                    FROM
                        V_PERSONA_LISTAR ";
            if($q != ""){
                $sql .= " WHERE
                            (PER_ESTADO = 1) AND
                            (SucCod = " . $_SESSION['_seccion'] . ") AND
                            (PER_RUT like N'%$q%')  ";
            }
            $sp = $conn->EjecutarSQL ( $sql );
            //$separador = "<br />MOD: ";
            if($opt == 3)
                while ( $row5 = mssql_fetch_array ( $sp ) ) {
                    $str .= trim ( $row5 [1] ) . "<br /> $row5[2] $row5[3] $row5[4] ";
                    $str .= '|';
                    $str .= trim ( $row5 [1] );
                    $str .= '|';
                    $str .= trim ( $row5 [0] );
                    $str .= '|';
                    $str .= trim ( "$row5[2] $row5[3] $row5[4]");
                    $str .= "\n";
                }
            else{
                $str ="Nombre: " . trim ( "$row5[2] $row5[3] $row5[4]" );
            }
    }
}
if (isset ( $tip ) && $tip == 'b'){
    
}
//echo $sql;
echo $str;
?>

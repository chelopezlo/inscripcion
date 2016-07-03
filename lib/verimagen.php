<?php
session_start();
require("../lib/utiles.php");
require_once("../conexion/class.conexionDB.inc.php");
require_once("../lib/parametros.php");

if(isset($_GET['id'])) {

    // you may have to modify login information for your database server:
    $conn = new conexionBD ( );
    $conn->SeleccionBBDD($_BASE_SIS);

    $Str_SQL = "SELECT
  persona.PER_FOTO,
  persona.PER_TIPOIMAGEN,
  persona.PER_PESOIMAGEN,
  persona.PER_NOMBREIMAGEN
FROM
  persona
WHERE
  persona.PER_IDPERSONA = $_GET[id]";

    if(!$result = @$conn->EjecutarSQL($Str_SQL)){die("ERROR, no se puede conectar con la base de datos.");}
    $rows = $conn->FetchArray($result);
    $imagen = $rows["PER_FOTO"];
    $nombre = $rows["PER_NOMBREIMAGEN"];
    $tipo = $rows["PER_TIPOIMAGEN"];
    $peso = $rows["PER_PESOIMAGEN"];

    header("Content-type: $tipo");
    header("Content-length: $peso");
    header("Content-Disposition: inline; filename=$nombre");

   echo $imagen;

}
?>
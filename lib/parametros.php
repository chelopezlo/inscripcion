<?php
/*
 * Archivo utilizado para almacenar parametros globales
 */
//$_BASE_ERP = "ERP_AITUE";
$_BASE_SIS = "SIBINA";
$_BD_SEGURIDAD = "SIBINA";


$_VAL_ACTIVO = 1;
$_VAL_INACTIVO = 0;
$_VAL_ESTADOS = array(1 => "ACTIVO", 0 => "INACTIVO");
$_VAL_ESTADOS_IMAGEN = array(1 => "lightbulb.png", 0 => "lightbulb_off.png");

$_VAL_ALUMNO = 1;
$_VAL_PROFESOR = 2;
$_VAL_TIPPER = array(1 => "ALUMNO", 2 => "PROFESOR");

$_VAL_MASCULINO = 1;
$_VAL_FEMENINO = 2;
$_VAL_GENERO = array(1 => "MASCULINO", 2 => "FEMENINO");
$_VAL_GENERO_IMAGEN = array(1 => "male.png", 2 => "female.png");

$_VAL_SOLTERO = 1;
$_VAL_CASADO = 2;
$_VAL_VIUDO = 3;
$_VAL_SEPARADO = 4;
$_VAL_DIVORCIADO = 5;
$_VAL_ESTCIVIL = array(1 => "SOLTERO/A", 2 => "CASADO/A", 3 => "VIUDO/A", 4 => "SEPARADO/A", 5 => "DIVORCIADO/A");$_VALOR_INSCRIPCION = array();array_push($_VALOR_INSCRIPCION, array('monto' => 20000, 'fecha_desde' => date_create('2016-06-05'), 'fecha_hasta' => date_create('2016-07-05') ));array_push($_VALOR_INSCRIPCION, array('monto' => 22000, 'fecha_desde' => date_create('2016-07-06'), 'fecha_hasta' => date_create('2016-08-06') ));array_push($_VALOR_INSCRIPCION, array('monto' => 25000, 'fecha_desde' => date_create('2016-08-07'), 'fecha_hasta' => date_create('2016-10-17') ));
?>
<?php
session_start();

$datos = array(
   "idReporte" => $_POST['idReporte'],
   "idUsuario" => $_SESSION['usuario']['id'], 
   "mantenimiento" => $_POST['mantenimiento'],
   "tipoServicio" => $_POST['tipoServicio'],
   // ELIMINADO: "asignado" => $_POST['asignado'],
   "fechaRealizacion" => $_POST['fechaRealizacion'],
   "trabajoRealizado" => $_POST['trabajoRealizado'],
   "material" => $_POST['material'],
   //"verificadoLiberado" => $_POST['verificadoLiberado'],
   //"fechaVerificado" => $_POST['fechaVerificado'],
   "fechaAprobado" => $_POST['fechaAprobado']
);

include "../../../clases/ReportesT.php";
$Reportes = new ReportesT();
echo $Reportes->terminarReporte($datos); 
?>
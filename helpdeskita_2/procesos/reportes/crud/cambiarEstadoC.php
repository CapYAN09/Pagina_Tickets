<?php
    $datos = array(
       "idReporte" => $_POST['idReporteCEC'], 
       "motivoCancelacion" => $_POST['cc'] // Agregar el motivo de cancelación
    );
 
    //echo json_encode($datos);
    include "../../../clases/ReportesT.php";
    $ReportesT = new ReportesT();
    echo json_encode($ReportesT->CambiarEstadoC($datos));
?>
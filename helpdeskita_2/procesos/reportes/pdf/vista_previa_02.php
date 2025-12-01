<?php
$reporte ="";

if(!isset($_GET['reporte'])){
    header("location:../../../vistas/inicio.php");
}else{
    $reporte = $_GET['reporte'];
}
date_default_timezone_set("America/Mexico_City");
$hoy = date('Y-m-d G:i:s', time());
// Require composer autoload
require_once __DIR__ . '/../../../public/mpdf/vendor/autoload.php';

$servidor = "172.30.247.185";
$usuario = "ccomputo";
$password = "Jarjar0904$";
$puerto = 3306;
$db = "b1o04dzhm1guhvmjcrwb";
$conexion = mysqli_connect($servidor,$usuario,$password,$db);

// Consulta SQL corregida sin comentarios
$consulta = "SELECT
    reportes.id_reporte AS idReporte,
    reportes.estado AS estado,
    reportes.id_depa AS idDepa,
    finalizados.id_mantenimiento AS mantenimiento,
    finalizados.tipo_servicio AS tipoServicio,
    finalizados.asignado AS asignado,
    finalizados.fecha_realizacion AS fechaRealizacion,
    finalizados.trabajo_realizado AS trabajoRealizado,
    finalizados.material AS material,
    finalizados.verificado_liberado AS verificadoLiberado,
    finalizados.fecha_verificado AS fechaVerificado,
    finalizados.aprobado AS aprobado,
    finalizados.fecha_aprobado AS fechaAprobado,
    encargados.nombre AS nombreEncargado
FROM
    t_reportes AS reportes
INNER JOIN
    t_reportes_finalizados AS finalizados ON finalizados.id_reporte = reportes.id_reporte
INNER JOIN
    t_cat_mantenimiento AS mantenimiento ON finalizados.id_mantenimiento = mantenimiento.id_mantenimiento
INNER JOIN
    t_encargados AS encargados ON finalizados.aprobado = encargados.id_encargado
WHERE reportes.id_reporte = '$reporte'
LIMIT 1";

$resultado = mysqli_query($conexion,$consulta);

$datosReporte = array();

while($row = mysqli_fetch_assoc($resultado)){
  $datosReporte[] = $row;
}

if(count($datosReporte)==0){
    header("location:../../../vistas/inicio.php");
    exit;
}

// Obtenemos el id_depa
$id_depa = isset($datosReporte[0]['idDepa']) ? $datosReporte[0]['idDepa'] : 0;

// 🔹 Determinar tabla según id_depa
switch ($id_depa) {
    case 1:
        $tabla_cat = "cat_CC";
        break;
    case 2:
        $tabla_cat = "cat_ME";
        break;
    case 3:
        $tabla_cat = "cat_RMS";
        break;
    default:
        $tabla_cat = "";
        break;
}

$folio_mostrar = $reporte; // Valor por defecto (en caso de no encontrar New_ID)

// 🔹 Buscar New_ID en la tabla correspondiente
if (!empty($tabla_cat)) {
    $consulta_folio = "SELECT New_ID FROM $tabla_cat WHERE id_reporte = '$reporte' LIMIT 1";
    $res_folio = mysqli_query($conexion, $consulta_folio);

    if ($res_folio && mysqli_num_rows($res_folio) > 0) {
        $row_folio = mysqli_fetch_assoc($res_folio);
        $folio_mostrar = $row_folio['New_ID'];
    }
}

// Definimos las variables para las X según el id_depa
$x_recursos = ($id_depa == 3) ? 'X' : '';
$x_mantenimiento = ($id_depa == 2) ? 'X' : '';
$x_centro_computo = ($id_depa == 1) ? 'X' : '';

// 🔹 Variables para Tipo de Mantenimiento (Interno/Externo)
$interno_x = ($datosReporte[0]['mantenimiento'] == "1") ? 'X' : '';
$externo_x = ($datosReporte[0]['mantenimiento'] != "1") ? 'X' : '';

// 🔹 Variables para Tipo de Servicio (podrías tener más opciones aquí)
$tipo_servicio = $datosReporte[0]['tipoServicio'];

// 🔹 FUNCIONES PARA FORMATO DE FECHA
function formatearFecha($fecha) {
    if (empty($fecha) || $fecha == '0000-00-00') {
        return '';
    }
    
    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return $fecha; // Devolver la fecha original si no se puede convertir
    }
    
    // Formato: día/mes/año (01/11/2025)
    return date('d/m/Y', $timestamp);
}

// Formatear las fechas
$fecha_realizacion_formateada = formatearFecha($datosReporte[0]['fechaRealizacion']);
$fecha_verificado_formateada = formatearFecha($datosReporte[0]['fechaVerificado']);
$fecha_aprobado_formateada = formatearFecha($datosReporte[0]['fechaAprobado']);

// Ruta de la imagen
$imagen_fondo = __DIR__ . '/orden.jpg';

// Verificar si la imagen existe
if (!file_exists($imagen_fondo)) {
    die("No se encuentra la imagen: " . $imagen_fondo);
}

// Convertir imagen a base64
$imagen_base64 = base64_encode(file_get_contents($imagen_fondo));
$imagen_data_uri = 'data:image/jpeg;base64,' . $imagen_base64;

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            position: relative;
            width: 210mm;
            height: 297mm;
            font-family: Arial, sans-serif;
        }
        .imagen-fondo {
            width: 100%;
            height: 141%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .datos {
            position: absolute;
            z-index: 2;
            font-family: Arial, sans-serif;
            background: transparent;
            border: none;
            margin: 0;
            padding: 0;
        }
        
        /* POSICIONES DE LOS DATOS - AJUSTA SEGÚN TU IMAGEN */
        .numero-control { top: 47mm; left: 159mm; font-size: 12px; font-weight: bold; width: 30mm; }
        .recursos { top: 63mm; left: 25mm; font-size: 14px; font-weight: bold; width: 10mm; text-align: center; }
        .mantenimiento { top: 63mm; left: 78mm; font-size: 14px; font-weight: bold; width: 10mm; text-align: center; }
        .computo { top: 63mm; left: 131mm; font-size: 14px; font-weight: bold; width: 10mm; text-align: center; }
        
        /* POSICIONES PARA TIPO DE MANTENIMIENTO */
        .interno { top: 76mm; left: 86mm; font-size: 14px; font-weight: bold; width: 10mm; text-align: center; }
        .externo { top: 76mm; left: 143mm; font-size: 14px; font-weight: bold; width: 10mm; text-align: center; }
        
        .tipo-servicio { top: 87mm; left: 110mm; font-size: 13px; width: 80mm; }
        .asignado { top: 95mm; left: 95mm; font-size: 13px; width: 80mm; }
        
        .fecha-realizacion { top: 106mm; left: 110mm; font-size: 13px; width: 80mm; color: red; }
        
        .trabajo-realizado { 
            top: 132mm; 
            left: 26mm; 
            font-size: 12px; 
            width: 85mm; 
            height: 45mm; 
            overflow: hidden; 
            line-height: 0.8;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            text-align: justify;
            column-width: 85mm;
        }
        .trabajo-realizado{ 
            top: 132mm; 
            left: 26mm; 
            font-size: 12px; 
            width: 90mm; 
            height: 100mm; 
            overflow: hidden; 
            line-height: 1.0;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            text-align: justify;
            column-width: 75mm;
        }
        .material { 
            top: 132mm; 
            left: 121mm; 
            font-size: 12px; 
            width: 60mm; 
            height: 100mm; 
            overflow: hidden; 
            line-height: 1.0;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            text-align: justify;
            column-width: 75mm;
        }
            .verificado-por { 
            top: 195mm; 
            left: 25mm; 
            font-size: 12px; 
            width: 60mm; 
            height: 8mm; 
            overflow: hidden;
            line-height: 1.0;
            word-wrap: break-word;
            word-break: keep-all;
            white-space: normal;
            text-align: left;
        }
        
        .verificado-por { top: 199mm; left: 50mm; font-size: 13px; width: 60mm;  }
        .fecha-verificado { top: 199mm; left: 105mm; font-size: 13px; width: 60mm; color: red;}
        .aprobado-por { top: 207mm; left: 50mm; font-size: 13px; width: 60mm; }
        .fecha-aprobado { top: 207mm; left: 105mm; font-size: 13px; width: 60mm; color: red;}
    </style>
</head>
<body>
    <img class="imagen-fondo" src="' . $imagen_data_uri . '">
    
    <!-- DATOS SOBREPUESTOS SOBRE LA IMAGEN -->
    <div class="datos numero-control">' . $folio_mostrar . '</div>
    
    <!-- DEPARTAMENTOS -->
    <div class="datos recursos">' . $x_recursos . '</div>
    <div class="datos mantenimiento">' . $x_mantenimiento . '</div>
    <div class="datos computo">' . $x_centro_computo . '</div>
    
    <!-- TIPO DE MANTENIMIENTO -->
    <div class="datos interno">' . $interno_x . '</div>
    <div class="datos externo">' . $externo_x . '</div>
    
    <!-- OTROS DATOS -->
    <div class="datos tipo-servicio">' . $tipo_servicio . '</div>
    <div class="datos asignado">' . $datosReporte[0]['asignado'] . '</div>
    <div class="datos fecha-realizacion">' . $fecha_realizacion_formateada . '</div>
    <div class="datos trabajo-realizado">' . $datosReporte[0]['trabajoRealizado'] . '</div>
    <div class="datos material">' . $datosReporte[0]['material'] . '</div>
    <div class="datos verificado-por">' . $datosReporte[0]['verificadoLiberado'] . '</div>
    <div class="datos fecha-verificado">' . $fecha_verificado_formateada . '</div>
    <div class="datos aprobado-por">' . $datosReporte[0]['nombreEncargado'] . '</div>
    <div class="datos fecha-aprobado">' . $fecha_aprobado_formateada . '</div>
</body>
</html>
';

// Create an instance of the class:
$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'margin_top' => 0,
    'margin_right' => 0,
    'margin_bottom' => 0,
    'margin_left' => 0
]);

// Añadimos el html
$mpdf->WriteHTML($html);

$mpdf->SetTitle("Servicio_Terminado_".$reporte);
$mpdf->Output("Servicio_Terminado_".$reporte.".pdf","I");

// Cerrar conexión
mysqli_close($conexion);
?>
<?php
$reporte ="";

if(!isset($_GET['reporte'])){
    header("location:../../../vistas/inicio.php");
    exit();
}else{
    $reporte = $_GET['reporte'];
}

date_default_timezone_set("America/Mexico_City");
$hoy = date('Y-m-d G:i:s', time());

// Require composer autoload
require_once __DIR__ . '/../../../public/mpdf/vendor/autoload.php';

// Conexión a la base de datos
$servidor = "172.30.247.185";
$usuario = "ccomputo";
$password = "Jarjar0904$";
$puerto = 3306;
$db = "b1o04dzhm1guhvmjcrwb";
$conexion = mysqli_connect($servidor,$usuario,$password,$db);

// CONSULTA PRINCIPAL MODIFICADA - INCLUYENDO APELLIDOS DEL SOLICITANTE Y CAMPOS NUEVOS
$consulta = "SELECT 
                tr.id_reporte, 
                tp.nombre as nombre_solicitante,
                tp.paterno as paterno_solicitante,
                tp.materno as materno_solicitante,
                tr.area_solicitante, 
                tr.fecha_elaboracion, 
                tr.descripcion, 
                tr.id_depa,
                tr.Asignado,
                tr.edificio,      -- NUEVO CAMPO
                tr.cubi,          -- NUEVO CAMPO
                tu_asignado.id_usuario,
                p_asignado.nombre as nombre_trabajador,
                p_asignado.paterno as paterno_trabajador,
                p_asignado.materno as materno_trabajador
             FROM t_reportes tr 
             INNER JOIN t_usuarios tu on tr.id_usuario = tu.id_usuario 
             INNER JOIN t_persona tp on tp.id_persona = tu.id_persona 
             LEFT JOIN t_usuarios tu_asignado ON tr.Asignado = tu_asignado.id_usuario
             LEFT JOIN t_persona p_asignado ON tu_asignado.id_persona = p_asignado.id_persona
             WHERE tr.id_reporte = '$reporte'";

$resultado = mysqli_query($conexion, $consulta);
$datosReporte = mysqli_fetch_assoc($resultado);

if(!$datosReporte){
    header("location:../../../vistas/inicio.php");
    exit();
}

// Obtener el id_depa
$id_depa = $datosReporte["id_depa"];

// Definir variables para las X según el departamento
$x_recursos = ($id_depa == 3) ? 'X' : '';
$x_mantenimiento = ($id_depa == 2) ? 'X' : '';
$x_centro_computo = ($id_depa == 1) ? 'X' : '';

// Obtener el nombre completo del SOLICITANTE (con apellidos)
$nombre_completo_solicitante = $datosReporte["nombre_solicitante"];
if (!empty($datosReporte["paterno_solicitante"])) {
    $nombre_completo_solicitante .= ' ' . $datosReporte["paterno_solicitante"];
}
if (!empty($datosReporte["materno_solicitante"])) {
    $nombre_completo_solicitante .= ' ' . $datosReporte["materno_solicitante"];
}

// Obtener el nombre completo del trabajador asignado (si existe)
$trabajador_asignado = "No asignado";
if (!empty($datosReporte["Asignado"]) && !empty($datosReporte["nombre_trabajador"])) {
    $nombre_completo_trabajador = $datosReporte["nombre_trabajador"];
    if (!empty($datosReporte["paterno_trabajador"])) {
        $nombre_completo_trabajador .= ' ' . $datosReporte["paterno_trabajador"];
    }
    if (!empty($datosReporte["materno_trabajador"])) {
        $nombre_completo_trabajador .= ' ' . $datosReporte["materno_trabajador"];
    }
    $trabajador_asignado = $nombre_completo_trabajador;
}

// Obtener edificio y cubículo (nuevos campos)
$edificio = !empty($datosReporte["edificio"]) ? $datosReporte["edificio"] : "No especificado";
$cubi = !empty($datosReporte["cubi"]) ? $datosReporte["cubi"] : "No especificado";

// FORMATO DE FECHA: Convertir a formato "día, mes, año"
$fecha_elaboracion = $datosReporte["fecha_elaboracion"];

// Verificar si la fecha tiene un formato válido
if (!empty($fecha_elaboracion)) {
    // Convertir a timestamp
    $timestamp = strtotime($fecha_elaboracion);
    
    if ($timestamp !== false) {
        // Meses en español
        $meses_espanol = array(
            1 => '01', 2 => '02', 3 => '03', 4 => '04',
            5 => '05', 6 => '06', 7 => '07', 8 => '08',
            9 => '09', 10 => '10', 11 => '11', 12 => '12'
        );
        
        // Extraer día, mes y año
        $dia = date('d', $timestamp);
        $mes_numero = date('n', $timestamp);
        $ano = date('Y', $timestamp);
        
        // Obtener el nombre del mes en español
        $mes = $meses_espanol[$mes_numero];
        
        // Formatear fecha como "15, Enero, 2024"
        $fecha_formateada = $dia . '/' . $mes . '/' . $ano;
    } else {
        // Si no se puede convertir, mostrar la fecha original
        $fecha_formateada = $fecha_elaboracion;
    }
} else {
    $fecha_formateada = "Fecha no especificada";
}

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

// Ruta de la imagen
$imagen_fondo = __DIR__ . '/soli.jpg';

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
            height: 200%;
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
        
        /* ===== POSICIONES DE LOS DATOS - AJUSTA SEGÚN TU IMAGEN ===== */
        
        /* DEPARTAMENTOS - Ajusta estas coordenadas según donde están los cuadros en tu imagen */
        .recursos { 
            top: 63mm; 
            left: 25mm; 
            font-size: 14px; 
            font-weight: bold; 
            width: 10mm; 
            text-align: center; 
        }
        .mantenimiento { 
            top: 63mm; 
            left: 78mm; 
            font-size: 14px; 
            font-weight: bold; 
            width: 10mm; 
            text-align: center; 
        }
        .computo { 
            top: 63mm; 
            left: 131mm; 
            font-size: 14px; 
            font-weight: bold; 
            width: 10mm; 
            text-align: center; 
        }
        
        /* FOLIO - Ajusta según donde debe ir el número de folio */
        .folio { 
            top: 47mm; 
            left: 150mm; 
            font-size: 12px; 
            font-weight: bold; 
            width: 39mm; 
        }
        
        /* ÁREA SOLICITANTE */
        .area-solicitante { 
            top: 82mm; 
            left: 27mm; 
            font-size: 13px; 
            width: 150mm; 
        }
        
        /* NOMBRE DEL SOLICITANTE*/
        .nombre-solicitante { 
            top: 101mm; 
            left: 27mm; 
            font-size: 13px; 
            width: 150mm; 
        }
        
        /* FECHA DE ELABORACIÓN */
        .fecha-elaboracion { 
            top: 120mm; 
            left: 100mm; 
            font-size: 13px; 
            width: 80mm; 
            color: red;
        }
        
        /* EDIFICIO - NUEVO CAMPO */
        .edificio { 
            top: 220mm; 
            left: 27mm; 
            font-size: 13px; 
            width: 70mm; 
        }
        
        /* CUBÍCULO - NUEVO CAMPO */
        .cubi { 
            top: 220mm; 
            left: 108mm; 
            font-size: 13px; 
            width: 70mm; 
        }
        
        /* DESCRIPCIÓN DEL SERVICIO - Ajusta altura y ancho según el espacio disponible */
        .descripcion { 
            top: 140mm; 
            left: 27mm; 
            font-size: 13px; 
            width: 155mm; 
            height: 50mm; 
            overflow: hidden; 
            line-height: 1.2;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            text-align: justify;
        }
        
        /* TÉCNICO ASIGNADO */
        .tecnico-asignado { 
            top: 195mm; 
            left: 50mm; 
            font-size: 13px; 
            width: 60mm; 
        }
        
        /* COPIAS - Ajusta según donde están las líneas de copias */
        .copias { 
            top: 230mm; 
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
    </style>
</head>
<body>
    <!-- IMAGEN DE FONDO -->
    <img class="imagen-fondo" src="' . $imagen_data_uri . '">
    
    <!-- DATOS SOBREPUESTOS SOBRE LA IMAGEN -->
    
    <!-- DEPARTAMENTOS -->
    <div class="datos recursos">' . $x_recursos . '</div>
    <div class="datos mantenimiento">' . $x_mantenimiento . '</div>
    <div class="datos computo">' . $x_centro_computo . '</div>
    
    <!-- FOLIO -->
    <div class="datos folio">' . $folio_mostrar . '</div>
    
    <!-- ÁREA SOLICITANTE -->
    <div class="datos area-solicitante">' . $datosReporte["area_solicitante"] . '</div>
    
    <!-- NOMBRE DEL SOLICITANTE (CON APELLIDOS) -->
    <div class="datos nombre-solicitante">' . $nombre_completo_solicitante . '</div>
    
    <!-- FECHA DE ELABORACIÓN - FORMATEADA -->
    <div class="datos fecha-elaboracion">' . $fecha_formateada . '</div>
    
    <!-- EDIFICIO (NUEVO CAMPO) -->
    <div class="datos edificio">' . $edificio . '</div>
    
    <!-- CUBÍCULO (NUEVO CAMPO) -->
    <div class="datos cubi">' . $cubi . '</div>
    
    <!-- DESCRIPCIÓN -->
    <div class="datos descripcion">' . nl2br($datosReporte["descripcion"]) . '</div>
    
    
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

$mpdf->SetTitle("Servicio_Pendiente_" . $folio_mostrar);
$mpdf->Output("Servicio_Pendiente_" . $folio_mostrar . ".pdf", "I");

// Cerrar conexión
mysqli_close($conexion);
?>
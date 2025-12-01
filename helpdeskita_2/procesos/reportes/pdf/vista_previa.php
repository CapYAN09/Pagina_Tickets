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

// CONSULTA PRINCIPAL
$consulta = "SELECT 
                tr.id_reporte, 
                tp.nombre, 
                tr.area_solicitante, 
                tr.fecha_elaboracion, 
                tr.descripcion, 
                tr.id_depa,
                tr.Asignado,
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

// Obtener el nombre completo del trabajador asignado (si existe)
$trabajador_asignado = "No asignado";
if (!empty($datosReporte["Asignado"]) && !empty($datosReporte["nombre_trabajador"])) {
    $nombre_completo = $datosReporte["nombre_trabajador"] . ' ' . $datosReporte["paterno_trabajador"];
    if (!empty($datosReporte["materno_trabajador"])) {
        $nombre_completo .= ' ' . $datosReporte["materno_trabajador"];
    }
    $trabajador_asignado = $nombre_completo;
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

// HTML del PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
     <table class="table_footer">
        <tr>
            <td rowspan="3"><img src="logoITA.png" width="100px" height="75px" class="logo_ita_header"></td>
            <td><strong>Nombre del Documento: Formato para Solicitud de Mantenimiento Correctivo</strong></td>
            <td><strong>Código: ITA-AD-PO-001-02</strong></td>
        </tr>
        <tr>
            <td rowspan="2"><strong>Referencia a la Norma ISO 9001:2015 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1</strong></td>
            <td><strong>Revisión: 0</strong></td>
        </tr>
        <tr>
            <td><strong>Página 1 de 2</strong></td>
        </tr>
    </table>
    <p class="titulo"><strong>SOLICITUD MANTENIMIENTO CORRECTIVO</strong></p>

    <table class="tabla_1">
        <tr>
            <td width="200px"><strong>Recursos Materiales y Servicios</strong></td>
            <td width="35px" class="input">'.$x_recursos.'</td>
        </tr>
        <tr>
            <td><strong>Mantenimiento de Equipo</strong></td>
            <td class="input">'.$x_mantenimiento.'</td>
        </tr>
        <tr>
            <td><strong>Centro de Cómputo</strong></td>
            <td class="input">'.$x_centro_computo.'</td>
        </tr>
    </table>

    <table class="folio">
        <tr>
            <td><strong>Folio:</strong></td>
            <td width="120px" class="input">'.$folio_mostrar.'</td>
        </tr>
    </table>

    <table class="area_solicitante" width="100%">
        <tr>
            <td><strong>Área Solicitante: </strong> '.$datosReporte["area_solicitante"].'</td>
        </tr>
    </table>

    <table class="tabla_descripcion" width="100%">
        <tr>
            <td><strong>Nombre y Firma del Solicitante: </strong> '.$datosReporte["nombre"].'</td>
        </tr>
        <tr>
            <td><strong>Fecha de Elaboración:</strong> '.$datosReporte["fecha_elaboracion"].'</td>
        </tr>
        <tr>
            <td><br><strong>Descripción del servicio solicitado o falla a reparar:</strong> <p><br>'.$datosReporte["descripcion"].'</p></td>
        </tr>
    </table>

    <table class="tabla_descripcion" width="100%">
        <tr>
            <td><strong>Técnico Asignado: </strong> '.$trabajador_asignado.'</td>
        </tr>
    </table>

    <div class="texto_departamento">
        c.c.p. Departamento de Planeación Programación y Presupuestación
        <br>
        c.c.p. Área Solicitante
    </div>
</body>
</html>
';

// Generar el PDF
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->SetHTMLFooter('
<div style="color: #666; font-size: 9px; padding: 6px; border-top: 1px solid #ddd;">
    <!-- Primer footer - Información institucional -->


    <!-- Segundo footer - Instrucciones (solo texto normal) -->
    <table width="100%" style="font-size: 8px; ">
        <tr>
            <td style="padding: 3px;">
                <strong>Instrucciones:</strong> Anotar la clase de mantenimiento a realizar, por ejemplo, eléctrico, plomería, herrería, pintura, obra civil, entre otros si es interno y si es externo revisar el anexo 8 del MSGC.<br>
                La firma puede ser autógrafa o digitalizada, preferentemente siendo esta última con la finalidad de que la versión se utilice de manera digital en contribución a la norma de Gestión ambiental.<br>
                Anotar el nombre del Titular del Departamento de Recursos Materiales y Servicios o Mantenimiento de equipo o centro de cómputo según sea el caso, quien aprueba el trabajo liberado.
            </td>
        </tr>
    </table>
    <table width="100%" style="font-size: 9px; color: #666; margin-bottom: 8px;">
        <tr>
            <td width="25%"><strong>Elaborado por:</strong> Jefes de Centro de Computo, Mantenimiento de Equipo, Recursos Materiales y Servicios</td>
            <td width="25%" align="center"><strong>Página 1 de 1</strong></td>
            <td width="25%" align="center"><strong>Código:</strong> ITA-AR-MPC-FO-04</td>
        </tr>
        <tr>
            <td><strong>Revisado por:</strong> Subdirector de Servicios Administrativos</td>
            <td align="center"><strong>Revisión:</strong> 1</td>
            <td align="center"><strong>Emisión:</strong> 07/11/2025</td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>Referencia a las normas:</strong> ISO 9001:2015 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1, ISO 14001:2015 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1, ISO 45001:2018 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1, ISO 50001:2018 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1
            </td>
        </tr>
    </table>
    
    
</div>');

$mpdf->SetTitle("Servicio_Pendiente_".$folio_mostrar);
$mpdf->Output("Servicio_Pendiente_".$folio_mostrar.".pdf","I");
?>

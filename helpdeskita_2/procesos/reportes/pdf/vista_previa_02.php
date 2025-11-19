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

$servidor = "localhost";
$usuario = "root";
$password = "";
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
}

// Obtenemos el id_depa
$id_depa = isset($datosReporte[0]['idDepa']) ? $datosReporte[0]['idDepa'] : 0;

// Definimos las variables para las X según el id_depa
$x_recursos = ($id_depa == 3) ? 'X' : '';
$x_mantenimiento = ($id_depa == 2) ? 'X' : '';
$x_centro_computo = ($id_depa == 1) ? 'X' : '';

$Mantenimiento = "";
if($datosReporte[0]['mantenimiento']=="1"){
  $Mantenimiento = "Interno <strong>X</strong>        Externo  ";
}else{
  $Mantenimiento = "Interno         Externo <strong>X</strong>";
}

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
       <td rowspan="3"><img src="logoITA.png" with="100px" height="75px" class="logo_ita_header"></td>
       <td><strong>Nombre del Documento: Formato para Orden de Trabajo de Mantenimiento</strong></td>
       <td><strong>Código: ITA- AD-PO-001-04</strong></td>
   </tr>
   <tr>
       <td rowspan="2"><strong>Referencia a la Norma ISO 9001:2015 6.1, 7.1, 7.2, 7.4, 7.5.1, 8.1</strong></td>
       <td><strong>Revisión: 0</strong></td>
   </tr>
   <tr>
       <td><strong>Página 1 de 1</strong></td>
   </tr>
</table>
<p class="titulo"><strong>ORDEN DE TRABAJO DE MANTENIMIENTO</strong></p>

<!-- TABLA CON LAS X SEGÚN EL DEPARTAMENTO -->
<table class="tabla_1">
    <tr>
        <td width="200px"><strong>Recursos Materiales y Servicios</strong></td>
        <td width="35px" class="input">'.$x_recursos.'</td>
    </tr>
    <tr>
        <td ><strong>Mantenimiento de Equipo</strong></td>
        <td class="input">'.$x_mantenimiento.'</td>
    </tr>
    <tr>
        <td ><strong>Centro de Cómputo</strong></td>
        <td class="input">'.$x_centro_computo.'</td>
    </tr>
</table>

<table class="folio">
 <tr>
       <td width="125px"><strong>Número de control: </strong></td>
       <td width="45px" class="input">'.$reporte.'</td>
   </tr>
</table>

<table class="area_solicitante" width="100%">
 <tr>
       <td ><strong>Mantenimiento: </strong>'.$Mantenimiento.' </td>
   </tr>
 <tr>
       <td ><strong>Tipo de servicio: </strong>'.$datosReporte[0]['tipoServicio'].'</td>
   </tr>
 <tr>
       <td ><strong>Asignado a: </strong>'.$datosReporte[0]['asignado'].'</td>
   </tr>
</table>

<table class="tabla_descripcion" width="100%">

 <tr>
   <td colspan=2><strong>Fecha de realización:</strong> '.$datosReporte[0]['fechaRealizacion'].'</td>
 </tr>
</table>

<table class="tabla_descripcion" width="100%">

 <tr>
   <td width="50%" class="encabezado_trabajo_material"><strong>Trabajo realizado:</strong></td>
   <td width="50%" class="encabezado_trabajo_material"><strong>Material utilizado (En caso de aplicar):</strong></td>
 </tr>
 <tr>
   <td height="80px" style="vertical-align: top;">'.$datosReporte[0]['trabajoRealizado'].'</td>
   <td height="80px" style="vertical-align: top;">'.$datosReporte[0]['material'].'</td>
 </tr>
</table>
<table class="tabla_descripcion" width="100%">
 <tr>
 <td ><strong>Verificado y Liberado por: </strong>'.$datosReporte[0]['verificadoLiberado'].'</td>
 <td ><strong>Fecha y Firma: </strong>'.$datosReporte[0]['fechaVerificado'].'</td>
 </tr>
 <tr>
 <td ><strong>Aprobado por: </strong>'.$datosReporte[0]['nombreEncargado'].'</td>
 <td ><strong>Fecha y Firma: </strong>'.$datosReporte[0]['fechaAprobado'].'</td>
 </tr>

</table>
<br>
<br>
<br>

<div class="texto_departamento">
 c.c.p. Departamento de Planeación Programación y Presupuestación
 <br>
 c.c.p. Área Solicitante
</div>
</body>
</html>
';

// Create an instance of the class:
$mpdf = new \Mpdf\Mpdf();

// Añadimos el html de la primera página
$mpdf->WriteHTML($html);
// Agregamos el footer
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

$mpdf->SetTitle("Servicio_Terminado_".$reporte);
$mpdf->Output("Servicio_Terminado_".$reporte.".pdf","I");
?>
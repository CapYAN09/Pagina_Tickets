<?php
include "../../clases/conexion.php";
session_start();

// Obtener datos de sesión
$idUsuario = $_SESSION['usuario']['id'];
$ubicacionUsuario = $_SESSION['usuario']['ubicacion'];

// Obtener nombre completo del usuario
$nombreCompleto = "";
if (isset($_SESSION['usuario']['nombre']) && isset($_SESSION['usuario']['paterno'])) {
    $nombreCompleto = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['paterno'];
    if (!empty($_SESSION['usuario']['materno'])) {
        $nombreCompleto .= ' ' . $_SESSION['usuario']['materno'];
    }
} else {
    $nombreCompleto = "Usuario no identificado";
}

$con = new conexion();
$conexion1 = $con->conectar();

// CONSULTA ACTUALIZADA - Incluir el campo Asignado
$sql = "SELECT
            reportes.id_reporte AS idReporte,
            usuarios.id_usuario AS idUsuario,
            reportes.folio AS folio,
            reportes.area_solicitante AS areaSolicitante,
            reportes.nombre_solicitante AS nombreSolicitante,
            depa.id_depa AS id_depa,
            depa.Nombre_depa AS departamento,
            reportes.fecha_elaboracion AS fechaElaboracion,
            reportes.descripcion AS descripcion,
            reportes.estado AS estado,
            reportes.Asignado AS asignado
        FROM
            t_reportes AS reportes
        INNER JOIN
            t_usuarios AS usuarios ON usuarios.id_usuario = reportes.id_usuario
        INNER JOIN
            cat_depa AS depa ON depa.id_depa = reportes.id_depa
        WHERE (reportes.estado = 1 OR reportes.estado = 2)
        AND depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));

// CONSULTA PARA OBTENER LOS TRABAJADORES
$sql_trabajadores = "SELECT id_trabajador, Nombre, ubicacion FROM t_trabajador WHERE ubicacion = '$ubicacionUsuario'";
$resultado_trabajadores = mysqli_query($conexion1, $sql_trabajadores) or die(mysqli_error($conexion1));

$trabajadores = array();
while ($trabajador = mysqli_fetch_array($resultado_trabajadores)) {
    $trabajadores[$trabajador['id_trabajador']] = $trabajador['Nombre'];
}
?>

<!-- Mostrar información del usuario -->


<!-- Tabla -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
        <thead>
            <th>ID</th>
            <th>Usuario</th>
            <th>Folio</th>
            <th>Área solicitante</th>
            <th>Solicitante</th>
            <th>Departamento</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Asignado a</th>
        </thead>
        <tbody>
            <?php
            while ($mostrar = mysqli_fetch_array($respuesta)) {
                $descripcion = strlen($mostrar['descripcion']) > 25 
                    ? substr($mostrar['descripcion'], 0, 25) . "..." 
                    : $mostrar['descripcion'];

                $nombreAsignado = "No asignado";
                if (!empty($mostrar['asignado']) && isset($trabajadores[$mostrar['asignado']])) {
                    $nombreAsignado = $trabajadores[$mostrar['asignado']];
                }
            ?>
            <tr>
                <td><?php echo $mostrar['idReporte']; ?></td>
                <td><?php echo $mostrar['idUsuario']; ?></td>
                <td><?php echo $mostrar['folio']; ?></td>
                <td><?php echo $mostrar['areaSolicitante']; ?></td>
                <td><?php echo $mostrar['nombreSolicitante']; ?></td>
                <td><?php echo $mostrar['departamento']; ?></td>
                <td><?php echo $mostrar['fechaElaboracion']; ?></td>
                <td><?php echo $descripcion; ?></td>
                <td>
                    <?php if ($mostrar['estado'] == 1) { ?>
                        <button class="btn btn-warning btn-sm" disabled>Pendiente</button>
                    <?php } elseif ($mostrar['estado'] == 2) { ?>
                        <button class="btn btn-info btn-sm" disabled>En proceso</button>
                    <?php } elseif ($mostrar['estado'] == 3) { ?>
                        <button class="btn btn-warning btn-sm" disabled>Finalizado</button>
                    <?php } ?>
                </td>
                <td>
                    <span class="form-control form-control-sm" style="background-color:#f8f9fa;">
                        <?php echo htmlspecialchars($nombreAsignado); ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true
        });
    });
</script>

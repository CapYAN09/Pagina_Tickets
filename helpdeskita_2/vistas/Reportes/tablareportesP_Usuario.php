<?php
    session_start();
    include "../../clases/conexion.php";
    $con = new conexion();
    $conexion1 = $con->conectar();
    $idUsuario = $_SESSION['usuario']['id'];

    $sql = "SELECT
                reportes.id_reporte AS reporte,
                usuarios.id_usuario AS usuario,
                usuarios.usuario AS nombreUsuario,
                reportes.folio AS folio,
                reportes.area_solicitante AS areaSolicitante,
                depa.id_depa AS id_depa,
                depa.Nombre_depa AS departamento,
                reportes.nombre_solicitante AS solicitante,
                reportes.fecha_elaboracion AS fecha,
                reportes.descripcion AS descripcion,
                reportes.estado AS estado,
                reportes.extra AS motivoCancelacion 
            FROM
                t_reportes AS reportes
            INNER JOIN
                t_usuarios AS usuarios ON usuarios.id_usuario = reportes.id_usuario
            INNER JOIN
                cat_depa AS depa ON depa.id_depa = reportes.id_depa
            WHERE
                reportes.id_usuario = $idUsuario";


    $respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
    $respuestaArray = array();

    while($row = mysqli_fetch_assoc($respuesta)){
        $respuestaArray[] = $row;
    }
?>

<style>
    .bg-very-light-red {
        background-color: #fc8a8aff !important;
    }
    .bg-very-light-green {
        background-color: #8afc8aff !important;
    }
</style>

<button id="button-crear_reportes" class="btn btn-primary" data-toggle="modal" data-target="#modalcrearReporte"
        onclick="obtenerDatosUsuario(<?php echo $respuestaArray[0]['usuario']?>)" >
    Crear reporte
</button>

<table class="table table-sm dt-responsive nowrap" id="tablaReportesDataTable" style="width:100%">
    <thead>
        <th>Reporte</th>
        <th>Usuario</th>
        <th>Folio</th>
        <th>Área solicitante</th>
        <th>Departamento</th>
        <th>Nombre Solicitante</th>
        <th>Fecha</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th>Comentarios</th>
    </thead>

    <tbody>
        <?php foreach ($respuestaArray as $mostrar): ?>
            <?php 
                $descripcion = (strlen($mostrar['descripcion']) > 25) 
                                ? substr($mostrar['descripcion'], 0, 25)."..." 
                                : $mostrar['descripcion'];
                
                // Procesar el motivo de cancelación
                $motivoCancelacion = '';
                if($mostrar['estado'] == 4 && !empty($mostrar['motivoCancelacion'])) {
                    $motivoCancelacion = (strlen($mostrar['motivoCancelacion']) > 30) 
                                        ? substr($mostrar['motivoCancelacion'], 0, 30)."..." 
                                        : $mostrar['motivoCancelacion'];
                }
                
                // Clase para fondo rojo muy suave si el estado es 4
                $fondoRojo = ($mostrar['estado'] == 4) ? 'bg-very-light-red' : '';
                $fondoverde = ($mostrar['estado'] == 3) ? 'bg-very-light-green' : '';
                ?>
                <tr class="<?php echo $fondoRojo . ' ' . $fondoverde; ?>">
                <td><?php echo $mostrar['reporte']; ?></td>
                <td><?php echo $mostrar['nombreUsuario']; ?></td>
                <td><?php echo $mostrar['folio']; ?></td>
                <td><?php echo $mostrar['areaSolicitante']; ?></td>
                <td><?php echo $mostrar['departamento']; ?></td>
                <td><?php echo $mostrar['solicitante']; ?></td>
                <td><?php echo $mostrar['fecha']; ?></td>
                <td><?php echo $descripcion; ?></td>
                <td>
                    <?php if($mostrar['estado'] == 1): ?>
                        <button class="btn btn-warning btn-sm" disabled>Pendiente</button>
                    <?php elseif($mostrar['estado'] == 2): ?>
                        <button class="btn btn-info btn-sm" disabled>En proceso</button>
                    <?php elseif($mostrar['estado'] == 3): ?>
                        <button class="btn btn-success btn-sm" disabled>Resuelto</button>
                    <?php elseif($mostrar['estado'] == 4): ?>
                        <button class="btn btn-danger btn-sm" disabled>Cancelado</button>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($mostrar['estado'] == 4): ?>
                        <?php if(!empty($motivoCancelacion)): ?>
                            <span title="<?php echo htmlspecialchars($mostrar['motivoCancelacion']); ?>">
                                <?php echo htmlspecialchars($motivoCancelacion); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Sin motivo registrado</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
       $('#tablaReportesDataTable').DataTable();
    });

    function firmarReporte(id){
        document.getElementById("idReporteF").value=id;
        $('#modalFirmarReporte').modal('show');
    }

    function generarPDF2(id){
        window.open("../procesos/reportes/pdf/vista_previa_02.php?reporte="+id);
    }
</script>
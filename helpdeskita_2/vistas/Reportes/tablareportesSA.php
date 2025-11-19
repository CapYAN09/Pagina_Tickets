<?php
include "../../clases/conexion.php";
session_start();

// Obtener datos de sesión
$idUsuario = $_SESSION['usuario']['id'];

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

// CONSULTA MODIFICADA - AHORA INCLUYE ESTADO 3 Y 4
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
        WHERE (reportes.estado = 1 OR reportes.estado = 2 OR reportes.estado = 3 OR reportes.estado = 4)";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));

// CONSULTA MODIFICADA - Obtener todos los trabajadores con rol 4
$sql_trabajadores = "SELECT 
                        u.id_usuario, 
                        p.nombre, 
                        p.paterno, 
                        p.materno,
                        u.ubicacion 
                    FROM t_usuarios u 
                    INNER JOIN t_persona p ON u.id_persona = p.id_persona 
                    WHERE u.id_rol = 4 
                    AND u.Estado = 1";

$resultado_trabajadores = mysqli_query($conexion1, $sql_trabajadores) or die(mysqli_error($conexion1));

// Crear array de trabajadores
$trabajadores = array();
while($trabajador = mysqli_fetch_array($resultado_trabajadores)) {
    $nombreCompletoTrabajador = $trabajador['nombre'] . ' ' . $trabajador['paterno'];
    if (!empty($trabajador['materno'])) {
        $nombreCompletoTrabajador .= ' ' . $trabajador['materno'];
    }
    $trabajadores[$trabajador['id_usuario']] = $nombreCompletoTrabajador;
}
?>

<!-- Puedes usar la variable $nombreCompleto donde necesites -->
<div style="display: none;" id="nombreUsuarioCompleto"><?php echo $nombreCompleto; ?></div>

<!-- CONTENEDOR PARA LA TABLA - IMPORTANTE -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesSADataTable" style="width:100%">
        <thead>
            <th>Reporte</th>
            <th>Usuario</th>
            <th>Folio</th>
            <th>Solicitante</th>
            <th>Nombre</th>
            <th>Departamento</th>
            <th>Fecha</th>
            <th>Descripcion</th>
            <th>Imprimir reporte de solicitud</th>
            <th>Estado</th>
            
            <th>Asignado a:</th>
        </thead>
        <tbody>
            <?php
            while($mostrar = mysqli_fetch_array($respuesta)){
                $descripcion = "";
                if(strlen($mostrar['descripcion']) > 25){
                    $descripcion = substr($mostrar['descripcion'], 0, 25)."...";
                }else{
                    $descripcion = $mostrar['descripcion'];
                }
                
                // Verificar si está asignado (0 = no asignado)
                $estaAsignado = !empty($mostrar['asignado']) && $mostrar['asignado'] != 0;
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
                    <?php if($mostrar['estado'] == 1 || $mostrar['estado'] == 3){ ?>
                        <?php if($estaAsignado) { ?>
                            <!-- Botón IMPRIMIR habilitado solo si está asignado -->
                            <button type="button" class="btn btn-success btn-sm" onclick="generarPDF(<?php echo $mostrar['idReporte']; ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php } else { ?>
                            <!-- Botón IMPRIMIR deshabilitado si no está asignado -->
                            <button type="button" class="btn btn-success btn-sm" disabled title="No asignado">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td>
                    <?php if($mostrar['estado'] == 1) { ?>
                        <!-- Estado 1: Pendiente - BOTÓN DESHABILITADO -->
                        <button class="btn btn-warning btn-sm" disabled>
                            Pendiente
                        </button>
                    <?php } else if($mostrar['estado'] == 2) { ?>
                        <!-- Estado 2: En proceso - BOTÓN DESHABILITADO -->
                        <button class="btn btn-info btn-sm" disabled>
                            En proceso
                        </button>
                    <?php } else if($mostrar['estado'] == 3) { ?>
                        <!-- Estado 3: Terminado/Completado - BOTÓN DESHABILITADO -->
                        <button class="btn btn-success btn-sm" disabled>
                            Terminado
                        </button>
                    <?php } else if($mostrar['estado'] == 4) { ?>
                        <!-- Estado 4: Cancelado - BOTÓN DESHABILITADO -->
                        <button class="btn btn-danger btn-sm" disabled>
                            Cancelado
                        </button>
                    <?php } ?>
                </td>

                <td>
                    <?php 
                        $nombreAsignado = "No asignado";
                        if(!empty($mostrar['asignado']) && isset($trabajadores[$mostrar['asignado']])) {
                            $nombreAsignado = $trabajadores[$mostrar['asignado']];
                        }
                    ?>
                    <!-- SIEMPRE mostrar solo texto, NO select -->
                    <span class="form-control form-control-sm" style="background-color: #e9ecef;">
                        <?php echo $nombreAsignado; ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    // Variable global para la instancia de DataTable
    var dataTableInstance = null;

    $(document).ready(function(){
        inicializarDataTable();
    });

    function inicializarDataTable() {
        // Destruir instancia anterior si existe
        if (dataTableInstance) {
            dataTableInstance.destroy();
        }
        
        // Inicializar DataTable
        dataTableInstance = $('#tablaReportesSADataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true
        });
    }

    // FUNCIÓN PARA RECARGAR LA TABLA - MANTENIDA PERO NO NECESARIA
    function recargarTabla() {
        // Esta función se mantiene pero no se usará ya que no hay modificaciones
        location.reload();
    }

    function terminarReporte(id) {
        // FUNCIÓN DESHABILITADA - No hace nada
        alert("No tiene permisos para terminar reportes");
    }

    function CambiarEstado(id) {
        // FUNCIÓN DESHABILITADA - No hace nada
        alert("No tiene permisos para cambiar estados");
    }

    function generarPDF(id){
        // SOLO esta función permanece activa para imprimir
        window.open("../procesos/reportes/pdf/vista_previa.php?reporte="+id);
    }

    function actualizarAsignado(selectElement) {
        // FUNCIÓN DESHABILITADA - No hace nada
        alert("No tiene permisos para asignar trabajadores");
        // Revertir cualquier cambio en el select (aunque no debería haber selects)
        selectElement.value = "";
    }
</script>
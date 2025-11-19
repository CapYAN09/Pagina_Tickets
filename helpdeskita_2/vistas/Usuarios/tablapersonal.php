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

// CONSULTA MODIFICADA - Solo mostrar reportes asignados al usuario actual con estado 2
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
        WHERE reportes.estado = 2  -- Solo reportes en proceso
        AND reportes.Asignado = $idUsuario  -- Solo reportes asignados al usuario actual
        AND depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));

// CONSULTA MODIFICADA - Obtener trabajadores de t_usuarios con rol 4 y misma ubicación
$sql_trabajadores = "SELECT 
                        u.id_usuario, 
                        p.nombre, 
                        p.paterno, 
                        p.materno,
                        u.ubicacion 
                    FROM t_usuarios u 
                    INNER JOIN t_persona p ON u.id_persona = p.id_persona 
                    WHERE u.id_rol = 4 
                    AND u.ubicacion = '$ubicacionUsuario'
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
    <?php if(mysqli_num_rows($respuesta) > 0): ?>
        <table class="table table-sm dt-responsive nowrap" id="tablaReportesPersonal" style="width:100%">
            <thead>
                <tr>
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
                    <th>Terminar reporte</th>
                    <th>Asignado a:</th>
                </tr>
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
                        <?php if($mostrar['estado'] == 2){ ?>
                            <!-- Botón IMPRIMIR habilitado para reportes en proceso -->
                            <button type="button" class="btn btn-success btn-sm" onclick="generarPDF(<?php echo $mostrar['idReporte']; ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php } ?>
                    </td>

                    
                    <td>
                        <?php if($mostrar['estado'] == 2) { ?>
                            <!-- Estado 2: En proceso -->
                            <button class="btn btn-info btn-sm" disabled>
                                En proceso
                            </button>
                        <?php } ?>
                    </td>

                    <td>
                        <?php if($estaAsignado) { ?>
                            <!-- Botón TERMINAR REPORTE habilitado -->
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalterminarReporte" onclick="terminarReporte(<?php echo $mostrar['idReporte'];?>)">
                                <i class="fas fa-check"></i>
                            </button>
                        <?php } ?>
                    </td>
                    <td>
                        <!-- Para personal, siempre mostrar solo texto con el nombre del asignado -->
                        <span class="form-control form-control-sm" style="background-color: #e9ecef;">
                            <?php 
                                $nombreAsignado = "No asignado";
                                if(!empty($mostrar['asignado']) && isset($trabajadores[$mostrar['asignado']])) {
                                    $nombreAsignado = $trabajadores[$mostrar['asignado']];
                                }
                                echo $nombreAsignado;
                            ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Mensaje cuando no hay datos -->
        <div class="alert alert-info text-center" role="alert">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h4>No hay reportes asignados</h4>
            <p class="mb-0">No tienes reportes asignados en este momento.</p>
        </div>
    <?php endif; ?>
</div>

<script>

        function terminarReporte(id) {
        document.getElementById("idReporte").value = id;
        
        var nombreCompleto = document.getElementById("nombreUsuarioCompleto").textContent;
        console.log("Usuario actual:", nombreCompleto);
        
        $('#modalterminarReporte').modal('show');
    }












    // Variable global para la instancia de DataTable
    var dataTableInstance = null;

    $(document).ready(function(){
        // Solo inicializar DataTable si hay datos
        if ($('#tablaReportesPersonal').length) {
            inicializarDataTable();
        }
    });

    function inicializarDataTable() {
        // Destruir instancia anterior si existe
        if (dataTableInstance) {
            dataTableInstance.destroy();
        }
        
        // Inicializar DataTable
        dataTableInstance = $('#tablaReportesPersonal').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false
        });
    }

    // FUNCIÓN PARA RECARGAR LA TABLA - MODIFICADA
    function recargarTabla() {
        $.ajax({
            url: 'tablapersonal.php', // Cambiado a la misma página
            type: 'GET',
            success: function(response) {
                // Reemplazar el contenido del contenedor
                $('#tablaReportesContainer').html(response);
                
                // Verificar si hay tabla para inicializar DataTable
                if ($('#tablaReportesPersonal').length) {
                    inicializarDataTable();
                }
                
                // Verificar si SweetAlert2 está disponible
                if (typeof Swal !== 'undefined') {
                    Swal.fire("Operación realizada", "Editado con éxito!", "success");
                } else {
                    // Fallback a alert normal
                    alert("Operación realizada - Editado con éxito!");
                }
            },
            error: function() {
                location.reload();
            }
        });
    }

    function terminarReporte(id) {
        document.getElementById("idReporte").value = id;
        
        var nombreCompleto = document.getElementById("nombreUsuarioCompleto").textContent;
        console.log("Usuario actual:", nombreCompleto);
        
        $('#modalterminarReporte').modal('show');
    }

    function CambiarEstado(id) {
        document.getElementById("idReporteCE").value = id;
        $('#modalCambiarEstado').modal('show');
    }

    function generarPDF(id){
        window.open("../procesos/reportes/pdf/vista_previa.php?reporte="+id);
    }
</script>
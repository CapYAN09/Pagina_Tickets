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
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
        <thead> 
            <th>Reporte</th>
            <th>ID del Usuario</th>
            <th>Folio</th>
            <th>Area del departamento solicitante</th>
            <th>Nombre del solicitante</th>
            <th>Departamento</th>
            <th>Fecha</th>
            <th>Descripcion</th>
            <th>Imprimir reporte de solicitud</th>
            <th>Estado</th>
            <th>Cancelar orden</th>
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
                <td>
                    <div style="white-space: pre-line; word-wrap: break-word; max-width: 300px; line-height: 1.4;">
                        <?php echo htmlspecialchars($descripcion); ?>
                    </div>
                </td>
                <td>
                    <?php if($mostrar['estado'] == 1){ ?>
                        <?php if($estaAsignado) { ?>
                            <!-- Botón IMPRIMIR habilitado solo si está asignado -->
                            <button type="button" class="btn btn-success btn-sm" onclick="generarPDF(<?php echo $mostrar['idReporte']; ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php } else { ?>
                            <!-- Botón IMPRIMIR deshabilitado si no está asignado -->
                            <button type="button" class="btn btn-success btn-sm" disabled title="Debe asignar un trabajador primero">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td>
                    <?php if($mostrar['estado'] == 1) { ?>
                        <?php if($estaAsignado) { ?>
                            <!-- Si está asignado, botón habilitado -->
                            <button class="btn btn-warning btn-sm" onclick="CambiarEstado(<?php echo $mostrar['idReporte']; ?>)">
                                Pendiente
                            </button>
                        <?php } else { ?>
                            <!-- Si NO está asignado, botón deshabilitado -->
                            <button class="btn btn-warning btn-sm" disabled title="Debe asignar un trabajador primero">
                                Pendiente
                            </button>
                        <?php } ?>
                    <?php } else if($mostrar['estado'] == 2) { ?>
                        <button class="btn btn-info btn-sm" disabled>
                            En proceso
                        </button>
                    <?php } else if($mostrar['estado'] == 4) { ?>
                        <button class="btn btn-danger btn-sm" disabled>
                            Cancelado
                        </button>
                    <?php } ?>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="CambiarEstadoC(<?php echo $mostrar['idReporte']; ?>)">
                        Cancelar
                    </button>
                </td>
                <td>
                    <?php if($mostrar['estado'] == 2) { ?>
                        <!-- Estado 2: Mostrar solo texto, no select -->
                        <span class="form-control form-control-sm" style="background-color: #e9ecef;">
                            <?php 
                                $nombreAsignado = "No asignado";
                                if(!empty($mostrar['asignado']) && isset($trabajadores[$mostrar['asignado']])) {
                                    $nombreAsignado = $trabajadores[$mostrar['asignado']];
                                }
                                echo $nombreAsignado;
                            ?>
                        </span>
                    <?php } else { ?>
                        <!-- Estado 1: Mostrar select para asignar -->
                        <select class="form-control form-control-sm asignado-select" 
                                data-reporte-id="<?php echo $mostrar['idReporte']; ?>" 
                                onchange="actualizarAsignado(this)">
                            <option value="">Seleccionar un trabajador...</option>
                            <?php foreach($trabajadores as $id => $nombre) { ?>
                                <option value="<?php echo $id; ?>" 
                                    <?php echo ($mostrar['asignado'] == $id) ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } ?>
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
        dataTableInstance = $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true
        });
    }

    // FUNCIÓN PARA RECARGAR LA TABLA
function recargarTabla() {
    // Mostrar loading o deshabilitar interacciones
    $('#tablaReportesContainer').addClass('loading');
    
    $.ajax({
        url: 'obtener_tabla_reportes.php',
        type: 'GET',
        success: function(response) {
            // Reemplazar el contenido del contenedor
            $('#tablaReportesContainer').html(response);
            
            // Reinicializar DataTable con la misma configuración
            setTimeout(function() {
                inicializarDataTable();
                $('#tablaReportesContainer').removeClass('loading');
                
                // Verificar si SweetAlert2 está disponible
                if (typeof Swal !== 'undefined') {
                    Swal.fire("Operación realizada", "Editado con éxito!", "success");
                } else {
                    // Fallback a alert normal
                    alert("Operación realizada - Editado con éxito!");
                }
            }, 100);
        },
        error: function() {
            $('#tablaReportesContainer').removeClass('loading');
            location.reload();
        }
    });
}

    function CambiarEstado(id) {
        document.getElementById("idReporteCE").value = id;
        $('#modalCambiarEstado').modal('show');
    }

    function CambiarEstadoC(id) {
        document.getElementById("idReporteCEC").value = id;
        $('#modalCancelarEstado').modal('show');
    }

    function generarPDF(id){
        window.open("../procesos/reportes/pdf/vista_previa.php?reporte="+id);
    }

    function actualizarAsignado(selectElement) {
        var reporteId = selectElement.getAttribute('data-reporte-id');
        var trabajadorId = selectElement.value;
        var nombreTrabajador = selectElement.options[selectElement.selectedIndex].text;
        
        console.log("Datos a enviar - Reporte ID:", reporteId, "Trabajador ID:", trabajadorId, "Nombre:", nombreTrabajador);
        
        // Mostrar confirmación
        if(confirm('¿Estás seguro de asignar este reporte a: ' + nombreTrabajador + '?')) {
            // Deshabilitar select mientras se procesa
            selectElement.disabled = true;
            selectElement.style.backgroundColor = '#f8f9fa';
            
            // Enviar datos via AJAX
            $.ajax({
                url: '../procesos/reportes/crud/actualizar_asignado.php',
                type: 'POST',
                data: {
                    reporte_id: reporteId,
                    trabajador_id: trabajadorId
                },
                success: function(response) {
                    console.log("Respuesta del servidor:", response);
                    
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            // RECARGAR LA TABLA COMPLETA después de éxito
                            recargarTabla();
                        } else {
                            alert('Error: ' + result.message);
                            selectElement.disabled = false;
                            selectElement.style.backgroundColor = '';
                            location.reload();
                        }
                    } catch (e) {
                        console.error("Error parseando JSON:", e, "Respuesta:", response);
                        // Si hay error pero quizás funcionó, recargar igual
                        recargarTabla();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX:", status, error);
                    alert('Error de conexión. Recargando página...');
                    location.reload();
                }
            });
        } else {
            // Revertir la selección si el usuario cancela
            selectElement.value = "";
        }
    }
</script>

<!-- Incluir el modal de cancelar -->
<?php include "modalCancelarEstado.php"; ?>
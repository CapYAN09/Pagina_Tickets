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

// CONSULTA ACTUALIZADA - Incluir el campo Asignado y folio personalizado
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
            reportes.Asignado AS asignado,
            /* OBTENER EL FOLIO PERSONALIZADO SEGÚN EL DEPARTAMENTO */
            CASE 
                WHEN depa.id_depa = 1 THEN cc.New_ID
                WHEN depa.id_depa = 2 THEN me.New_ID
                WHEN depa.id_depa = 3 THEN rms.New_ID
                ELSE reportes.id_reporte
            END AS folio_personalizado
        FROM
            t_reportes AS reportes
        INNER JOIN
            t_usuarios AS usuarios ON usuarios.id_usuario = reportes.id_usuario
        INNER JOIN
            cat_depa AS depa ON depa.id_depa = reportes.id_depa
        /* LEFT JOIN para cada tabla de folios */
        LEFT JOIN cat_CC AS cc ON cc.id_reporte = reportes.id_reporte
        LEFT JOIN cat_ME AS me ON me.id_reporte = reportes.id_reporte
        LEFT JOIN cat_RMS AS rms ON rms.id_reporte = reportes.id_reporte
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

<style>
    /* Estilo para texto en columnas verticales para la descripción */
    .texto-columna-descripcion {
        white-space: normal !important;
        word-wrap: break-word !important;
        max-height: none !important;
        height: auto !important;
        min-height: 50px;
        overflow: visible !important;
        display: block !important;
        line-height: 1.4;
        padding: 5px;
        text-align: left;
        font-size: 0.875rem;
    }
    
    /* Asegurar que todas las celdas se alineen arriba */
    #tablaReportesAdminDataTable tbody td {
        vertical-align: top !important;
    }
    
    /* Contenedor específico para la celda de descripción */
    .celda-descripcion {
        max-width: 300px !important;
        min-width: 200px !important;
        width: auto !important;
    }
    
    /* Estilo para folio personalizado */
    .folio-personalizado {
        font-weight: bold;
        color: #2c3e50;
    }
    
    /* Loading state */
    #tablaReportesContainer.loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>

<!-- Puedes usar la variable $nombreCompleto donde necesites -->
<div style="display: none;" id="nombreUsuarioCompleto"><?php echo $nombreCompleto; ?></div>

<!-- CONTENEDOR PARA LA TABLA - IMPORTANTE -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
        <thead> 
            <th>Folio</th>
            <th>ID Usuario</th>
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
            if (mysqli_num_rows($respuesta) == 0) {
                echo '<tr><td colspan="11" class="text-center">No hay reportes pendientes o en proceso</td></tr>';
            } else {
                while($mostrar = mysqli_fetch_array($respuesta)): 
                    // Obtener el folio a mostrar (personalizado o el original)
                    $folioMostrar = !empty($mostrar['folio_personalizado']) 
                                  ? $mostrar['folio_personalizado'] 
                                  : $mostrar['folio'];
                    
                    $descripcion = $mostrar['descripcion'];
                    
                    // Limpiar y formatear texto para la descripción
                    $descripcionFormateada = htmlspecialchars($descripcion);
                    
                    // Reemplazar saltos de línea por espacios
                    $descripcionFormateada = str_replace(["\r\n", "\n", "\r"], ' ', $descripcionFormateada);
                    
                    // Dividir texto en líneas más cortas para mejor visualización (en columnas)
                    $descripcionDividida = wordwrap($descripcionFormateada, 50, "<br>", true);
                    
                    // Verificar si está asignado (0 = no asignado)
                    $estaAsignado = !empty($mostrar['asignado']) && $mostrar['asignado'] != 0;
                ?>
                <tr>
                    <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
                    <td class="folio-personalizado">
                        <span data-toggle="tooltip" title="<?php echo !empty($mostrar['folio_personalizado']) ? 'Folio personalizado' : 'Folio original'; ?>">
                            <?php echo $folioMostrar; ?>
                        </span>
                    </td>
                    
                    <td><?php echo $mostrar['idUsuario']; ?></td>
                    <td><?php echo $mostrar['areaSolicitante']; ?></td>
                    <td><?php echo $mostrar['nombreSolicitante']; ?></td>
                    <td><?php echo $mostrar['departamento']; ?></td>
                    <td><?php echo $mostrar['fechaElaboracion']; ?></td>
                    <td class="celda-descripcion">
                        <div class="texto-columna-descripcion">
                            <?php echo $descripcionDividida; ?>
                        </div>
                    </td>
                    <td>
                        <?php if($mostrar['estado'] == 1){ ?>
                            <?php if($estaAsignado) { ?>
                                <!-- Botón IMPRIMIR habilitado solo si está asignado -->
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="generarPDF('<?php echo $mostrar['idReporte']; ?>', '<?php echo $folioMostrar; ?>')"
                                        data-toggle="tooltip" title="Generar PDF">
                                    <i class="fas fa-print"></i>
                                </button>
                            <?php } else { ?>
                                <!-- Botón IMPRIMIR deshabilitado si no está asignado -->
                                <button type="button" class="btn btn-success btn-sm" disabled 
                                        data-toggle="tooltip" title="Debe asignar un trabajador primero">
                                    <i class="fas fa-print"></i>
                                </button>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($mostrar['estado'] == 1) { ?>
                            <?php if($estaAsignado) { ?>
                                <!-- Si está asignado, botón habilitado -->
                                <button class="btn btn-warning btn-sm" 
                                        onclick="CambiarEstado(<?php echo $mostrar['idReporte']; ?>)"
                                        data-toggle="tooltip" title="Cambiar a En Proceso">
                                    Pendiente
                                </button>
                            <?php } else { ?>
                                <!-- Si NO está asignado, botón deshabilitado -->
                                <button class="btn btn-warning btn-sm" disabled 
                                        data-toggle="tooltip" title="Debe asignar un trabajador primero">
                                    Pendiente
                                </button>
                            <?php } ?>
                        <?php } else if($mostrar['estado'] == 2) { ?>
                            <button class="btn btn-info btn-sm" disabled
                                    data-toggle="tooltip" title="En proceso">
                                En proceso
                            </button>
                        <?php } ?>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm" 
                                onclick="CambiarEstadoC(<?php echo $mostrar['idReporte']; ?>)"
                                data-toggle="tooltip" title="Cancelar reporte">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                    <td>
                        <?php if($mostrar['estado'] == 2) { ?>
                            <!-- Estado 2: Mostrar solo texto, no select -->
                            <span class="form-control form-control-sm" style="background-color: #e9ecef; border: none;">
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
                                <option value="">Seleccionar...</option>
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
                <?php endwhile;
            } ?>
        </tbody>
    </table>
</div>

<script>
    // Variable global para la instancia de DataTable
    var dataTableInstance = null;

    $(document).ready(function(){
        inicializarDataTable();
        
        // Inicializar tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
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
            "responsive": true,
            "autoWidth": false,
            "order": [[0, 'desc']], // Ordenar por folio descendente
            "drawCallback": function(settings) {
                // Ajustar altura de las celdas de descripción después de dibujar
                $('.texto-columna-descripcion').each(function() {
                    $(this).css({
                        'height': 'auto',
                        'max-height': 'none',
                        'min-height': '50px'
                    });
                    
                    // Si el contenido es muy largo, establecer altura máxima
                    var contentHeight = $(this).prop('scrollHeight');
                    if (contentHeight > 300) {
                        $(this).css('max-height', '300px');
                        $(this).css('overflow-y', 'auto');
                    }
                });
                
                // Asegurar alineación superior
                $('#tablaReportesAdminDataTable tbody td').css('vertical-align', 'top');
                
                // Inicializar tooltips después de dibujar
                $('[data-toggle="tooltip"]').tooltip();
            },
            "columnDefs": [
                {
                    "targets": 6, // Columna de Descripción (ahora índice 6 porque quitamos ID Reporte)
                    "className": "celda-descripcion dt-body-top",
                    "render": function(data, type, row) {
                        // Para exportaciones y búsquedas
                        if (type === 'filter' || type === 'sort') {
                            return data.replace(/<br\s*\/?>/gi, " ").replace(/&nbsp;/g, ' ');
                        }
                        return data;
                    }
                },
                {
                    "targets": [0], // Columna de Folio
                    "className": "folio-personalizado dt-body-top"
                },
                {
                    "targets": '_all',
                    "className": "dt-body-top"
                }
            ]
        });
    }

    // FUNCIÓN PARA RECARGAR LA TABLA
    function recargarTabla() {
        // Mostrar loading
        $('#tablaReportesContainer').addClass('loading');
        
        $.ajax({
            url: 'obtener_tabla_reportes.php',
            type: 'GET',
            success: function(response) {
                // Reemplazar el contenido del contenedor
                $('#tablaReportesContainer').html(response);
                
                // Reinicializar DataTable
                setTimeout(function() {
                    inicializarDataTable();
                    $('#tablaReportesContainer').removeClass('loading');
                    
                    // Mostrar mensaje de éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Operación realizada',
                            text: 'Asignación actualizada con éxito!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert("Asignación actualizada con éxito!");
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

    // FUNCIÓN ACTUALIZADA PARA GENERAR PDF
    function generarPDF(id, folio_personalizado = null) {
        var url = "../procesos/reportes/pdf/vista_previa.php?reporte=" + id;
        
        // Opcional: agregar folio como parámetro (para el título)
        if (folio_personalizado) {
            url += "&folio=" + encodeURIComponent(folio_personalizado);
        }
        
        window.open(url);
    }

    function actualizarAsignado(selectElement) {
        var reporteId = selectElement.getAttribute('data-reporte-id');
        var trabajadorId = selectElement.value;
        var nombreTrabajador = selectElement.options[selectElement.selectedIndex].text;
        
        if (!trabajadorId) {
            // Si no se seleccionó ningún trabajador
            if (confirm('¿Desea quitar la asignación actual?')) {
                // Enviar valor vacío para quitar asignación
                enviarAsignacion(reporteId, '', '');
            } else {
                // Revertir a valor anterior
                selectElement.value = "";
            }
            return;
        }
        
        // Mostrar confirmación para asignar
        if(confirm('¿Estás seguro de asignar este reporte a: ' + nombreTrabajador + '?')) {
            enviarAsignacion(reporteId, trabajadorId, nombreTrabajador);
        } else {
            // Revertir la selección si el usuario cancela
            selectElement.value = "";
        }
    }
    
    function enviarAsignacion(reporteId, trabajadorId, nombreTrabajador) {
        // Deshabilitar select mientras se procesa
        var selectElement = $('select[data-reporte-id="' + reporteId + '"]')[0];
        if (selectElement) {
            selectElement.disabled = true;
            selectElement.style.backgroundColor = '#f8f9fa';
        }
        
        // Enviar datos via AJAX
        $.ajax({
            url: '../procesos/reportes/crud/actualizar_asignado.php',
            type: 'POST',
            data: {
                reporte_id: reporteId,
                trabajador_id: trabajadorId
            },
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if(result.success) {
                        recargarTabla();
                    } else {
                        alert('Error: ' + result.message);
                        if (selectElement) {
                            selectElement.disabled = false;
                            selectElement.style.backgroundColor = '';
                        }
                    }
                } catch (e) {
                    console.error("Error parseando JSON:", e, "Respuesta:", response);
                    recargarTabla();
                }
            },
            error: function(xhr, status, error) {
                alert('Error de conexión. Recargando página...');
                location.reload();
            }
        });
    }
</script>

<!-- Incluir el modal de cancelar -->
<?php include "modalCancelarEstado.php"; ?>
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

// CONSULTA MODIFICADA - INCLUYE FOLIO PERSONALIZADO
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
        WHERE depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
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
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        border-left: 3px solid #007bff;
    }
    
    /* Estado personalizado */
    .estado-container {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .estado-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 8px;
        border-radius: 4px;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .estado-item.activo {
        background-color: #e9ecef;
        border-left: 3px solid #28a745;
    }
    
    .estado-checkbox {
        display: flex;
        align-items: center;
    }
    
    .custom-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: white;
        cursor: default;
    }
    
    .custom-checkbox.warning {
        background-color: #ffc107;
    }
    
    .custom-checkbox.info {
        background-color: #17a2b8;
    }
    
    .custom-checkbox.success {
        background-color: #28a745;
    }
    
    .custom-checkbox.danger {
        background-color: #dc3545;
    }
    
    .custom-checkbox.checked {
        border: 2px solid #495057;
    }
    
    .check-icon {
        display: none;
    }
    
    .custom-checkbox.checked .check-icon {
        display: block;
    }
    
    .estado-info {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .estado-icon {
        font-size: 14px;
    }
    
    .estado-texto {
        font-size: 12px;
        font-weight: 500;
    }
    
    /* Información del solicitante */
    .solicitante-info {
        display: flex;
        flex-direction: column;
    }
    
    .solicitante-nombre {
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .solicitante-area {
        font-size: 11px;
    }
    
    /* Información de fecha */
    .fecha-info {
        display: flex;
        flex-direction: column;
    }
    
    .fecha-texto {
        font-size: 13px;
        font-weight: 500;
    }
    
    .fecha-hora {
        font-size: 11px;
    }
</style>

<!-- Incluir el CSS externo -->
<link rel="stylesheet" href="../public/css/resumen.css">

<!-- Puedes usar la variable $nombreCompleto donde necesites -->
<div style="display: none;" id="nombreUsuarioCompleto"><?php echo $nombreCompleto; ?></div>

<!-- CONTENEDOR PARA LA TABLA - IMPORTANTE -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
        <thead>
            <th>Folio</th> <!-- CAMBIADO DE "ID Reporte" A "Folio" -->
            <th>Solicitante</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Estado</th>
        </thead>
        <tbody>
            <?php
            while($mostrar = mysqli_fetch_array($respuesta)){
                // Obtener el folio a mostrar (personalizado o el original)
                $folioMostrar = !empty($mostrar['folio_personalizado']) 
                              ? $mostrar['folio_personalizado'] 
                              : "#" . $mostrar['idReporte'];
                
                $descripcion = $mostrar['descripcion']; // Descripción completa
                $descripcionFormateada = htmlspecialchars($descripcion);

                // Reemplazar saltos de línea por espacios
                $descripcionFormateada = str_replace(["\r\n", "\n", "\r"], ' ', $descripcionFormateada);

                // Dividir texto en líneas más cortas para mejor visualización (en columnas)
                $descripcionDividida = wordwrap($descripcionFormateada, 50, "<br>", true);
                
                // Formatear fecha
                $fechaFormateada = date('d/m/Y', strtotime($mostrar['fechaElaboracion']));
                
                // Definir colores y clases para cada estado
                $estados = [
                    1 => ['nombre' => 'Pendiente', 'color' => 'warning', 'icono' => '⏳'],
                    2 => ['nombre' => 'En proceso', 'color' => 'info', 'icono' => '🔄'],
                    3 => ['nombre' => 'Cerrado', 'color' => 'success', 'icono' => '✅'],
                    4 => ['nombre' => 'Cancelado', 'color' => 'danger', 'icono' => '❌']
                ];
            ?>
            <tr>
                <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
                <td>
                    <div class="folio-personalizado" data-toggle="tooltip" 
                         title="<?php echo !empty($mostrar['folio_personalizado']) ? 'Folio personalizado' : 'Folio original'; ?>">
                        <?php echo $folioMostrar; ?>
                    </div>
                    <?php if(!empty($mostrar['folio_personalizado'])): ?>
                    <small class="text-muted d-block mt-1">
                        ID: <?php echo $mostrar['idReporte']; ?>
                    </small>
                    <?php endif; ?>
                </td>
                
                <td>
                    <div class="solicitante-info">
                        <div class="solicitante-nombre fw-semibold">
                            <?php echo htmlspecialchars($mostrar['nombreSolicitante']); ?>
                        </div>
                        <div class="solicitante-area text-muted small">
                            <?php echo htmlspecialchars($mostrar['areaSolicitante']); ?>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fecha-info">
                        <div class="fecha-texto"><?php echo $fechaFormateada; ?></div>
                        <div class="fecha-hora small text-muted">
                            <?php echo date('H:i', strtotime($mostrar['fechaElaboracion'])); ?>
                        </div>
                    </div>
                </td>
                <!-- Descripción -->
                <td class="celda-descripcion">
                    <div class="texto-columna-descripcion">
                        <?php echo $descripcionDividida; ?>
                    </div>
                </td>
                <td>
                    <div class="estado-container">
                        <?php foreach($estados as $numeroEstado => $infoEstado): ?>
                            <div class="estado-item <?php echo $mostrar['estado'] == $numeroEstado ? 'activo' : ''; ?>">
                                <div class="estado-checkbox">
                                    <div class="custom-checkbox <?php echo $infoEstado['color']; ?> 
                                        <?php echo $mostrar['estado'] == $numeroEstado ? 'checked' : ''; ?>">
                                        <span class="check-icon">✓</span>
                                    </div>
                                </div>
                                <div class="estado-info">
                                    <span class="estado-icon"><?php echo $infoEstado['icono']; ?></span>
                                    <span class="estado-texto"><?php echo $infoEstado['nombre']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
        
        // Inicializar DataTable con configuración completa
        dataTableInstance = $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "order": [[0, "desc"]], // Ordenar por Folio descendente
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
                    "targets": 3, // Índice de la columna Descripción (0-based)
                    "className": "celda-descripcion dt-body-top",
                    "render": function(data, type, row) {
                        // Para exportaciones y búsquedas en DataTable
                        if (type === 'filter' || type === 'sort') {
                            return data.replace(/<br\s*\/?>/gi, " ").replace(/&nbsp;/g, ' ');
                        }
                        return data;
                    }
                },
                {
                    "targets": 0, // Columna de Folio
                    "className": "dt-body-top folio-column"
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
        $.ajax({
            url: 'obtener_tabla_reportes.php',
            type: 'GET',
            success: function(response) {
                // Reemplazar el contenido del contenedor
                $('#tablaReportesContainer').html(response);
                // Reinicializar DataTable
                inicializarDataTable();
                
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

    // FUNCIÓN ACTUALIZADA PARA GENERAR PDF
    function generarPDF(id, folio_personalizado = null) {
        var url = "../procesos/reportes/pdf/vista_previa.php?reporte=" + id;
        
        // Opcional: agregar folio como parámetro
        if (folio_personalizado) {
            url += "&folio=" + encodeURIComponent(folio_personalizado);
        }
        
        window.open(url);
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
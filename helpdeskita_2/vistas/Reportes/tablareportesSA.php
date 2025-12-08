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

<style>
    /* Estilo para texto en columnas verticales */
    .texto-columna {
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
    
    /* Para que todas las celdas se alineen arriba */
    #tablaReportesSADataTable tbody td {
        vertical-align: top !important;
    }
    
    /* Estilo específico para celdas de texto largo */
    .celda-texto-largo {
        max-width: 250px !important;
        min-width: 150px !important;
        width: auto !important;
    }
    
    /* Estilo para folio personalizado */
    .folio-personalizado {
        font-weight: bold;
        color: #2c3e50;
    }
</style>

<!-- Puedes usar la variable $nombreCompleto donde necesites -->
<div style="display: none;" id="nombreUsuarioCompleto"><?php echo $nombreCompleto; ?></div>

<!-- CONTENEDOR PARA LA TABLA - IMPORTANTE -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesSADataTable" style="width:100%">
        <thead>
            <th>Folio</th> <!-- CAMBIADO DE "Reporte" A "Folio" -->
            <th>Usuario</th>
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
                // Obtener el folio a mostrar (personalizado o el original)
                $folioMostrar = !empty($mostrar['folio_personalizado']) 
                              ? $mostrar['folio_personalizado'] 
                              : $mostrar['folio'];
                
                $descripcion = trim($mostrar['descripcion']);
                $descripcionFormateada = htmlspecialchars($descripcion);
                // Reemplazar saltos de línea por espacios
                $descripcionFormateada = str_replace(["\r\n", "\n", "\r"], ' ', $descripcionFormateada);
                // Dividir texto en líneas más cortas
                $descripcionDividida = wordwrap($descripcionFormateada, 50, "<br>", true);
                
                // Verificar si está asignado (0 = no asignado)
                $estaAsignado = !empty($mostrar['asignado']) && $mostrar['asignado'] != 0;
            ?>
            <tr>
                <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
                <td class="folio-personalizado">
                    <?php echo $folioMostrar; ?>
                </td>
                
                <td><?php echo $mostrar['idUsuario']; ?></td>
                <td><?php echo $mostrar['areaSolicitante']; ?></td>
                <td><?php echo $mostrar['nombreSolicitante']; ?></td>
                <td><?php echo $mostrar['departamento']; ?></td>
                <td><?php echo $mostrar['fechaElaboracion']; ?></td>
                
                <!-- Descripción con ajuste de texto -->
                <td class="celda-texto-largo">
                    <div class="texto-columna" style="height: auto; max-height: none;">
                        <?php echo $descripcionDividida; ?>
                    </div>
                </td>
                
                <td>
                    <?php if($mostrar['estado'] == 1 || $mostrar['estado'] == 3){ ?>
                        <?php if($estaAsignado) { ?>
                            <!-- Botón IMPRIMIR habilitado solo si está asignado -->
                            <button type="button" class="btn btn-success btn-sm" 
                                    onclick="generarPDF('<?php echo $mostrar['idReporte']; ?>', '<?php echo $folioMostrar; ?>')">
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
            "responsive": true,
            "order": [[0, 'desc']], // Ordenar por folio descendente
            "drawCallback": function(settings) {
                // Ajustar altura de las celdas de texto después de dibujar
                $('.texto-columna').each(function() {
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
                $('#tablaReportesSADataTable tbody td').css('vertical-align', 'top');
            },
            "columnDefs": [
                {
                    "targets": 6, // Columna de Descripción (índice 6)
                    "width": "250px",
                    "className": "celda-texto-largo dt-body-top",
                    "render": function(data, type, row) {
                        // Para exportaciones y búsquedas
                        if (type === 'filter' || type === 'sort') {
                            return data.replace(/<br\s*\/?>/gi, " ").replace(/&nbsp;/g, ' ');
                        }
                        return data;
                    }
                },
                {
                    "targets": 0, // Columna de Folio
                    "className": "folio-personalizado dt-body-top"
                },
                {
                    "targets": '_all',
                    "className": "dt-body-top"
                }
            ]
        });
        
        // Ajustar texto automáticamente
        ajustarTextoColumnas();
        
        // Ajustar cuando cambia el tamaño de la ventana
        $(window).on('resize', function() {
            setTimeout(ajustarTextoColumnas, 100);
        });
    }

    // FUNCIÓN PARA AJUSTAR TEXTO EN COLUMNAS
    function ajustarTextoColumnas() {
        $('.texto-columna').each(function() {
            var texto = $(this).html();
            var ancho = $(this).width();
            
            // Calcular caracteres por línea según ancho (aproximadamente)
            var charsPorLinea = Math.floor(ancho / 8); // 8px por carácter aprox
            if (charsPorLinea < 10) charsPorLinea = 10;
            if (charsPorLinea > 80) charsPorLinea = 80;
            
            // Aplicar wordwrap dinámico
            var textoWrapped = texto.replace(/<br>/g, ' ')
                                   .replace(/\s+/g, ' ')
                                   .trim();
            
            // Dividir en líneas
            var palabras = textoWrapped.split(' ');
            var lineas = [];
            var lineaActual = '';
            
            palabras.forEach(function(palabra) {
                if ((lineaActual + ' ' + palabra).length <= charsPorLinea) {
                    lineaActual += (lineaActual ? ' ' : '') + palabra;
                } else {
                    if (lineaActual) lineas.push(lineaActual);
                    lineaActual = palabra;
                }
            });
            
            if (lineaActual) lineas.push(lineaActual);
            
            // Actualizar contenido
            $(this).html(lineas.join('<br>'));
            
            // Ajustar altura
            $(this).css('height', 'auto');
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

    // FUNCIÓN ACTUALIZADA PARA GENERAR PDF
    function generarPDF(id, folio_personalizado = null){
        var url = "../procesos/reportes/pdf/vista_previa.php?reporte=" + id;
        
        // Opcional: agregar folio como parámetro
        if (folio_personalizado) {
            url += "&folio=" + encodeURIComponent(folio_personalizado);
        }
        
        window.open(url);
    }

    function actualizarAsignado(selectElement) {
        // FUNCIÓN DESHABILITADA - No hace nada
        alert("No tiene permisos para asignar trabajadores");
        // Revertir cualquier cambio en el select (aunque no debería haber selects)
        if (selectElement) {
            selectElement.value = "";
        }
    }
</script>
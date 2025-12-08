<?php
include "../../clases/conexion.php";
session_start();

// Obtener la ubicación del usuario desde la sesión
$ubicacionUsuario = $_SESSION['usuario']['ubicacion'];

$con = new conexion();
$conexion1 = $con->conectar();

// Consulta modificada para filtrar por ubicación usando cat_depa y obtener folio personalizado
$sql = "SELECT DISTINCT
        reportes.id_reporte AS idReporte,
        reportes.estado AS estado,
        reportes.area_solicitante AS areaSolicitante,
        depa.Nombre_depa AS departamento,
        depa.id_depa AS id_depa,  -- Agregado para obtener el ID del departamento
        mantenimiento.descripcion AS mantenimiento,
        finalizados.tipo_servicio AS tipoServicio,
        finalizados.asignado AS asignado,
        finalizados.fecha_realizacion AS fechaRealizacion,
        finalizados.trabajo_realizado AS trabajoRealizado,
        finalizados.verificado_liberado AS verificadoLiberado,
        finalizados.fecha_verificado AS fechaVerificado,
        finalizados.aprobado AS aprobado,
        finalizados.fecha_aprobado AS fechaAprobado,
        finalizados.firma_verificacion AS firmaVerificacion,
        encargados.nombre AS nombreEncargado,
        finalizados.documento_recogido AS recogido,
        finalizados.material AS materialUtilizado,
        /* OBTENER EL FOLIO PERSONALIZADO SEGÚN EL DEPARTAMENTO */
        CASE 
            WHEN depa.id_depa = 1 THEN cc.New_ID
            WHEN depa.id_depa = 2 THEN me.New_ID
            WHEN depa.id_depa = 3 THEN rms.New_ID
            ELSE reportes.id_reporte
        END AS folio_personalizado
        FROM t_reportes AS reportes
        INNER JOIN t_reportes_finalizados AS finalizados 
            ON finalizados.id_reporte = reportes.id_reporte
        INNER JOIN t_cat_mantenimiento AS mantenimiento 
            ON finalizados.id_mantenimiento = mantenimiento.id_mantenimiento
        INNER JOIN t_encargados AS encargados 
            ON finalizados.aprobado = encargados.id_encargado
        INNER JOIN cat_depa AS depa 
            ON reportes.id_depa = depa.id_depa
        /* LEFT JOIN para cada tabla de folios */
        LEFT JOIN cat_CC AS cc ON cc.id_reporte = reportes.id_reporte
        LEFT JOIN cat_ME AS me ON me.id_reporte = reportes.id_reporte
        LEFT JOIN cat_RMS AS rms ON rms.id_reporte = reportes.id_reporte
        WHERE depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
$numFilas = mysqli_num_rows($respuesta);
?>

<style>
    /* Estilo para texto en columnas verticales */
    .texto-vertical {
        writing-mode: vertical-lr; /* Texto de arriba hacia abajo */
        text-orientation: mixed;   /* Mantiene la orientación normal de las letras */
        transform: rotate(0deg);   /* Sin rotación adicional */
        white-space: normal !important;
        word-wrap: break-word !important;
        height: auto !important;
        min-height: 100px;
        max-width: 200px !important; /* Ancho limitado para columnas */
        padding: 5px !important;
        text-align: left !important;
        vertical-align: top !important;
        overflow-wrap: break-word !important;
        display: inline-block !important;
    }
    
    /* Alternativa: texto en múltiples líneas verticales */
    .texto-columna {
        column-width: 200px; /* Ancho de columna */
        column-gap: 10px;
        white-space: normal !important;
        word-wrap: break-word !important;
        max-height: none !important;
        height: auto !important;
        min-height: 100px;
        overflow: visible !important;
        display: block !important;
    }
    
    /* Para que todas las celdas se alineen arriba */
    #tablaReportesAdminDataTable tbody td {
        vertical-align: top !important;
    }
    
    /* Ajustes para DataTable */
    #tablaReportesAdminDataTable {
        table-layout: auto !important;
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

<!-- Mensaje informativo de la ubicación -->
<div class="row mb-3">
    <div class="col-md-12">
        <small class="text-muted">
            Mostrando reportes del departamento: <strong><?php echo $ubicacionUsuario; ?></strong>
        </small>
    </div>
</div>

<?php if($numFilas > 0): ?>
<!-- Solo mostrar la tabla si hay registros -->
<table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
    <thead>
        <tr>
            <th>Folio</th> <!-- Cambiado de "Id Reporte" a "Folio" -->
            <th>Departamento</th>
            <th>Área Solicitante</th>
            <th>Firmado</th>
            <th>Mantenimiento</th>
            <th>Tipo servicio</th>
            <th>Asignado</th>
            <th>Fecha realización</th>
            <th style="min-width: 200px; max-width: 300px;">Trabajo realizado</th>
            <th style="min-width: 200px; max-width: 300px;">Materiales utilizados</th>
            <th>Solicitante</th>
            <th>Fecha de verificado</th>
            <th>Aprobado</th>
            <th>Fecha de aprobado</th>
            <th>Imprimir reporte</th>
            <th>¿Reporte recogido?</th>
        </tr>
    </thead>

    <tbody>
        <?php while($mostrar = mysqli_fetch_array($respuesta)): 
            // Obtener el folio a mostrar (personalizado o el original)
            $folioMostrar = !empty($mostrar['folio_personalizado']) 
                          ? $mostrar['folio_personalizado'] 
                          : $mostrar['idReporte'];
            
            $trabajoRealizado = trim($mostrar['trabajoRealizado']);
            $materialUtilizado = trim($mostrar['materialUtilizado']);
            
            // Limpiar y formatear texto
            $trabajoFormateado = htmlspecialchars($trabajoRealizado);
            $materialFormateado = !empty($materialUtilizado) ? htmlspecialchars($materialUtilizado) : 'Sin materiales';
            
            // Reemplazar saltos de línea por espacios
            $trabajoFormateado = str_replace(["\r\n", "\n", "\r"], ' ', $trabajoFormateado);
            $materialFormateado = str_replace(["\r\n", "\n", "\r"], ' ', $materialFormateado);
            
            // Dividir texto en líneas más cortas para mejor visualización
            $trabajoDividido = wordwrap($trabajoFormateado, 50, "<br>", true);
            $materialDividido = wordwrap($materialFormateado, 50, "<br>", true);
        ?>
        <tr>
            <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
            <td class="folio-personalizado">
                <span data-toggle="tooltip" title="<?php echo !empty($mostrar['folio_personalizado']) ? 'Folio personalizado' : 'Folio original'; ?>">
                    <?php echo $folioMostrar; ?>
                </span>
            </td>
            
            <td><?php echo $mostrar['departamento']; ?></td>
            <td><?php echo $mostrar['areaSolicitante']; ?></td>
            <td>
                <?php if($mostrar['firmaVerificacion'] == 1): ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled data-toggle="tooltip" title="No firmado">
                        <i class="fas fa-question"></i>
                  </button>
                <?php elseif($mostrar['firmaVerificacion'] == 2): ?>
                  <button type="button" class="btn btn-success btn-sm" disabled data-toggle="tooltip" title="Firmado">
                        <i class="fas fa-check"></i>
                  </button>
                <?php endif; ?>
            </td>
            <td><?php echo $mostrar['mantenimiento']; ?></td>
            <td><?php echo $mostrar['tipoServicio']; ?></td>
            <td><?php echo $mostrar['asignado']; ?></td>
            <td><?php echo $mostrar['fechaRealizacion']; ?></td>
            
            <!-- Trabajo realizado - Texto en columna vertical -->
            <td class="celda-texto-largo">
                <div class="texto-columna" style="height: auto; max-height: none;">
                    <?php echo $trabajoDividido; ?>
                </div>
            </td>
            
            <!-- Materiales utilizados - Texto en columna vertical -->
            <td class="celda-texto-largo">
                <div class="texto-columna" style="height: auto; max-height: none;">
                    <?php echo $materialDividido; ?>
                </div>
            </td>
            
            <td><?php echo $mostrar['verificadoLiberado']; ?></td>
            <td><?php echo $mostrar['fechaVerificado']; ?></td>
            <td><?php echo $mostrar['nombreEncargado']; ?></td>
            <td><?php echo $mostrar['fechaAprobado']; ?></td>
            
            <td>
                <?php if($mostrar['estado'] == 3 && $mostrar['firmaVerificacion'] == 2): ?>
                    <button class="btn btn-info btn-sm" 
                            onclick="generarPDF2('<?php echo $mostrar['idReporte']; ?>', '<?php echo $folioMostrar; ?>')"
                            data-toggle="tooltip" title="Generar PDF">
                        <i class="fas fa-print"></i>
                    </button>
                <?php else: ?>
                    <button class="btn btn-warning btn-sm" disabled
                            data-toggle="tooltip" title="<?php echo ($mostrar['firmaVerificacion'] != 2) ? 'Debe estar firmado' : 'No disponible'; ?>">
                        <i class="fas fa-print"></i>
                    </button>
                <?php endif; ?>
            </td>

            <td>
                <?php if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 1): ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled data-toggle="tooltip" title="No firmado">
                        <i class="fas fa-times"></i>
                  </button>
                <?php elseif($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 2): ?>
                  <button type="button" class="btn btn-info btn-sm" 
                          onclick="RecogerReporte('<?php echo $mostrar['idReporte']; ?>')"
                          data-toggle="tooltip" title="Marcar como recogido">
                        <i class="fas fa-check"></i>
                  </button>
                <?php else: ?>
                  <button type="button" class="btn btn-success btn-sm" disabled data-toggle="tooltip" title="Ya recogido">
                        <i class="fas fa-check"></i>
                  </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        // Inicializar tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        // Inicializar DataTable con configuración optimizada
        var table = $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false,
            "scrollX": true, // Permitir scroll horizontal si es necesario
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
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
                $('#tablaReportesAdminDataTable tbody td').css('vertical-align', 'top');
                
                // Inicializar tooltips después de dibujar
                $('[data-toggle="tooltip"]').tooltip();
            },
            "columnDefs": [
                {
                    "targets": [0], // Columna de Folio
                    "className": "folio-personalizado dt-body-top"
                },
                {
                    "targets": [8, 9], // Trabajo realizado (8) y Materiales (9)
                    "width": "250px", // Ancho fijo para estas columnas
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
                    "targets": '_all',
                    "className": "dt-body-top"
                }
            ],
            "initComplete": function() {
                // Ajustar alturas después de inicializar
                setTimeout(function() {
                    $('.texto-columna').each(function() {
                        $(this).css('height', 'auto');
                    });
                    
                    // Inicializar tooltips
                    $('[data-toggle="tooltip"]').tooltip();
                }, 100);
            }
        });
        
        // Función para ajustar texto automáticamente
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
        
        // Ajustar cuando cambia el tamaño de la ventana
        $(window).on('resize', function() {
            setTimeout(ajustarTextoColumnas, 100);
        });
        
        // Ajustar inicialmente
        setTimeout(ajustarTextoColumnas, 200);
    });
</script>

<?php else: ?>
<!-- Mostrar mensaje cuando no hay registros -->
<div class="alert alert-info text-center" role="alert">
    <i class="fas fa-info-circle fa-2x mb-3"></i>
    <h4>No hay reportes finalizados</h4>
    <p class="mb-0">No se encontraron reportes finalizados para el departamento: <strong><?php echo $ubicacionUsuario; ?></strong></p>
</div>
<?php endif; ?>

<script>
    // FUNCIÓN ACTUALIZADA PARA GENERAR PDF
    function generarPDF2(id, folio_personalizado = null) {
        var url = "../procesos/reportes/pdf/vista_previa_02.php?reporte=" + id;
        
        // Opcional: agregar folio como parámetro
        if (folio_personalizado) {
            url += "&folio=" + encodeURIComponent(folio_personalizado);
        }
        
        window.open(url);
    }
    
    function RecogerReporte(id) {
        document.getElementById("idReporteR").value = id;
        $('#modalRecoger').modal('show');
    }
</script>
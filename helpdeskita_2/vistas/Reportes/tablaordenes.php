<?php
include "../../clases/conexion.php";
$con = new conexion();
$conexion1 = $con->conectar();

// Iniciamos las variables para filtrar las fechas
$desde="";
$hasta="";
if(isset($_GET['desde']) && isset($_GET['hasta'])){
    $desde = $_GET['desde'];
    $hasta = $_GET['hasta'];
}

// CONSULTA MODIFICADA - INCLUYE FOLIO PERSONALIZADO Y DEPARTAMENTO
$sql = "SELECT DISTINCT
        reportes.id_reporte AS idReporte,
        reportes.estado AS estado,
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
        LEFT JOIN cat_RMS AS rms ON rms.id_reporte = reportes.id_reporte";
$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
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
    #tablaReportesAdminDataTable tbody td {
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

<table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
    <thead>
        <th>Folio</th> <!-- CAMBIADO DE "Id Reporte" A "Folio" -->
        <th>Mantenimiento</th>
        <th>Tipo servicio</th>
        <th>Asignado</th>
        <th>Fecha realización</th>
        <th>Verificado</th>
        <th style="min-width: 200px; max-width: 300px;">Trabajo realizado</th>
        
        <th>Fecha de verificado</th>
        <th>Aprobado</th>
        <th>Fecha de aprobado</th>
        <th>Imprimir reporte</th>
        <th>Firmado</th>
        <th>¿Reporte recogido?</th>
    </thead>

    <tbody>
        <?php
            while($mostrar = mysqli_fetch_array($respuesta)){
                // Obtener el folio a mostrar (personalizado o el original)
                $folioMostrar = !empty($mostrar['folio_personalizado']) 
                              ? $mostrar['folio_personalizado'] 
                              : $mostrar['idReporte'];
                
                $trabajoRealizado = trim($mostrar['trabajoRealizado']);
                $trabajoFormateado = htmlspecialchars($trabajoRealizado);
                // Reemplazar saltos de línea por espacios
                $trabajoFormateado = str_replace(["\r\n", "\n", "\r"], ' ', $trabajoFormateado);
                // Dividir texto en líneas más cortas
                $trabajoDividido = wordwrap($trabajoFormateado, 50, "<br>", true);
        ?>

        <tr>
            <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
            <td class="folio-personalizado">
                <?php echo $folioMostrar; ?>
            </td>
            
            <td><?php echo $mostrar['mantenimiento']; ?></td>
            <td><?php echo $mostrar['tipoServicio']; ?></td>
            <td><?php echo $mostrar['asignado']; ?></td>
            <td><?php echo $mostrar['fechaRealizacion']; ?></td>
            <td><?php echo $mostrar['verificadoLiberado']; ?></td>
            <!-- Trabajo realizado con ajuste de texto -->
            <td class="celda-texto-largo">
                <div class="texto-columna" style="height: auto; max-height: none;">
                    <?php echo $trabajoDividido; ?>
                </div>
            </td>
            
            
            <td><?php echo $mostrar['fechaVerificado']; ?></td>
            <td><?php echo $mostrar['nombreEncargado']; ?></td>
            <td><?php echo $mostrar['fechaAprobado']; ?></td>
            
            <td>
                <!-- Botón de imprimir siempre visible -->
                <button class="btn btn-warning btn-sm" 
                        onclick="generarPDF2('<?php echo $mostrar['idReporte']; ?>', '<?php echo $folioMostrar; ?>')"
                        title="Generar PDF">
                    <i class="fas fa-print"></i>
                </button>
            </td>

            <td>
                <?php if($mostrar['firmaVerificacion'] == 1) { ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled title="No firmado">
                        <i class="fas fa-question"></i>
                  </button>
                <?php } else if($mostrar['firmaVerificacion'] == 2) { ?>
                  <button type="button" class="btn btn-success btn-sm" disabled title="Firmado">
                        <i class="fas fa-check"></i>
                  </button>
                <?php } ?>
            </td>

            <td>
                <?php if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 1) { ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled title="No firmado">
                        <i class="fas fa-times"></i>
                  </button>
                <?php } else if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 2) { ?>
                  <button type="button" class="btn btn-info btn-sm" 
                          onclick="RecogerReporte('<?php echo $mostrar['idReporte']; ?>')"
                          title="Marcar como recogido">
                        <i class="fas fa-check"></i>
                  </button>
                <?php } else { ?>
                  <button type="button" class="btn btn-success btn-sm" disabled title="Ya recogido">
                        <i class="fas fa-check"></i>
                  </button>
                <?php } ?>
            </td>
        </tr>
    
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        // Inicializar DataTable con configuración mejorada
        var table = $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false,
            "scrollX": true,
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
            },
            "columnDefs": [
                {
                    "targets": 5, // Columna de Trabajo realizado (índice 5)
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
        
        // Función para ajustar texto automáticamente
        function ajustarTextoColumnas() {
            $('.texto-columna').each(function() {
                var texto = $(this).html();
                var ancho = $(this).width();
                
                var charsPorLinea = Math.floor(ancho / 8);
                if (charsPorLinea < 10) charsPorLinea = 10;
                if (charsPorLinea > 80) charsPorLinea = 80;
                
                var textoWrapped = texto.replace(/<br>/g, ' ')
                                       .replace(/\s+/g, ' ')
                                       .trim();
                
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
                
                $(this).html(lineas.join('<br>'));
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
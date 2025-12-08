<?php
    session_start();
    include "../../clases/conexion.php";
    $con = new conexion();
    $conexion1 = $con->conectar();
    $idUsuario = $_SESSION['usuario']['id'];
    
    // CONSULTA MODIFICADA - OBTIENE EL FOLIO PERSONALIZADO
    $sql = "SELECT
                reportes.id_reporte AS idReporte,
                usuarios.id_usuario AS idUsuario,
                reportes.folio AS folio,
                depa.Nombre_depa AS departamento,
                depa.id_depa AS id_depa,  
                reportes.area_solicitante AS areaSolicitante,
                reportes.nombre_solicitante AS nombreSolicitante,
                reportes.fecha_elaboracion AS fechaElaboracion,
                reportes.descripcion AS descripcion,
                reportes.estado AS estado,
                finalizados.id_reporte AS idReporte1,
                finalizados.documento_recogido AS recogido,
                finalizados.firma_verificacion AS firmaVerificacion,
                -- OBTENER EL FOLIO PERSONALIZADO SEGÚN EL DEPARTAMENTO
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
                t_reportes_finalizados AS finalizados ON reportes.id_reporte = finalizados.id_reporte
            INNER JOIN
                cat_depa AS depa ON depa.id_depa = reportes.id_depa
            -- LEFT JOIN para cada tabla de folios
            LEFT JOIN cat_CC AS cc ON cc.id_reporte = reportes.id_reporte
            LEFT JOIN cat_ME AS me ON me.id_reporte = reportes.id_reporte
            LEFT JOIN cat_RMS AS rms ON rms.id_reporte = reportes.id_reporte
            WHERE
                reportes.id_usuario = ?";

    // USAR CONSULTA PREPARADA POR SEGURIDAD
    $stmt = mysqli_prepare($conexion1, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $respuestaArray = array();
    while($row = mysqli_fetch_assoc($resultado)){
        $respuestaArray[] = $row;
    }
?>

<?php
    $mostrar1 = $respuestaArray;
?>

<style>
    /* Estilo para texto en columnas verticales */
    .texto-columna {
        column-width: 200px; /* Ancho de columna */
        column-gap: 10px;
        white-space: normal !important;
        word-wrap: break-word !important;
        max-height: none !important;
        height: auto !important;
        min-height: 50px;
        overflow: visible !important;
        display: block !important;
    }
    
    /* Para que todas las celdas se alineen arriba */
    #tablaReportesDataTable tbody td {
        vertical-align: top !important;
    }
    
    /* Ajustes para DataTable */
    #tablaReportesDataTable {
        table-layout: auto !important;
    }
    
    /* Estilo específico para celdas de texto largo */
    .celda-texto-largo {
        max-width: 250px !important;
        min-width: 150px !important;
        width: auto !important;
    }
    
    /* Estilo para indicar que es folio personalizado */
    .folio-personalizado {
        font-weight: bold;
        color: #2c3e50;
    }
</style>

<button id="button-crear_reportes" class="btn btn-primary" data-toggle="modal" data-target="#modalcrearReporte"
        onclick="obtenerDatosUsuario(<?php echo isset($mostrar1[0]['idUsuario']) ? $mostrar1[0]['idUsuario'] : ''; ?>)" hidden>
    Crear reporte
</button>

<table class="table table-sm dt-responsive nowrap" id="tablaReportesDataTable" style="width:100%">
    <thead>
        <th>Folio</th> <!-- CAMBIADO DE "Reporte" A "Folio" -->
        <th>ID Reporte</th>
        <th>Usuario</th>
        <th>Área solicitante</th>
        <th>Departamento</th>
        <th>Solicitante</th>
        <th>Fecha</th>
        <th style="min-width: 200px; max-width: 300px;">Descripción</th>
        <th>Estado</th>
        <th>Imprimir reporte terminado</th>
        <th>¿Liberar Servicio Terminado?</th>
        <th>Reporte recogido</th>
    </thead>

    <tbody>
        <?php
            foreach ($respuestaArray as $mostrar) {
                // Obtener el folio a mostrar (personalizado o el original)
                $folioMostrar = !empty($mostrar['folio_personalizado']) 
                              ? $mostrar['folio_personalizado'] 
                              : $mostrar['folio'];
                
                // Verificar si es un folio personalizado
                $esFolioPersonalizado = !empty($mostrar['folio_personalizado']);
                
                // Procesar el texto para mostrarlo en columnas
                $descripcion = trim($mostrar['descripcion']);
                $descripcionFormateada = htmlspecialchars($descripcion);
                
                // Reemplazar saltos de línea por espacios
                $descripcionFormateada = str_replace(["\r\n", "\n", "\r"], ' ', $descripcionFormateada);
                
                // Dividir texto en líneas más cortas para mejor visualización
                $descripcionDividida = wordwrap($descripcionFormateada, 50, "<br>", true);
        ?>

        <tr>
            <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
            <td class="folio-personalizado">
                <span title="<?php echo $esFolioPersonalizado ? 'Folio personalizado' : 'Folio original'; ?>">
                    <?php echo $folioMostrar; ?>
                </span>
            </td>
            
            <!-- ID DEL REPORTE (PARA REFERENCIA) -->
            <td>
                <small class="text-muted"><?php echo $mostrar['idReporte']; ?></small>
            </td>
            
            <td><?php echo $mostrar['idUsuario']; ?></td>
            <td><?php echo $mostrar['areaSolicitante']; ?></td>
            <td><?php echo $mostrar['departamento']; ?></td>
            <td><?php echo $mostrar['nombreSolicitante']; ?></td>
            <td><?php echo $mostrar['fechaElaboracion']; ?></td>
            
            <!-- Descripción - Texto en columna vertical -->
            <td class="celda-texto-largo">
                <div class="texto-columna" style="height: auto; max-height: none;">
                    <?php echo $descripcionDividida; ?>
                </div>
            </td>
           
            <td>
                <?php if($mostrar['estado'] == 1) { ?>
                    <button class="btn btn-warning btn-sm" disabled>
                        Pendiente
                    </button>
                <?php } else { ?>
                    <button class="btn btn-info btn-sm" disabled>
                        Resuelto
                    </button>
                <?php } ?>
            </td>
            
            <td>
                <?php if($mostrar['estado'] == 3 && $mostrar['firmaVerificacion'] == 2) { ?>
                  <button type="button" class="btn btn-info btn-sm" 
                          onclick="generarPDF2('<?php echo $mostrar['idReporte']; ?>', '<?php echo $folioMostrar; ?>')">
                      <i class="fas fa-print"></i>
                  </button>
                <?php } else { ?>
                    <button type="button" class="btn btn-warning btn-sm" disabled>
                        <i class="fas fa-print"></i>
                    </button>
                <?php } ?>
            </td>

            <td>
                <?php if($mostrar['estado'] == 3 && $mostrar['firmaVerificacion'] == 1) { ?>
                  <button type="button" class="btn btn-info btn-sm" 
                          onclick="firmarReporte('<?php echo $mostrar['idReporte']; ?>')">
                        <i class="fas fa-check"></i>
                  </button>
                <?php } else if($mostrar['estado'] == 3 && $mostrar['firmaVerificacion'] == 2) { ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-check"></i>
                  </button>
                <?php } ?>
            </td>

            <td>
                <?php if($mostrar['recogido'] == 2) { ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-check"></i>
                  </button>
                <?php } else { ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-times"></i>
                  </button>
                <?php } ?>
            </td>
        </tr>

        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        // Inicializar DataTable con configuración optimizada
        $('#tablaReportesDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false,
            "scrollX": true,
            "order": [[0, 'desc']], // Ordenar por folio descendente
            "columnDefs": [
                {
                    "targets": 7, // Columna de Descripción (ahora índice 7)
                    "width": "250px",
                    "className": "celda-texto-largo dt-body-top",
                    "render": function(data, type, row) {
                        if (type === 'filter' || type === 'sort') {
                            return data.replace(/<br\s*\/?>/gi, " ").replace(/&nbsp;/g, ' ');
                        }
                        return data;
                    }
                },
                {
                    "targets": [0], // Columna de Folio (primera columna)
                    "orderData": [1], // Ordenar por ID del reporte (columna 1)
                    "className": "dt-body-top"
                },
                {
                    "targets": '_all',
                    "className": "dt-body-top"
                }
            ],
            "drawCallback": function(settings) {
                // Ajustar altura de las celdas de texto después de dibujar
                $('.texto-columna').each(function() {
                    $(this).css({
                        'height': 'auto',
                        'max-height': 'none',
                        'min-height': '50px'
                    });
                    
                    var contentHeight = $(this).prop('scrollHeight');
                    if (contentHeight > 300) {
                        $(this).css('max-height', '300px');
                        $(this).css('overflow-y', 'auto');
                    }
                });
                
                $('#tablaReportesDataTable tbody td').css('vertical-align', 'top');
                
                // Agregar tooltips a los folios personalizados
                $('.folio-personalizado span').tooltip({
                    trigger: 'hover',
                    placement: 'top'
                });
            }
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
        
        // Inicializar tooltips de Bootstrap
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        $(window).on('resize', function() {
            setTimeout(ajustarTextoColumnas, 100);
        });
        
        setTimeout(ajustarTextoColumnas, 200);
    });

    function firmarReporte(id) {
        document.getElementById("idReporteF").value = id;
        $('#modalFirmarReporte').modal('show');
    }

    function generarPDF2(id, folio_personalizado = null) {
        // Usar siempre el id_reporte original para buscar el PDF
        // El folio_personalizado solo es para mostrar en la tabla
        var url = "../procesos/reportes/pdf/vista_previa_02.php?reporte=" + id;
        
        // Opcional: agregar folio personalizado como parámetro para el título
        if (folio_personalizado) {
            url += "&folio=" + encodeURIComponent(folio_personalizado);
        }
        
        window.open(url);
    }
</script>
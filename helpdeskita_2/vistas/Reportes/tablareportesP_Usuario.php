<?php
    session_start();
    include "../../clases/conexion.php";
    $con = new conexion();
    $conexion1 = $con->conectar();
    $idUsuario = $_SESSION['usuario']['id'];

    // CONSULTA MODIFICADA - OBTIENE EL FOLIO PERSONALIZADO
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
                reportes.extra AS motivoCancelacion,
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

<style>
    .bg-very-light-red {
        background-color: #fc8a8aff !important;
    }
    .bg-very-light-green {
        background-color: #8afc8aff !important;
    }
    
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
    
    /* Estilo específico para celdas de texto largo */
    .celda-texto-largo {
        max-width: 250px !important;
        min-width: 150px !important;
        width: auto !important;
    }
</style>

<button id="button-crear_reportes" class="btn btn-primary" data-toggle="modal" data-target="#modalcrearReporte"
        onclick="obtenerDatosUsuario(<?php echo $respuestaArray[0]['usuario'] ?? ''?>)" >
    Crear reporte
</button>

<table class="table table-sm dt-responsive nowrap" id="tablaReportesDataTable" style="width:100%">
    <thead>
        <th>Folio</th> <!-- CAMBIADO DE "Reporte" A "Folio" -->
        <th>Usuario</th>
        <th>Área solicitante</th>
        <th>Departamento</th>
        <th>Nombre Solicitante</th>
        <th>Fecha</th>
        <th style="min-width: 200px; max-width: 300px;">Descripción</th>
        <th>Estado</th>
        <th>Comentarios</th>
        <th>Acciones</th> <!-- NUEVA COLUMNA PARA ACCIONES -->
    </thead>

    <tbody>
        <?php foreach ($respuestaArray as $mostrar): ?>
            <?php 
                // Formatear descripción
                $descripcion = trim($mostrar['descripcion']);
                $descripcionFormateada = htmlspecialchars($descripcion);
                $descripcionFormateada = str_replace(["\r\n", "\n", "\r"], ' ', $descripcionFormateada);
                $descripcionDividida = wordwrap($descripcionFormateada, 50, "<br>", true);
                
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
                
                // Obtener el folio a mostrar
                $folioMostrar = !empty($mostrar['folio_personalizado']) 
                              ? $mostrar['folio_personalizado'] 
                              : $mostrar['reporte'];
                ?>
                <tr class="<?php echo $fondoRojo . ' ' . $fondoverde; ?>">
                <!-- MOSTRAR EL FOLIO PERSONALIZADO -->
                <td><?php echo $folioMostrar; ?></td>
                <td><?php echo $mostrar['nombreUsuario']; ?></td>
                <td><?php echo $mostrar['areaSolicitante']; ?></td>
                <td><?php echo $mostrar['departamento']; ?></td>
                <td><?php echo $mostrar['solicitante']; ?></td>
                <td><?php echo $mostrar['fecha']; ?></td>
                
                <!-- Descripción en formato de columna vertical -->
                <td class="celda-texto-largo">
                    <div class="texto-columna" style="height: auto; max-height: none;">
                        <?php echo $descripcionDividida; ?>
                    </div>
                </td>
                
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
                <td>
                    <?php if($mostrar['estado'] == 1): ?>
                        <button class="btn btn-info btn-sm" onclick="generarPDF2('<?php echo $mostrar['reporte']; ?>', '<?php echo $folioMostrar; ?>')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        // Inicializar DataTable con configuración optimizada
        var table = $('#tablaReportesDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false,
            "scrollX": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
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
                $('#tablaReportesDataTable tbody td').css('vertical-align', 'top');
            },
            "columnDefs": [
                {
                    "targets": [6], // Descripción (ahora índice 6 porque agregamos columna)
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
                    "targets": '_all',
                    "className": "dt-body-top"
                }
            ],
            "initComplete": function() {
                setTimeout(function() {
                    $('.texto-columna').each(function() {
                        $(this).css('height', 'auto');
                    });
                }, 100);
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
        
        // Ajustar cuando cambia el tamaño de la ventana
        $(window).on('resize', function() {
            setTimeout(ajustarTextoColumnas, 100);
        });
        
        // Ajustar inicialmente
        setTimeout(ajustarTextoColumnas, 200);
    });

    function firmarReporte(id){
        document.getElementById("idReporteF").value=id;
        $('#modalFirmarReporte').modal('show');
    }

    // FUNCIÓN MODIFICADA - AHORA ACEPTA FOLIO PERSONALIZADO
    function generarPDF2(id, folio_personalizado = null){
        // Usar siempre el id_reporte original para buscar el PDF
        // El folio_personalizado solo es para mostrar en la tabla
        window.open("../procesos/reportes/pdf/vista_previa_02.php?reporte="+id);
    }
</script>
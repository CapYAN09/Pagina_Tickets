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

// CONSULTA MODIFICADA - MOSTRAR TODOS LOS REPORTES SIN FILTRAR POR ESTADO
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
        WHERE depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
?>

<!-- Incluir el CSS externo -->
<link rel="stylesheet" href="../public/css/resumen.css">

<!-- Puedes usar la variable $nombreCompleto donde necesites -->
<div style="display: none;" id="nombreUsuarioCompleto"><?php echo $nombreCompleto; ?></div>

<!-- CONTENEDOR PARA LA TABLA - IMPORTANTE -->
<div id="tablaReportesContainer">
    <table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
        <thead>
            <th>ID Reporte</th>
            <th>Solicitante</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Estado</th>
        </thead>
        <tbody>
            <?php
            while($mostrar = mysqli_fetch_array($respuesta)){
                $descripcion = $mostrar['descripcion']; // Descripción completa
                
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
                <td class="fw-bold">#<?php echo $mostrar['idReporte']; ?></td>
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
                <td>
                    <div class="descripcion-contenido">
                        <?php echo htmlspecialchars($descripcion); ?>
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
            "order": [[0, "desc"]] // Ordenar por ID de reporte descendente
        });
    }

    // FUNCIÓN PARA RECARGAR LA TABLA - NUEVA
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
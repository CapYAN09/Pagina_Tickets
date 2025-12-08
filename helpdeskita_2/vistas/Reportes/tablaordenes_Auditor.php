<?php
include "../../clases/conexion.php";
$con = new conexion();
$conexion1 = $con->conectar();

// Iniciamos las variables para filtrar las fechas
$desde = "";
$hasta = "";
$whereCondition = "";

if(isset($_GET['desde']) && isset($_GET['hasta']) && !empty($_GET['desde']) && !empty($_GET['hasta'])){
    $desde = $_GET['desde'];
    $hasta = $_GET['hasta'];
    
    // Las fechas ya vienen en formato yyyy-mm-dd del input date
    // Solo asegurarnos de que estén en el formato correcto para MySQL
    $desde_mysql = $desde; // yyyy-mm-dd
    $hasta_mysql = $hasta; // yyyy-mm-dd
    
    // Agregar condición WHERE para filtrar por fecha_realizacion
    $whereCondition = " WHERE DATE(finalizados.fecha_realizacion) BETWEEN '$desde_mysql' AND '$hasta_mysql'";
    
    // Para debugging - muestra la consulta SQL
    // echo "<!-- SQL: $sql -->";
}

// Consulta con filtro de fechas
$sql = "SELECT DISTINCT
        reportes.id_reporte AS idReporte,
        reportes.estado AS estado,
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
        finalizados.documento_recogido AS recogido
        FROM t_reportes AS reportes
        INNER JOIN t_reportes_finalizados AS finalizados 
            ON finalizados.id_reporte = reportes.id_reporte
        INNER JOIN t_cat_mantenimiento AS mantenimiento 
            ON finalizados.id_mantenimiento = mantenimiento.id_mantenimiento
        INNER JOIN t_encargados AS encargados 
            ON finalizados.aprobado = encargados.id_encargado"
        . $whereCondition;

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
$numFilas = mysqli_num_rows($respuesta);
?>

<!-- Formulario para filtrar por fechas -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter"></i> Filtrar Reportes por Fecha de Realización
            </div>
            <div class="card-body">
                <form method="GET" action="" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="desde" class="mr-2"><strong>Desde:</strong></label>
                        <input type="date" class="form-control" id="desde" name="desde" 
                               value="<?php echo $desde; ?>" required>
                    </div>
                    <div class="form-group mr-3 mb-2">
                        <label for="hasta" class="mr-2"><strong>Hasta:</strong></label>
                        <input type="date" class="form-control" id="hasta" name="hasta" 
                               value="<?php echo $hasta; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <?php if(!empty($desde) && !empty($hasta)): ?>
                    <a href="?" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times"></i> Limpiar Filtro
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Mostrar información del filtro aplicado -->
<?php if(!empty($desde) && !empty($hasta)): ?>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-info py-2">
            <small>
                <i class="fas fa-info-circle"></i> 
                Filtro aplicado: 
                <strong>Desde:</strong> <?php echo date('d/m/Y', strtotime($desde)); ?> 
                <strong>Hasta:</strong> <?php echo date('d/m/Y', strtotime($hasta)); ?>
                | <strong>Reportes encontrados:</strong> <?php echo $numFilas; ?>
                <?php if(!empty($desde)): ?>
                | <strong>Consulta:</strong> BETWEEN '<?php echo $desde; ?>' AND '<?php echo $hasta; ?>'
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($numFilas > 0): ?>
<table class="table table-sm dt-responsive nowrap" id="tablaReportesAdminDataTable" style="width:100%">
    <thead>
        <tr>
            <th>Id Reporte</th>
            <th>Mantenimiento</th>
            <th>Tipo servicio</th>
            <th>Asignado</th>
            <th>Fecha realización</th>
            <th>Trabajo realizado</th>
            <th>Verificado</th>
            <th>Fecha de verificado</th>
            <th>Aprobado</th>
            <th>Fecha de aprobado</th>
            <th>Imprimir reporte</th>
            <th>Firmado</th>
            <th>¿Reporte recogido?</th>
        </tr>
    </thead>

    <tbody>
        <?php
            while($mostrar = mysqli_fetch_array($respuesta)){
                $trabajoRealizado = "";
                if(strlen($mostrar['trabajoRealizado']) > 25){
                    $trabajoRealizado = substr($mostrar['trabajoRealizado'], 0, 25)."...";
                }else{
                    $trabajoRealizado = $mostrar['trabajoRealizado'];
                }
        ?>
        <tr>
            <td><?php echo $mostrar['idReporte']; ?></td>
            <td><?php echo $mostrar['mantenimiento']; ?></td>
            <td><?php echo $mostrar['tipoServicio']; ?></td>
            <td><?php echo $mostrar['asignado']; ?></td>
            <td>
                <?php 
                // Mostrar fecha en formato legible
                echo date('d/m/Y', strtotime($mostrar['fechaRealizacion'])); 
                ?>
            </td>
            <td title="<?php echo htmlspecialchars($mostrar['trabajoRealizado']); ?>">
                <?php echo $trabajoRealizado; ?>
            </td>
            <td><?php echo $mostrar['verificadoLiberado']; ?></td>
            <td>
                <?php 
                if(!empty($mostrar['fechaVerificado'])) {
                    echo date('d/m/Y', strtotime($mostrar['fechaVerificado']));
                }
                ?>
            </td>
            <td><?php echo $mostrar['nombreEncargado']; ?></td>
            <td>
                <?php 
                if(!empty($mostrar['fechaAprobado'])) {
                    echo date('d/m/Y', strtotime($mostrar['fechaAprobado']));
                }
                ?>
            </td>
            
            <td>
                <button class="btn btn-warning btn-sm" onclick="generarPDF2(<?php echo $mostrar['idReporte'];?>)">
                    <i class="fas fa-print"></i>
                </button>
            </td>

            <td>
                <?php if($mostrar['firmaVerificacion'] == 1) { ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-question"></i>
                  </button>
                <?php
                } else if($mostrar['firmaVerificacion'] == 2) {
                ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-check"></i>
                  </button>
                <?php
                }
                ?>
            </td>

            <td>
                <?php if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 1) { ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-times"></i>
                  </button>
                <?php
                } else if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 2) {
                ?>
                  <button type="button" class="btn btn-info btn-sm" onclick= "RecogerReporte(<?php echo $mostrar['idReporte'];?>)">
                        <i class="fas fa-check"></i>
                  </button>
                <?php
                } else {
                ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-check"></i>
                  </button>
                  <?php 
                  }
                  ?>
            </td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<?php else: ?>
<!-- Mensaje cuando no hay resultados -->
<div class="alert alert-warning text-center" role="alert">
    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
    <h4>No se encontraron reportes</h4>
    <p class="mb-0">
        <?php if(!empty($desde) && !empty($hasta)): ?>
        No hay reportes finalizados en el rango de fechas seleccionado (<?php echo date('d/m/Y', strtotime($desde)); ?> - <?php echo date('d/m/Y', strtotime($hasta)); ?>).
        <?php if(!empty($desde)): ?>
        <br><small>Consulta ejecutada: BETWEEN '<?php echo $desde; ?>' AND '<?php echo $hasta; ?>'</small>
        <?php endif; ?>
        <?php else: ?>
        No hay reportes finalizados disponibles.
        <?php endif; ?>
    </p>
</div>
<?php endif; ?>

<script>
    $(document).ready(function(){
       $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "order": [[4, "desc"]],
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "pageLength": 10
        }); 

        // Validación básica de fechas en el cliente
        $('form').on('submit', function(e) {
            var desde = $('#desde').val();
            var hasta = $('#hasta').val();
            
            if (desde && hasta) {
                if (new Date(desde) > new Date(hasta)) {
                    alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta"');
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
    
    function generarPDF2(id) {
        window.open("../procesos/reportes/pdf/vista_previa_02.php?reporte="+id);
    }
    
    function RecogerReporte(id) {
        document.getElementById("idReporteR").value = id;
        $('#modalRecoger').modal('show');
    }
</script>
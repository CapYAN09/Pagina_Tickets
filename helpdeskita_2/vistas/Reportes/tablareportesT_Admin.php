<?php
include "../../clases/conexion.php";
session_start();

// Obtener la ubicación del usuario desde la sesión
$ubicacionUsuario = $_SESSION['usuario']['ubicacion'];

$con = new conexion();
$conexion1 = $con->conectar();

// Consulta modificada para filtrar por ubicación usando cat_depa
$sql = "SELECT DISTINCT
        reportes.id_reporte AS idReporte,
        reportes.estado AS estado,
        reportes.area_solicitante AS areaSolicitante,
        depa.Nombre_depa AS departamento,
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
            ON finalizados.aprobado = encargados.id_encargado
        INNER JOIN cat_depa AS depa 
            ON reportes.id_depa = depa.id_depa
        WHERE depa.Nombre_depa = '$ubicacionUsuario'";

$respuesta = mysqli_query($conexion1, $sql) or die(mysqli_error($conexion1));
$numFilas = mysqli_num_rows($respuesta);
?>

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
            <th>Id Reporte</th>
            <th>Departamento</th>
            <th>Área Solicitante</th>
            <th>Firmado</th>
            <th>Mantenimiento</th>
            <th>Tipo servicio</th>
            <th>Asignado</th>
            <th>Fecha realización</th>
            <th>Trabajo realizado</th>
            <th>Solisitante</th>
            <th>Fecha de verificado</th>
            <th>Aprobado</th>
            <th>Fecha de aprobado</th>
            <th>Imprimir reporte</th>
            <th>¿Reporte recogido?</th>
        </tr>
    </thead>

    <tbody>
        <?php while($mostrar = mysqli_fetch_array($respuesta)): 
            $trabajoRealizado = "";
            if(strlen($mostrar['trabajoRealizado']) > 25){
                $trabajoRealizado = substr($mostrar['trabajoRealizado'], 0, 25)."...";
            }else{
                $trabajoRealizado = $mostrar['trabajoRealizado'];
            }
        ?>
        <tr>
            <td><?php echo $mostrar['idReporte']; ?></td>
            <td><?php echo $mostrar['departamento']; ?></td>
            <td><?php echo $mostrar['areaSolicitante']; ?></td>
            <td>
                <?php if($mostrar['firmaVerificacion'] == 1): ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-question"></i>
                  </button>
                <?php elseif($mostrar['firmaVerificacion'] == 2): ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-check"></i>
                  </button>
                <?php endif; ?>
            </td>
            <td><?php echo $mostrar['mantenimiento']; ?></td>
            <td><?php echo $mostrar['tipoServicio']; ?></td>
            <td><?php echo $mostrar['asignado']; ?></td>
            <td><?php echo $mostrar['fechaRealizacion']; ?></td>
            <td><?php echo $trabajoRealizado; ?></td>
            <td><?php echo $mostrar['verificadoLiberado']; ?></td>
            <td><?php echo $mostrar['fechaVerificado']; ?></td>
            <td><?php echo $mostrar['nombreEncargado']; ?></td>
            <td><?php echo $mostrar['fechaAprobado']; ?></td>
            
            <td>
                <?php if($mostrar['estado'] == 3 && $mostrar['firmaVerificacion'] == 2): ?>
                    <button class="btn btn-info btn-sm" onclick="generarPDF2(<?php echo $mostrar['idReporte'];?>)">
                        <i class="fas fa-print"></i>
                    </button>
                <?php else: ?>
                    <button class="btn btn-warning btn-sm" disabled>
                        <i class="fas fa-print"></i>
                    </button>
                <?php endif; ?>
            </td>

           

            <td>
                <?php if($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 1): ?>
                  <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-times"></i>
                  </button>
                <?php elseif($mostrar['recogido'] == 1 && $mostrar['firmaVerificacion'] == 2): ?>
                  <button type="button" class="btn btn-info btn-sm" onclick="RecogerReporte(<?php echo $mostrar['idReporte'];?>)">
                        <i class="fas fa-check"></i>
                  </button>
                <?php else: ?>
                  <button type="button" class="btn btn-success btn-sm" disabled>
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
       $('#tablaReportesAdminDataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false
        }); 
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
    function generarPDF2(id) {
        window.open("../procesos/reportes/pdf/vista_previa_02.php?reporte="+id);
    }
    
    function RecogerReporte(id) {
        document.getElementById("idReporteR").value = id;
        $('#modalRecoger').modal('show');
    }
</script>
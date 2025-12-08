<?php
 
include "conexion.php";

class ReportesT extends conexion
{
    public function terminarReporte($datos)
    {
        $conexion = Conexion::conectar();
        
        if (!$conexion) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        // Obtener el id_depa del usuario desde la sesión
        $idUsuario = $datos['idUsuario'];
        $idDepa = $this->obtenerIdDepartamentoUsuario($idUsuario);
        
        // Obtener automáticamente el valor de "aprobado" según la ubicación
        $aprobado = $this->obtenerValorAprobado($idDepa);
        
        // Obtener el nombre del trabajador asignado desde t_usuarios
        $nombreAsignado = $this->obtenerNombreTrabajador($datos['idReporte']);
        
        // OBTENER LA FECHA DE ELABORACIÓN DEL REPORTE ORIGINAL
        $fechaElaboracion = $this->obtenerFechaElaboracion($datos['idReporte']);

        $nombreVerificadoLiberado = $this->obtenerNombreUsuarioReporte($datos['idReporte']);
        
        // Consulta INSERT actualizada - ahora guardamos el NOMBRE en lugar del ID
        $sql = "
        INSERT INTO t_reportes_finalizados(
            id_reporte, 
            id_depa,
            id_mantenimiento, 
            tipo_servicio, 
            asignado,  -- ESTE CAMPO AHORA RECIBE EL NOMBRE, NO EL ID
            fecha_realizacion, 
            trabajo_realizado, 
            material, 
            verificado_liberado, 
            fecha_verificado,  -- AQUÍ SE INSERTA LA FECHA DE ELABORACIÓN DEL REPORTE ORIGINAL
            aprobado, 
            fecha_aprobado
        ) VALUES (
            '".mysqli_real_escape_string($conexion, $datos['idReporte'])."', 
            '".mysqli_real_escape_string($conexion, $idDepa)."',
            '".mysqli_real_escape_string($conexion, $datos['mantenimiento'])."', 
            '".mysqli_real_escape_string($conexion, $datos['tipoServicio'])."', 
            '".mysqli_real_escape_string($conexion, $nombreAsignado)."',  -- AQUÍ VA EL NOMBRE
            '".mysqli_real_escape_string($conexion, $datos['fechaRealizacion'])."', 
            '".mysqli_real_escape_string($conexion, $datos['trabajoRealizado'])."', 
            '".mysqli_real_escape_string($conexion, $datos['material'])."', 
            '".mysqli_real_escape_string($conexion, $nombreVerificadoLiberado)."', 
            '".mysqli_real_escape_string($conexion, $fechaElaboracion)."',  -- AQUÍ VA LA FECHA DE ELABORACIÓN
            '".mysqli_real_escape_string($conexion, $aprobado)."',
            '".mysqli_real_escape_string($conexion, $datos['fechaAprobado'])."'
        )";
        
        // Consulta UPDATE para cambiar el estado del reporte
        $sql1 = "UPDATE t_reportes SET estado = 3
                WHERE id_reporte = '".mysqli_real_escape_string($conexion, $datos['idReporte'])."'";

        // Ejecutar ambas consultas
        $resultado = mysqli_query($conexion, $sql);
        $resultado2 = mysqli_query($conexion, $sql1);

        if($resultado && $resultado2){
            return 1;
        }else{
            throw new Exception("Error en la consulta: " . mysqli_error($conexion));
        }
    }


////////////////////////////////

 private function obtenerNombreUsuarioReporte($idReporte)
{
    try {
        $conexion = Conexion::conectar();
        
        if (!$conexion) {
            return 'Usuario Sistema';
        }
        
        // Consulta corregida con las tablas reales
        $sql = "
            SELECT per.nombre, per.paterno, per.materno 
            FROM t_reportes rep
            INNER JOIN t_usuarios usu ON rep.id_usuario = usu.id_usuario
            INNER JOIN t_persona per ON usu.id_persona = per.id_persona
            WHERE rep.id_reporte = '".mysqli_real_escape_string($conexion, $idReporte)."'
        ";
        
        $resultado = mysqli_query($conexion, $sql);
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $fila = mysqli_fetch_assoc($resultado);
            
            // Construir el nombre completo
            $nombreCompleto = $fila['nombre'] . ' ' . $fila['paterno'];
            if (!empty($fila['materno'])) {
                $nombreCompleto .= ' ' . $fila['materno'];
            }
            
            return $nombreCompleto;
        } else {
            return 'Usuario Sistema';
        }
        
    } catch (Exception $e) {
        return 'Usuario Sistema';
    }
}


//////////////////////////////



    // NUEVA FUNCIÓN: Obtener la fecha de elaboración del reporte original
    private function obtenerFechaElaboracion($idReporte)
    {
        $conexion = Conexion::conectar();
        
        if (!$conexion) {
            return date('Y-m-d'); // Retorna fecha actual si hay error
        }
        
        $sql = "SELECT fecha_elaboracion FROM t_reportes 
                WHERE id_reporte = '".mysqli_real_escape_string($conexion, $idReporte)."'";
        
        $resultado = mysqli_query($conexion, $sql);
        
        if($resultado && mysqli_num_rows($resultado) > 0) {
            $fila = mysqli_fetch_assoc($resultado);
            return $fila['fecha_elaboracion'];
        } else {
            return date('Y-m-d'); // Retorna fecha actual si no encuentra el reporte
        }
    }

    // Función MODIFICADA: Obtener el nombre del trabajador desde t_usuarios
    private function obtenerNombreTrabajador($idReporte)
    {
        $conexion = Conexion::conectar();
        
        if (!$conexion) {
            return 'No asignado';
        }
        
        // Primero obtenemos el ID del usuario asignado desde t_reportes
        $sql_asignado = "SELECT Asignado FROM t_reportes WHERE id_reporte = '".mysqli_real_escape_string($conexion, $idReporte)."'";
        $resultado_asignado = mysqli_query($conexion, $sql_asignado);
        
        if($resultado_asignado && mysqli_num_rows($resultado_asignado) > 0) {
            $fila_reporte = mysqli_fetch_assoc($resultado_asignado);
            $idUsuarioAsignado = $fila_reporte['Asignado'];
            
            // Si el asignado es 0 o vacío, retornar 'No asignado'
            if(empty($idUsuarioAsignado) || $idUsuarioAsignado == 0) {
                return 'No asignado';
            }
            
            // Luego obtenemos el nombre completo desde la tabla t_usuarios unida con t_persona
            $sql_nombre = "SELECT 
                                p.nombre, 
                                p.paterno, 
                                p.materno 
                           FROM t_usuarios u 
                           INNER JOIN t_persona p ON u.id_persona = p.id_persona 
                           WHERE u.id_usuario = '".mysqli_real_escape_string($conexion, $idUsuarioAsignado)."'";
            
            $resultado_nombre = mysqli_query($conexion, $sql_nombre);
            
            if($resultado_nombre && mysqli_num_rows($resultado_nombre) > 0) {
                $fila_usuario = mysqli_fetch_assoc($resultado_nombre);
                
                // Construir nombre completo
                $nombreCompleto = $fila_usuario['nombre'] . ' ' . $fila_usuario['paterno'];
                if (!empty($fila_usuario['materno'])) {
                    $nombreCompleto .= ' ' . $fila_usuario['materno'];
                }
                
                return $nombreCompleto;
            } else {
                // Si no encuentra el usuario, retornar el ID con un mensaje
                return 'Usuario ID: ' . $idUsuarioAsignado . ' (no encontrado)';
            }
        }
        
        return 'No asignado';
    }

    // ... resto de tus métodos existentes (sin cambios)
    private function obtenerIdDepartamentoUsuario($idUsuario)
    {
        $conexion = Conexion::conectar();
        
        $sql_ubicacion = "SELECT ubicacion FROM t_usuarios WHERE id_usuario = '$idUsuario'";
        $resultado_ubicacion = mysqli_query($conexion, $sql_ubicacion);
        
        if($resultado_ubicacion && mysqli_num_rows($resultado_ubicacion) > 0) {
            $fila_usuario = mysqli_fetch_assoc($resultado_ubicacion);
            $ubicacion = $fila_usuario['ubicacion'];
            
            $sql_depa = "SELECT id_depa FROM cat_depa WHERE Nombre_depa = '$ubicacion'";
            $resultado_depa = mysqli_query($conexion, $sql_depa);
            
            if($resultado_depa && mysqli_num_rows($resultado_depa) > 0) {
                $fila_depa = mysqli_fetch_assoc($resultado_depa);
                return $fila_depa['id_depa'];
            }
        }
        
        return 1;
    }

    private function obtenerValorAprobado($idDepa)
    {
        switch($idDepa) {
            case 1: // Centro de computo
                return 2;
            case 2: // Mantenimiento de Equipo
                return 1;
            case 3: // Recursos Materiales y Servicio
                return 3;
            default:
                return 1;
        }
    } 

    public function CambiarEstado($datos)
    {
      $conexion = Conexion::conectar();
      $sql = "UPDATE t_reportes SET estado = 2
      WHERE id_reporte = '".$datos['idReporte']."'";
      $resultado = mysqli_query($conexion,$sql);
      if($resultado){
        return 1;
      }else{
        return 0;
      }
    }

    public function CambiarEstadoC($datos)
{
    $conexion = Conexion::conectar();
    
    // Escapar el texto del motivo de cancelación para seguridad
    $motivoCancelacion = mysqli_real_escape_string($conexion, $datos['motivoCancelacion']);
    
    // Actualizar el estado a 4 (cancelado) y agregar el motivo en el campo "extra"
    $sql = "UPDATE t_reportes SET 
            estado = 4,
            extra = 'Cancelado: " . $motivoCancelacion . "'
            WHERE id_reporte = '" . mysqli_real_escape_string($conexion, $datos['idReporte']) . "'";
    
    $resultado = mysqli_query($conexion, $sql);
    
    if($resultado){
        return 1;
    }else{
        return 0;
    }
}

    public function RecogerReporte($datos)
    {
      $conexion = Conexion::conectar();
      $sql = "UPDATE t_reportes_finalizados SET documento_recogido = 2
      WHERE id_reporte = '".$datos['idReporte']."'";
      $resultado = mysqli_query($conexion,$sql);
      if($resultado){
          return 1;
        }else{
          return 0;
        }
    }
}
?>
<?php
include "conexion.php";

class Personal extends conexion
{
    public function agregarNuevoPersonal($datos)
    {
        // Validar que los campos no estén vacíos
        if (empty(trim($datos['nombre'])) || empty(trim($datos['ubicacion']))) {
            return "empty_fields";
        }

        $conexion = Conexion::conectar();

        $sql = 'INSERT INTO t_trabajador(Nombre, ubicacion)
                VALUES ("' . $datos['nombre'] . '", "' . $datos['ubicacion'] . '")';

        $respuesta = mysqli_query($conexion, $sql);

        if ($respuesta) {
            return "1";
        } else {
            return "0";
        }
    }

    public function editarPersonal($datos)
    {
        // Validar campos obligatorios
        if (empty(trim($datos['nombre'])) || empty(trim($datos['ubicacion']))) {
            return "empty_fields";
        }

        $conexion = Conexion::conectar();
        
        // Usar mysqli en lugar de prepared statements para consistencia
        $sql = "UPDATE t_trabajador SET 
                    Nombre = '" . mysqli_real_escape_string($conexion, $datos['nombre']) . "',
                    ubicacion = '" . mysqli_real_escape_string($conexion, $datos['ubicacion']) . "'
                WHERE id_trabajador = " . intval($datos['idTrabajador']);
        
        $respuesta = mysqli_query($conexion, $sql);
        
        if ($respuesta) {
            return "1";
        } else {
            return "0: " . mysqli_error($conexion);
        }
    }
    public function eliminarPersonal($idTrabajador)
{
    $conexion = Conexion::conectar();
    
    // Consulta DELETE
    $sql = "DELETE FROM t_trabajador WHERE id_trabajador = " . intval($idTrabajador);
    
    $respuesta = mysqli_query($conexion, $sql);
    
    if ($respuesta) {
        return "1";
    } else {
        return "0: " . mysqli_error($conexion);
    }
}
}
?>
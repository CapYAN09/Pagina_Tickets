<?php
// Headers primero para JSON
header('Content-Type: application/json');

// Incluir conexión usando ruta relativa simple
include '../../../clases/conexion.php';

// Verificar si se recibieron los datos
if(!isset($_POST['reporte_id']) || !isset($_POST['trabajador_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $con = new conexion();
    $conexion1 = $con->conectar();
    
    if (!$conexion1) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    $reporte_id = intval($_POST['reporte_id']);
    $trabajador_id = $_POST['trabajador_id'];
    
    // Validar que el reporte_id sea mayor a 0
    if ($reporte_id <= 0) {
        throw new Exception('ID de reporte inválido');
    }
    
    // Preparar la consulta
    if(empty($trabajador_id) || $trabajador_id === '') {
        // Asignar NULL
        $sql = "UPDATE t_reportes SET Asignado = NULL WHERE id_reporte = $reporte_id";
    } else {
        // Asignar ID del trabajador
        $trabajador_id = intval($trabajador_id);
        $sql = "UPDATE t_reportes SET Asignado = $trabajador_id WHERE id_reporte = $reporte_id";
    }
    
    // Ejecutar consulta
    $resultado = mysqli_query($conexion1, $sql);
    
    if($resultado) {
        $filas_afectadas = mysqli_affected_rows($conexion1);
        
        if($filas_afectadas > 0) {
            echo json_encode(['success' => true, 'message' => 'Asignación actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el reporte o no hubo cambios']);
        }
    } else {
        throw new Exception(mysqli_error($conexion1));
    }
    
    mysqli_close($conexion1);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
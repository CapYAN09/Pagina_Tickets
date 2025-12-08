<?php
include '../../conexion.php';

header('Content-Type: application/json');

// Agregar logging para depuración
error_log("obtenerFechaElaboracion.php llamado con id_reporte: " . ($_GET['id_reporte'] ?? 'NO PROPORCIONADO'));

if(isset($_GET['id_reporte']) && !empty($_GET['id_reporte'])) {
    $id_reporte = $_GET['id_reporte'];
    
    $query = "SELECT fecha_elaboracion FROM t_reportes WHERE id_reporte = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_reporte);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $fecha_original = $row['fecha_elaboracion'];
            
            error_log("Fecha original de BD: " . $fecha_original);
            
            // Intentar convertir la fecha al formato YYYY-MM-DD
            $timestamp = strtotime($fecha_original);
            
            if ($timestamp !== false) {
                $fecha_elaboracion = date('Y-m-d', $timestamp);
                $fecha_formateada = date('d/m/Y', $timestamp);
            } else {
                // Si no se puede parsear, usar la fecha original
                $fecha_elaboracion = $fecha_original;
                $fecha_formateada = $fecha_original;
            }
            
            echo json_encode([
                'success' => true,
                'fecha_elaboracion' => $fecha_elaboracion,
                'fecha_elaboracion_formateada' => $fecha_formateada
            ]);
        } else {
            error_log("Reporte no encontrado: " . $id_reporte);
            echo json_encode(['success' => false, 'message' => 'Reporte no encontrado']);
        }
        
        $stmt->close();
    } else {
        error_log("Error en prepare: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
    }
} else {
    error_log("ID de reporte no proporcionado o vacío");
    echo json_encode(['success' => false, 'message' => 'ID de reporte no proporcionado']);
}

$conn->close();
?>
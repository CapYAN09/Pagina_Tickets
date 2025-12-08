<?php
// procesos/Usuarios/crud/procesar_personal.php

// Incluir las clases necesarias
include "../../../clases/Personal.php";

if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    // Crear instancia de la clase Personal
    $personalObj = new Personal();
    
    // Preparar datos para edición
    $datos = array(
        'idTrabajador' => $_POST['idTrabajador'],
        'nombre' => $_POST['nombre'],
        'ubicacion' => $_POST['ubicacion']
    );
    
    // Llamar al método editarPersonal
    $resultado = $personalObj->editarPersonal($datos);
    
    // Devolver respuesta
    echo $resultado;
    
} else {
    echo "0: Acción no válida";
}
?>
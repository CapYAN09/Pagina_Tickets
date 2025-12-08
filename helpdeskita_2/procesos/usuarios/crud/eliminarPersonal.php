<?php
// procesos/usuarios/crud/eliminarPersonal.php

include "../../../clases/Personal.php";

if (isset($_POST['idTrabajador'])) {
    
    // Crear instancia de la clase Personal
    $personalObj = new Personal();
    
    // Llamar al método eliminarPersonal
    $resultado = $personalObj->eliminarPersonal($_POST['idTrabajador']);
    
    // Devolver respuesta
    echo $resultado;
    
} else {
    echo "0: ID no proporcionado";
}
?>
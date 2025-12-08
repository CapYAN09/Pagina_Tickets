<?php
    $datos = array(
        'idTrabajador' => $_POST['idTrabajador'],
        'nombre' => $_POST['nombre'],
        'ubicacion' => $_POST['ubicacion']
    );

    include "../../../clases/Personal.php";
    $Personal = new Personal();
    echo $Personal->editarPersonal($datos);
?>
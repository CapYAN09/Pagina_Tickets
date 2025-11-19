<?php

$datos = array(
   "nombre" => $_POST['Pnombre'],
   "ubicacion" => $_POST['Pubicacion']
);

include "../../../clases/Personal.php";
$Personal = new Personal();
echo $Personal->agregarNuevoPersonal($datos);

?>

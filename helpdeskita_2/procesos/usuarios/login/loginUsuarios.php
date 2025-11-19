<?php
    include "../../../clases/Usuarios.php";
    session_start();
    
    $usuario = $_POST['login'];
    $password = $_POST['password']; // Mantener en texto plano

    // DEBUG: Verificar qué se está enviando
    error_log("Login attempt - Usuario: " . $usuario . ", Password: " . $password);
     
    $Usuarios = new Usuarios();
    
    // Enviar la contraseña en texto plano, NO encriptada
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("Resultado del login: " . $resultado);
    echo $resultado;
?>
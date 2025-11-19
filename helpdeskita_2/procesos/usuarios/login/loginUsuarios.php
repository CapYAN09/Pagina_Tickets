<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log detallado
error_log("=== LOGINUSUARIOS.PHP INICIADO ===");

// Incluir usando rutas absolutas
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';
include_once $ruta_base . '/clases/funciones_encriptacion.php';
include_once $ruta_base . '/clases/Usuarios.php';

session_start();

// Verificar datos POST
if(empty($_POST['login']) || empty($_POST['password'])) {
    error_log("ERROR: Campos POST vacíos");
    echo "0";
    exit;
}

$usuario = $_POST['login'];
$password = $_POST['password'];

error_log("Procesando login para: $usuario");

try {
    $Usuarios = new Usuarios();
    
    // DEBUG: Verificar contraseñas
    error_log("Password recibido: $password");
    
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("Resultado final: $resultado");
    echo $resultado;
    
} catch(Exception $e) {
    error_log("EXCEPCIÓN: " . $e->getMessage());
    echo "0";
}
?>
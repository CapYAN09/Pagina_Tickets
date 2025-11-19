<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log del dominio actual
error_log("=== LOGIN DESDE: " . ($_SERVER['HTTP_HOST'] ?? 'Desconocido') . " ===");
error_log("REQUEST URI: " . ($_SERVER['REQUEST_URI'] ?? 'No definido'));

// Configurar sesión para el dominio
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.aguascalientes.tecnm.mx',
    'secure' => true, // HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Incluir archivos con rutas absolutas
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';
error_log("📁 Ruta base: " . $ruta_base);

// Verificar si la ruta existe
if (!file_exists($ruta_base)) {
    // Intentar rutas alternativas comunes en servidores Linux/Nginx
    $rutas_alternativas = [
        '/var/www/html/helpdeskita_2',
        '/usr/share/nginx/html/helpdeskita_2',
        '/home/usuario/www/helpdeskita_2'
    ];
    
    foreach ($rutas_alternativas as $ruta_alt) {
        if (file_exists($ruta_alt)) {
            $ruta_base = $ruta_alt;
            error_log("✅ Usando ruta alternativa: " . $ruta_base);
            break;
        }
    }
}

if (!file_exists($ruta_base . '/clases/Usuarios.php')) {
    error_log("❌ ERROR: No se puede encontrar Usuarios.php");
    echo "0";
    exit;
}

include_once $ruta_base . '/clases/funciones_encriptacion.php';
include_once $ruta_base . '/clases/Usuarios.php';

$usuario = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

error_log("🔐 Login intentado - Usuario: " . $usuario);

if(empty($usuario) || empty($password)) {
    error_log("❌ Campos vacíos");
    echo "0";
    exit;
}

try {
    $Usuarios = new Usuarios();
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("🎯 Resultado login: " . $resultado);
    
    if($resultado == 1) {
        error_log("✅ Sesión iniciada para: " . $usuario);
        error_log("📋 Datos sesión: " . print_r($_SESSION['usuario'] ?? 'No sesión', true));
    }
    
    echo $resultado;
    
} catch(Exception $e) {
    error_log("💥 EXCEPCIÓN: " . $e->getMessage());
    echo "0";
}
?>
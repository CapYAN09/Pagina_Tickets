<?php
// login_prueba.php
session_start();

// Configuración de la base de datos
$servidor = "172.30.247.185";
$usuario_db = "ccomputo";
$password_db = "Jarjar0904$";
$puerto = 3306;
$basedatos = "b1o04dzhm1guhvmjcrwb";

// Tus funciones de encriptación
function getEncryptedPassword($password){
    define('METHOD','AES-256-CBC');
    define('SECRET_KEY','Tecnologico');
    define('SECRET_IV','990520');

    $output=FALSE;
    $key=hash('sha256', SECRET_KEY);
    $iv=substr(hash('sha256', SECRET_IV), 0, 16);
    $output=openssl_encrypt($password, METHOD, $key, 0, $iv);
    
    return base64_encode($output);
}

function getUnencryptedPassword($encrypted){
    // Desencriptar la contraseña
    define('METHOD','AES-256-CBC');
    define('SECRET_KEY','Tecnologico');
    define('SECRET_IV','990520');
    
    $key=hash('sha256', SECRET_KEY);
    $iv=substr(hash('sha256', SECRET_IV), 0, 16);
    
    return openssl_decrypt(base64_decode($encrypted), METHOD, $key, 0, $iv);
}

// Cerrar sesión si se solicita
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . str_replace('?logout=1', '', $_SERVER['PHP_SELF']));
    exit;
}

$error = "";
$conexion = null;

// Procesar login cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Conectar a la base de datos
        $conexion = new mysqli($servidor, $usuario_db, $password_db, $basedatos, $puerto);
        
        if ($conexion->connect_error) {
            throw new Exception("Error de conexión: " . $conexion->connect_error);
        }
        
        // Buscar usuario por la columna 'usuario' en la tabla t_usuarios
        $username_clean = $conexion->real_escape_string($username);
        $sql = "SELECT id_usuario, usuario, password, id_rol, Estado FROM t_usuarios WHERE usuario = '$username_clean' AND Estado = 1 LIMIT 1";
        $resultado = $conexion->query($sql);
        
        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $password_bd = $usuario['password'];
            
            // Desencriptar la contraseña de la base de datos
            $password_desencriptada = getUnencryptedPassword($password_bd);
            
            // Verificar si la contraseña ingresada coincide con la desencriptada
            if ($password === $password_desencriptada) {
                // Login exitoso
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $usuario['id_usuario'];
                $_SESSION['username'] = $usuario['usuario'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = "❌ Contraseña incorrecta";
            }
        } else {
            $error = "❌ Usuario no encontrado o usuario inactivo";
        }
        
        $conexion->close();
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Login</title>
    
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Panel cuando el usuario está logueado -->
            <div class="user-panel">
                <h2>✅ ¡Bienvenido!</h2>
                
                <div class="user-info">
                    <p><strong>ID Usuario:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['id_rol']); ?></p>
                </div>
                
                <a href="?logout=1" class="logout-btn">
                    🔓 Cerrar Sesión
                </a>
            </div>
            
        <?php else: ?>
            <!-- Formulario de Login cuando NO está logueado -->
            <h2>🔐 Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required placeholder="Ingresa tu nombre de usuario">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                </div>
                
                <button type="submit" name="login">Ingresar al Sistema</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
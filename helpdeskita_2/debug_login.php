<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>DEBUG CON USUARIO REAL: Hector</h2>";

// Incluir las clases necesarias
$ruta_usuarios = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2/clases/Usuarios.php';
$ruta_encriptacion = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2/clases/funciones_encriptacion.php';

if(file_exists($ruta_usuarios) && file_exists($ruta_encriptacion)) {
    include_once $ruta_usuarios;
    include_once $ruta_encriptacion;
    echo "✅ Clases incluidas correctamente<br>";
} else {
    echo "❌ Error: No se encontraron las clases<br>";
    exit;
}

// Probar con el usuario real
$test_user = "Hector";
$test_pass = "123456789";

echo "Usuario de prueba: $test_user<br>";
echo "Contraseña de prueba: $test_pass<br>";

try {
    $Usuarios = new Usuarios();
    
    // Probar la encriptación/desencriptación primero
    $encrypted = getEncryptedPassword($test_pass);
    $decrypted = getUnencryptedPassword($encrypted);
    
    echo "Contraseña original: $test_pass<br>";
    echo "Contraseña encriptada: $encrypted<br>";
    echo "Contraseña desencriptada: $decrypted<br>";
    echo "¿Coinciden?: " . ($test_pass === $decrypted ? "✅ SÍ" : "❌ NO") . "<br><br>";
    
    // Ahora probar el login
    echo "<h3>Probando login...</h3>";
    $resultado = $Usuarios->loginUsuario($test_user, $test_pass);
    echo "Resultado del login: $resultado<br>";
    
    if($resultado == 1) {
        echo "✅ LOGIN EXITOSO<br>";
    } else {
        echo "❌ LOGIN FALLIDO<br>";
        
        // Debug adicional - verificar si el usuario existe
        echo "<h3>Debug adicional:</h3>";
        
        $conexion = new conexion();
        $conn = $conexion->conectar();
        
        if($conn) {
            $sql = "SELECT usuario, password FROM t_usuarios WHERE usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $test_user);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                echo "Usuario encontrado en BD: " . $user_data['usuario'] . "<br>";
                echo "Password en BD (encriptado): " . $user_data['password'] . "<br>";
                
                $password_bd_desencriptado = getUnencryptedPassword($user_data['password']);
                echo "Password BD (desencriptado): $password_bd_desencriptado<br>";
                echo "¿Coincide con '$test_pass'?: " . ($test_pass === $password_bd_desencriptado ? "✅ SÍ" : "❌ NO") . "<br>";
            } else {
                echo "❌ Usuario no encontrado en la BD<br>";
            }
            
            $stmt->close();
        } else {
            echo "❌ No se pudo conectar a la BD<br>";
        }
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
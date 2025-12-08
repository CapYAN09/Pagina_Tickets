<?php
include_once 'config/session_config.php';
iniciarSesionSegura();

echo "<h1>Test Completo de Sesión</h1>";

echo "<h3>Estado Actual:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";

if(isset($_SESSION['usuario'])) {
    echo "<div style='color: green; font-weight: bold;'>✅ USUARIO EN SESIÓN</div>";
    echo "<pre>" . print_r($_SESSION['usuario'], true) . "</pre>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ NO HAY USUARIO</div>";
}

echo "<h3>Probar Login:</h3>";
echo '<form action="procesos/usuarios/login/loginUsuarios.php" method="POST">';
echo 'Usuario: <input type="text" name="login" value="Hector"><br>';
echo 'Password: <input type="password" name="password" value="123456789"><br>';
echo '<input type="submit" value="Probar Login Directo">';
echo '</form>';

echo "<h3>Cookies:</h3>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
?>
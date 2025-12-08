<?php
// test_proyecto.php - Verificar rutas DENTRO de helpdeskita_2
echo "<h2>Verificación de Rutas - Proyecto helpdeskita_2</h2>";

echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script actual: " . __FILE__ . "<br>";
echo "URL actual: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br><br>";

// Rutas desde la perspectiva del proyecto
$archivosProyecto = [
    'clases/Usuarios.php' => __DIR__ . '/clases/Usuarios.php',
    'procesos/usuarios/login/loginUsuarios.php' => __DIR__ . '/procesos/usuarios/login/loginUsuarios.php',
    'vistas/inicio.php' => __DIR__ . '/vistas/inicio.php',
    'index.html' => __DIR__ . '/index.html',
    'public/js-usuarios/login.js' => __DIR__ . '/public/js-usuarios/login.js'
];

foreach($archivosProyecto as $nombre => $ruta) {
    $existe = file_exists($ruta);
    echo "{$nombre}: " . ($existe ? "✅ EXISTE" : "❌ NO EXISTE") . "<br>";
}

echo "<br><h3>Prueba de inclusión de Usuarios.php:</h3>";
$rutaUsuarios = __DIR__ . '/clases/Usuarios.php';
if(file_exists($rutaUsuarios)) {
    include $rutaUsuarios;
    echo "✅ Usuarios.php incluido correctamente<br>";
    
    // Probar instancia
    try {
        $Usuarios = new Usuarios();
        echo "✅ Clase Usuarios instanciada correctamente<br>";
    } catch(Exception $e) {
        echo "❌ Error al instanciar Usuarios: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ No se pudo encontrar Usuarios.php<br>";
}
?>
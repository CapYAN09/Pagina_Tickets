<?php
// info.php - Verificar configuración del servidor Nginx
echo "<h1>Información del Servidor - sistematickets</h1>";

$info = [
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'No definido',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'No definido',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'No definido',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'No definido',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'No definido',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'No definido'
];

foreach($info as $key => $value) {
    echo "<strong>$key:</strong> $value<br>";
}

echo "<h2>Rutas probadas:</h2>";
$rutas = [
    $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2',
    $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2/index.html',
    '/var/www/html/helpdeskita_2',
    '/var/www/sistematickets/helpdeskita_2',
    '/usr/share/nginx/html/helpdeskita_2'
];

foreach($rutas as $ruta) {
    $existe = file_exists($ruta);
    $es_dir = is_dir($ruta);
    echo $ruta . " - " . 
         ($existe ? "✅ EXISTE" : "❌ NO EXISTE") . 
         ($es_dir ? " (DIR)" : " (FILE)") . "<br>";
}

echo "<h2>PHP Info:</h2>";
echo "Session ID: " . (session_id() ?: 'No iniciada') . "<br>";
echo "Session Status: " . session_status() . "<br>";
?>
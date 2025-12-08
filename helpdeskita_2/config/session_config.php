<?php
// config/session_config.php
session_start();
function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración consistente para sesiones
        session_name('SISTEMATICKETS_SESS');
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', 1);
        
        // Configuración para funcionar con proxy reverso
        session_set_cookie_params([
            'lifetime' => 86400, // 24 horas
            'path' => '/',
            'domain' => '', // Vacío para proxy
            'secure' => false, // Permitir HTTP en proxy
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_start();
        
        // Log para debugging (opcional)
        error_log("Sesión iniciada - ID: " . session_id() . " - Name: " . session_name());
    }
}
?>
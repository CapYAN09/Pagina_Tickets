<?php 
// funciones_encriptacion.php - VERSIÓN CORREGIDA

// Prevenir redeclaración
if (!function_exists('getEncryptedPassword')) {

    // Definir constantes UNA sola vez
    define('ENCRYPT_METHOD','AES-256-CBC');
    define('ENCRYPT_SECRET_KEY','Tecnologico');
    define('ENCRYPT_SECRET_IV','990520');

    function getEncryptedPassword($password){
        $output = FALSE;
        $key = hash('sha256', ENCRYPT_SECRET_KEY);
        $iv = substr(hash('sha256', ENCRYPT_SECRET_IV), 0, 16);
        $output = openssl_encrypt($password, ENCRYPT_METHOD, $key, 0, $iv);
        
        return base64_encode($output);
    }

    function getUnencryptedPassword($encrypted){
        $key = hash('sha256', ENCRYPT_SECRET_KEY);
        $iv = substr(hash('sha256', ENCRYPT_SECRET_IV), 0, 16);
        
        return openssl_decrypt(base64_decode($encrypted), ENCRYPT_METHOD, $key, 0, $iv);
    }

}
?>
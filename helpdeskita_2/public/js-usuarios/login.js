function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === DEPURACIÓN EN TIEMPO REAL ===");
    
    var usuario = $('#login').val();
    var password = $('#password').val();
    
    console.log("📝 Datos capturados del formulario:");
    console.log("Usuario:", usuario);
    console.log("Password:", password);
    console.log("Longitud usuario:", usuario.length);
    console.log("Longitud password:", password.length);
    
    // Verificar exactamente qué caracteres se están enviando
    console.log("Usuario (char codes):", Array.from(usuario).map(c => c.charCodeAt(0)));
    console.log("Password (char codes):", Array.from(password).map(c => c.charCodeAt(0)));
    
    var url = "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php";
    console.log("🎯 URL destino:", url);
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            login: usuario,
            password: password
        },
        success: function(respuesta) {
            console.log("✅ RESPUESTA DEL SERVIDOR:");
            console.log("Respuesta RAW:", respuesta);
            console.log("Tipo:", typeof respuesta);
            console.log("Longitud:", respuesta.length);
            
            var respuestaTrim = respuesta.trim();
            console.log("Respuesta TRIM:", respuestaTrim);
            console.log("¿Es '1'?:", respuestaTrim === "1");
            console.log("¿Es '0'?:", respuestaTrim === "0");
            
            if(respuestaTrim === "1") {
                console.log("🎉 LOGIN EXITOSO - Redirigiendo...");
                window.location.href = "/helpdeskita_2/vistas/inicio.php";
            } else {
                console.log("❌ LOGIN FALLIDO - Código:", respuestaTrim);
                Swal.fire("Error", "Usuario o contraseña incorrectos. Código: " + respuestaTrim, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 ERROR AJAX:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("ReadyState:", xhr.readyState);
            console.error("Status HTTP:", xhr.status);
            console.error("Response Text:", xhr.responseText);
            
            Swal.fire("Error de conexión", "Status: " + status + " - Error: " + error, "error");
        }
    });
    
    return false;
}
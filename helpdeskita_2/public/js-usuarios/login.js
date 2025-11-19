function loginusuario() {
    event.preventDefault();
    
    console.log("🔍 === INICIANDO LOGIN ===");
    
    var formData = {
        login: $('#login').val(),
        password: $('#password').val()
    };
    
    console.log("📤 Datos a enviar:", formData);
    
    // Hacer la petición AJAX manualmente para mejor control
    $.ajax({
        type: "POST",
        url: "/helpdeskita_2/procesos/usuarios/login/loginUsuarios.php",
        data: formData,
        dataType: "text",
        success: function(respuesta, status, xhr) {
            console.log("✅ Success callback ejecutado");
            console.log("Status:", status);
            console.log("Respuesta RAW:", respuesta);
            console.log("Respuesta length:", respuesta.length);
            
            var respuestaTrim = respuesta.trim();
            console.log("Respuesta trimmeada:", respuestaTrim);
            console.log("¿Es igual a '1'?", respuestaTrim === "1");
            
            if(respuestaTrim === "1") {
                console.log("🎉 Login exitoso - Redirigiendo...");
                window.location.href = "/helpdeskita_2/vistas/inicio.php";
            } else {
                console.log("❌ Login fallido - Respuesta:", respuestaTrim);
                Swal.fire("Error", "Login fallido. Respuesta: " + respuestaTrim, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("💥 Error callback ejecutado");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("ReadyState:", xhr.readyState);
            console.error("Status HTTP:", xhr.status);
            console.error("Status Text:", xhr.statusText);
            console.error("Response Text:", xhr.responseText);
            
            Swal.fire("Error de conexión", 
                "Status: " + status + "\nError: " + error + "\nHTTP: " + xhr.status, 
                "error");
        },
        complete: function(xhr, status) {
            console.log("📋 Complete callback - Status:", status);
        }
    });
    
    return false;
}
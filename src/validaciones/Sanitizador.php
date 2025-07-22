<?php 
class Sanitizador {
    // Sanitiza un texto eliminando etiquetas HTML y espacios en blanco
    public static function limpiarTexto(string $texto): string {
        return htmlspecialchars(trim($texto));
    }
  
    public static function limpiarCorreo(string $correo): string {
        return filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
    }
}


?>
<?php
class Validador {
    // Valida que el correo electrónico tenga un formato correcto
    public static function validarCorreo($correo) {
        return filter_var($correo, FILTER_VALIDATE_EMAIL);
    }

    // Valida que el número sea un entero positivo
    public static function validarPassword($password) {
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password);
    }

    // Valida que el número sea un entero positivo
    public static function validarTexto(string $texto): bool {
        return (bool)preg_match('/^[\p{L}\s]+$/u', $texto);
    }

    // Valida que el número sea un entero positivo
    public static function validarLongitud(string $texto, int $min): bool {
        return mb_strlen(trim($texto)) >= $min;
    }

}

?>
<?php
class Validador {
    public static function validarCorreo($correo) {
        return filter_var($correo, FILTER_VALIDATE_EMAIL);
    }

    public static function validarNumero($numero) {
        return is_numeric($numero);
    }

    public static function validarPassword($password) {
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password);
    }

}

?>
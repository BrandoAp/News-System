<?php
class ControlErrores implements InterfazControlErrores {
    private $errores = [];

    public function registrarError(string $mensaje): void {
        $this->errores[] = $mensaje;
    }

    public function obtenerErrores(): array {
        return $this->errores;
    }

    public function hayErrores(): bool {
        return !empty($this->errores);
    }

    public function limpiarErrores(): void {
    $this->errores = [];
}

}






?>
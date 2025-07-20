<?php
interface InterfazControlErrores {
    public function registrarError(string $mensaje): void;
    public function obtenerErrores(): array;
    public function hayErrores(): bool;
}


?>
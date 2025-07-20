<?php
interface ILogin {
    public function validarLogin(array $data): array;
    public function autenticar(string $nombre, string $contrasena): array;
}


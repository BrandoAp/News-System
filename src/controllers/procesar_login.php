<?php
require_once __DIR__ . '/./loginController.php';

// Verifica si la solicitud es POST para procesar el inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController();
    $controller->procesar($_POST);
}

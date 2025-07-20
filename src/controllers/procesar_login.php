<?php
require_once __DIR__ . '/./loginController.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController();
    $controller->procesar($_POST);
}

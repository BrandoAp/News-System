<?php
// public/dashboard.php
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__   . '/../db/conexionDB.php';
require_once __DIR__   . '/../src/modules/Dashboard.php';

$pdo       = ConexionDB::obtenerInstancia()->obtenerConexion();
$dashboard = new Dashboard($pdo);

// Métricas generales
$totalUsuarios     = $dashboard->usuariosTotales();
$usuariosActivos   = $dashboard->usuariosActivos();
$usuariosInactivos = $dashboard->usuariosInactivos();

$totalCategorias   = $dashboard->categoriasTotales();
$catsActivas       = $dashboard->categoriasActivas();
$catsInactivas     = $dashboard->categoriasInactivas();

$totalNoticias     = $dashboard->noticiasTotales();
$notPub            = $dashboard->noticiasPublicadas();
$notArchivadas     = $dashboard->noticiasArchivadas();

$visitasHoy        = $dashboard->visitasHoy();

// Top 5 categorías por número de noticias
$topCats  = $dashboard->topCategorias(5);

// Últimas 5 noticias
$ultimas5 = $dashboard->ultimasNoticias(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include __DIR__ . '/header.php'; ?>

  <main class="container mx-auto p-6 space-y-8">
    <h1 class="text-4xl font-bold text-blue-900">📊 Dashboard del Sistema de Noticias</h1>

    <!-- Cards métricas generales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php 
      $cards = [
        ['Usuarios Totales',      $totalUsuarios,     '#00aaff'],
        ['Usuarios Activos',      $usuariosActivos,   '#60cb26'],
        ['Usuarios Inactivos',    $usuariosInactivos, '#f80505'],

        ['Categorías Totales',    $totalCategorias,   '#3b3bf3'],
        ['Categorías Activas',    $catsActivas,       '#007e82'],
        ['Categorías Inactivas',  $catsInactivas,     '#f9820b'],

        ['Noticias Totales',      $totalNoticias,     '#500bf9'],
        ['Noticias Publicadas',   $notPub,            '#d50bf9'],
        ['Noticias Archivadas',   $notArchivadas,     '#bab2dc'],

        ['Visitas Hoy',           $visitasHoy,        '#00bcd4'],
      ];

      foreach ($cards as [$label, $value, $color]): ?>
        <div
          class="p-6 rounded-lg shadow-lg text-white"
          style="background-color: <?= htmlspecialchars($color) ?>;"
        >
          <h2 class="text-lg font-semibold"><?= htmlspecialchars($label) ?></h2>
          <p class="text-3xl font-bold mt-2"><?= htmlspecialchars($value) ?></p>
        </div>
      <?php endforeach; ?>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

      <!-- Top 5 categorías -->
      <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">🏆 Top 5 Categorías</h2>
        <table class="w-full text-left">
          <thead>
            <tr class="border-b">
              <th class="py-2">Categoría</th>
              <th class="py-2"># Noticias</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topCats as $row): ?>
            <tr class="hover:bg-gray-50">
              <td class="py-2"><?= htmlspecialchars($row['nombre']) ?></td>
              <td class="py-2 font-bold"><?= $row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- Últimas 5 noticias -->
      <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">🕒 Últimas 5 Noticias</h2>
        <ul class="space-y-2">
          <?php foreach ($ultimas5 as $n): ?>
            <li class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
              <span><?= htmlspecialchars($n['titulo']) ?></span>
              <small class="text-gray-500">
                <?php if ((int)$n['id_estado'] === 4): ?>
                  <em>Archivada</em>
                <?php elseif (!empty($n['publicado_en'])): ?>
                  <?= date('d/m/Y H:i', strtotime($n['publicado_en'])) ?>
                <?php else: ?>
                  <em>Sin publicar</em>
                <?php endif; ?>
              </small>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>

    </div>
  </main>
</body>
</html>

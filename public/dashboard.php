<?php
// public/dashboard.php
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
//Gerald Gonzalez
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';
require_once __DIR__ . '/../src/controllers/NoticiasController.php';
require_once __DIR__ . '/../src/controllers/pagina_publica_controller.php';
require_once __DIR__ . '/../src/modules/Categoria.php';

$pdo     = ConexionDB::obtenerInstancia()->obtenerConexion();
$db      = new DatabaseManager($pdo);
$notCtrl = new NoticiasController($pdo);
$pubCtrl = new PaginaPublicaController($pdo);
$catMod  = new Categoria();

// Métricas principales
$totalUsuarios       = $db->count('usuarios');
$usuariosActivos     = $db->count('usuarios', ['id_estado' => 1]);
$usuariosInactivos   = $db->count('usuarios', ['id_estado' => 2]);

$totalCategorias     = $db->count('categorias');
$catsActivas         = $db->count('categorias', ['id_estado' => 1]);
$catsInactivas       = $db->count('categorias', ['id_estado' => 2]);

$totalNoticias       = $db->count('noticias');
$notPub              = $db->scalar("SELECT COUNT(*) FROM noticias WHERE id_estado = ?", [3]) ?: 0;
$notArchivadas       = $db->scalar("SELECT COUNT(*) FROM noticias WHERE id_estado = ?", [4]) ?: 0;

$visitasHoy          = $pubCtrl->obtenerVisitasHoy();

// Top 5 categorías por número de noticias
$topCats = $db->query("
    SELECT c.nombre, COUNT(n.id) AS total
    FROM categorias c
    LEFT JOIN noticias n ON n.id_categoria = c.id
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 5
");

// Últimas 5 noticias
$ultimas5 = $db->query("
    SELECT id, titulo, publicado_en, id_estado
    FROM noticias
    ORDER BY creado_en DESC
    LIMIT 5
");
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
        ['Usuarios Totales', $totalUsuarios, 'bg-blue-500'],
        ['Usuarios Activos',  $usuariosActivos, 'bg-green-500'],
        ['Usuarios Inactivos',$usuariosInactivos, 'bg-red-500'],

        ['Categorías Totales', $totalCategorias, 'bg-indigo-500'],
        ['Categorías Activas', $catsActivas, 'bg-green-600'],
        ['Categorías Inactivas',$catsInactivas, 'bg-red-600'],

        ['Noticias Totales',   $totalNoticias, 'bg-purple-500'],
        ['Publicadas',         $notPub, 'bg-green-700'],
        ['Archivadas',         $notArchivadas, 'bg-gray-500'],

        ['Visitas Hoy',        $visitasHoy, 'bg-teal-500'],
      ];
      foreach ($cards as [$label, $value, $color]): ?>
      <div class="p-6 rounded-lg shadow-lg text-white <?= $color ?>">
        <h2 class="text-lg font-semibold"><?= $label ?></h2>
        <p class="text-3xl font-bold mt-2"><?= $value ?></p>
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

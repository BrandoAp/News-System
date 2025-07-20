<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Dependencias ---
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';
require_once __DIR__ . '/../src/controllers/NoticiasController.php';

$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$dbManager = new DatabaseManager($pdo);
$noticiasController = new NoticiasController($pdo);

// --- Inicialización ---
$modo_edicion = false;
$noticia = [
    'id' => null, 'titulo' => '', 'autor' => '', 'contenido' => '', 'id_categoria' => 1
];
$errores = [];

// Obtener categorías para el selector
$categorias = $noticiasController->obtenerCategorias();

// --- Lógica de Modo Edición (GET) ---
if (isset($_GET['id'])) {
    $id_noticia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $datos_noticia = $noticiasController->obtenerNoticiaPorId($id_noticia);
    if ($datos_noticia) {
        $modo_edicion = true;
        $noticia = $datos_noticia;
    } else {
        $_SESSION['mensaje_error'] = 'La noticia que intentas editar no existe.';
        header('Location: indexnoticia.php');
        exit();
    }
}

// --- Lógica de Procesamiento de Formulario (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $id_noticia = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_SPECIAL_CHARS);
    $contenido = filter_input(INPUT_POST, 'contenido', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
    $publicar_ahora = isset($_POST['publicar_ahora']);

    // Validación
    if (empty($titulo)) $errores['titulo'] = 'El título es obligatorio.';
    if (empty($autor)) $errores['autor'] = 'El autor es obligatorio.';
    if (empty($contenido)) $errores['contenido'] = 'El contenido es obligatorio.';
    if (!$id_categoria || $id_categoria <= 0) $errores['categoria'] = 'Debes seleccionar una categoría válida.';
    
    // Verificar que la categoría existe y está activa
    if ($id_categoria) {
        $categoria_valida = false;
        foreach ($categorias as $cat) {
            if ($cat['id'] == $id_categoria) {
                $categoria_valida = true;
                break;
            }
        }
        if (!$categoria_valida) {
            $errores['categoria'] = 'La categoría seleccionada no es válida.';
        }
    }
    
    if (empty($errores)) {
        try {
            $id_estado_publicado = $noticiasController->obtenerIdEstadoPorNombre('publicado');
            $id_estado_inactivo = $noticiasController->obtenerIdEstadoPorNombre('inactivo');

            $datos_noticia = [
                'titulo' => $titulo,
                'autor' => $autor,
                'contenido' => $contenido,
                'id_usuario_creador' => 1, // Cambiar por el ID del usuario logueado
                'id_categoria' => $id_categoria,
                'id_estado' => $publicar_ahora ? $id_estado_publicado : $id_estado_inactivo,
            ];

            if ($id_noticia) { // Actualizar
                $resultado = $dbManager->updateSeguro('noticias', $datos_noticia, ['id' => $id_noticia]);
                if ($resultado) {
                    $_SESSION['mensaje_exito'] = 'Noticia actualizada con éxito.';
                } else {
                    throw new Exception('No se pudo actualizar la noticia.');
                }
            } else { // Crear
                $datos_noticia['publicado_en'] = $publicar_ahora ? date('Y-m-d H:i:s') : null;
                $resultado = $dbManager->insertSeguro('noticias', $datos_noticia);
                if ($resultado) {
                    $_SESSION['mensaje_exito'] = 'Noticia creada con éxito.';
                } else {
                    throw new Exception('No se pudo crear la noticia.');
                }
            }

            // Aquí iría la lógica para subir imágenes...
            
            header('Location: indexnoticia.php');
            exit();

        } catch (Exception $e) {
            error_log("Error en gestionar_noticia.php: " . $e->getMessage());
            $errores['general'] = 'Error al guardar en la base de datos: ' . $e->getMessage();
        }
    }
    // Si hay errores, repoblar los datos del formulario
    $noticia['titulo'] = $titulo;
    $noticia['autor'] = $autor;
    $noticia['contenido'] = $contenido;
    $noticia['id_categoria'] = $id_categoria;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar' : 'Crear' ?> Noticia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto mt-10 p-8 bg-white max-w-4xl rounded-lg shadow-xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-4">
        <?= $modo_edicion ? 'Editar Noticia' : 'Crear Nueva Noticia' ?>
    </h1>

    <?php if (!empty($errores['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($errores['general']) ?></div>
    <?php endif; ?>

    <form action="gestionar_noticia.php<?= $modo_edicion ? '?id=' . $noticia['id'] : '' ?>" method="POST" enctype="multipart/form-data">
        <?php if ($modo_edicion): ?>
            <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">Título:</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($errores['titulo']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errores['titulo'])): ?><p class="text-red-500 text-xs italic"><?= $errores['titulo'] ?></p><?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="autor" class="block text-gray-700 text-sm font-bold mb-2">Autor:</label>
            <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($noticia['autor']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($errores['autor']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errores['autor'])): ?><p class="text-red-500 text-xs italic"><?= $errores['autor'] ?></p><?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="id_categoria" class="block text-gray-700 text-sm font-bold mb-2">Categoría:</label>
            <select id="id_categoria" name="id_categoria" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($errores['categoria']) ? 'border-red-500' : '' ?>">
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= ($noticia['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errores['categoria'])): ?><p class="text-red-500 text-xs italic"><?= $errores['categoria'] ?></p><?php endif; ?>
        </div>

        <div class="mb-6">
            <label for="contenido" class="block text-gray-700 text-sm font-bold mb-2">Contenido:</label>
            <textarea id="contenido" name="contenido" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($errores['contenido']) ? 'border-red-500' : '' ?>"><?= htmlspecialchars($noticia['contenido']) ?></textarea>
            <?php if (isset($errores['contenido'])): ?><p class="text-red-500 text-xs italic"><?= $errores['contenido'] ?></p><?php endif; ?>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Imágenes (hasta 3):</label>
            <input type="file" name="imagenes[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, GIF. Máximo 3 imágenes.</p>
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="publicar_ahora" class="form-checkbox h-5 w-5 text-blue-600" <?= ($modo_edicion && $noticia['id_estado'] == $noticiasController->obtenerIdEstadoPorNombre('publicado')) ? 'checked' : 'checked' ?>>
                <span class="ml-2 text-sm text-gray-700">Publicar inmediatamente</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Si no marcas esta opción, la noticia se guardará como borrador.</p>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-200">
                <?= $modo_edicion ? 'Actualizar' : 'Crear' ?> Noticia
            </button>
            <a href="./indexnoticia.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800 transition duration-200">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php if (!empty($categorias)): ?>
<script>
// Opcional: Agregar validación del lado del cliente
document.getElementById('id_categoria').addEventListener('change', function() {
    if (this.value === '') {
        this.classList.add('border-red-500');
    } else {
        this.classList.remove('border-red-500');
    }
});

// Validación antes de enviar el formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const categoria = document.getElementById('id_categoria').value;
    if (!categoria || categoria === '') {
        e.preventDefault();
        alert('Por favor, selecciona una categoría para la noticia.');
        document.getElementById('id_categoria').focus();
        return false;
    }
});
</script>
<?php endif; ?>

</body>
</html>